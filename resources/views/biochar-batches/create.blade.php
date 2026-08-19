<x-app-layout>
    <div class="container max-w-2xl mx-auto mt-6 p-4">
        <h1 class="text-xl font-bold text-center text-gray-800 mb-4">Add Biochar Batch</h1>

        <div class="bg-white shadow rounded-lg p-6">
            <div class="mb-4">
                <label class="font-semibold">Location</label>
                <div class="p-2 border rounded bg-gray-50">
                    {{ $plantingLocation->location }}
                </div>
            </div>

            <form method="POST" action="{{ route('biochar-batches.store') }}">
                @csrf
                <input type="hidden" name="planting_location_id" value="{{ $plantingLocation->id }}">

                <div class="mb-4">
                    <label class="block font-semibold mb-1">Applied To</label>
                    <select name="tree_planting_id" class="w-full p-2 border rounded @error('tree_planting_id') border-red-500 @enderror">
                        <option value="" @selected(old('tree_planting_id') === null || old('tree_planting_id') === '')>— Whole location / no specific planting —</option>
                        @foreach($plantingLocation->treePlantings as $planting)
                            <option value="{{ $planting->id }}" @selected(old('tree_planting_id') == $planting->id)>
                                {{ $planting->treeType->name ?? 'N/A' }} — {{ \Carbon\Carbon::parse($planting->planting_date)->format('Y-m-d') }} ({{ $planting->number_of_trees }} trees)
                            </option>
                        @endforeach
                    </select>
                    @error('tree_planting_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    @error('planting_location_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="block font-semibold mb-1">Quantity (kg)</label>
                    <input type="number" step="0.01" min="0" name="quantity_kg"
                           class="w-full p-2 border rounded @error('quantity_kg') border-red-500 @enderror"
                           value="{{ old('quantity_kg') }}" required>
                    @error('quantity_kg')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="block font-semibold mb-1">Source</label>
                    <input type="text" name="source"
                           class="w-full p-2 border rounded @error('source') border-red-500 @enderror"
                           value="{{ old('source') }}"
                           placeholder="e.g. coconut husk, invasive species clearing"
                           required>
                    @error('source')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="block font-semibold mb-1">Batch Reference (optional)</label>
                    <input type="text" name="batch_reference"
                           class="w-full p-2 border rounded @error('batch_reference') border-red-500 @enderror"
                           value="{{ old('batch_reference') }}">
                    @error('batch_reference')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="block font-semibold mb-1">Application Date</label>
                    <input type="date" name="application_date"
                           class="w-full p-2 border rounded @error('application_date') border-red-500 @enderror"
                           value="{{ old('application_date', now()->format('Y-m-d')) }}"
                           required>
                    @error('application_date')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
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
                    <a href="{{ route('biochar-batches.index', $plantingLocation) }}"
                       class="px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-50 transition-colors">Cancel</a>
                    <button type="submit" class="px-4 py-2 bg-amber-700 text-white rounded hover:bg-amber-800 transition-colors">Save Batch</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
