
<x-app-layout>
    <div class="max-w-xl mx-auto mt-10 p-6 bg-white shadow-md rounded-lg">
        <h1 class="text-xl font-bold text-center text-gray-800 mb-4">Edit Inspection</h1>

        <form method="POST" action="{{ route('inspections.update', $inspection) }}" class="space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" name="planting_location_id" value="{{ $inspection->planting_location_id }}">

            <!-- Inspection Date -->
            <div>
                <label class="label">
                    <span class="label-text">Inspection Date</span>
                </label>
                <input type="date"
                       name="inspection_date"
                       class="input input-bordered w-full"
                       value="{{ old('inspection_date', $inspection->inspection_date) }}"
                       required>
            </div>

            <!-- Verified Status -->
            <div>
                <label class="label">
                    <span class="label-text">Pass?</span>
                </label>
                <select name="verified" class="select select-bordered w-full" required>
                    <option value="">-- Select --</option>
                    <option value="1" @if(old('verified', $inspection->verified)==="1" || $inspection->verified==1) selected @endif>Yes</option>
                    <option value="0" @if(old('verified', $inspection->verified)==="0" || $inspection->verified==0) selected @endif>No</option>
                </select>
            </div>

            <!-- Comment -->
            <div>
                <label class="label">
                    <span class="label-text">Comment</span>
                </label>
                <textarea name="comment"
                          class="textarea textarea-bordered w-full h-24"
                          placeholder="Add any relevant comments...">{{ old('comment', $inspection->comment) }}</textarea>
            </div>

            <!-- Buttons -->
            <div class="flex justify-end space-x-2 pt-4">
                <button type="submit" class="btn btn-primary btn-sm">Save Changes</button>
                <a href="{{ route('planting-locations.show', $inspection->planting_location_id) }}"
                   class="btn btn-outline btn-sm">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
