<x-app-layout>
    @if(session('success'))
        <div class="flex justify-center mt-4 mb-4">
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded shadow-md w-fit flex items-center">
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

        <!-- Location Button -->
        <div class="flex justify-center flex-wrap gap-2 mb-4">
            @role('Admin|SuperAdmin|Monitor|Grower')
            <a href="{{ route('planting-locations.edit', $plantingLocation) }}" class="bg-yellow-500 text-white px-3 py-1 text-xs rounded hover:bg-yellow-600 transition-colors">Edit Site Data</a>
            @endrole
            @role('Admin|SuperAdmin')
            <form action="{{ route('planting-locations.destroy', $plantingLocation) }}" method="POST"
                  onsubmit="return confirm('Are you sure you want to delete this location?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-500 text-white px-3 py-1 text-xs rounded hover:bg-red-600 transition-colors">Delete</button>
            </form>
            @endrole


        </div>
        <div class="flex justify-center">
            <a href="{{ route('public.planting-locations.show', $plantingLocation->public_code) }}" class="bg-yellow-500 text-white px-3 py-1 text-lg rounded hover:bg-yellow-600 transition-colors">PUBLIC PAGE</a>
        </div>
        <div class="flex justify-center mt-5">
            <a href="{{ route('planting-locations.qr-label', $plantingLocation) }}"
               target="_blank"
               class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition-colors">
                Print QR Label
            </a>
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



    <!-- Trees Planted -->
    <h2 class="text-2xl font-semibold mt-12 mb-3 text-center">Trees Planted</h2>

    <div class="flex justify-end max-w-4xl mx-auto mt-6 mb-2">
        <a href="{{ route('tree-plantings.create', ['planting_location_id' => $plantingLocation->id]) }}" class="bg-primary text-white px-4 py-2 text-sm rounded hover:bg-green-700 transition-colors">
            ➕ New Tree Planting
        </a>
    </div>
    @if($plantingLocation->treePlantings->isEmpty())
        <p class="text-center text-sm text-gray-500">No tree plantings recorded yet.</p>
    @else
        <div class="max-w-4xl mx-auto border border-gray-300 rounded-lg shadow p-4 bg-white">
            <div class="overflow-x-auto">
                <table class="w-auto mx-auto text-sm border-collapse">
                    <thead>
                    <tr>
                        <th class="px-2 py-1 whitespace-nowrap border-b">Date</th>
                        <th class="px-2 py-1 whitespace-nowrap border-b">#</th>
                        <th class="px-2 py-1 whitespace-nowrap border-b">Tree</th>
                        <th class="px-2 py-1 whitespace-nowrap border-b">Years</th>
                        <th class="px-2 py-1 whitespace-nowrap border-b">Status</th>
                        <th class="px-2 py-1 whitespace-nowrap border-b">Added By</th>
                        <th class="px-2 py-1 whitespace-nowrap text-center border-b">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($plantingLocation->treePlantings()->orderBy('planting_date', 'desc')->get() as $planting)
                        <tr class="@if($planting->status === 1) text-red-600 @elseif($planting->status === 2) text-green-600 @endif border-b">
                            <td class="px-2 py-1 whitespace-nowrap">{{ \Carbon\Carbon::parse($planting->planting_date)->format('Y-m-d') }}</td>
                            <td class="px-2 py-1 whitespace-nowrap">{{ $planting->number_of_trees }}</td>
                            <td class="px-2 py-1 whitespace-nowrap">{{ $planting->treeType->name ?? 'N/A' }}</td>
                            <td class="px-2 py-1 whitespace-nowrap">{{ number_format(\Carbon\Carbon::parse($planting->planting_date)->diffInDays(now()) / 365.25, 1) }}</td>
                            <td class="px-2 py-1 whitespace-nowrap">{{ $planting->statusRelation->tree_planting_status ?? 'N/A' }}</td>
                            <td class="px-2 py-1 whitespace-nowrap">{{ $planting->user->name ?? 'N/A' }}</td>
                            <td class="px-2 py-1 whitespace-nowrap">
                                <div class="flex flex-wrap justify-center gap-1">
                                    @role('Admin|SuperAdmin|Monitor|Grower')
                                    <a href="{{ route('tree-plantings.edit', $planting) }}" class="bg-yellow-500 text-white px-2 py-1 text-xs rounded hover:bg-yellow-600 transition-colors">Edit</a>
                                    @endrole
                                    @role('Admin|SuperAdmin')
                                    <form action="{{ route('tree-plantings.destroy', $planting) }}" method="POST" onsubmit="return confirm('Delete this planting?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="bg-red-500 text-white px-2 py-1 text-xs rounded hover:bg-red-600 transition-colors">Delete</button>
                                    </form>
                                    @endrole
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Inspections -->
    <h2 class="text-2xl font-semibold mt-12 mb-3 text-center">Inspections</h2>
    <div class="flex justify-end max-w-4xl mx-auto mt-6 mb-2">
        @role('Admin|SuperAdmin|Monitor')
        <a href="{{ route('inspections.create', ['planting_location_id' => $plantingLocation->id]) }}" class="bg-primary text-white px-4 py-2 text-sm rounded hover:bg-green-700 transition-colors">
            ➕ New Inspection
        </a>
        @endrole
    </div>

    @if($plantingLocation->inspections->isEmpty())
        <p class="text-center text-sm text-gray-500">No inspections recorded yet.</p>
    @else
        <div class="max-w-4xl mx-auto border border-gray-300 rounded-lg shadow p-4 bg-white">
            <div class="overflow-x-auto">
                <table class="w-auto mx-auto text-sm border-collapse">
                    <thead>
                    <tr>
                        <th class="px-2 py-1 whitespace-nowrap border-b">Date</th>
                        <th class="px-2 py-1 whitespace-nowrap border-b">Comment</th>
                        <th class="px-2 py-1 whitespace-nowrap border-b">Pass?</th>
                        <th class="px-2 py-1 whitespace-nowrap border-b">Monitor</th>
                        @role('Admin|SuperAdmin|Monitor')
                        <th class="px-2 py-1 whitespace-nowrap text-center border-b">Actions</th>
                        @endrole
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($plantingLocation->inspections()->orderBy('inspection_date', 'desc')->get() as $inspection)
                        <tr class="border-b">
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
                                    @role('Admin|SuperAdmin|Monitor')
                                    <a href="{{ route('inspections.edit', $inspection) }}" class="bg-yellow-500 text-white px-2 py-1 text-xs rounded hover:bg-yellow-600 transition-colors">Edit</a>
                                    @endrole
                                    @role('Admin|SuperAdmin')
                                    <form action="{{ route('inspections.destroy', $inspection) }}" method="POST" onsubmit="return confirm('Delete this inspection?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="bg-red-500 text-white px-2 py-1 text-xs rounded hover:bg-red-600 transition-colors">Delete</button>
                                    </form>
                                    @endrole
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Contributors -->
    <h2 class="text-2xl font-semibold mt-12 mb-3 text-center">Contributors</h2>
    <div class="flex justify-end max-w-4xl mx-auto mb-2 ">
        @role('Admin|SuperAdmin|Monitor|Grower')
        <a href="{{ route('planting-locations.edit', $plantingLocation) }}"
           class="bg-yellow-500 text-white px-4 py-2 text-sm rounded hover:bg-yellow-600 transition-colors">
            ✏️ Edit Contributors
        </a>
        @endrole
    </div>

    <div class="max-w-4xl mx-auto border border-gray-300 rounded-lg shadow p-6 bg-white mb-10 text-center">
        @if($plantingLocation->contributors)
            <div class="prose max-w-none">
                {!! $plantingLocation->contributors !!}
            </div>
        @else
            <p class="text-center text-sm text-gray-500">No contributors listed yet.</p>
        @endif
    </div>


        <!-- Photos -->
        <h2 class="text-2xl font-semibold mt-12 mb-3 text-center">Photos</h2>
        <div class="flex flex-wrap justify-center items-center gap-3 mb-6 mt-4">

            {{-- Camera capture (existing) --}}
            <a href="{{ route('pictures.create', $plantingLocation->id) }}">
                <button class="bg-primary text-white px-6 py-3 rounded-lg hover:bg-green-700 transition-colors font-medium">
                    📷 Take Photo
                </button>
            </a>

            {{-- Upload from device (new) --}}
            <a href="{{ route('pictures.upload.form', $plantingLocation) }}">
                <button class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors font-medium">
                    🖼 Upload from Device
                </button>
            </a>

        </div>

        @if($plantingLocation->pictures->count())
            <div class="max-w-5xl mx-auto grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-10">
                @foreach($plantingLocation->pictures()->latest()->get() as $pic)

                    @php
                        // Can this user manage this picture?
                        $canManage = Auth::check() && (
                            Auth::id() === $pic->user_id ||
                            Auth::user()->hasRole(['Admin', 'SuperAdmin'])
                        );
                    @endphp

                    <div class="border rounded-lg shadow overflow-hidden bg-white flex flex-col">

                        {{-- Image --}}
                        <div class="relative">
                            <img
                                src="{{ asset('storage/' . $pic->path) }}"
                                alt="Photo"
                                class="w-full h-40 object-cover"
                            >

                            {{-- "Show on welcome" badge (always visible so everyone can see the state) --}}
                            <span class="absolute top-1 left-1 text-xs px-1.5 py-0.5 rounded
                        {{ $pic->show_on_welcome ? 'bg-green-600 text-white' : 'bg-gray-400 text-white' }}">
                        {{ $pic->show_on_welcome ? '🌐 Welcome' : '🚫 Hidden' }}
                    </span>
                        </div>

                        {{-- Meta --}}
                        <div class="px-2 py-1 text-xs text-gray-500">
                            {{ $pic->created_at->format('Y-m-d H:i') }}
                            @if($pic->user)
                                · {{ $pic->user->name }}
                            @endif
                        </div>

                        {{-- Actions (only shown to users with permission) --}}
                        @if($canManage)
                            <div class="flex items-center justify-between gap-1 px-2 pb-2 mt-auto">

                                {{-- Toggle "show on welcome page" --}}
                                <form
                                    action="{{ route('pictures.toggle-welcome', $pic) }}"
                                    method="POST"
                                    title="{{ $pic->show_on_welcome ? 'Hide from welcome page' : 'Show on welcome page' }}"
                                >
                                    @csrf
                                    @method('PATCH')
                                    <button
                                        type="submit"
                                        class="flex items-center gap-1 text-xs px-2 py-1 rounded border transition-colors
                                    {{ $pic->show_on_welcome
                                        ? 'border-green-500 text-green-700 hover:bg-green-50'
                                        : 'border-gray-400 text-gray-600 hover:bg-gray-50' }}"
                                    >
                                        {{-- Visual toggle switch --}}
                                        <span class="relative inline-block w-7 h-4 flex-shrink-0">
                                    <span class="block w-full h-full rounded-full transition-colors
                                        {{ $pic->show_on_welcome ? 'bg-green-500' : 'bg-gray-300' }}">
                                    </span>
                                    <span class="absolute top-0.5 left-0.5 w-3 h-3 bg-white rounded-full shadow transition-transform
                                        {{ $pic->show_on_welcome ? 'translate-x-3' : 'translate-x-0' }}">
                                    </span>
                                </span>
                                        Welcome
                                    </button>
                                </form>

                                {{-- Delete --}}
                                <form
                                    action="{{ route('pictures.destroy', $pic) }}"
                                    method="POST"
                                    onsubmit="return confirm('Delete this photo? This cannot be undone.')"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="submit"
                                        class="text-xs px-2 py-1 rounded bg-red-500 text-white hover:bg-red-600 transition-colors"
                                    >
                                        🗑 Delete
                                    </button>
                                </form>

                            </div>
                        @endif

                    </div>
                @endforeach
            </div>
        @else
            <p class="text-center text-gray-500 mb-10">No pictures uploaded yet.</p>
        @endif

</x-app-layout>
