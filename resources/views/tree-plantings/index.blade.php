<x-app-layout>
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <h1 class="text-2xl font-bold mb-4 text-center">Tree Plantings</h1>

        <div class="flex justify-center mb-4">
            <a href="{{ route('tree-plantings.create') }}" class="btn btn-primary btn-sm">➕ Add New Planting</a>
        </div>

        <div class="overflow-x-auto">
            <table class="table table-zebra table-sm w-full text-sm">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Location</th>
                    <th>Tree Type</th>
                    <th>#</th>
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
                        <td>{{ $planting->statusRelation->tree_planting_status ?? 'N/A' }}</td>
                        <td class="flex flex-wrap gap-1">
                            <a href="{{ route('tree-plantings.show', $planting) }}" class="btn btn-info btn-xs">View</a>
                            <a href="{{ route('tree-plantings.edit', $planting) }}" class="btn btn-warning btn-xs">Edit</a>
                            <form action="{{ route('tree-plantings.destroy', $planting) }}" method="POST" onsubmit="return confirm('Delete this planting?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-error btn-xs">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
