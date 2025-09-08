<x-app-layout>
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <h1 class="text-2xl font-bold mb-4 text-center">Tree Plantings</h1>


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
                        <td>{{ $planting->planting_date->format('Y-m-d') }}</td>
                        <td>{{ $planting->plantingLocation->location ?? 'N/A' }}</td>
                        <td>{{ $planting->treeType->name ?? 'N/A' }}</td>
                        <td>{{ $planting->number_of_trees }}</td>
                        <td>{{ $planting->statusRelation->tree_planting_status ?? 'N/A' }}</td>
                        <td class="flex flex-wrap gap-1">
                            <a href="{{ route('planting-locations.show', $planting->plantingLocation) }}" class="btn btn-info btn-xs">View {{ $planting->plantingLocation->location ?? 'N/A' }}</a>

                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <!-- Add Pagination Links -->
        <div class="mt-6">
            {{ $treePlantings->links('custom.pagination') }}
        </div>

    </div>
</x-app-layout>
