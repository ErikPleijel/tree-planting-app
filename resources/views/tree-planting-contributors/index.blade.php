<x-app-layout>
    @if(session('success'))
        <div class="flex justify-center mt-4 mb-4">
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded shadow-md w-fit">
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

    <div class="max-w-3xl mx-auto mt-6 px-4">
        <h1 class="text-2xl font-bold text-center text-gray-800 mb-1">Contributors</h1>
        <p class="text-center text-gray-600 mb-6">
            {{ $treePlanting->treeType->name ?? 'N/A' }} — planted {{ \Carbon\Carbon::parse($treePlanting->planting_date)->format('Y-m-d') }}
            at {{ $treePlanting->plantingLocation->location ?? 'N/A' }}
        </p>

        @if($treePlanting->contributors->isEmpty())
            <p class="text-center text-sm text-gray-500 mb-6">No contributors attached yet.</p>
        @else
            <div class="border border-gray-300 rounded-lg shadow p-4 bg-white mb-6">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm border-collapse">
                        <thead>
                        <tr>
                            <th class="px-2 py-1 text-left border-b">Name</th>
                            <th class="px-2 py-1 text-left border-b">Website</th>
                            <th class="px-2 py-1 text-left border-b">Note</th>
                            <th class="px-2 py-1 text-center border-b">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($treePlanting->contributors as $contributor)
                            <tr class="border-b">
                                <td class="px-2 py-1 whitespace-nowrap">{{ $contributor->name }}</td>
                                <td class="px-2 py-1 whitespace-nowrap">
                                    @if($contributor->website)
                                        <a href="{{ $contributor->website }}" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:underline">{{ $contributor->website }}</a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-2 py-1">{{ $contributor->pivot->note ?: '—' }}</td>
                                <td class="px-2 py-1 whitespace-nowrap text-center">
                                    <form action="{{ route('tree-planting-contributors.detach', [$treePlanting, $contributor]) }}" method="POST" onsubmit="return confirm('Detach this contributor?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="bg-red-500 text-white px-2 py-1 text-xs rounded hover:bg-red-600 transition-colors">Detach</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <div class="bg-white shadow rounded-lg p-6"
             x-data="{
                mode: 'search',
                query: '',
                results: [],
                selectedId: null,
                selectedName: '',
                async doSearch() {
                    if (this.query.length < 1) { this.results = []; return; }
                    const resp = await fetch('{{ route('contributors.search') }}?q=' + encodeURIComponent(this.query));
                    this.results = await resp.json();
                },
                select(result) {
                    this.selectedId = result.id;
                    this.selectedName = result.name;
                    this.results = [];
                    this.query = result.name;
                }
             }">
            <h2 class="text-lg font-semibold mb-3">Add Contributor</h2>

            <div class="flex gap-2 mb-4">
                <button type="button" @click="mode = 'search'"
                        :class="mode === 'search' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'"
                        class="px-3 py-1 text-sm rounded transition-colors">
                    Existing Contributor
                </button>
                <button type="button" @click="mode = 'new'"
                        :class="mode === 'new' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'"
                        class="px-3 py-1 text-sm rounded transition-colors">
                    Add New Contributor
                </button>
            </div>

            <form method="POST" action="{{ route('tree-planting-contributors.attach', $treePlanting) }}">
                @csrf

                <div x-show="mode === 'search'" class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search Contributors</label>
                    <div class="relative">
                        <input
                            type="text"
                            x-model="query"
                            @input.debounce.300ms="doSearch()"
                            placeholder="Type an organization name…"
                            class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
                            autocomplete="off"
                        >
                        <ul x-show="results.length > 0"
                            class="absolute z-10 w-full bg-white border border-gray-300 rounded mt-1 shadow-lg max-h-60 overflow-y-auto">
                            <template x-for="result in results" :key="result.id">
                                <li @click="select(result)"
                                    class="px-3 py-2 text-sm cursor-pointer hover:bg-blue-50"
                                    x-text="result.name"></li>
                            </template>
                        </ul>
                    </div>
                    <p x-show="selectedName" class="mt-2 text-sm text-green-700 font-medium">
                        Selected: <span x-text="selectedName"></span>
                    </p>
                    <input type="hidden" name="contributor_id" :value="mode === 'search' ? selectedId : ''">
                </div>

                <div x-show="mode === 'new'" class="space-y-3 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Organization Name</label>
                        <input type="text" name="name"
                               class="w-full border border-gray-300 rounded px-3 py-2 text-sm"
                               value="{{ old('name') }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Website (optional)</label>
                        <input type="url" name="website"
                               class="w-full border border-gray-300 rounded px-3 py-2 text-sm"
                               placeholder="https://example.org"
                               value="{{ old('website') }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Contact Email (optional)</label>
                        <input type="email" name="contact_email"
                               class="w-full border border-gray-300 rounded px-3 py-2 text-sm"
                               value="{{ old('contact_email') }}">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Note (optional)</label>
                    <textarea name="note" rows="2"
                              class="w-full border border-gray-300 rounded px-3 py-2 text-sm">{{ old('note') }}</textarea>
                </div>

                <div class="flex justify-end gap-2">
                    <a href="{{ route('planting-locations.show', $treePlanting->planting_location_id) }}"
                       class="px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-50 transition-colors">Cancel</a>
                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded hover:bg-green-700 transition-colors">Attach</button>
                </div>
            </form>
        </div>

        <div class="text-center mt-6 mb-10">
            <a href="{{ route('planting-locations.show', $treePlanting->planting_location_id) }}" class="text-blue-600 hover:underline text-sm">
                ← Back to location
            </a>
        </div>
    </div>
</x-app-layout>
