<x-app-layout>
    <div class="container">
        <h1>Planting Locations</h1>

        <a href="{{ route('planting-locations.create') }}" class="btn btn-primary mb-3">Add New Location</a>

        <table class="table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Location</th>
                <th>Division</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @foreach($plantingLocations as $location)
                <tr>
                    <td>{{ $location->id }}</td>
                    <td>{{ $location->location }}</td>
                    <td>{{ $location->division->LGA_name ?? 'N/A' }}</td>
                    <td>{{ $location->status->planting_location_status ?? 'N/A' }}</td>
                    <td>
                        <a href="{{ route('planting-locations.show', $location) }}" class="btn btn-info btn-sm">View</a>
                        <a href="{{ route('planting-locations.edit', $location) }}" class="btn btn-warning btn-sm">Edit</a>
                        @if(auth()->user()->role->name === 'Admin')
                            <form action="{{ route('planting-locations.destroy', $location) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm" onclick="return confirm('Delete this location?')">Delete</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</x-app-layout>
