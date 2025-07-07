<x-app-layout>
    <div class="container">
        <h1>Add Tree Planting</h1>

        <form method="POST" action="{{ route('tree-plantings.store') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Planting Date</label>
                <input type="date" name="planting_date" class="form-control" value="{{ old('planting_date') }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Number of Trees</label>
                <input type="number" name="number_of_trees" class="form-control" value="{{ old('number_of_trees') }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Tree Type</label>
                <select name="tree_type_id" class="form-select" required>
                    <option value="">-- Select Tree Type --</option>
                    @foreach($treeTypes as $type)
                        <option value="{{ $type->id }}" @if(old('tree_type_id')==$type->id) selected @endif>
                            {{ $type->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Location</label>
                <select name="planting_location_id" class="form-select" required>
                    <option value="">-- Select Location --</option>
                    @foreach($locations as $location)
                        <option value="{{ $location->id }}" @if(old('planting_location_id')==$location->id) selected @endif>
                            {{ $location->location }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select" required>
                    <option value="">-- Select Status --</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status->id }}" @if(old('status')==$status->id) selected @endif>
                            {{ $status->tree_planting_status }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button class="btn btn-primary">Save Planting</button>
            <a href="{{ route('tree-plantings.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</x-app-layout>
