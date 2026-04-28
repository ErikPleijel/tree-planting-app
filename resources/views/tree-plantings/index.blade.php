<x-app-layout>
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <h1 class="text-4xl font-bold mb-4 text-center">Tree Plantings</h1>


        {{-- Filters --}}
        <form method="GET" action="{{ route('tree-plantings.index') }}" class="mb-4 flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Tree Type</label>
                <select name="tree_type_id" class="border border-gray-300 rounded px-3 py-2 text-sm focus:ring-primary focus:border-primary">
                    <option value="">All tree types</option>
                    @foreach($treeTypes as $type)
                        <option value="{{ $type->id }}" {{ request('tree_type_id') == $type->id ? 'selected' : '' }}>
                            {{ $type->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                <select name="status" class="border border-gray-300 rounded px-3 py-2 text-sm focus:ring-primary focus:border-primary">
                    <option value="">All</option>
                    @foreach($statuses as $s)
                        <option value="{{ $s->id }}" {{ request('status') == $s->id ? 'selected' : '' }}>
                            {{ $s->tree_planting_status }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Sort by</label>
                <select name="sort" class="border border-gray-300 rounded px-3 py-2 text-sm focus:ring-primary focus:border-primary">
                    <option value="date_asc"      {{ request('sort', 'date_asc') === 'date_asc'      ? 'selected' : '' }}>Date: Oldest First</option>
                    <option value="date_desc"     {{ request('sort') === 'date_desc'                  ? 'selected' : '' }}>Date: Newest First</option>
                    <option value="tree_type_asc" {{ request('sort') === 'tree_type_asc'              ? 'selected' : '' }}>Tree Type: A → Z</option>
                    <option value="tree_type_desc"{{ request('sort') === 'tree_type_desc'             ? 'selected' : '' }}>Tree Type: Z → A</option>
                    <option value="location_asc"  {{ request('sort') === 'location_asc'              ? 'selected' : '' }}>Location: A → Z</option>
                    <option value="location_desc" {{ request('sort') === 'location_desc'              ? 'selected' : '' }}>Location: Z → A</option>
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="bg-primary text-white px-4 py-2 rounded text-sm hover:bg-green-700 transition-colors">
                    Apply
                </button>
                @if(request()->hasAny(['tree_type_id', 'status', 'sort']))
                    <a href="{{ route('tree-plantings.index') }}" class="border border-gray-300 text-gray-700 px-4 py-2 rounded text-sm hover:bg-gray-50 transition-colors">
                        Reset
                    </a>
                @endif
            </div>
        </form>

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

        <div class="mt-6">
            {{ $treePlantings->links('custom.pagination') }}
        </div>
    </div>
</x-app-layout>
