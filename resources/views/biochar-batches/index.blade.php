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
        <h1 class="text-2xl font-bold text-center text-gray-800 mb-1">Biochar Batches</h1>
        <p class="text-center text-gray-600 mb-6">{{ $plantingLocation->location }}</p>

        <div class="flex justify-end mb-4">
            <a href="{{ route('biochar-batches.create', ['planting_location_id' => $plantingLocation->id]) }}" class="bg-amber-700 text-white px-4 py-2 text-sm rounded hover:bg-amber-800 transition-colors">
                ➕ New Biochar Batch
            </a>
        </div>

        @if($batches->isEmpty())
            <p class="text-center text-sm text-gray-500 mb-10">No biochar batches recorded yet.</p>
        @else
            <div class="border border-gray-300 rounded-lg shadow p-4 bg-white mb-10">
                <div class="overflow-x-auto">
                    <table class="w-auto mx-auto text-sm border-collapse">
                        <thead>
                        <tr>
                            <th class="px-2 py-1 whitespace-nowrap border-b">Date</th>
                            <th class="px-2 py-1 whitespace-nowrap border-b">Quantity (kg)</th>
                            <th class="px-2 py-1 whitespace-nowrap border-b">Source</th>
                            <th class="px-2 py-1 whitespace-nowrap border-b">Batch Ref</th>
                            <th class="px-2 py-1 whitespace-nowrap border-b">Applied To</th>
                            <th class="px-2 py-1 whitespace-nowrap border-b">Notes</th>
                            <th class="px-2 py-1 whitespace-nowrap border-b">Recorded By</th>
                            <th class="px-2 py-1 whitespace-nowrap text-center border-b">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($batches as $batch)
                            <tr class="border-b">
                                <td class="px-2 py-1 whitespace-nowrap">{{ $batch->application_date->format('Y-m-d') }}</td>
                                <td class="px-2 py-1 whitespace-nowrap">{{ $batch->quantity_kg }}</td>
                                <td class="px-2 py-1 whitespace-nowrap">{{ $batch->source }}</td>
                                <td class="px-2 py-1 whitespace-nowrap">{{ $batch->batch_reference ?: '—' }}</td>
                                <td class="px-2 py-1 whitespace-nowrap">
                                    @if($batch->treePlanting)
                                        {{ $batch->treePlanting->treeType->name ?? 'N/A' }} ({{ $batch->treePlanting->planting_date->format('Y-m-d') }})
                                    @else
                                        <span class="text-gray-400">Whole location</span>
                                    @endif
                                </td>
                                <td class="px-2 py-1">{{ $batch->notes ?: '—' }}</td>
                                <td class="px-2 py-1 whitespace-nowrap">{{ $batch->user->name ?? 'N/A' }}</td>
                                <td class="px-2 py-1 whitespace-nowrap">
                                    <div class="flex flex-wrap justify-center gap-1">
                                        <a href="{{ route('biochar-batches.edit', $batch) }}" class="bg-yellow-500 text-white px-2 py-1 text-xs rounded hover:bg-yellow-600 transition-colors">Edit</a>
                                        <form action="{{ route('biochar-batches.destroy', $batch) }}" method="POST" onsubmit="return confirm('Delete this biochar batch?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="bg-red-500 text-white px-2 py-1 text-xs rounded hover:bg-red-600 transition-colors">Delete</button>
                                        </form>
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
            <a href="{{ route('planting-locations.show', $plantingLocation) }}" class="text-blue-600 hover:underline text-sm">
                ← Back to location
            </a>
        </div>
    </div>
</x-app-layout>
