<x-app-layout>
    @if(session('success'))
        <div class="flex justify-center mt-4 mb-4">
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

    <div class="max-w-2xl mx-auto mt-6 p-4 bg-white shadow rounded-lg">
        <!-- Rest of the existing content -->
        <div class="text-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">{{ $plantingLocation->location }}</h1>
            <p class="text-lg text-gray-600 mb-2">{{ $plantingLocation->division->LGA_name ?? 'N/A' }}</p>
            <p class="text-gray-600 italic mb-3">{{ $plantingLocation->comment ?: '—' }}</p>
            <div class="flex justify-center gap-4">
                <p class="text-gray-600">
                    <span class="font-semibold">Lat:</span> {{ $plantingLocation->latitude ?: '—' }}
                </p>
                <p class="text-gray-600">
                    <span class="font-semibold">Long:</span> {{ $plantingLocation->longitude ?: '—' }}
                </p>
            </div>
        </div>

        <div class="flex justify-center flex-wrap gap-2 mb-4">

            <a href="{{ route('planting-locations.edit', $plantingLocation) }}" class="btn btn-warning btn-xs">Edit</a>
            <form action="{{ route('planting-locations.destroy', $plantingLocation) }}" method="POST"
                  onsubmit="return confirm('Are you sure you want to delete this location?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-error btn-xs">Delete</button>
            </form>
        </div>
    </div>

    <div class="mx-auto w-full max-w-xl px-4 mt-10">
        <x-map2
            lat="{{ $plantingLocation->latitude }}"
            lng="{{ $plantingLocation->longitude }}"
            :zoom="10"
            :markers="$markers"
        />
    </div>



    <!-- Keep the first success message at the top -->
    <h2 class="text-2xl font-semibold mt-12 mb-3 text-center">Trees Planted</h2>

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
                        {{--<th class="px-2 py-1 whitespace-nowrap">ID</th>--}}
                        <th class="px-2 py-1 whitespace-nowrap">Date</th>
                        <th class="px-2 py-1 whitespace-nowrap">#</th>
                        <th class="px-2 py-1 whitespace-nowrap">Tree</th>
                        <th class="px-2 py-1 whitespace-nowrap">Status</th>
                        <th class="px-2 py-1 whitespace-nowrap text-center">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($plantingLocation->treePlantings()->orderBy('planting_date', 'desc')->get() as $planting)
                        <tr>
                            {{--<td class="px-2 py-1 whitespace-nowrap">{{ $planting->id }}</td>--}}
                            <td class="px-2 py-1 whitespace-nowrap">{{ \Carbon\Carbon::parse($planting->planting_date)->format('Y-m-d') }}</td>
                            <td class="px-2 py-1 whitespace-nowrap">{{ $planting->number_of_trees }}</td>
                            <td class="px-2 py-1 whitespace-nowrap">{{ $planting->treeType->name ?? 'N/A' }}</td>

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




{{-- Add this section before the closing </x-app-layout> tag --}}

<h2 class="text-2xl font-semibold mt-12 mb-3 text-center">Inspections</h2>
<div class="flex justify-end max-w-4xl mx-auto mt-6 mb-2">
    <a href="{{ route('inspections.create', ['planting_location_id' => $plantingLocation->id]) }}" class="btn btn-primary btn-sm">
        ➕ New Inspection
    </a>
</div>

@if($plantingLocation->inspections->isEmpty())
    <p class="text-center text-sm text-gray-500">No inspections recorded yet.</p>
@else
    <div class="max-w-4xl mx-auto border border-gray-300 rounded-lg shadow p-4 bg-white">
        <div class="overflow-x-auto">
            <table class="table table-auto table-sm w-fit mx-auto text-sm">
                <thead>
                    <tr>
                        <th class="px-2 py-1 whitespace-nowrap">Date</th>
                        <th class="px-2 py-1 whitespace-nowrap">Comment</th>
                        <th class="px-2 py-1 whitespace-nowrap">Pass?</th>
                        <th class="px-2 py-1 whitespace-nowrap">Inspector</th>
                        <th class="px-2 py-1 whitespace-nowrap text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($plantingLocation->inspections()->orderBy('inspection_date', 'desc')->get() as $inspection)
                        <tr>
                            <td class="px-2 py-1 whitespace-nowrap">{{ \Carbon\Carbon::parse($inspection->inspection_date)->format('Y-m-d') }}</td>
                            <td class="px-2 py-1">{{ $inspection->comment ?: '—' }}</td>
                            <td class="px-2 py-1 whitespace-nowrap">
                                @if($inspection->verified)
                                    <span class="text-green-600">✓</span>
                                @else
                                    <span class="text-red-600">✗</span>
                                @endif
                            </td>
                            <td class="px-2 py-1 whitespace-nowrap">{{ $inspection->user->name ?? 'N/A' }}</td>
                            <td class="px-2 py-1 whitespace-nowrap">
                                <div class="flex flex-wrap justify-center gap-1">
                                    <a href="{{ route('inspections.edit', $inspection) }}" class="btn btn-warning btn-xs">Edit</a>
                                    <form action="{{ route('inspections.destroy', $inspection) }}" method="POST" onsubmit="return confirm('Delete this inspection?')">
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

        <h2 class="text-2xl font-semibold mt-12 mb-3 text-center">Photos</h2>
    <div class="flex justify-center items-center ">
        <a href="{{ route('pictures.create', $plantingLocation->id) }}">
            <button class="btn btn-primary mt-4">📷 Take Photo</button>
        </a>
    </div>


    @if($plantingLocation->pictures->count())
        {{--<h3 class="text-lg font-semibold mb-2 text-center">Uploaded Photos</h3>--}}

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
</x-app-layout>
