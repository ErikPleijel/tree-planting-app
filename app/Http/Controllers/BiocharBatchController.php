<?php

namespace App\Http\Controllers;

use App\Models\BiocharBatch;
use App\Models\PlantingLocation;
use App\Models\TreePlanting;
use App\Services\ChangeLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BiocharBatchController extends Controller
{
    /**
     * All batches locatable at this PlantingLocation — both ones tied to
     * a specific TreePlanting there and ones tied to the location only.
     */
    public function index(PlantingLocation $plantingLocation)
    {
        $batches = BiocharBatch::where('planting_location_id', $plantingLocation->id)
            ->with(['treePlanting.treeType', 'user'])
            ->orderBy('application_date', 'desc')
            ->get();

        return view('biochar-batches.index', compact('plantingLocation', 'batches'));
    }

    public function create(Request $request)
    {
        $plantingLocation = PlantingLocation::findOrFail($request->planting_location_id);
        $plantingLocation->load('treePlantings.treeType');

        return view('biochar-batches.create', compact('plantingLocation'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateLinkage($request);
        $validated = array_merge($validated, $this->validateFields($request));
        $validated['user_id'] = auth()->id();

        $batch = BiocharBatch::create($validated);

        return redirect()
            ->route('biochar-batches.index', $batch->planting_location_id)
            ->with('success', 'Biochar batch recorded successfully.');
    }

    public function edit(BiocharBatch $biocharBatch)
    {
        $biocharBatch->load(['plantingLocation', 'treePlanting.treeType']);

        return view('biochar-batches.edit', compact('biocharBatch'));
    }

    /**
     * Only the recordable-values fields are editable here — the
     * location/planting link is fixed at creation, matching how
     * TreePlantingMeasurement's edit form doesn't allow re-linking to a
     * different TreePlanting either.
     */
    public function update(Request $request, ChangeLogger $changeLogger, BiocharBatch $biocharBatch)
    {
        $validated = $this->validateFields($request);

        $trackedFields = ['quantity_kg', 'source', 'batch_reference', 'application_date', 'notes'];

        $original = collect($trackedFields)
            ->mapWithKeys(fn ($field) => [$field => $biocharBatch->{$field}])
            ->all();

        DB::transaction(function () use ($biocharBatch, $validated, $trackedFields, $original, $changeLogger) {
            $biocharBatch->update($validated);

            $old = [];
            $new = [];

            foreach ($trackedFields as $field) {
                if ((string) $original[$field] !== (string) $biocharBatch->{$field}) {
                    $old[$field] = $original[$field];
                    $new[$field] = $biocharBatch->{$field};
                }
            }

            if (! empty($old)) {
                $changeLogger->record(
                    loggableType: 'BiocharBatch',
                    loggableId: $biocharBatch->id,
                    action: 'batch_updated',
                    old: $old,
                    new: $new,
                );
            }
        });

        return redirect()
            ->route('biochar-batches.index', $biocharBatch->planting_location_id)
            ->with('success', 'Biochar batch updated successfully.');
    }

    public function destroy(BiocharBatch $biocharBatch)
    {
        $locationId = $biocharBatch->planting_location_id;
        $biocharBatch->delete();

        return redirect()
            ->route('biochar-batches.index', $locationId)
            ->with('success', 'Biochar batch deleted successfully.');
    }

    /**
     * A batch must be locatable somewhere. If a specific TreePlanting is
     * given, planting_location_id is derived from it server-side rather
     * than trusted from the request, so it can never contradict the
     * planting it's linked to.
     */
    private function validateLinkage(Request $request): array
    {
        $validated = $request->validate([
            'planting_location_id' => 'nullable|integer|exists:planting_locations,id',
            'tree_planting_id'     => 'nullable|integer|exists:tree_plantings,id',
        ]);

        if (empty($validated['planting_location_id']) && empty($validated['tree_planting_id'])) {
            throw ValidationException::withMessages([
                'planting_location_id' => 'A biochar batch must be linked to a location or a specific planting.',
            ]);
        }

        if (! empty($validated['tree_planting_id'])) {
            $treePlanting = TreePlanting::findOrFail($validated['tree_planting_id']);
            $validated['planting_location_id'] = $treePlanting->planting_location_id;
        }

        return $validated;
    }

    private function validateFields(Request $request): array
    {
        return $request->validate([
            'quantity_kg'       => 'required|numeric|min:0',
            'source'            => 'required|string|max:255',
            'batch_reference'   => 'nullable|string|max:255',
            'application_date'  => 'required|date|before_or_equal:'.now()->toDateString(),
            'notes'             => 'nullable|string',
        ]);
    }
}
