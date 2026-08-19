<?php

namespace App\Http\Controllers;

use App\Models\PlantingLocation;
use App\Models\TreePlanting;
use Illuminate\Http\Request;
use App\Services\MapMarkerService;
use App\Services\ChangeLogger;
use App\Services\GeoJsonPolygonValidator;
use App\Services\GeometryFingerprint;
use Illuminate\Support\Facades\DB;

class PlantingLocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = PlantingLocation::with([
            'division',
            'status',
            'treePlantings' => function ($query) {
                $query->orderBy('planting_date', 'desc');
            },
            'treePlantings.treeType',
            'treePlantings.statusRelation',
            'treePlantings.statusUpdatedBy',
        ]);

        // Apply division filter
        if ($request->filled('division')) {
            $query->where('division_id', $request->division);
        }

        // Apply search filter
        if ($request->filled('search')) {
            $query->where('location', 'like', '%' . $request->search . '%');
        }

        $sort = $request->input('sort', 'name_asc');
        $query->orderBy('location', $sort === 'name_desc' ? 'desc' : 'asc');

        $plantingLocations = $query
            ->withSum('treePlantings as total_trees', 'number_of_trees')
            ->paginate(20)
            ->withQueryString();

        $divisions = \App\Models\Division::orderBy('LGA_name')->get();

        return view('planting-locations.index', compact('plantingLocations', 'divisions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('planting-locations.create', [
            'divisions' => \App\Models\Division::all(),
            'statuses' => \App\Models\PlantingLocationStatus::all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(
        Request $request,
        ChangeLogger $changeLogger,
        GeoJsonPolygonValidator $boundaryValidator,
        GeometryFingerprint $fingerprinter
    ) {
        $validated = $request->validate([
            'location'            => 'required|string|max:255',
            'division_id'         => 'required|exists:division,id',
            'status_id'           => 'required|exists:planting_location_status,id',
            'comment'             => 'nullable|string',
            'contributors'        => 'nullable|string',
            'latitude'            => 'nullable|numeric|between:-90,90',
            'longitude'           => 'nullable|numeric|between:-180,180',
            'capture_method'      => 'nullable|in:manual,gps_button',
            'gps_accuracy_meters' => 'nullable|numeric|min:0',
        ]);

        // Metadata about the coordinate-setting event itself, not real
        // columns on PlantingLocation — pulled out before create() so
        // they only ever reach the ChangeLog, never the model.
        $captureMethod  = $validated['capture_method'] ?? null;
        $accuracyMeters = $validated['gps_accuracy_meters'] ?? null;
        unset($validated['capture_method'], $validated['gps_accuracy_meters']);

        // Structural validation + auto-closing lives in a dedicated
        // service rather than the standard rule array, since it's more
        // than a simple rule chain can express.
        $boundary = $boundaryValidator->validate($request->input('boundary_geojson'));

        $validated['user_id']          = auth()->id();
        $validated['contributors']     = $request->contributors
            ? strip_tags($request->contributors, '<p><br><strong><em><u><ol><ul><li><a><span>')
            : null;
        $validated['boundary_geojson'] = $boundary;

        $plantingLocation = DB::transaction(function () use ($validated, $captureMethod, $accuracyMeters, $changeLogger, $boundary, $fingerprinter) {
            $plantingLocation = PlantingLocation::create($validated);

            // A location's very first coordinate-setting — likely the
            // only one that ever happens for most locations — previously
            // went entirely unlogged, since Phase 1 only instrumented
            // update(). Skipped if no coordinates were actually provided.
            if ($plantingLocation->latitude !== null && $plantingLocation->longitude !== null) {
                $changeLogger->record(
                    loggableType: 'PlantingLocation',
                    loggableId: $plantingLocation->id,
                    action: 'coordinates_set',
                    old: null,
                    new: [
                        'latitude'        => $plantingLocation->latitude,
                        'longitude'       => $plantingLocation->longitude,
                        'capture_method'  => $captureMethod,
                        'accuracy_meters' => $accuracyMeters,
                    ],
                );
            }

            // Fingerprint only — never the raw coordinates array — per
            // the investigation's flag about JSON column bloat.
            if ($boundary !== null) {
                $changeLogger->record(
                    loggableType: 'PlantingLocation',
                    loggableId: $plantingLocation->id,
                    action: 'boundary_set',
                    old: null,
                    new: $fingerprinter->fingerprint($boundary),
                );
            }

            return $plantingLocation;
        });

        return redirect()
            ->route('planting-locations.show', $plantingLocation->id)
            ->with('success', 'Planting location created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(
        Request $request,
        MapMarkerService $markerService,
        PlantingLocation $plantingLocation
    ) {
        $filters = [
            'id' => $plantingLocation->id,
        ];

        $markers = $markerService->getMarkers($filters);

        $plantingLocation->load([
            'division',
            'pictures',
            'status',
            'treePlantings.treeType',
            'treePlantings.statusRelation',
        ]);

        return view('planting-locations.show', compact('plantingLocation', 'markers'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PlantingLocation $plantingLocation)
    {
        return view('planting-locations.edit', [
            'plantingLocation' => $plantingLocation,
            'divisions'        => \App\Models\Division::all(),
            'statuses'         => \App\Models\PlantingLocationStatus::all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        Request $request,
        ChangeLogger $changeLogger,
        PlantingLocation $plantingLocation,
        GeoJsonPolygonValidator $boundaryValidator,
        GeometryFingerprint $fingerprinter
    ) {
        $validated = $request->validate([
            'location'            => 'required|string|max:255',
            'division_id'         => 'required|exists:division,id',
            'status_id'           => 'required|exists:planting_location_status,id',
            'comment'             => 'nullable|string',
            'contributors'        => 'nullable|string',
            'latitude'            => 'nullable|numeric|between:-90,90',
            'longitude'           => 'nullable|numeric|between:-180,180',
            'capture_method'      => 'nullable|in:manual,gps_button',
            'gps_accuracy_meters' => 'nullable|numeric|min:0',
        ]);

        $captureMethod  = $validated['capture_method'] ?? null;
        $accuracyMeters = $validated['gps_accuracy_meters'] ?? null;
        unset($validated['capture_method'], $validated['gps_accuracy_meters']);

        $boundary = $boundaryValidator->validate($request->input('boundary_geojson'));

        $validated['contributors']     = $request->contributors
            ? strip_tags($request->contributors, '<p><br><strong><em><u><ol><ul><li><a><span>')
            : null;
        $validated['boundary_geojson'] = $boundary;

        $originalLatitude  = $plantingLocation->latitude;
        $originalLongitude = $plantingLocation->longitude;
        $originalStatusId  = $plantingLocation->status_id;
        $originalBoundary  = $plantingLocation->boundary_geojson;

        DB::transaction(function () use (
            $plantingLocation, $validated, $changeLogger,
            $originalLatitude, $originalLongitude, $originalStatusId,
            $captureMethod, $accuracyMeters,
            $originalBoundary, $boundary, $fingerprinter
        ) {
            $plantingLocation->update($validated);

            $coordinatesChanged = (string) $originalLatitude !== (string) $plantingLocation->latitude
                || (string) $originalLongitude !== (string) $plantingLocation->longitude;

            if ($coordinatesChanged) {
                $changeLogger->record(
                    loggableType: 'PlantingLocation',
                    loggableId: $plantingLocation->id,
                    action: 'coordinates_updated',
                    old: ['latitude' => $originalLatitude, 'longitude' => $originalLongitude],
                    new: [
                        'latitude'        => $plantingLocation->latitude,
                        'longitude'       => $plantingLocation->longitude,
                        'capture_method'  => $captureMethod,
                        'accuracy_meters' => $accuracyMeters,
                    ],
                );
            }

            if ((int) $originalStatusId !== (int) $plantingLocation->status_id) {
                $changeLogger->record(
                    loggableType: 'PlantingLocation',
                    loggableId: $plantingLocation->id,
                    action: 'status_changed',
                    old: ['status_id' => $originalStatusId],
                    new: ['status_id' => $plantingLocation->status_id],
                );
            }

            // Compare by fingerprint hash, not raw JSON string equality —
            // semantically identical geometry can differ in incidental
            // formatting (float precision, key order). Skipped entirely
            // when nothing changed, same no-op protection Phase 2
            // established for measurement edits.
            $originalHash = $originalBoundary !== null ? $fingerprinter->fingerprint($originalBoundary)['hash'] : null;
            $newHash      = $boundary !== null ? $fingerprinter->fingerprint($boundary)['hash'] : null;

            if ($originalHash !== $newHash) {
                $changeLogger->record(
                    loggableType: 'PlantingLocation',
                    loggableId: $plantingLocation->id,
                    action: 'boundary_updated',
                    old: $originalBoundary !== null ? $fingerprinter->fingerprint($originalBoundary) : null,
                    new: $boundary !== null ? $fingerprinter->fingerprint($boundary) : null,
                );
            }
        });

        return redirect()
            ->route('planting-locations.show', $plantingLocation)
            ->with('success', 'Planting Location updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PlantingLocation $plantingLocation)
    {
        if (! auth()->user()->hasRole(['Admin', 'SuperAdmin'])) {
            abort(403, 'Only admins can delete locations.');
        }

        $plantingLocation->delete();

        return redirect()
            ->route('planting-locations.index')
            ->with('success', 'Planting Location deleted.');
    }

    public function qrLabel(PlantingLocation $plantingLocation)
    {
        return view('planting-locations.qr-label', compact('plantingLocation'));
    }

    public function moveForm(PlantingLocation $plantingLocation)
    {
        $plantingLocation->load([
            'treePlantings.treeType',
            'treePlantings.statusRelation',
            'treePlantings.user',
            'treePlantings.statusUpdatedBy',
        ]);

        return view('planting-locations.move', compact('plantingLocation'));
    }

    public function executeMove(Request $request, ChangeLogger $changeLogger, PlantingLocation $plantingLocation)
    {
        $request->validate([
            'planting_ids'   => 'required|array|min:1',
            'planting_ids.*' => 'integer',
            'destination_id' => 'required|integer|exists:App\Models\PlantingLocation,id',
        ]);

        if ((int) $request->destination_id === $plantingLocation->id) {
            return back()->withErrors(['destination_id' => 'Destination must be a different location.']);
        }

        // Fetch id + current planting_location_id before the mass update
        // overwrites it, so each row's move can be logged individually.
        $validPlantings = $plantingLocation->treePlantings()
            ->whereIn('id', $request->planting_ids)
            ->get(['id', 'planting_location_id']);

        if ($validPlantings->isEmpty()) {
            return back()->withErrors(['planting_ids' => 'No valid plantings selected.']);
        }

        $destination = PlantingLocation::findOrFail($request->destination_id);

        DB::transaction(function () use ($validPlantings, $destination, $changeLogger) {
            TreePlanting::whereIn('id', $validPlantings->pluck('id'))
                ->update(['planting_location_id' => $destination->id]);

            // One ChangeLog row per moved planting (not one per batch), so
            // "was this specific planting ever moved" can be answered
            // directly without re-deriving it from a batch record.
            foreach ($validPlantings as $planting) {
                $changeLogger->record(
                    loggableType: 'TreePlanting',
                    loggableId: $planting->id,
                    action: 'moved',
                    old: ['planting_location_id' => $planting->planting_location_id],
                    new: ['planting_location_id' => $destination->id],
                );
            }
        });

        $count = $validPlantings->count();

        return redirect()
            ->route('planting-locations.show', $plantingLocation)
            ->with('success', "{$count} planting(s) moved to {$destination->location}.");
    }

    public function search(Request $request)
    {
        $q       = $request->get('q', '');
        $exclude = $request->get('exclude');

        $locations = PlantingLocation::with('division')
            ->where('location', 'like', '%' . $q . '%')
            ->when($exclude, fn ($query) => $query->where('id', '!=', (int) $exclude))
            ->orderBy('location')
            ->limit(10)
            ->get()
            ->map(fn ($loc) => [
                'id'       => $loc->id,
                'location' => $loc->location,
                'lga_name' => $loc->division->LGA_name ?? 'N/A',
            ]);

        return response()->json($locations);
    }
}
