<x-app-layout>
    <div class="max-w-5xl mx-auto px-4 py-6">
        <h1 class="text-3xl font-bold text-center text-gray-800 mb-6">Planting Locations</h1>

        <div class="text-right mb-4">
            <a href="{{ route('planting-locations.create') }}"
               class="btn btn-primary">
                Add New Location
            </a>
        </div>

<div class="mb-6 flex justify-center"> <!-- Center the form container -->
    <form action="{{ route('planting-locations.index') }}" method="GET" class="flex items-center gap-4"> <!-- Center items vertically and remove flex-1 -->
        <!-- Division Filter -->
        <div class="w-[150px] text-center"> <!-- Add text-center -->
            <select name="division" class="select select-bordered w-full text-center"> <!-- Add text-center -->
                <option value="">All</option>
                @foreach($divisions as $division)
                    <option value="{{ $division->id }}" {{ request('division') == $division->id ? 'selected' : '' }}>
                        {{ $division->LGA_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Search Input -->
        <div class="w-[150px] text-center"> <!-- Add text-center -->
            <input type="text"
                   name="search"
                   placeholder="Search..."
                   value="{{ request('search') }}"
                   class="input input-bordered w-full text-center"> <!-- Add text-center -->
        </div>

        <!-- Filter Button -->
        <button type="submit" class="btn btn-secondary">
            Filter
        </button>

        <!-- Reset Button -->
        @if(request()->hasAny(['division', 'search']))
            <a href="{{ route('planting-locations.index') }}" class="btn btn-ghost">
                Reset
            </a>
        @endif
    </form>
</div>

        <div class="overflow-x-auto">
            <table class="table w-auto mx-auto text-sm bg-white shadow-lg rounded-lg">
                <thead class="bg-gray-100 hidden">
                <tr>
                    {{--<th class="px-6 py-3 text-left whitespace-nowrap">ID</th>--}}
                    <th class="px-6 py-3 text-left whitespace-nowrap text-lg">Location</th>
                    <th class="px-6 py-3 text-left whitespace-nowrap text-lg">Division</th>
                    {{--<th class="px-6 py-3 text-left whitespace-nowrap">Status</th>--}}
                    <th class="px-6 py-3 text-left whitespace-nowrap text-lg">Total Trees</th>
                    <th class="px-6 py-3 text-left whitespace-nowrap text-lg"></th>

                </tr>
                </thead>
                <tbody>
                @foreach($plantingLocations as $location)
                    <tr class="border-t hover:bg-gray-50 transition">
                        {{-- <td class="px-6 py-3">{{ $location->id }}</td> --}}
                        <td class="px-6 py-2 font-bold text-2xl">{{ $location->location }}</td>
                        <td class="px-6 py-2 font-bold text-lg">{{ $location->division->LGA_name ?? 'N/A' }}</td>
                        {{-- <td class="px-6 py-3">{{ $location->statusRelation->planting_location_status ?? 'N/A' }}</td> --}}
                        <td class="px-6 py-2 text-center hidden">{{ $location->total_trees ?? 0 }}</td>
                        <td class="px-6 py-2 space-x-2">
                            <a href="{{ route('planting-locations.show', $location) }}" class="btn btn-sm btn-info">View</a>
                        </td>
                    </tr>
                    {{-- Subtable for Tree Plantings --}}
                    <tr>
                        <td colspan="6" class="pt-0 pb-0"> <!-- Remove padding -->
                            @if($location->treePlantings->count())
                                <table class="w-full text-sm border">
                                    <thead class="bg-gray-100 hidden">
                                    <tr>
                                        {{-- <th class="px-4 py-2 border">ID</th> --}}
                                        <th class="px-4 py-2 border">Planting Date</th>
                                        <th class="px-4 py-2 border"></th>
                                        <th class="px-4 py-2 border">Trees</th>
                                        <th class="px-4 py-2 border">Status</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($location->treePlantings as $planting)
                                        <tr class="hover:bg-gray-50">
                                            {{--<td class="px-4 py-2 border">{{ $planting->id }}</td>--}}
                                            <td class="px-2 py-1 border">{{ $planting->planting_date->format('Y-m-d') }}</td>
                                            <td class="px-2 py-1 border text-right">{{ $planting->number_of_trees }}</td>
                                            <td class="px-2 py-1 border">{{ $planting->treeType->name ?? 'N/A' }}</td>
                                            <td class="px-2 py-1 border">{{ $planting->statusRelation->tree_planting_status ?? 'N/A' }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            @else
                                <p class="text-gray-500">No tree plantings recorded for this location.</p>
                            @endif
                        </td>
                    </tr>
                    <tr> <!-- Wrap the total in proper tr -->
                        <td class="px-6 py-1 text-center" colspan="6"> <!-- Changed colspan to 6 to match table structure -->
                            <div class="relative top-[-4px] text-lg font-bold flex justify-center w-full border border-gray-300 rounded">
                                Total trees: {{ $location->total_trees ?? 0 }}
                            </div>
                        </td>
                    </tr>
                    <tr class="h-8"></tr>


                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">
        {{ $plantingLocations->links('custom.pagination') }}
    </div>
</x-app-layout>
