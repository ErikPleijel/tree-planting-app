<x-app-layout>
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <h1 class="text-4xl font-bold mb-4 text-center"><i class="fas fa-seedling mr-2"></i>Tree Plantings</h1>


        {{-- Filters --}}
        @php $hasFilters = request()->hasAny(['search', 'tree_type_id', 'status', 'sort']); @endphp
        <div class="filter-container">
            <div class="filter-form-content">
                <form method="GET" action="{{ route('tree-plantings.index') }}" class="filter-form">
                    <div class="filter-grid filter-grid-4">
                        <div>
                            <label for="search" class="filter-label">Search</label>
                            <input type="text"
                                   id="search"
                                   name="search"
                                   placeholder="Country, Location"
                                   value="{{ request('search') }}"
                                   class="filter-input {{ request('search') ? 'filter-active' : '' }}">
                        </div>

                        <div>
                            <label for="tree_type_id" class="filter-label">Tree Type</label>
                            <select name="tree_type_id" id="tree_type_id" class="filter-select {{ request('tree_type_id') ? 'filter-active' : '' }}">
                                <option value="">All tree types</option>
                                @foreach($treeTypes as $type)
                                    <option value="{{ $type->id }}" {{ request('tree_type_id') == $type->id ? 'selected' : '' }}>
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="status" class="filter-label">Status</label>
                            <select name="status" id="status" class="filter-select {{ request('status') ? 'filter-active' : '' }}">
                                <option value="">All</option>
                                @foreach($statuses as $s)
                                    <option value="{{ $s->id }}" {{ request('status') == $s->id ? 'selected' : '' }}>
                                        {{ $s->tree_planting_status }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="sort" class="filter-label">Sort by</label>
                            <select name="sort" id="sort" class="filter-select {{ request('sort') && request('sort') !== 'date_desc' ? 'filter-active' : '' }}">
                                <option value="date_desc"     {{ request('sort', 'date_desc') === 'date_desc'     ? 'selected' : '' }}>Date: Newest First</option>
                                <option value="date_asc"      {{ request('sort') === 'date_asc'                   ? 'selected' : '' }}>Date: Oldest First</option>
                                <option value="tree_type_asc" {{ request('sort') === 'tree_type_asc'              ? 'selected' : '' }}>Tree Type: A → Z</option>
                                <option value="tree_type_desc"{{ request('sort') === 'tree_type_desc'             ? 'selected' : '' }}>Tree Type: Z → A</option>
                                <option value="location_asc"  {{ request('sort') === 'location_asc'              ? 'selected' : '' }}>Location: A → Z</option>
                                <option value="location_desc" {{ request('sort') === 'location_desc'              ? 'selected' : '' }}>Location: Z → A</option>
                            </select>
                        </div>
                    </div>

                    <div class="filter-actions">
                        <div class="filter-button-group">
                            <button type="submit" class="filter-btn-primary">
                                <i class="fas fa-search mr-1"></i>Filter
                            </button>
                            <a @if($hasFilters) href="{{ route('tree-plantings.index') }}" @endif
                               class="filter-btn-secondary {{ $hasFilters ? 'filter-btn-secondary-active' : 'filter-btn-disabled' }}">
                                <i class="fas fa-times mr-1"></i>Clear
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm border-collapse bg-white">
                <thead>
                <tr class="border-b bg-gray-50">
                    <th class="px-4 py-2 text-left">Updated at</th>
                    <th class="px-4 py-2 text-left">Location</th>
                    <th class="px-4 py-2 text-left">Division</th>
                    <th class="px-4 py-2 text-left">Tree Type</th>
                    <th class="px-4 py-2 text-left">#</th>
                    <th class="px-4 py-2 text-left">Status</th>
                    <th class="px-4 py-2 text-left">Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach($treePlantings as $index => $planting)
                    <tr class="border-b {{ $index % 2 === 0 ? 'bg-white' : 'bg-gray-50' }}">
                        <td class="px-4 py-2">{{ $planting->updated_at->format('Y-m-d H:i') }}</td>
                        <td class="px-4 py-2">{{ $planting->plantingLocation->location ?? 'N/A' }}</td>
                        <td class="px-4 py-2">{{ $planting->plantingLocation->division->LGA_name ?? 'N/A' }}</td>
                        <td class="px-4 py-2">{{ $planting->treeType->name ?? 'N/A' }}</td>
                        <td class="px-4 py-2">{{ $planting->number_of_trees }}</td>
                        <td class="px-4 py-2">
                            {{ $planting->statusRelation->tree_planting_status ?? 'N/A' }}
                            @if($planting->statusRelation?->tree_planting_status === 'Verified' && $planting->statusUpdatedBy)
                                <span class="text-gray-500 text-xs">by {{ $planting->statusUpdatedBy->name }}</span>
                            @endif
                        </td>
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
