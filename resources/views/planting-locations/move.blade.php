<x-app-layout>
    <div class="max-w-4xl mx-auto mt-8 px-4"
         x-data="{
             query: '',
             results: [],
             selectedId: null,
             selectedName: '',
             async doSearch() {
                 if (this.query.length < 1) { this.results = []; return; }
                 const resp = await fetch(
                     '/planting-locations/search?q=' + encodeURIComponent(this.query) +
                     '&exclude={{ $plantingLocation->id }}'
                 );
                 this.results = await resp.json();
             },
             selectDestination(result) {
                 this.selectedId = result.id;
                 this.selectedName = result.location + ' (' + result.lga_name + ')';
                 this.results = [];
                 this.query = result.location;
             },
             selectAll() {
                 document.querySelectorAll('input[name=\'planting_ids[]\']').forEach(cb => cb.checked = true);
             },
             deselectAll() {
                 document.querySelectorAll('input[name=\'planting_ids[]\']').forEach(cb => cb.checked = false);
             },
             submitMove() {
                 const checked = document.querySelectorAll('input[name=\'planting_ids[]\']:checked');
                 if (checked.length === 0) { alert('Please select at least one planting to move.'); return; }
                 if (!this.selectedId) { alert('Please select a destination location.'); return; }
                 if (!confirm('Move ' + checked.length + ' selected planting(s) to \'' + this.selectedName + '\'? This cannot be undone.')) return;
                 document.getElementById('move-form').submit();
             }
         }">

        <h1 class="text-2xl font-bold text-gray-800 mb-1">Move Plantings to Another Location</h1>
        <p class="text-gray-600 mb-6">Select the plantings you want to move, then search for the destination location.</p>

        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                @foreach($errors->all() as $error)
                    <p class="text-sm">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form id="move-form" method="POST" action="{{ route('planting-locations.move', $plantingLocation) }}">
            @csrf
            <input type="hidden" name="destination_id" :value="selectedId">

            {{-- Plantings table --}}
            @if($plantingLocation->treePlantings->isEmpty())
                <p class="text-center text-sm text-gray-500 mb-6">No tree plantings recorded at this location.</p>
            @else
                <div class="flex gap-2 mb-3">
                    <button type="button" @click="selectAll()"
                            class="bg-gray-200 text-gray-700 px-3 py-1 text-xs rounded hover:bg-gray-300 transition-colors">
                        Select All
                    </button>
                    <button type="button" @click="deselectAll()"
                            class="bg-gray-200 text-gray-700 px-3 py-1 text-xs rounded hover:bg-gray-300 transition-colors">
                        Deselect All
                    </button>
                </div>

                <div class="border border-gray-300 rounded-lg shadow p-4 bg-white mb-6">
                    <div class="overflow-x-auto">
                        <table class="w-auto mx-auto text-sm border-collapse">
                            <thead>
                            <tr>
                                <th class="px-2 py-1 border-b"></th>
                                <th class="px-2 py-1 whitespace-nowrap border-b">Date</th>
                                <th class="px-2 py-1 whitespace-nowrap border-b">#</th>
                                <th class="px-2 py-1 whitespace-nowrap border-b">Tree</th>
                                <th class="px-2 py-1 whitespace-nowrap border-b">Years</th>
                                <th class="px-2 py-1 whitespace-nowrap border-b">Added By</th>
                                <th class="px-2 py-1 whitespace-nowrap border-b">Status</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($plantingLocation->treePlantings()->with('treeType','statusRelation','user','statusUpdatedBy')->orderBy('planting_date', 'desc')->get() as $planting)
                                <tr class="@if($planting->status === 1) text-red-600 @elseif($planting->status === 2) text-green-600 @endif border-b">
                                    <td class="px-2 py-1 text-center">
                                        <input type="checkbox" name="planting_ids[]" value="{{ $planting->id }}"
                                               class="w-4 h-4 accent-blue-600">
                                    </td>
                                    <td class="px-2 py-1 whitespace-nowrap">{{ \Carbon\Carbon::parse($planting->planting_date)->format('Y-m-d') }}</td>
                                    <td class="px-2 py-1 whitespace-nowrap">{{ $planting->number_of_trees }}</td>
                                    <td class="px-2 py-1 whitespace-nowrap">{{ $planting->treeType->name ?? 'N/A' }}</td>
                                    <td class="px-2 py-1 whitespace-nowrap">{{ number_format(\Carbon\Carbon::parse($planting->planting_date)->diffInDays(now()) / 365.25, 1) }}</td>
                                    <td class="px-2 py-1 whitespace-nowrap">{{ $planting->user->name ?? 'N/A' }}</td>
                                    <td class="px-2 py-1 whitespace-nowrap">
                                        {{ $planting->statusRelation->tree_planting_status ?? 'N/A' }}
                                        @if($planting->statusRelation?->tree_planting_status === 'Verified' && $planting->statusUpdatedBy)
                                            <span class="text-xs opacity-70">by {{ $planting->statusUpdatedBy->name }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- Destination search --}}
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Destination Location</label>
                <div class="relative">
                    <input
                        type="text"
                        x-model="query"
                        @input.debounce.300ms="doSearch()"
                        placeholder="Type a location name to search…"
                        class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
                        autocomplete="off"
                    >
                    <ul x-show="results.length > 0"
                        class="absolute z-10 w-full bg-white border border-gray-300 rounded mt-1 shadow-lg max-h-60 overflow-y-auto">
                        <template x-for="result in results" :key="result.id">
                            <li @click="selectDestination(result)"
                                class="px-3 py-2 text-sm cursor-pointer hover:bg-blue-50 flex justify-between">
                                <span x-text="result.location"></span>
                                <span class="text-gray-400 text-xs self-center" x-text="result.lga_name"></span>
                            </li>
                        </template>
                    </ul>
                </div>
                <p x-show="selectedName" class="mt-2 text-sm text-green-700 font-medium">
                    Selected: <span x-text="selectedName"></span>
                </p>
            </div>

            {{-- Actions --}}
            <div class="flex gap-3">
                <button type="button" @click="submitMove()"
                        class="bg-blue-600 text-white px-5 py-2 text-sm rounded hover:bg-blue-700 transition-colors">
                    Move Selected
                </button>
                <a href="{{ route('planting-locations.show', $plantingLocation) }}"
                   class="bg-gray-200 text-gray-700 px-5 py-2 text-sm rounded hover:bg-gray-300 transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</x-app-layout>
