<?php

namespace App\Http\Controllers;

use App\Models\BiocharBatch;
use App\Models\Division;
use App\Models\Inspection;
use App\Models\PlantingLocation;
use App\Models\TreePlanting;
use App\Models\TreePlantingMeasurement;
use App\Models\TreeType;
use App\Models\User;
use App\Services\PolygonAreaCalculator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    /**
     * Sources with a genuine geographic (division) relationship.
     * tree_types and users are deliberately absent — neither has any
     * relation, direct or indirect, to a PlantingLocation/Division (a
     * TreeType is a species reference row, a User isn't "in" a
     * division), so the division filter is inert for both, server-side
     * and not just hidden client-side.
     */
    private const GEO_FILTERABLE_SOURCES = [
        'planting_locations',
        'tree_plantings',
        'inspections',
        'tree_planting_measurements',
        'biochar_batches',
    ];

    private const SOURCE_LABELS = [
        'planting_locations'         => 'Planting Locations',
        'tree_plantings'             => 'Tree Plantings',
        'inspections'                => 'Inspections',
        'tree_planting_measurements' => 'Tree Planting Measurements',
        'biochar_batches'            => 'Biochar Batches',
        'tree_types'                 => 'Tree Types',
        'users'                      => 'Users',
    ];

    /**
     * Reference-ID prefixes — the export's first column for every
     * source, e.g. LOC-1234. Replaces a plain numeric id column, not
     * added alongside it.
     */
    private const REFERENCE_PREFIXES = [
        'planting_locations'         => 'LOC',
        'tree_plantings'             => 'PLT',
        'inspections'                => 'INS',
        'tree_planting_measurements' => 'MSR',
        'biochar_batches'            => 'BIO',
        'tree_types'                 => 'TYP',
        'users'                      => 'USR',
    ];

    private const PREVIEW_PER_PAGE = 50;

    private const DOWNLOAD_CHUNK_SIZE = 200;

    /**
     * Preview: filter form + a paginated (not full) sample of the
     * currently-selected table/filter combination.
     */
    public function index(Request $request, PolygonAreaCalculator $areaCalculator)
    {
        $source = $this->resolveSource($request);
        $divisionId = $this->resolveDivisionId($request, $source);
        $search = $this->resolveSearch($request);

        $paginator = $this->baseQuery($source, $divisionId, $search)
            ->paginate(self::PREVIEW_PER_PAGE)
            ->withQueryString();

        $rows = $paginator->getCollection()
            ->map(fn ($model) => $this->mapRow($source, $model, $areaCalculator));

        $divisions = Division::whereHas('plantingLocations')->orderBy('LGA_name')->get();

        return view('export.index', [
            'sources'              => self::SOURCE_LABELS,
            'geoFilterableSources' => self::GEO_FILTERABLE_SOURCES,
            'source'               => $source,
            'divisionId'           => $divisionId,
            'search'               => $search,
            'divisions'            => $divisions,
            'columnLabels'         => $this->columnLabels($source),
            'rows'                 => $rows,
            'paginator'            => $paginator,
        ]);
    }

    /**
     * Streams the FULL filtered dataset (not paginated) as a CSV.
     * Rows are written incrementally via chunk() — the result set is
     * never loaded into memory as a whole, regardless of row count.
     */
    public function download(Request $request, PolygonAreaCalculator $areaCalculator)
    {
        $source = $this->resolveSource($request);
        $divisionId = $this->resolveDivisionId($request, $source);
        $search = $this->resolveSearch($request);

        $query = $this->baseQuery($source, $divisionId, $search);
        $columnLabels = $this->columnLabels($source);
        $filename = $source.'-'.now()->format('Y-m-d_His').'.csv';

        return response()->streamDownload(function () use ($query, $columnLabels, $source, $areaCalculator) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM, then a "sep=" directive as the very first line —
            // both are Excel-specific: the BOM keeps accented characters
            // rendering correctly, and "sep=," forces Excel to use comma
            // as the delimiter regardless of the OS locale's default list
            // separator (many non-US locales default to semicolon, which
            // otherwise squashes every row into a single column). Neither
            // changes the actual delimiter used below, and both are
            // ignored harmlessly by any other CSV consumer.
            fwrite($handle, "\xEF\xBB\xBF");
            fwrite($handle, "sep=,\n");

            fputcsv($handle, array_values($columnLabels));

            $query->chunk(self::DOWNLOAD_CHUNK_SIZE, function ($chunk) use ($handle, $source, $areaCalculator) {
                foreach ($chunk as $model) {
                    fputcsv($handle, $this->mapRow($source, $model, $areaCalculator));
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function resolveSource(Request $request): string
    {
        $source = $request->get('table', 'planting_locations');

        return array_key_exists($source, self::SOURCE_LABELS) ? $source : 'planting_locations';
    }

    /**
     * Returns null (no filter applied) for any source without a
     * genuine geographic relationship, regardless of what the request
     * actually contains — server-side enforcement, not just a
     * client-side hidden dropdown.
     */
    private function resolveDivisionId(Request $request, string $source): ?int
    {
        if (! in_array($source, self::GEO_FILTERABLE_SOURCES, true)) {
            return null;
        }

        $division = $request->get('division');

        return ($division !== null && $division !== '') ? (int) $division : null;
    }

    /**
     * Trimmed, non-empty search term, or null. Meaning is resolved per
     * source in applySearch() — "search" isn't the same query across a
     * planting-location table vs. tree_types vs. users.
     */
    private function resolveSearch(Request $request): ?string
    {
        $search = trim((string) $request->get('search', ''));

        return $search !== '' ? $search : null;
    }

    /**
     * One query builder shared by both index() and download(), so the
     * preview and the full export can never drift apart on what rows
     * or columns they include.
     */
    private function baseQuery(string $source, ?int $divisionId, ?string $search = null): Builder
    {
        return match ($source) {
            'planting_locations' => PlantingLocation::query()
                ->with(['division', 'status'])
                ->withSum('treePlantings as total_trees', 'number_of_trees')
                ->withSum('biocharBatches as total_biochar_kg', 'quantity_kg')
                ->withCount('inspections as inspection_count')
                ->when($divisionId, fn ($q) => $q->where('division_id', $divisionId))
                ->when($search, fn ($q) => $q->where(function ($sq) use ($search) {
                    $sq->where('location', 'like', "%{$search}%")
                        ->orWhereHas('division', fn ($dq) => $dq->where('LGA_name', 'like', "%{$search}%"));
                }))
                ->orderBy('id'),

            'tree_plantings' => TreePlanting::query()
                ->with(['treeType', 'statusRelation', 'statusUpdatedBy', 'plantingLocation.division'])
                // Never TreePlanting::division() — see finding #1: that
                // relation always silently returns null (no division_id
                // column on tree_plantings). The real path is via
                // plantingLocation.
                ->when($divisionId, fn ($q) => $q->whereHas(
                    'plantingLocation',
                    fn ($pq) => $pq->where('division_id', $divisionId)
                ))
                ->when($search, fn ($q) => $q->whereHas(
                    'plantingLocation',
                    fn ($pq) => $this->applyLocationOrDivisionSearch($pq, $search)
                ))
                ->orderBy('id'),

            'inspections' => Inspection::query()
                ->with(['plantingLocation.division', 'user'])
                ->when($divisionId, fn ($q) => $q->whereHas(
                    'plantingLocation',
                    fn ($pq) => $pq->where('division_id', $divisionId)
                ))
                ->when($search, fn ($q) => $q->whereHas(
                    'plantingLocation',
                    fn ($pq) => $this->applyLocationOrDivisionSearch($pq, $search)
                ))
                ->orderBy('id'),

            'tree_planting_measurements' => TreePlantingMeasurement::query()
                ->with(['recordedBy', 'verifiedBy', 'treePlanting.treeType', 'treePlanting.plantingLocation.division'])
                // Two-hop, both hops via real relations — never through
                // TreePlanting::division().
                ->when($divisionId, fn ($q) => $q->whereHas(
                    'treePlanting.plantingLocation',
                    fn ($pq) => $pq->where('division_id', $divisionId)
                ))
                ->when($search, fn ($q) => $q->whereHas(
                    'treePlanting.plantingLocation',
                    fn ($pq) => $this->applyLocationOrDivisionSearch($pq, $search)
                ))
                ->orderBy('id'),

            'biochar_batches' => BiocharBatch::query()
                ->with(['plantingLocation.division', 'treePlanting.treeType', 'user'])
                ->when($divisionId, fn ($q) => $q->whereHas(
                    'plantingLocation',
                    fn ($pq) => $pq->where('division_id', $divisionId)
                ))
                ->when($search, fn ($q) => $q->whereHas(
                    'plantingLocation',
                    fn ($pq) => $this->applyLocationOrDivisionSearch($pq, $search)
                ))
                ->orderBy('id'),

            'tree_types' => TreeType::query()
                ->withSum('treePlantings as total_trees_planted', 'number_of_trees')
                ->when($search, fn ($q) => $q->where(function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%")
                        ->orWhere('latin_name', 'like', "%{$search}%");
                }))
                ->orderBy('id'),

            // Explicit column allow-list at the SQL level — email,
            // email_verified_at, password, remember_token,
            // profile_picture_path, and picture_id are never selected,
            // so they physically cannot appear in $model's attributes
            // even if a future change to mapRow() tried to reference
            // them. This is the strongest guarantee available, stronger
            // than relying on $hidden (which only protects
            // toArray()/toJson(), not direct attribute access).
            'users' => User::query()
                ->select(['id', 'name', 'telephone', 'country', 'gender', 'last_login_at', 'created_at'])
                ->with('roles')
                // name only — email is deliberately excluded from this
                // feature entirely (see the users column allow-list
                // above), so it must never be searchable either, even
                // though a search filter has no direct export exposure.
                ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
                ->orderBy('id'),
        };
    }

    /**
     * Shared "location name OR its division's name" search clause, used
     * inside a whereHas('planting-location relation', ...) closure by
     * every source that reaches a PlantingLocation indirectly. Grouped
     * in its own where(function...) so the internal OR can't leak out
     * and break AND-precedence with whatever sibling clause (e.g. the
     * division filter's own whereHas) is combined with it — same fix
     * already established in PlantingLocationController::index().
     */
    private function applyLocationOrDivisionSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($sq) use ($search) {
            $sq->where('location', 'like', "%{$search}%")
                ->orWhereHas('division', fn ($dq) => $dq->where('LGA_name', 'like', "%{$search}%"));
        });
    }

    private function columnLabels(string $source): array
    {
        return match ($source) {
            'planting_locations' => [
                'reference_id'              => 'Reference ID',
                'public_code'               => 'Public Code',
                'location'                  => 'Location',
                'division'                  => 'Division',
                'latitude'                  => 'Latitude',
                'longitude'                 => 'Longitude',
                'comment'                   => 'Comment',
                'status'                    => 'Status',
                'total_trees'               => 'Total Trees',
                'area_hectares'             => 'Area (ha)',
                'density_trees_per_hectare' => 'Density (trees/ha)',
                'total_biochar_kg'          => 'Total Biochar (kg)',
                'inspection_count'          => 'Inspections',
                'created_at'                => 'Created At',
            ],
            'tree_plantings' => [
                'reference_id'      => 'Reference ID',
                'planting_date'     => 'Planting Date',
                'number_of_trees'   => 'Number of Trees',
                'tree_type'         => 'Tree Type',
                'status'            => 'Status',
                'biochar'           => 'Biochar (per-tree, legacy)',
                'planting_location' => 'Planting Location',
                'division'          => 'Division',
                'latitude'          => 'Latitude',
                'longitude'         => 'Longitude',
                'status_updated_by' => 'Status Updated By',
                'created_at'        => 'Created At',
            ],
            'inspections' => [
                'reference_id'      => 'Reference ID',
                'inspection_date'   => 'Inspection Date',
                'comment'           => 'Comment',
                'verified'          => 'Verified',
                'planting_location' => 'Planting Location',
                'division'          => 'Division',
                'inspector'         => 'Inspector',
                'created_at'        => 'Created At',
            ],
            'tree_planting_measurements' => [
                'reference_id'        => 'Reference ID',
                'measurement_date'    => 'Measurement Date',
                'trees_surviving'     => 'Trees Surviving',
                'height_avg_cm'       => 'Height Avg (cm)',
                'dbh_avg_cm'          => 'DBH Avg (cm)',
                'canopy_cover_pct'    => 'Canopy %',
                'measurement_source'  => 'Measurement Source',
                'notes'               => 'Notes',
                'recorded_by'         => 'Recorded By',
                'verified_by'         => 'Verified By',
                'verified_at'         => 'Verified At',
                'tree_planting_date'  => 'Planting Date',
                'tree_type'           => 'Tree Type',
                'planting_location'   => 'Planting Location',
                'division'            => 'Division',
                'latitude'            => 'Latitude',
                'longitude'           => 'Longitude',
                'created_at'          => 'Created At',
            ],
            'biochar_batches' => [
                'reference_id'       => 'Reference ID',
                'quantity_kg'        => 'Quantity (kg)',
                'source'             => 'Source',
                'batch_reference'    => 'Batch Reference',
                'application_date'   => 'Application Date',
                'notes'              => 'Notes',
                'planting_location'  => 'Planting Location',
                'division'           => 'Division',
                'latitude'           => 'Latitude',
                'longitude'          => 'Longitude',
                'tree_planting_date' => 'Linked Planting Date',
                'tree_type'          => 'Linked Tree Type',
                'recorded_by'        => 'Recorded By',
                'created_at'         => 'Created At',
            ],
            'tree_types' => [
                'reference_id'        => 'Reference ID',
                'name'                => 'Name',
                'latin_name'          => 'Latin Name',
                'description'         => 'Description',
                'wood_density_kg_m3'  => 'Wood Density (kg/m³)',
                'carbon_fraction'     => 'Carbon Fraction',
                'source_reference'    => 'Source Reference',
                'total_trees_planted' => 'Total Trees Planted',
                'created_at'          => 'Created At',
            ],
            // email, email_verified_at, password, remember_token,
            // profile_picture_path, picture_id: deliberately excluded.
            'users' => [
                'reference_id'  => 'Reference ID',
                'name'          => 'Name',
                'telephone'     => 'Telephone',
                'country'       => 'Country',
                'gender'        => 'Gender',
                'roles'         => 'Role(s)',
                'last_login_at' => 'Last Login',
                'created_at'    => 'Created At',
            ],
        };
    }

    /**
     * Ordered values matching columnLabels()'s key order exactly — one
     * mapper shared by the preview and the download, so they can never
     * disagree on what a row looks like.
     */
    private function mapRow(string $source, $row, PolygonAreaCalculator $areaCalculator): array
    {
        return match ($source) {
            'planting_locations'         => $this->mapPlantingLocation($row, $areaCalculator),
            'tree_plantings'             => $this->mapTreePlanting($row),
            'inspections'                => $this->mapInspection($row),
            'tree_planting_measurements' => $this->mapMeasurement($row),
            'biochar_batches'            => $this->mapBiocharBatch($row),
            'tree_types'                 => $this->mapTreeType($row),
            'users'                      => $this->mapUser($row),
        };
    }

    private function referenceId(string $source, int $id): string
    {
        return self::REFERENCE_PREFIXES[$source].'-'.$id;
    }

    private function mapPlantingLocation(PlantingLocation $row, PolygonAreaCalculator $areaCalculator): array
    {
        // Reuses the exact calculation PlantingLocationController::show()
        // uses for its "Location Data" table — same service, same
        // null-safe density guard, not reimplemented.
        $areaHectares = $areaCalculator->calculateHectares($row->boundary_geojson);
        $totalTrees = (int) ($row->total_trees ?? 0);
        $density = ($areaHectares !== null && $areaHectares > 0) ? $totalTrees / $areaHectares : null;

        return [
            $this->referenceId('planting_locations', $row->id),
            $row->public_code,
            $row->location,
            $row->division->LGA_name ?? '',
            $row->latitude,
            $row->longitude,
            $row->comment,
            $row->status->planting_location_status ?? '',
            $totalTrees,
            $areaHectares !== null ? number_format($areaHectares, 2) : '',
            $density !== null ? number_format($density, 2) : '',
            number_format((float) ($row->total_biochar_kg ?? 0), 2),
            (int) ($row->inspection_count ?? 0),
            $row->created_at?->toDateTimeString(),
        ];
    }

    private function mapTreePlanting(TreePlanting $row): array
    {
        $location = $row->plantingLocation;

        return [
            $this->referenceId('tree_plantings', $row->id),
            $row->planting_date?->toDateString(),
            $row->number_of_trees,
            $row->treeType->name ?? '',
            $row->statusRelation->tree_planting_status ?? '',
            $row->biochar,
            $location->location ?? '',
            $location->division->LGA_name ?? '',
            $location->latitude ?? '',
            $location->longitude ?? '',
            $row->statusUpdatedBy->name ?? '',
            $row->created_at?->toDateTimeString(),
        ];
    }

    private function mapInspection(Inspection $row): array
    {
        $location = $row->plantingLocation;

        return [
            $this->referenceId('inspections', $row->id),
            $row->inspection_date ? \Carbon\Carbon::parse($row->inspection_date)->toDateString() : '',
            $row->comment,
            $row->verified ? 'Yes' : 'No',
            $location->location ?? '',
            $location->division->LGA_name ?? '',
            $row->user->name ?? '',
            $row->created_at?->toDateTimeString(),
        ];
    }

    private function mapMeasurement(TreePlantingMeasurement $row): array
    {
        $treePlanting = $row->treePlanting;
        $location = $treePlanting?->plantingLocation;

        return [
            $this->referenceId('tree_planting_measurements', $row->id),
            $row->measurement_date?->toDateString(),
            $row->trees_surviving,
            $row->height_avg_cm,
            $row->dbh_avg_cm,
            $row->canopy_cover_pct,
            $row->measurement_source,
            $row->notes,
            $row->recordedBy->name ?? '',
            $row->verifiedBy->name ?? '',
            $row->verified_at?->toDateTimeString(),
            $treePlanting?->planting_date?->toDateString(),
            $treePlanting?->treeType->name ?? '',
            $location->location ?? '',
            $location->division->LGA_name ?? '',
            $location->latitude ?? '',
            $location->longitude ?? '',
            $row->created_at?->toDateTimeString(),
        ];
    }

    private function mapBiocharBatch(BiocharBatch $row): array
    {
        $location = $row->plantingLocation;
        $treePlanting = $row->treePlanting;

        return [
            $this->referenceId('biochar_batches', $row->id),
            $row->quantity_kg,
            $row->source,
            $row->batch_reference,
            $row->application_date?->toDateString(),
            $row->notes,
            $location->location ?? '',
            $location->division->LGA_name ?? '',
            $location->latitude ?? '',
            $location->longitude ?? '',
            $treePlanting?->planting_date?->toDateString(),
            $treePlanting?->treeType->name ?? '',
            $row->user->name ?? '',
            $row->created_at?->toDateTimeString(),
        ];
    }

    private function mapTreeType(TreeType $row): array
    {
        return [
            $this->referenceId('tree_types', $row->id),
            $row->name,
            $row->latin_name,
            $row->description,
            $row->wood_density_kg_m3,
            $row->carbon_fraction,
            $row->source_reference,
            (int) ($row->total_trees_planted ?? 0),
            $row->created_at?->toDateTimeString(),
        ];
    }

    private function mapUser(User $row): array
    {
        return [
            $this->referenceId('users', $row->id),
            $row->name,
            $row->telephone,
            $row->country,
            $row->gender,
            $row->getRoleNames()->implode(', '),
            $row->last_login_at?->toDateTimeString(),
            $row->created_at?->toDateTimeString(),
        ];
    }
}
