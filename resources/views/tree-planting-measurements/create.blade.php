<x-app-layout>
    <div class="container max-w-2xl mx-auto mt-6 p-4">
        <h1 class="text-xl font-bold text-center text-gray-800 mb-4">Add Measurement</h1>

        <div class="bg-white shadow rounded-lg p-6">
            <div class="mb-4">
                <label class="font-semibold">Planting</label>
                <div class="p-2 border rounded bg-gray-50">
                    {{ $treePlanting->treeType->name ?? 'N/A' }} — planted {{ \Carbon\Carbon::parse($treePlanting->planting_date)->format('Y-m-d') }}
                    at {{ $treePlanting->plantingLocation->location ?? 'N/A' }}
                </div>
            </div>

            <form method="POST" action="{{ route('tree-planting-measurements.store', $treePlanting) }}">
                @csrf

                <div class="mb-4">
                    <label class="block font-semibold mb-1">Measurement Date</label>
                    <input type="date" name="measurement_date"
                           class="w-full p-2 border rounded @error('measurement_date') border-red-500 @enderror"
                           value="{{ old('measurement_date', now()->format('Y-m-d')) }}"
                           required>
                    @error('measurement_date')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="block font-semibold mb-1">Trees Surviving</label>
                    <input type="number" name="trees_surviving" min="0" max="{{ $treePlanting->number_of_trees }}"
                           class="w-full p-2 border rounded @error('trees_surviving') border-red-500 @enderror"
                           value="{{ old('trees_surviving') }}"
                           placeholder="Out of {{ $treePlanting->number_of_trees }} planted">
                    @error('trees_surviving')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block font-semibold mb-1">Avg Height (cm)</label>
                        <input type="number" step="0.01" min="0" name="height_avg_cm"
                               class="w-full p-2 border rounded @error('height_avg_cm') border-red-500 @enderror"
                               value="{{ old('height_avg_cm') }}">
                        @error('height_avg_cm')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block font-semibold mb-1">Avg DBH (cm)</label>
                        <input type="number" step="0.01" min="0" name="dbh_avg_cm"
                               class="w-full p-2 border rounded @error('dbh_avg_cm') border-red-500 @enderror"
                               value="{{ old('dbh_avg_cm') }}">
                        @error('dbh_avg_cm')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block font-semibold mb-1">Canopy Cover (%)</label>
                        <input type="number" step="0.01" min="0" max="100" name="canopy_cover_pct"
                               class="w-full p-2 border rounded @error('canopy_cover_pct') border-red-500 @enderror"
                               value="{{ old('canopy_cover_pct') }}">
                        @error('canopy_cover_pct')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block font-semibold mb-1">Notes (optional)</label>
                    <textarea name="notes"
                              class="w-full p-2 border rounded @error('notes') border-red-500 @enderror"
                              rows="3">{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-2">
                    <a href="{{ route('tree-planting-measurements.index', $treePlanting) }}"
                       class="px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-50 transition-colors">Cancel</a>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">Save Measurement</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
