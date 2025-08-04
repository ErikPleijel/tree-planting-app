<x-app-layout>
    <div class="max-w-xl mx-auto mt-10 p-6 bg-white shadow-md rounded-lg">
        <h1 class="text-xl font-bold text-center text-gray-600 mb-1">Add Trees in</h1>
        <p class="text-center text-2xl text-gray-800 mb-4">{{ $location->location }}</p>

        <form method="POST" action="{{ route('tree-plantings.store') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="planting_location_id" value="{{ $location->id }}">

            <!-- Planting Date -->
            <div>
                <label class="label">
                    <span class="label-text">Planting Date</span>
                </label>
                <input type="date" name="planting_date" class="input input-bordered w-full"
                       value="{{ old('planting_date', now()->toDateString()) }}" required>
            </div>

            <!-- Number of Trees -->
            <div>
                <label class="label">
                    <span class="label-text">Number of Trees</span>
                </label>
                <input type="number" name="number_of_trees" class="input input-bordered w-full"
                       value="{{ old('number_of_trees') }}" required>
            </div>

            <!-- Tree Type -->
            <div>
                <label class="label">
                    <span class="label-text">Tree Type</span>
                </label>
                <select name="tree_type_id" class="select select-bordered w-full" required>
                    <option value="">-- Select Tree Type --</option>
                    @foreach($treeTypes as $type)
                        <option value="{{ $type->id }}" @if(old('tree_type_id')==$type->id) selected @endif>
                            {{ $type->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Status -->
            @role('Admin|SuperAdmin|Monitor')
                <div>
                    <label class="label">
                        <span class="label-text">Status</span>
                    </label>
                    <select name="status" class="select select-bordered w-full" required>
                        <option value="">-- Select Status --</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status->id }}" @if(old('status')==$status->id) selected @endif>
                                {{ $status->tree_planting_status }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @else
                <input type="hidden" name="status" value="1">
            @endrole

            <!-- Buttons -->
            <div class="flex justify-end space-x-2 pt-4">
                <button type="submit" class="btn btn-primary btn-sm">Save</button>
                <a href="{{ route('planting-locations.show', $location->id) }}" class="btn btn-outline btn-sm">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
