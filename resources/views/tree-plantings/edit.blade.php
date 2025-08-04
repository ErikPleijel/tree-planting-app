<x-app-layout>
    <div class="max-w-2xl mx-auto mt-10 p-6 bg-white shadow-md rounded-lg">

        <h1 class="text-xl font-bold text-center text-gray-600 mb-1">Edit Trees in</h1>
        <p class="text-center text-2xl text-gray-800 mb-4">{{ $treePlanting->plantingLocation->location }}</p>


        <form method="POST" action="{{ route('tree-plantings.update', $treePlanting) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <!-- Add hidden input for planting_location_id -->
            <input type="hidden" name="planting_location_id" value="{{ $treePlanting->planting_location_id }}">

            <!-- Planting Date -->
            <div>
                <label class="label" for="planting_date">
                    <span class="label-text">Planting Date</span>
                </label>
                <input type="date" id="planting_date" name="planting_date"
                       class="input input-bordered w-full"
                       value="{{ old('planting_date', $treePlanting->planting_date->format('Y-m-d')) }}" required>
            </div>

            <!-- Number of Trees -->
            <div>
                <label class="label" for="number_of_trees">
                    <span class="label-text">Number of Trees</span>
                </label>
                <input type="number" id="number_of_trees" name="number_of_trees"
                       class="input input-bordered w-full"
                       value="{{ old('number_of_trees', $treePlanting->number_of_trees) }}" required>
            </div>

            <!-- Tree Type -->
            <div>
                <label class="label" for="tree_type_id">
                    <span class="label-text">Tree Type</span>
                </label>
                <select id="tree_type_id" name="tree_type_id" class="select select-bordered w-full" required>
                    <option value="">-- Select Tree Type --</option>
                    @foreach($treeTypes as $type)
                        <option value="{{ $type->id }}" @if(old('tree_type_id', $treePlanting->tree_type_id)==$type->id) selected @endif>
                            {{ $type->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Status -->
            @role('Admin|SuperAdmin|Monitor')
            <div>
                <label class="label" for="status">
                    <span class="label-text">Status</span>
                </label>
                <select id="status" name="status" class="select select-bordered w-full" required>
                    <option value="">-- Select Status --</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status->id }}" @if(old('status', $treePlanting->status)==$status->id) selected @endif>
                            {{ $status->tree_planting_status }}
                        </option>
                    @endforeach
                </select>
            </div>
        @else
            <input type="hidden" name="status" value="{{ $treePlanting->status }}">
        @endrole

            <!-- Buttons -->
            <div class="flex justify-end space-x-2 pt-4">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="{{ route('planting-locations.show', $treePlanting->planting_location_id) }}" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
