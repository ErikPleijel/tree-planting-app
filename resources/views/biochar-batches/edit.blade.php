<x-app-layout>
    <div class="max-w-2xl mx-auto mt-10 p-6 bg-white shadow-md rounded-lg">
        <h1 class="text-xl font-bold text-center text-gray-800 mb-4">Edit Biochar Batch</h1>

        <div class="mb-4">
            <label class="font-semibold">Applied To</label>
            <div class="p-2 border rounded bg-gray-50">
                {{ $biocharBatch->plantingLocation->location ?? 'N/A' }}
                @if($biocharBatch->treePlanting)
                    — {{ $biocharBatch->treePlanting->treeType->name ?? 'N/A' }} ({{ $biocharBatch->treePlanting->planting_date->format('Y-m-d') }})
                @else
                    — whole location, no specific planting
                @endif
            </div>
        </div>

        <form method="POST" action="{{ route('biochar-batches.update', $biocharBatch) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Quantity (kg)</label>
                <input type="number" step="0.01" min="0" name="quantity_kg"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm @error('quantity_kg') border-red-500 @enderror"
                       value="{{ old('quantity_kg', $biocharBatch->quantity_kg) }}"
                       required>
                @error('quantity_kg')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Source</label>
                <input type="text" name="source"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm @error('source') border-red-500 @enderror"
                       value="{{ old('source', $biocharBatch->source) }}"
                       required>
                @error('source')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Batch Reference</label>
                <input type="text" name="batch_reference"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm @error('batch_reference') border-red-500 @enderror"
                       value="{{ old('batch_reference', $biocharBatch->batch_reference) }}">
                @error('batch_reference')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Application Date</label>
                <input type="date" name="application_date"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm @error('application_date') border-red-500 @enderror"
                       value="{{ old('application_date', $biocharBatch->application_date->format('Y-m-d')) }}"
                       required>
                @error('application_date')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea name="notes"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm h-24 resize-none @error('notes') border-red-500 @enderror">{{ old('notes', $biocharBatch->notes) }}</textarea>
                @error('notes')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end space-x-2 pt-4">
                <button type="submit" class="px-4 py-2 bg-amber-700 text-white text-sm rounded hover:bg-amber-800 transition-colors">Save Changes</button>
                <a href="{{ route('biochar-batches.index', $biocharBatch->planting_location_id) }}"
                   class="px-4 py-2 border border-gray-300 text-gray-700 text-sm rounded hover:bg-gray-50 transition-colors">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
