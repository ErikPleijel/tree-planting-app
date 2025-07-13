<x-app-layout>
    <div class="max-w-5xl mx-auto px-4 py-6">
        <h1 class="text-3xl font-bold text-center text-gray-800 mb-6">Planting Locations</h1>

        <div class="text-right mb-4">
            <a href="{{ route('planting-locations.create') }}"
               class="btn btn-primary">
                Add New Location
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="table w-auto mx-auto text-sm bg-white shadow-lg rounded-lg">
                <thead class="bg-gray-100">
                <tr>
                    <th class="px-6 py-3 text-left whitespace-nowrap">ID</th>
                    <th class="px-6 py-3 text-left whitespace-nowrap">Location</th>
                    <th class="px-6 py-3 text-left whitespace-nowrap">Division</th>
                    <th class="px-6 py-3 text-left whitespace-nowrap">Status</th>
                    <th class="px-6 py-3 text-left whitespace-nowrap">Total Trees</th>
                    <th class="px-6 py-3 text-left whitespace-nowrap">Actions</th>

                </tr>
                </thead>
                <tbody>
                @foreach($plantingLocations as $location)
                    <tr class="border-t hover:bg-gray-50 transition">
                        <td class="px-6 py-3">{{ $location->id }}</td>
                        <td class="px-6 py-3">{{ $location->location }}</td>
                        <td class="px-6 py-3">{{ $location->division->LGA_name ?? 'N/A' }}</td>
                        <td class="px-6 py-3">{{ $location->statusRelation->planting_location_status ?? 'N/A' }}</td>
                        <td class="px-6 py-3 text-center">{{ $location->total_trees ?? 0 }}</td>

                        <td class="px-6 py-3 space-x-2">
                            <a href="{{ route('planting-locations.show', $location) }}" class="btn btn-sm btn-info">View</a>

                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
