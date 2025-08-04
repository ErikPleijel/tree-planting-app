<x-app-layout>
    <x-slot name="header">
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
    </x-slot>

    <div class="bg-white p-6 rounded shadow max-w-3xl mx-auto mt-8">
        <h2 class="text-2xl font-bold mb-4 text-center">DASHBOARD</h2>

        <!-- Rest of the existing content -->
        <p class="mb-4">
            This dashboard gives you an overview of current tree planting activities in your assigned areas. As an Monitor or Field Worker, your role is essential in ensuring that each planting meets the standards for survival, documentation, and long-term sustainability.
        </p>

        <h3 class="text-xl font-semibold mt-6 mb-2">Your Responsibilities:</h3>
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
            <li>If you’re new, be sure to read the <a href="#" class="text-blue-600 underline">Field Manual</a> or ask your team leader for help.</li>
        </ol>

        <p class="text-sm text-gray-600">
            Thank you for your work in restoring our environment — one tree at a time.
        </p>
    </div>

    <div class="max-w-7xl mx-auto mt-8 px-4 sm:px-6 lg:px-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            <h2 class="text-2xl font-semibold mb-4">My Tree Plantings</h2>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
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
            {{ $planting->planting_date ? $planting->planting_date->format('Y-m-d') : 'Not set' }}
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
            <a href="{{ route('planting-locations.show', $planting->plantingLocation) }}" class="btn btn-sm btn-info">View</a>
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

</x-app-layout>
