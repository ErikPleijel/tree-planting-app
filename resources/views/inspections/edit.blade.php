<x-app-layout>
    <div class="max-w-xl mx-auto mt-10 p-6 bg-white shadow-md rounded-lg">
        <h1 class="text-xl font-bold text-center text-gray-800 mb-4">Edit Inspection</h1>

        <form method="POST" action="{{ route('inspections.update', $inspection) }}" class="space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" name="planting_location_id" value="{{ $inspection->planting_location_id }}">

            <!-- Inspection Date -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Inspection Date
                </label>
                <input type="date"
                       name="inspection_date"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       value="{{ old('inspection_date', $inspection->inspection_date) }}"
                       required>
            </div>

            <!-- Verified Status -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Pass?
                </label>
                <select name="verified" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                    <option value="">-- Select --</option>
                    <option value="1" @if(old('verified', $inspection->verified)==="1" || $inspection->verified==1) selected @endif>Yes</option>
                    <option value="0" @if(old('verified', $inspection->verified)==="0" || $inspection->verified==0) selected @endif>No</option>
                </select>
            </div>

            <!-- Comment -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Comment
                </label>
                <textarea name="comment"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 h-24 resize-none"
                          placeholder="Add any relevant comments...">{{ old('comment', $inspection->comment) }}</textarea>
            </div>

            <!-- Buttons -->
            <div class="flex justify-end space-x-2 pt-4">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 transition-colors">Save Changes</button>
                <a href="{{ route('planting-locations.show', $inspection->planting_location_id) }}"
                   class="px-4 py-2 border border-gray-300 text-gray-700 text-sm rounded hover:bg-gray-50 transition-colors">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
