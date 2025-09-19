<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Tree Plantings Report') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <!-- Filters Section -->
                    <div class="mb-6">
                        <form method="GET" action="{{ route('tree-plantings.report') }}" class="space-y-4">
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                                <!-- Tree Type Filter -->
                                <div>
                                    <label for="tree_type_id" class="block text-sm font-medium text-gray-700">Tree Type</label>
                                    <select name="tree_type_id" id="tree_type_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">All Types</option>
                                        @foreach($treeTypes as $type)
                                            <option value="{{ $type->id }}" {{ request('tree_type_id') == $type->id ? 'selected' : '' }}>
                                                {{ $type->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Division Filter -->
                                <div>
                                    <label for="division_id" class="block text-sm font-medium text-gray-700">Division</label>
                                    <select name="division_id" id="division_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">All Divisions</option>
                                        @foreach($divisions as $division)
                                            <option value="{{ $division->id }}" {{ request('division_id') == $division->id ? 'selected' : '' }}>
                                                {{ $division->LGA_name }}
                                            </option>

                                        @endforeach

                                    </select>
                                </div>

                                <!-- Status Filter -->
                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                                    <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">All Statuses</option>
                                        @foreach($statuses as $status)
                                            <option value="{{ $status->id }}" {{ request('status') == $status->id ? 'selected' : '' }}>
                                                {{ $status->tree_planting_status }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Search -->
                                <div>
                                    <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                           placeholder="Search by location...">
                                </div>
                            </div>

                            <div class="flex justify-end space-x-2">
                                <button type="submit" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    Filter Results
                                </button>

                                @if(request()->hasAny(['tree_type_id', 'division_id', 'status', 'search']))
                                    <a href="{{ route('tree-plantings.report') }}"
                                       class="inline-flex justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                        Reset Filters
                                    </a>
                                @endif
                            </div>
                        </form>
                    </div>

                    <!-- Results Table -->
                    <div class="overflow-x-auto">
<table class="min-w-full divide-y divide-gray-200">
    <thead class="bg-gray-50">
    <tr>
        <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
            Date
        </th>
        <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
            Age
        </th>
        <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
            Division
        </th>
        <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
            Location
        </th>
        <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
            Tree Type
        </th>
        <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
            Number of Trees
        </th>
        <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
            Status
        </th>
        <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
            Actions
        </th>
    </tr>
    </thead>
    <tbody class="bg-white divide-y divide-gray-200">
    @foreach($treePlantings as $planting)
        <tr>
            <td class="px-6 py-4 whitespace-nowrap">
    {{ $planting->planting_date->format('Y-m-d') }}
</td>
            <td class="px-6 py-4 whitespace-nowrap">
                @php
                    $plantingDate = \Carbon\Carbon::parse($planting->planting_date);
                    $now = \Carbon\Carbon::now();

                    $diff = $plantingDate->diff($now);
                    $years = $diff->y;
                    $months = $diff->m;
                    $days = $diff->d;
                @endphp
                @if($years > 0)
                    {{ $years }} {{ Str::plural('year', $years) }}
                @endif
                @if($months > 0)
                    {{ $years > 0 ? ', ' : '' }}{{ $months }} {{ Str::plural('month', $months) }}
                @endif
                @if($days > 0)
                    {{ ($years > 0 || $months > 0) ? ', ' : '' }}{{ $days }} {{ Str::plural('day', $days) }}
                @endif
                @if($years === 0 && $months === 0 && $days === 0)
                    Planted today
                @endif
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                {{ $planting->plantingLocation->division->LGA_name }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                {{ $planting->plantingLocation->location }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                {{ $planting->treeType->name }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                {{ $planting->number_of_trees }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                {{ $planting->statusRelation->tree_planting_status }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
    <a href="{{ route('planting-locations.show', $planting->planting_location_id) }}"
       class="bg-blue-500 text-white px-3 py-1 text-sm rounded hover:bg-blue-600 transition-colors">
        View
    </a>
</td>
        </tr>
    @endforeach
    </tbody>
</table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4">
                        {{ $treePlantings->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
