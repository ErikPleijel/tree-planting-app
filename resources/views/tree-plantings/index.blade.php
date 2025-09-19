<x-app-layout>
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <h1 class="text-2xl font-bold mb-4 text-center">Tree Plantings</h1>


        <div class="overflow-x-auto">
            <table class="w-full text-sm border-collapse bg-white">
                <thead>
                <tr class="border-b bg-gray-50">
                    <th class="px-4 py-2 text-left">ID</th>
                    <th class="px-4 py-2 text-left">Date</th>
                    <th class="px-4 py-2 text-left">Location</th>
                    <th class="px-4 py-2 text-left">Tree Type</th>
                    <th class="px-4 py-2 text-left">#</th>
                    <th class="px-4 py-2 text-left">Status</th>
                    <th class="px-4 py-2 text-left">Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach($treePlantings as $index => $planting)
                    <tr class="border-b {{ $index % 2 === 0 ? 'bg-white' : 'bg-gray-50' }}">
                        <td class="px-4 py-2">{{ $planting->id }}</td>
                        <td class="px-4 py-2">{{ $planting->planting_date->format('Y-m-d') }}</td>
                        <td class="px-4 py-2">{{ $planting->plantingLocation->location ?? 'N/A' }}</td>
                        <td class="px-4 py-2">{{ $planting->treeType->name ?? 'N/A' }}</td>
                        <td class="px-4 py-2">{{ $planting->number_of_trees }}</td>
                        <td class="px-4 py-2">{{ $planting->statusRelation->tree_planting_status ?? 'N/A' }}</td>
                        <td class="px-4 py-2">
                            <div class="flex flex-wrap gap-1">
                                <a href="{{ route('planting-locations.show', $planting->plantingLocation) }}" class="bg-blue-500 text-white px-2 py-1 text-xs rounded hover:bg-blue-600 transition-colors">View {{ $planting->plantingLocation->location ?? 'N/A' }}</a>
                            </div>
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
