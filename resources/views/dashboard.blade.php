<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            @if(Auth::user()->profile_picture_path)
                <img src="{{ asset('storage/' . Auth::user()->profile_picture_path) }}"
                     alt="Profile photo"
                     class="w-20 h-24 object-cover rounded-lg flex-shrink-0">
            @else
                <div class="w-20 h-24 rounded-lg bg-gray-200 flex items-center justify-center flex-shrink-0">
                    <svg class="w-8 h-8 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/>
                    </svg>
                </div>
            @endif
            <div class="text-xl font-semibold leading-tight text-gray-800">
                <h1>{{ Auth::user()->name }}</h1>
                <p class="text-lg mt-1">
                    Your role:
                    @role('Admin|SuperAdmin|Monitor|Grower')
                        <span class="bg-green-800 text-white px-2 py-1 rounded">{{ Auth::user()->roles->first()->name }}</span>
                    @else
                        <span class="bg-yellow-200 px-2 py-1 rounded">You have not been assigned a role yet</span>
                    @endrole
                </p>
            </div>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8">
        <h2 class="text-4xl font-bold mb-4 text-center"><i class="fas fa-gauge-high mr-2"></i>DASHBOARD</h2>

        <p class="text-center text-gray-600 mb-6 max-w-2xl mx-auto">
            This dashboard gives you an overview of current tree planting activities in your assigned areas. As a Monitor or Field Worker, your role is essential in ensuring that each planting meets the standards for survival, documentation, and long-term sustainability.
        </p>

        {{-- No photo reminder --}}
        @if(!Auth::user()->profile_picture_path)
        <div class="text-center mb-4">
            <p class="text-lg font-semibold text-orange-600">
                ❗ You have not uploaded a profile photo yet.
                <a href="{{ route('profile.edit') }}" class="underline hover:text-orange-800">Upload one on your Profile page.</a>
            </p>
        </div>
        @endif

        @role('SuperAdmin')
        <div class="text-center mb-8">
            <a href="{{ route('divisions.index') }}"
               class="inline-block bg-primary text-white px-6 py-3 rounded-lg hover:bg-green-700 transition-colors">
                Manage Divisions
            </a>
        </div>
        @endrole

        {{-- Stat cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
            <div class="bg-white rounded-lg shadow p-6 text-center">
                <p class="text-4xl font-bold text-green-700">{{ number_format($statTreesPlanted) }}</p>
                <p class="mt-2 text-sm font-medium text-gray-600 uppercase tracking-wide">Trees Planted by You</p>
            </div>
            @if($statVerifications !== null)
            <div class="bg-white rounded-lg shadow p-6 text-center">
                <p class="text-4xl font-bold text-yellow-600">{{ number_format($statVerifications) }}</p>
                <p class="mt-2 text-sm font-medium text-gray-600 uppercase tracking-wide">Plantings Verified by You</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6 text-center">
                <p class="text-4xl font-bold text-blue-600">{{ number_format($statInspections) }}</p>
                <p class="mt-2 text-sm font-medium text-gray-600 uppercase tracking-wide">Inspections Carried Out by You</p>
            </div>
            @endif
        </div>
    </div>

    <div class="max-w-7xl mx-auto mt-8 px-4 sm:px-6 lg:px-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            <h2 class="text-2xl font-semibold mb-4">My Tree Plantings</h2>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Updated at</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Species</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($treePlantings as $planting)
    <tr class="{{ $planting->status === 1 ? 'bg-red-100' : ($planting->status === 2 ? 'bg-green-100' : '') }}">
        <td class="px-6 py-4 whitespace-nowrap">
            {{ $planting->updated_at ? $planting->updated_at->format('Y-m-d H:i') : 'Not set' }}
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
            {{ $planting->plantingLocation?->division?->LGA_name ?? 'No Division Assigned' }}
        </td>
        <td class="px-6 py-4">
            {{ $planting->treeType->name ?? 'No Species' }}
        </td>
        <td class="px-6 py-4">
            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                {{ $planting->statusRelation?->tree_planting_status === 'Planted' ? 'bg-green-100 text-green-800' :
                   ($planting->statusRelation?->tree_planting_status === 'Verified' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                {{ ucfirst($planting->statusRelation?->tree_planting_status ?? 'Unknown') }}
            </span>
        </td>
        <td class="px-6 py-4">
            <a href="{{ route('planting-locations.show', $planting->plantingLocation) }}" class="bg-blue-500 text-white px-3 py-1 text-sm rounded hover:bg-blue-600 transition-colors">View</a>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="5" class="px-6 py-4 text-center text-gray-500">
            No tree plantings found
        </td>
    </tr>
@endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $treePlantings->links() }}
            </div>
        </div>
    </div>
</div>

@if($verifications !== null)
<div class="max-w-7xl mx-auto mt-8 px-4 sm:px-6 lg:px-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            <h2 class="text-2xl font-semibold mb-4">My Verifications</h2>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Updated at</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tree Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($verifications as $planting)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ $planting->updated_at->format('Y-m-d H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ $planting->plantingLocation?->location ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ $planting->treeType?->name ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ $planting->number_of_trees }}
                                </td>
                                <td class="px-6 py-4">
                                    <a href="{{ route('planting-locations.show', $planting->plantingLocation) }}" class="bg-blue-500 text-white px-3 py-1 text-sm rounded hover:bg-blue-600 transition-colors">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                    No verifications found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $verifications->links() }}
            </div>
        </div>
    </div>
</div>
@endif

@if($inspections !== null)
<div class="max-w-7xl mx-auto mt-8 px-4 sm:px-6 lg:px-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            <h2 class="text-2xl font-semibold mb-4">My Inspections</h2>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Updated at</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Comment</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Verified</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($inspections as $inspection)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ $inspection->updated_at->format('Y-m-d H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ $inspection->plantingLocation?->location ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ $inspection->comment ?? '—' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ $inspection->verified ? 'Yes' : 'No' }}
                                </td>
                                <td class="px-6 py-4">
                                    <a href="{{ route('planting-locations.show', $inspection->plantingLocation) }}" class="bg-blue-500 text-white px-3 py-1 text-sm rounded hover:bg-blue-600 transition-colors">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                    No inspections found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $inspections->links() }}
            </div>
        </div>
    </div>
</div>
@endif

<div class="bg-white p-6 rounded shadow max-w-3xl mx-auto mt-8 mb-8">
    <h3 class="text-xl font-semibold mt-2 mb-2">Your Responsibilities:</h3>
    <ul class="list-disc list-inside mb-4">
        <li>Review and verify newly planted trees in the field.</li>
        <li>Record accurate inspection results and upload supporting photos if required.</li>
        <li>Report any issues such as missing trees, poor soil conditions, or pest damage.</li>
        <li>Ensure that each planting location is properly geotagged and documented.</li>
        <li><strong>Always write down all key data on paper as a backup</strong>, in case of technical problems or poor network coverage.</li>
    </ul>

    <h3 class="text-xl font-semibold mt-6 mb-2">Getting Started:</h3>
    <ol class="list-decimal list-inside mb-4">
        <li>Use the navigation menu to access <strong>Plantings</strong>, <strong>Inspections</strong>, or <strong>Locations</strong>.</li>
        <li>Click on any record to view details, update status, or submit inspection notes.</li>
    </ol>

    <p class="text-sm text-gray-600">
        Thank you for your work in restoring our environment — one tree at a time.
    </p>
</div>

</x-app-layout>
