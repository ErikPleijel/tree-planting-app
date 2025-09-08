<x-app-layout>
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <h1 class="text-2xl font-bold mb-4 text-center">Inspections</h1>

        <!-- Search and Filter Form -->
        <div class="card bg-base-100 shadow-md mb-6">
            <div class="card-body">
                <form method="GET" action="{{ route('inspections.index') }}" class="flex flex-wrap gap-4 items-end">
                    <!-- Search Input -->
                    <div class="form-control flex-1 min-w-64">
                        <label class="label">
                            <span class="label-text">Search</span>
                        </label>
                        <input
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Search by location, monitor, or comment..."
                            class="input input-bordered w-full"
                        >
                    </div>

                    <!-- Status Filter -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Status</span>
                        </label>
                        <select name="status" class="select select-bordered">
                            <option value="">All Status</option>
                            <option value="verified" {{ request('status') === 'verified' ? 'selected' : '' }}>Verified</option>
                            <option value="unverified" {{ request('status') === 'unverified' ? 'selected' : '' }}>Unverified</option>
                        </select>
                    </div>

                    <!-- Action Buttons -->
                    <div class="form-control">
                        <button type="submit" class="btn btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            Search
                        </button>
                    </div>

                    <!-- Clear Filters -->
                    @if(request('search') || request('status'))
                        <div class="form-control">
                            <a href="{{ route('inspections.index') }}" class="btn btn-ghost">
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
            <div class="alert alert-info mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span>
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
            <table class="table table-zebra table-sm w-full text-sm">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Location</th>
                    <th>Monitor</th>
                    <th>Status</th>
                    <th>Comment</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($inspections as $inspection)
                    <tr>
                        <td>{{ $inspection->id }}</td>
                        <td>{{ \Carbon\Carbon::parse($inspection->inspection_date)->format('Y-m-d') }}</td>
                        <td>{{ $inspection->plantingLocation->location ?? 'N/A' }}</td>
                        <td>{{ $inspection->user->name ?? 'N/A' }}</td>
                        <td>
                            <div class="badge {{ $inspection->verified ? 'badge-success' : 'badge-warning' }}">
                                {{ $inspection->verified ? 'Verified' : 'Unverified' }}
                            </div>
                        </td>
                        <td>{{ $inspection->comment ?: '-' }}</td>
                        <td class="flex flex-wrap gap-1">
                            <a href="{{ route('planting-locations.show', $inspection->plantingLocation) }}" class="btn btn-info btn-xs">
                                View {{ $inspection->plantingLocation->location ?? 'N/A' }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-8 text-gray-500">
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
