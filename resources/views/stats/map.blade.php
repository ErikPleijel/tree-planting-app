<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h2 class="text-2xl font-bold mb-6 text-center">Tree Planting Locations Map</h2>

                <!-- Map Component -->
                <div class="w-full mb-6">
                    <x-map
                        :markers="$markers"
                        :zoom="8"
                        :lat="9.75"
                        :lng="5.6"
                    />
                </div>

                <!-- Map Stats -->
                <div class="mt-6 flex justify-around items-center">
                    <div class="bg-blue-50 p-4 rounded-lg w-64">
                        <h3 class="font-semibold text-blue-700">Total Locations</h3>
                        <p class="text-2xl font-bold text-blue-800">{{ $totalLocations }}</p>
                    </div>
                    <div class="bg-blue-50 p-4 rounded-lg w-64">
                        <h3 class="font-semibold text-blue-700">Total Trees</h3>
                        <p class="text-2xl font-bold text-blue-800">{{ $totalTrees }}</p>
                    </div>
                </div>

                <!-- Planting Locations Table -->
                <div class="mt-8">
                    <h3 class="text-xl font-bold mb-4">Planting Locations</h3>

                    <!-- Search Form -->
                    <div class="mb-4">
                        <form action="{{ route('stats.map') }}" method="GET" class="flex gap-4">
                            <div class="w-[250px]">
                                <input type="text"
                                       name="search"
                                       value="{{ $search }}"
                                       placeholder="Search by location..."
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            </div>
                            <button type="submit"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                Search
                            </button>
                            @if($search)
                                <a href="{{ route('stats.map') }}"
                                   class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                                    Clear
                                </a>
                            @endif
                        </form>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white border border-gray-300">
    <thead>
        <tr class="bg-gray-100">
            <th class="px-6 py-3 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
            <th class="px-6 py-3 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">LGA</th>
            <th class="px-6 py-3 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Trees</th>
            @role('Admin|SuperAdmin|Monitor|Grower')
                <th class="px-6 py-3 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            @endrole
        </tr>
    </thead>
    <tbody class="bg-white divide-y divide-gray-200">
        @foreach($plantingLocations as $location)
            <tr>
                <td class="px-6 py-4 whitespace-nowrap">{{ $location->location }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ $location->division ? $location->division->LGA_name : 'N/A' }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ $location->treePlantings->sum('number_of_trees') }}</td>
                @role('Admin|SuperAdmin|Monitor|Grower')
                    <td class="px-6 py-4 whitespace-nowrap">
                        <a href="{{ route('planting-locations.show', $location->id) }}"
                           class="btn btn-sm btn-info">
                            View
                        </a>
                    </td>
                @endrole
            </tr>
        @endforeach
    </tbody>
</table>
                    </div>

                        <!-- Pagination -->
                        <div class="mt-4">
                            {{ $plantingLocations->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
