<x-app-layout>
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <h1 class="text-4xl font-bold mb-4 text-center"><i class="fas fa-clipboard-check mr-2"></i>Inspections</h1>

        <!-- Search and Filter Form -->
        @php $hasFilters = request()->hasAny(['search', 'status']); @endphp
        <div class="filter-container">
            <div class="filter-form-content">
                <form method="GET" action="{{ route('inspections.index') }}" class="filter-form">
                    <div class="filter-grid filter-grid-2">
                        <div>
                            <label for="search" class="filter-label">Search</label>
                            <input
                                type="text"
                                id="search"
                                name="search"
                                value="{{ request('search') }}"
                                placeholder="Search by location, division, country, monitor, or comment..."
                                class="filter-input {{ request('search') ? 'filter-active' : '' }}"
                            >
                        </div>

                        <div>
                            <label for="status" class="filter-label">Status</label>
                            <select name="status" id="status" class="filter-select {{ request('status') ? 'filter-active' : '' }}">
                                <option value="">All Status</option>
                                <option value="verified" {{ request('status') === 'verified' ? 'selected' : '' }}>Verified</option>
                                <option value="unverified" {{ request('status') === 'unverified' ? 'selected' : '' }}>Unverified</option>
                            </select>
                        </div>
                    </div>

                    <div class="filter-actions">
                        <div class="filter-button-group">
                            <button type="submit" class="filter-btn-primary">
                                <i class="fas fa-search mr-1"></i>Filter
                            </button>
                            <a @if($hasFilters) href="{{ route('inspections.index') }}" @endif
                               class="filter-btn-secondary {{ $hasFilters ? 'filter-btn-secondary-active' : 'filter-btn-disabled' }}">
                                <i class="fas fa-times mr-1"></i>Clear
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Results Info -->
        @if(request('search') || request('status'))
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6 text-blue-600 mr-3" fill="none" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-blue-700">
                    Showing filtered results
                    @if(request('search'))
                        for "<strong>{{ request('search') }}</strong>"
                    @endif
                    @if(request('status'))
                        with status "<strong>{{ ucfirst(request('status')) }}</strong>"
                    @endif
                    ({{ $inspections->total() }} {{ Str::plural('result', $inspections->total()) }})
                </span>
            </div>
        @endif

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200 text-sm">
                <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monitor</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Comment</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                @forelse($inspections as $inspection)
                    <tr class="{{ $loop->even ? 'bg-gray-50' : 'bg-white' }}">
                        <td class="px-6 py-4 whitespace-nowrap">{{ $inspection->id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ \Carbon\Carbon::parse($inspection->inspection_date)->format('Y-m-d') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $inspection->plantingLocation->location ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $inspection->user->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $inspection->verified ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ $inspection->verified ? 'Verified' : 'Unverified' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">{{ $inspection->comment ?: '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a href="{{ route('planting-locations.show', $inspection->plantingLocation) }}" class="bg-blue-500 text-white px-2 py-1 text-xs rounded hover:bg-blue-600 transition-colors">
                                View {{ $inspection->plantingLocation->location ?? 'N/A' }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                            @if(request('search') || request('status'))
                                No inspections found matching your criteria.
                            @else
                                No inspections found.
                            @endif
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <!-- Add Pagination Links -->
        <div class="mt-6">
            {{ $inspections->links('custom.pagination') }}
        </div>
    </div>
</x-app-layout>
