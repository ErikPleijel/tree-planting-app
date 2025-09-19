<x-app-layout>
    <div class="container max-w-2xl mx-auto mt-6 p-4">
        <h1 class="text-xl font-bold text-center text-gray-800 mb-4">Add Inspection</h1>

        <div class="bg-white shadow rounded-lg p-6">
            <div class="mb-4">
                <label class="font-semibold">Location</label>
                <div class="p-2 border rounded bg-gray-50">
                    {{ $plantingLocation->location }}
                </div>
            </div>

            <form method="POST" action="{{ route('inspections.store') }}">
                @csrf
                <input type="hidden" name="planting_location_id" value="{{ $plantingLocation->id }}">

                <div class="mb-4">
                    <label class="block font-semibold mb-1">Inspection Date</label>
                    <input type="date" name="inspection_date"
                           class="w-full p-2 border rounded @error('inspection_date') border-red-500 @enderror"
                           value="{{ old('inspection_date', now()->format('Y-m-d')) }}"
                           required>
                    @error('inspection_date')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="block font-semibold mb-1">Pass?</label>
                    <select name="verified"
                            class="w-full p-2 border rounded @error('verified') border-red-500 @enderror"
                            required>
                        <option value="">-- Select --</option>
                        <option value="1" @selected(old('verified') == "1")>Yes</option>
                        <option value="0" @selected(old('verified') == "0")>No</option>
                    </select>
                    @error('verified')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="block font-semibold mb-1">Comment</label>
                    <textarea name="comment"
                              class="w-full p-2 border rounded @error('comment') border-red-500 @enderror"
                              rows="4">{{ old('comment') }}</textarea>
                    @error('comment')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-2">
                    <a href="{{ route('planting-locations.show', $plantingLocation) }}"
                       class="px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-50 transition-colors">Cancel</a>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">Save Inspection</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
