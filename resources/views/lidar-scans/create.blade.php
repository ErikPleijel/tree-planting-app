<x-app-layout>
    <div class="container max-w-2xl mx-auto mt-6 p-4">
        <h1 class="text-xl font-bold text-center text-gray-800 mb-4">Attach LiDAR Scan</h1>

        <div class="bg-white shadow rounded-lg p-6">
            @if($measurement)
                <div class="mb-4">
                    <label class="font-semibold">Measurement</label>
                    <div class="p-2 border rounded bg-gray-50">
                        {{ $measurement->measurement_date->format('Y-m-d') }} —
                        {{ $measurement->treePlanting->treeType->name ?? 'N/A' }}
                        at {{ $measurement->treePlanting->plantingLocation->location ?? 'N/A' }}
                    </div>
                </div>
            @else
                <p class="text-sm text-gray-500 mb-4">
                    Not linked to a specific measurement yet — this scan will be saved
                    on its own and can be referenced later.
                </p>
            @endif

            <form method="POST" action="{{ route('lidar-scans.store') }}" enctype="multipart/form-data">
                @csrf

                <input type="hidden" name="tree_planting_measurement_id" value="{{ $measurement->id ?? old('tree_planting_measurement_id') }}">

                <div class="mb-4">
                    <label class="block font-semibold mb-1">Scan Source</label>
                    <select name="scan_source"
                            class="w-full p-2 border rounded @error('scan_source') border-red-500 @enderror">
                        <option value="">— Select —</option>
                        <option value="phone_lidar" {{ old('scan_source') === 'phone_lidar' ? 'selected' : '' }}>Phone/Tablet LiDAR</option>
                        <option value="drone" {{ old('scan_source') === 'drone' ? 'selected' : '' }}>Drone</option>
                        <option value="satellite" {{ old('scan_source') === 'satellite' ? 'selected' : '' }}>Satellite</option>
                    </select>
                    @error('scan_source')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="block font-semibold mb-1">Scan File (mesh, point cloud, or report — optional)</label>
                    <input type="file" name="scan_file"
                           class="w-full p-2 border rounded @error('scan_file') border-red-500 @enderror">
                    <p class="text-xs text-gray-500 mt-1">
                        Accepted formats: USDZ, OBJ, PLY, LAS, LAZ, GLB, GLTF, PDF, ZIP. Max 100 MB.
                    </p>
                    @error('scan_file')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block font-semibold mb-1">Height (cm)</label>
                        <input type="number" step="0.01" min="0" name="lidar_height_cm"
                               class="w-full p-2 border rounded @error('lidar_height_cm') border-red-500 @enderror"
                               value="{{ old('lidar_height_cm') }}">
                        @error('lidar_height_cm')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block font-semibold mb-1">DBH Estimate (cm)</label>
                        <input type="number" step="0.01" min="0" name="lidar_dbh_estimate_cm"
                               class="w-full p-2 border rounded @error('lidar_dbh_estimate_cm') border-red-500 @enderror"
                               value="{{ old('lidar_dbh_estimate_cm') }}">
                        @error('lidar_dbh_estimate_cm')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block font-semibold mb-1">Canopy Area (m²)</label>
                        <input type="number" step="0.01" min="0" name="lidar_canopy_area_m2"
                               class="w-full p-2 border rounded @error('lidar_canopy_area_m2') border-red-500 @enderror"
                               value="{{ old('lidar_canopy_area_m2') }}">
                        @error('lidar_canopy_area_m2')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block font-semibold mb-1">Confidence</label>
                    <select name="scan_confidence"
                            class="w-full p-2 border rounded @error('scan_confidence') border-red-500 @enderror">
                        <option value="">— Not rated —</option>
                        <option value="high" {{ old('scan_confidence') === 'high' ? 'selected' : '' }}>High</option>
                        <option value="medium" {{ old('scan_confidence') === 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="low" {{ old('scan_confidence') === 'low' ? 'selected' : '' }}>Low</option>
                    </select>
                    @error('scan_confidence')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="block font-semibold mb-1">Method Note (optional)</label>
                    <textarea name="method_note"
                              class="w-full p-2 border rounded @error('method_note') border-red-500 @enderror"
                              rows="3"
                              placeholder="Which app was used, scan conditions, anything worth noting.">{{ old('method_note') }}</textarea>
                    @error('method_note')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-2">
                    @if($measurement)
                        <a href="{{ route('tree-planting-measurements.index', $measurement->tree_planting_id) }}"
                           class="px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-50 transition-colors">Cancel</a>
                    @endif
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">Upload Scan</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
