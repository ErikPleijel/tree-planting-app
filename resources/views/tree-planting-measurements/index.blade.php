<x-app-layout>
    @if(session('success'))
        <div class="flex justify-center mt-4 mb-4">
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded shadow-md w-fit flex items-center">
                <span class="text-sm">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="flex justify-center mt-4 mb-4">
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded shadow-md w-fit">
                @foreach($errors->all() as $error)
                    <p class="text-sm">{{ $error }}</p>
                @endforeach
            </div>
        </div>
    @endif

    <div class="max-w-4xl mx-auto mt-6 px-4">
        <h1 class="text-2xl font-bold text-center text-gray-800 mb-1">Measurements</h1>
        <p class="text-center text-gray-600 mb-6">
            {{ $treePlanting->treeType->name ?? 'N/A' }} — planted {{ \Carbon\Carbon::parse($treePlanting->planting_date)->format('Y-m-d') }}
            at {{ $treePlanting->plantingLocation->location ?? 'N/A' }}
        </p>

        <div class="flex justify-end mb-4">
            <a href="{{ route('tree-planting-measurements.create', $treePlanting) }}" class="bg-primary text-white px-4 py-2 text-sm rounded hover:bg-green-700 transition-colors">
                ➕ New Measurement
            </a>
        </div>

        @if($measurements->isEmpty())
            <p class="text-center text-sm text-gray-500 mb-10">No measurements recorded yet.</p>
        @else
            <div class="border border-gray-300 rounded-lg shadow p-4 bg-white mb-10">
                <div class="overflow-x-auto">
                    <table class="w-auto mx-auto text-sm border-collapse">
                        <thead>
                        <tr>
                            <th class="px-2 py-1 whitespace-nowrap border-b">Date</th>
                            <th class="px-2 py-1 whitespace-nowrap border-b">Surviving</th>
                            <th class="px-2 py-1 whitespace-nowrap border-b">Height avg (cm)</th>
                            <th class="px-2 py-1 whitespace-nowrap border-b">DBH avg (cm)</th>
                            <th class="px-2 py-1 whitespace-nowrap border-b">Canopy %</th>
                            <th class="px-2 py-1 whitespace-nowrap border-b">Notes</th>
                            <th class="px-2 py-1 whitespace-nowrap border-b">Recorded By</th>
                            <th class="px-2 py-1 whitespace-nowrap border-b">Verified</th>
                            <th class="px-2 py-1 whitespace-nowrap border-b">LiDAR</th>
                            <th class="px-2 py-1 whitespace-nowrap text-center border-b">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($measurements as $measurement)
                            <tr class="border-b">
                                <td class="px-2 py-1 whitespace-nowrap">{{ $measurement->measurement_date->format('Y-m-d') }}</td>
                                <td class="px-2 py-1 whitespace-nowrap">{{ $measurement->trees_surviving ?? '—' }}</td>
                                <td class="px-2 py-1 whitespace-nowrap">{{ $measurement->height_avg_cm ?? '—' }}</td>
                                <td class="px-2 py-1 whitespace-nowrap">{{ $measurement->dbh_avg_cm ?? '—' }}</td>
                                <td class="px-2 py-1 whitespace-nowrap">{{ $measurement->canopy_cover_pct ?? '—' }}</td>
                                <td class="px-2 py-1">{{ $measurement->notes ?: '—' }}</td>
                                <td class="px-2 py-1 whitespace-nowrap">{{ $measurement->recordedBy->name ?? 'N/A' }}</td>
                                <td class="px-2 py-1 whitespace-nowrap">
                                    @if($measurement->verified_at)
                                        <span class="text-green-600">✓ by {{ $measurement->verifiedBy->name ?? 'N/A' }}</span>
                                        <div class="text-xs text-gray-400">{{ $measurement->verified_at->format('Y-m-d') }}</div>
                                    @else
                                        <span class="text-yellow-600">Pending</span>
                                    @endif
                                </td>
                                <td class="px-2 py-1 whitespace-nowrap">
                                    @if($measurement->lidarScans->isNotEmpty())
                                        <span class="text-green-700" title="{{ $measurement->lidarScans->count() }} linked scan(s)">📡 {{ $measurement->lidarScans->count() }}</span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                    @role('Admin|SuperAdmin|Monitor|Grower')
                                        <div>
                                            <a href="{{ route('lidar-scans.create', ['tree_planting_measurement_id' => $measurement->id]) }}" class="text-xs text-blue-600 hover:underline">+ Attach Scan</a>
                                        </div>
                                    @endrole
                                </td>
                                <td class="px-2 py-1 whitespace-nowrap">
                                    <div class="flex flex-wrap justify-center gap-1">
                                        @role('Admin|SuperAdmin|Monitor|Grower')
                                        <a href="{{ route('tree-planting-measurements.edit', $measurement) }}" class="bg-yellow-500 text-white px-2 py-1 text-xs rounded hover:bg-yellow-600 transition-colors">Edit</a>
                                        @endrole

                                        @if(!$measurement->verified_at)
                                            @role('Admin|SuperAdmin|Monitor')
                                                @if((int) $measurement->user_id !== (int) auth()->id())
                                                    <form action="{{ route('tree-planting-measurements.verify', $measurement) }}" method="POST" onsubmit="return confirm('Verify this measurement?')">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="bg-blue-600 text-white px-2 py-1 text-xs rounded hover:bg-blue-700 transition-colors">Verify</button>
                                                    </form>
                                                @else
                                                    <span class="text-xs text-gray-400 self-center" title="You recorded this — another privileged user must verify it">(self-recorded)</span>
                                                @endif
                                            @endrole
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <div class="text-center mb-10">
            <a href="{{ route('planting-locations.show', $treePlanting->planting_location_id) }}" class="text-blue-600 hover:underline text-sm">
                ← Back to location
            </a>
        </div>
    </div>
</x-app-layout>
