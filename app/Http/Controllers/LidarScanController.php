<?php

namespace App\Http\Controllers;

use App\Models\LidarScan;
use App\Models\TreePlantingMeasurement;
use App\Services\ChangeLogger;
use Illuminate\Http\Request;

class LidarScanController extends Controller
{
    /**
     * Fixed vocabulary for scan_confidence — enforced here (validation
     * layer), not at the DB level, same convention as
     * tree_planting_measurements.canopy_cover_pct's 0-100 range.
     */
    private const CONFIDENCE_LEVELS = ['high', 'medium', 'low'];

    /**
     * Scan export formats this app expects from phone/drone LiDAR apps
     * (mesh/point cloud files, or a report the app itself generated).
     * Checked via extension rather than Laravel's mimes: rule — several of
     * these (.usdz, .glb, .las, .laz) aren't in the default MIME-type
     * database Laravel/Symfony ship with, which would cause mimes: to
     * falsely reject legitimate files.
     */
    private const ALLOWED_SCAN_EXTENSIONS = ['usdz', 'obj', 'ply', 'las', 'laz', 'glb', 'gltf', 'pdf', 'zip'];

    /**
     * Guess pending real-world file samples from phone LiDAR scanning
     * apps. Photo uploads (PictureController) cap at 8 MB; scan exports
     * (mesh/point cloud) are typically much larger, so that limit isn't
     * appropriate here — 100 MB is a starting ceiling, not a measured one.
     */
    private const MAX_SCAN_FILE_KB = 100 * 1024;

    public function create(Request $request)
    {
        $measurement = null;
        $measurementId = $request->get('tree_planting_measurement_id');

        if ($measurementId) {
            // A stale/invalid link shouldn't block uploading an unlinked
            // scan — find(), not findOrFail().
            $measurement = TreePlantingMeasurement::with(['treePlanting.plantingLocation', 'treePlanting.treeType'])
                ->find($measurementId);
        }

        return view('lidar-scans.create', compact('measurement'));
    }

    public function store(Request $request, ChangeLogger $changeLogger)
    {
        $validated = $request->validate([
            'tree_planting_measurement_id' => ['nullable', 'exists:tree_planting_measurements,id'],
            'scan_source'                  => ['nullable', 'string', 'max:255'],
            'scan_file'                    => ['nullable', 'file', 'max:'.self::MAX_SCAN_FILE_KB],
            'lidar_height_cm'              => ['nullable', 'numeric', 'min:0'],
            'lidar_dbh_estimate_cm'        => ['nullable', 'numeric', 'min:0'],
            'lidar_canopy_area_m2'         => ['nullable', 'numeric', 'min:0'],
            'scan_confidence'              => ['nullable', 'string', 'in:'.implode(',', self::CONFIDENCE_LEVELS)],
            'method_note'                  => ['nullable', 'string'],
        ]);

        if ($request->hasFile('scan_file')) {
            $extension = strtolower($request->file('scan_file')->getClientOriginalExtension());

            if (! in_array($extension, self::ALLOWED_SCAN_EXTENSIONS, true)) {
                return back()->withErrors([
                    'scan_file' => 'Allowed formats: '.implode(', ', self::ALLOWED_SCAN_EXTENSIONS).'.',
                ])->withInput();
            }
        }

        $attributes = collect($validated)->except('scan_file')->all();
        $attributes['user_id'] = auth()->id();

        if ($request->hasFile('scan_file')) {
            $attributes['scan_file_path'] = $request->file('scan_file')->store('lidar-scans', 'public');
        }

        $scan = LidarScan::create($attributes);

        // measurement_source describes what produced the parent
        // measurement's numbers, not any one scan — see the LiDAR
        // Integration DECISIONS.md entry. Only flips 'manual' to
        // 'lidar_assisted' on link; never overwrites an already-set
        // non-manual source.
        if ($scan->tree_planting_measurement_id) {
            $measurement = TreePlantingMeasurement::find($scan->tree_planting_measurement_id);

            if ($measurement && $measurement->measurement_source === 'manual') {
                $measurement->measurement_source = 'lidar_assisted';
                $measurement->save();
            }
        }

        $changeLogger->record(
            loggableType: 'LidarScan',
            loggableId: $scan->id,
            action: 'lidar_scan_added',
            old: null,
            new: $attributes,
        );

        if ($scan->tree_planting_measurement_id) {
            return redirect()
                ->route('tree-planting-measurements.index', $scan->treePlantingMeasurement->tree_planting_id)
                ->with('success', 'LiDAR scan uploaded.');
        }

        return redirect()
            ->route('lidar-scans.create')
            ->with('success', 'LiDAR scan uploaded (not yet linked to a measurement).');
    }
}
