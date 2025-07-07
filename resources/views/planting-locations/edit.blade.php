<x-app-layout>
    <div class="container">
        <h1>Edit Planting Location</h1>

        <form method="POST" action="{{ route('planting-locations.update', $plantingLocation) }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">Location Name</label>
                <input type="text" name="location" class="form-control" value="{{ old('location', $plantingLocation->location) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Division</label>
                <select name="division_id" class="form-select" required>
                    <option value="">-- Select Division --</option>
                    @foreach($divisions as $division)
                        <option value="{{ $division->id }}" @if(old('division_id', $plantingLocation->division_id)==$division->id) selected @endif>
                            {{ $division->LGA_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select" required>
                    <option value="">-- Select Status --</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status->id }}" @if(old('status', $plantingLocation->status)==$status->id) selected @endif>
                            {{ $status->planting_location_status }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Comment</label>
                <textarea name="comment" class="form-control">{{ old('comment', $plantingLocation->comment) }}</textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Latitude</label>
                <input type="text" name="latitude" class="form-control" value="{{ old('latitude', $plantingLocation->latitude) }}">
            </div>

            <div class="mb-3">
                <label class="form-label">Longitude</label>
                <input type="text" name="longitude" class="form-control" value="{{ old('longitude', $plantingLocation->longitude) }}">
            </div>

            <button class="btn btn-primary">Save Changes</button>
            <a href="{{ route('planting-locations.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</x-app-layout>
