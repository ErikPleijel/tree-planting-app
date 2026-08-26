<x-app-layout>
    <div class="max-w-5xl mx-auto px-4 py-6">
        <h1 class="text-3xl font-bold text-center text-gray-800 mb-6">Planting Locations</h1>

        <div class="text-right mb-4">
            <a href="{{ route('planting-locations.create') }}"
               class="bg-primary text-white px-6 py-3 rounded-lg hover:bg-green-700 transition-colors">
                Add New Location
            </a>
        </div>

@php $hasFilters = request()->hasAny(['division', 'search', 'sort']); @endphp
<div class="filter-container">
    <div class="filter-form-content">
        <form action="{{ route('planting-locations.index') }}" method="GET" class="filter-form">
            <div class="filter-grid filter-grid-3">
                <div>
                    <label for="division" class="filter-label">Division</label>
                    <select name="division" id="division" class="filter-select {{ request('division') ? 'filter-active' : '' }}">
                        <option value="">All</option>
                        @foreach($divisions as $division)
                            <option value="{{ $division->id }}" {{ request('division') == $division->id ? 'selected' : '' }}>
                                {{ $division->LGA_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="search" class="filter-label">Search</label>
                    <input type="text"
                           id="search"
                           name="search"
                           placeholder="Search..."
                           value="{{ request('search') }}"
                           class="filter-input {{ request('search') ? 'filter-active' : '' }}">
                </div>

                <div>
                    <label for="sort" class="filter-label">Sort By</label>
                    <select name="sort" id="sort" class="filter-select {{ request('sort') === 'name_desc' ? 'filter-active' : '' }}">
                        <option value="name_asc" {{ request('sort', 'name_asc') === 'name_asc' ? 'selected' : '' }}>Name A → Z</option>
                        <option value="name_desc" {{ request('sort') === 'name_desc' ? 'selected' : '' }}>Name Z → A</option>
                    </select>
                </div>
            </div>

            <div class="filter-actions">
                <div class="filter-button-group">
                    <button type="submit" class="filter-btn-primary">
                        <i class="fas fa-search mr-1"></i>Filter
                    </button>
                    <a @if($hasFilters) href="{{ route('planting-locations.index') }}" @endif
                       class="filter-btn-secondary {{ $hasFilters ? 'filter-btn-secondary-active' : 'filter-btn-disabled' }}">
                        <i class="fas fa-times mr-1"></i>Clear
                    </a>
                </div>
            </div>
        </form>
    </div>
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
                            <a href="{{ route('planting-locations.show', $location) }}" class="bg-blue-500 text-white px-3 py-1 text-sm rounded hover:bg-blue-600 transition-colors">View</a>
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
                                            <td class="px-2 py-1 border">
                                                {{ $planting->statusRelation->tree_planting_status ?? 'N/A' }}
                                                @if($planting->statusRelation?->tree_planting_status === 'Verified' && $planting->statusUpdatedBy)
                                                    <span class="text-gray-500 text-xs">by {{ $planting->statusUpdatedBy->name }}</span>
                                                @endif
                                            </td>
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
