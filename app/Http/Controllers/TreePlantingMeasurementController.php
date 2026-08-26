<?php

namespace App\Http\Controllers;

use App\Models\TreePlanting;
use App\Models\TreePlantingMeasurement;
use App\Services\ChangeLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TreePlantingMeasurementController extends Controller
{
    /**
     * Measurement history for one specific TreePlanting cohort.
     */
    public function index(TreePlanting $treePlanting)
    {
        $treePlanting->load(['plantingLocation', 'treeType']);

        $measurements = $treePlanting->measurements()
            ->with(['recordedBy', 'verifiedBy', 'lidarScans'])
            ->orderBy('measurement_date', 'desc')
            ->get();

        return view('tree-planting-measurements.index', compact('treePlanting', 'measurements'));
    }

    public function create(TreePlanting $treePlanting)
    {
        $treePlanting->load(['plantingLocation', 'treeType']);

        return view('tree-planting-measurements.create', compact('treePlanting'));
    }

    public function store(Request $request, TreePlanting $treePlanting)
    {
        $validated = $this->validateMeasurement($request, $treePlanting);

        $validated['tree_planting_id'] = $treePlanting->id;
        $validated['user_id'] = auth()->id();

        TreePlantingMeasurement::create($validated);

        return redirect()
            ->route('tree-planting-measurements.index', $treePlanting)
            ->with('success', 'Measurement recorded successfully.');
    }

    public function edit(TreePlantingMeasurement $measurement)
    {
        $measurement->load('treePlanting');

        return view('tree-planting-measurements.edit', [
            'measurement'  => $measurement,
            'treePlanting' => $measurement->treePlanting,
        ]);
    }

    /**
     * Correcting values, not attesting to them — kept separate from
     * verify() so the two actions can't be conflated in one submission.
     *
     * If the measurement is currently verified and a tracked field
     * actually changes, the edit also resets verified_by_user_id/
     * verified_at — a verification that no longer matches the recorded
     * values is not a verification. That reset is folded into the same
     * save() as the edited fields (one write), but logged as its own
     * separate ChangeLog entry so "edited" and "verification invalidated"
     * remain independently queryable.
     */
    public function update(Request $request, ChangeLogger $changeLogger, TreePlantingMeasurement $measurement)
    {
        $treePlanting = $measurement->treePlanting;
        $validated = $this->validateMeasurement($request, $treePlanting);

        $trackedFields = ['measurement_date', 'trees_surviving', 'height_avg_cm', 'dbh_avg_cm', 'canopy_cover_pct', 'notes'];

        $original = collect($trackedFields)
            ->mapWithKeys(fn ($field) => [$field => $measurement->{$field}])
            ->all();

        $wasVerified              = $measurement->verified_by_user_id !== null;
        $originalVerifiedByUserId = $measurement->verified_by_user_id;
        $originalVerifiedAt       = $measurement->verified_at;

        DB::transaction(function () use (
            $measurement, $validated, $trackedFields, $original, $changeLogger,
            $wasVerified, $originalVerifiedByUserId, $originalVerifiedAt
        ) {
            $measurement->fill($validated);

            $old = [];
            $new = [];

            foreach ($trackedFields as $field) {
                if ((string) $original[$field] !== (string) $measurement->{$field}) {
                    $old[$field] = $original[$field];
                    $new[$field] = $measurement->{$field};
                }
            }

            $fieldsChanged = ! empty($old);
            $resetVerification = $wasVerified && $fieldsChanged;

            if ($resetVerification) {
                $measurement->verified_by_user_id = null;
                $measurement->verified_at = null;
            }

            $measurement->save();

            if ($fieldsChanged) {
                $changeLogger->record(
                    loggableType: 'TreePlantingMeasurement',
                    loggableId: $measurement->id,
                    action: 'measurement_updated',
                    old: $old,
                    new: $new,
                );
            }

            if ($resetVerification) {
                $changeLogger->record(
                    loggableType: 'TreePlantingMeasurement',
                    loggableId: $measurement->id,
                    action: 'measurement_verification_reset',
                    old: [
                        'verified_by_user_id' => $originalVerifiedByUserId,
                        'verified_at'         => $originalVerifiedAt?->toISOString(),
                    ],
                    new: [
                        'verified_by_user_id' => null,
                        'verified_at'         => null,
                    ],
                );
            }
        });

        return redirect()
            ->route('tree-planting-measurements.index', $measurement->tree_planting_id)
            ->with('success', 'Measurement updated successfully.');
    }

    /**
     * Attesting to values, not correcting them. Route middleware already
     * restricts this to Admin|SuperAdmin|Monitor; the self-verification
     * rule below applies on top of that, regardless of role.
     */
    public function verify(ChangeLogger $changeLogger, TreePlantingMeasurement $measurement)
    {
        if ((int) $measurement->user_id === (int) auth()->id()) {
            return back()->withErrors([
                'verify' => 'You cannot verify a measurement you recorded yourself.',
            ]);
        }

        DB::transaction(function () use ($measurement, $changeLogger) {
            // Set programmatically via direct attribute assignment, not
            // mass-assigned through update(), so verified_at can never be
            // supplied by a caller.
            $measurement->verified_by_user_id = auth()->id();
            $measurement->verified_at = now();
            $measurement->save();

            $changeLogger->record(
                loggableType: 'TreePlantingMeasurement',
                loggableId: $measurement->id,
                action: 'measurement_verified',
                old: null,
                new: [
                    'verified_by_user_id' => $measurement->verified_by_user_id,
                    'verified_at'         => $measurement->verified_at->toISOString(),
                ],
            );
        });

        return redirect()
            ->route('tree-planting-measurements.index', $measurement->tree_planting_id)
            ->with('success', 'Measurement verified.');
    }

    private function validateMeasurement(Request $request, TreePlanting $treePlanting): array
    {
        return $request->validate([
            'measurement_date' => [
                'required',
                'date',
                'after_or_equal:'.$treePlanting->planting_date->toDateString(),
                'before_or_equal:'.now()->toDateString(),
            ],
            'trees_surviving' => [
                'nullable',
                'integer',
                'min:0',
                'max:'.$treePlanting->number_of_trees,
            ],
            'height_avg_cm'    => 'nullable|numeric|min:0',
            'dbh_avg_cm'       => 'nullable|numeric|min:0',
            'canopy_cover_pct' => 'nullable|numeric|min:0|max:100',
            'notes'            => 'nullable|string',
        ]);
    }
}
