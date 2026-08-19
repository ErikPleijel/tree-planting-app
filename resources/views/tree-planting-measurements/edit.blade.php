<x-app-layout>
    <div class="max-w-2xl mx-auto mt-10 p-6 bg-white shadow-md rounded-lg">
        <h1 class="text-xl font-bold text-center text-gray-800 mb-4">Edit Measurement</h1>

        <div class="mb-4">
            <label class="font-semibold">Planting</label>
            <div class="p-2 border rounded bg-gray-50">
                {{ $treePlanting->treeType->name ?? 'N/A' }} — planted {{ \Carbon\Carbon::parse($treePlanting->planting_date)->format('Y-m-d') }}
                at {{ $treePlanting->plantingLocation->location ?? 'N/A' }}
            </div>
        </div>

        @if($measurement->verified_at)
            <div class="mb-4 bg-yellow-50 border border-yellow-300 text-yellow-800 text-sm px-3 py-2 rounded">
                ✓ Currently verified by {{ $measurement->verifiedBy->name ?? 'N/A' }} on {{ $measurement->verified_at->format('Y-m-d') }}.
                Changing any value below will clear this verification — it will need to be re-verified.
            </div>
        @endif

        <form method="POST" action="{{ route('tree-planting-measurements.update', $measurement) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Measurement Date</label>
                <input type="date" name="measurement_date"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm @error('measurement_date') border-red-500 @enderror"
                       value="{{ old('measurement_date', $measurement->measurement_date->format('Y-m-d')) }}"
                       required>
                @error('measurement_date')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Trees Surviving</label>
                <input type="number" name="trees_surviving" min="0" max="{{ $treePlanting->number_of_trees }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm @error('trees_surviving') border-red-500 @enderror"
                       value="{{ old('trees_surviving', $measurement->trees_surviving) }}">
                @error('trees_surviving')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Avg Height (cm)</label>
                    <input type="number" step="0.01" min="0" name="height_avg_cm"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm @error('height_avg_cm') border-red-500 @enderror"
                           value="{{ old('height_avg_cm', $measurement->height_avg_cm) }}">
                    @error('height_avg_cm')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Avg DBH (cm)</label>
                    <input type="number" step="0.01" min="0" name="dbh_avg_cm"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm @error('dbh_avg_cm') border-red-500 @enderror"
                           value="{{ old('dbh_avg_cm', $measurement->dbh_avg_cm) }}">
                    @error('dbh_avg_cm')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Canopy Cover (%)</label>
                    <input type="number" step="0.01" min="0" max="100" name="canopy_cover_pct"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm @error('canopy_cover_pct') border-red-500 @enderror"
                           value="{{ old('canopy_cover_pct', $measurement->canopy_cover_pct) }}">
                    @error('canopy_cover_pct')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea name="notes"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm h-24 resize-none @error('notes') border-red-500 @enderror"
                          placeholder="Add any relevant notes...">{{ old('notes', $measurement->notes) }}</textarea>
                @error('notes')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end space-x-2 pt-4">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 transition-colors">Save Changes</button>
                <a href="{{ route('tree-planting-measurements.index', $treePlanting) }}"
                   class="px-4 py-2 border border-gray-300 text-gray-700 text-sm rounded hover:bg-gray-50 transition-colors">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
