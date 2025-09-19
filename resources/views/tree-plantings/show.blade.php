<x-app-layout>
    <div class="max-w-3xl mx-auto p-6 bg-white shadow rounded">
        <h1 class="text-2xl font-bold mb-4">Tree Planting Details</h1>

        <div class="space-y-2 text-gray-700">
            <p><strong>Location:</strong> {{ $treePlanting->plantingLocation->location ?? 'N/A' }}</p>
            <p><strong>Tree Type:</strong> {{ $treePlanting->treeType->name ?? 'N/A' }}</p>
            <p><strong>Status:</strong> {{ $treePlanting->status->name ?? 'N/A' }}</p>
            <p><strong>Date Planted:</strong> {{ $treePlanting->planted_at ?? 'N/A' }}</p>
            <!-- Add more fields if needed -->
        </div>

        <div class="mt-6">
            <a href="{{ route('tree-plantings.index') }}"
               class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 transition-colors">← Back to List</a>
        </div>
    </div>
</x-app-layout>
