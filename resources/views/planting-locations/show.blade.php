<x-app-layout>
    <div class="max-w-2xl mx-auto mt-6 p-4 bg-white shadow rounded-lg">
        <h1 class="text-xl font-bold text-center text-gray-800 mb-4">Planting Location Details</h1>

        <div class="flex justify-center flex-wrap gap-2 mb-4">
            <a href="{{ route('planting-locations.index') }}" class="btn btn-outline btn-xs">← Back</a>
            <a href="{{ route('planting-locations.edit', $plantingLocation) }}" class="btn btn-warning btn-xs">Edit</a>
            <form action="{{ route('planting-locations.destroy', $plantingLocation) }}" method="POST"
                  onsubmit="return confirm('Are you sure you want to delete this location?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-error btn-xs">Delete</button>
            </form>
        </div>

        <div class="space-y-3 text-sm text-gray-700 border border-gray-300 rounded-md p-4 bg-gray-400">


        <!-- Status -->
            <div>
                <label class="font-semibold">Status</label>
                <div class="p-2 border rounded bg-gray-50">
                    {{ $plantingLocation->statusRelation->planting_location_status ?? 'N/A' }}
                </div>
            </div>

            <!-- Location and LGA -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="font-semibold">Location Name</label>
                    <div class="p-2 border rounded bg-gray-50">
                        {{ $plantingLocation->location }}
                    </div>
                </div>
                <div>
                    <label class="font-semibold">LGA</label>
                    <div class="p-2 border rounded bg-gray-50">
                        {{ $plantingLocation->division->LGA_name ?? 'N/A' }}
                    </div>
                </div>
            </div>

            <!-- Comment -->
            <div>
                <label class="font-semibold">Comment</label>
                <div class="p-2 border rounded bg-gray-50 whitespace-pre-line">
                    {{ $plantingLocation->comment ?: '—' }}
                </div>
            </div>

            <!-- Coordinates -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="font-semibold">Latitude</label>
                    <div class="p-2 border rounded bg-gray-50">
                        {{ $plantingLocation->latitude ?: '—' }}
                    </div>
                </div>
                <div>
                    <label class="font-semibold">Longitude</label>
                    <div class="p-2 border rounded bg-gray-50">
                        {{ $plantingLocation->longitude ?: '—' }}
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="flex justify-center items-center ">
        <a href="{{ route('pictures.create', $plantingLocation->id) }}">
            <button class="btn btn-primary mt-4">📷 Take Photo</button>
        </a>
    </div>

    @if($plantingLocation->pictures->count())
        <h3 class="text-lg font-semibold mb-2 text-center">Uploaded Photos</h3>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach($plantingLocation->pictures as $pic)
                <div class="border p-2 rounded shadow">
                    <img
                        src="{{ asset('storage/' . $pic->path) }}"
                        alt="Picture"
                        class="w-full h-auto rounded"
                    >
                    <p class="text-xs text-gray-600 mt-1">Uploaded {{ $pic->created_at->format('Y-m-d H:i') }}</p>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-gray-500">No pictures uploaded yet.</p>
    @endif


    <h2 class="text-lg font-semibold mt-6 mb-3 text-center">Tree Plantings at this Location</h2>
    @if(session('success'))
        <div class="flex justify-center mt-4">
            <div class="alert alert-success shadow-md w-fit px-6 py-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current h-5 w-5 mr-2" fill="none"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12l2 2l4 -4M12 22C6.48 22 2 17.52 2 12S6.48 2 12 2s10 4.48 10 10s-4.48 10-10 10z" />
                </svg>
                <span class="text-sm">{{ session('success') }}</span>
            </div>
        </div>
    @endif
    <div class="flex justify-end max-w-4xl mx-auto mt-6 mb-2">
        <a href="{{ route('tree-plantings.create', ['planting_location_id' => $plantingLocation->id]) }}" class="btn btn-primary btn-sm">
            ➕ New Tree Planting
        </a>
    </div>
@if($plantingLocation->treePlantings->isEmpty())
        <p class="text-center text-sm text-gray-500">No tree plantings recorded yet.</p>
    @else
        <div class="max-w-4xl mx-auto border border-gray-300 rounded-lg shadow p-4 bg-white">
            <div class="overflow-x-auto">

                <table class="table table-auto table-sm w-fit mx-auto text-sm">
                    <thead>
                    <tr>
                        <th class="px-2 py-1 whitespace-nowrap">ID</th>
                        <th class="px-2 py-1 whitespace-nowrap">Date</th>
                        <th class="px-2 py-1 whitespace-nowrap">Tree</th>
                        <th class="px-2 py-1 whitespace-nowrap">#</th>
                        <th class="px-2 py-1 whitespace-nowrap">Status</th>
                        <th class="px-2 py-1 whitespace-nowrap text-center">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($plantingLocation->treePlantings as $planting)
                        <tr>
                            <td class="px-2 py-1 whitespace-nowrap">{{ $planting->id }}</td>
                            <td class="px-2 py-1 whitespace-nowrap">{{ $planting->planting_date }}</td>
                            <td class="px-2 py-1 whitespace-nowrap">{{ $planting->treeType->name ?? 'N/A' }}</td>
                            <td class="px-2 py-1 whitespace-nowrap">{{ $planting->number_of_trees }}</td>
                            <td class="px-2 py-1 whitespace-nowrap">{{ $planting->statusRelation->tree_planting_status ?? 'N/A' }}</td>
                            <td class="px-2 py-1 whitespace-nowrap">
                                <div class="flex flex-wrap justify-center gap-1">

                                    <a href="{{ route('tree-plantings.edit', $planting) }}" class="btn btn-warning btn-xs">Edit</a>
                                    <form action="{{ route('tree-plantings.destroy', $planting) }}" method="POST" onsubmit="return confirm('Delete this planting?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-error btn-xs">Delete</button>
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



    <div class="mx-auto w-full max-w-xl px-4 mt-10">
        <x-map
            {{-- id="location-map" --}}
            lat="{{ $plantingLocation->latitude ?: '—' }}"
            lng=" {{ $plantingLocation->longitude ?: '—' }}"
            :zoom="10"
            :markers="$markers"
            {{-- height="400px" --}}
            {{-- width="100%" --}}
        />

    </div>
</x-app-layout>
