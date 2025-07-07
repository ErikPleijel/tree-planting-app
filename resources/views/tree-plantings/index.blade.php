<x-app-layout>
    <div class="container">
        <h1>Tree Plantings</h1>

        <a href="{{ route('tree-plantings.create') }}" class="btn btn-primary mb-3">Add New Planting</a>

        <table class="table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Date</th>
                <th>Location</th>
                <th>Tree Type</th>
                <th>Number</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @foreach($treePlantings as $planting)
                <tr>
                    <td>{{ $planting->id }}</td>
                    <td>{{ $planting->planting_date }}</td>
                    <td>{{ $planting->plantingLocation->location ?? 'N/A' }}</td>
                    <td>{{ $planting->treeType->name ?? 'N/A' }}</td>
                    <td>{{ $planting->number_of_trees }}</td>
                    <td>{{ $planting->status->tree_planting_status ?? 'N/A' }}</td>
                    <td>
                        <a href="{{ route('tree-plantings.show', $planting) }}" class="btn btn-info btn-sm">View</a>
                        <a href="{{ route('tree-plantings.edit', $planting) }}" class="btn btn-warning btn-sm">Edit</a>
                        <form action="{{ route('tree-plantings.destroy', $planting) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm" onclick="return confirm('Delete this planting?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</x-app-layout>
