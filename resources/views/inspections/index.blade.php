<x-app-layout>
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <h1 class="text-2xl font-bold mb-4 text-center">Inspections</h1>

        <!-- Search and Filter Form -->
        <div class="bg-white shadow-md rounded-lg mb-6">
            <div class="p-6">
                <form method="GET" action="{{ route('inspections.index') }}" class="flex flex-wrap gap-4 items-end">
                    <!-- Search Input -->
                    <div class="flex-1 min-w-64">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Search
                        </label>
                        <input
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Search by location, monitor, or comment..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Status
                        </label>
                        <select name="status" class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Status</option>
                            <option value="verified" {{ request('status') === 'verified' ? 'selected' : '' }}>Verified</option>
                            <option value="unverified" {{ request('status') === 'unverified' ? 'selected' : '' }}>Unverified</option>
                        </select>
                    </div>

                    <!-- Action Buttons -->
                    <div>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors inline-flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            Search
                        </button>
                    </div>

                    <!-- Clear Filters -->
                    @if(request('search') || request('status'))
                        <div>
                            <a href="{{ route('inspections.index') }}" class="px-4 py-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded transition-colors inline-flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Clear
                            </a>
                        </div>
                    @endif
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
