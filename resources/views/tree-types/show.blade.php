<x-app-layout>
    <div class="max-w-3xl mx-auto mt-10 p-6 bg-white shadow-md rounded-lg">
        <div class="flex items-start justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">{{ $treeType->name }}</h1>

                @if($treeType->latin_name)
                    <p class="mt-1 text-lg text-gray-600 italic">{{ $treeType->latin_name }}</p>
                @endif
            </div>

            <div class="flex gap-2">
                <a href="{{ route('tree-types.edit', $treeType) }}"
                   class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600 transition-colors">
                    Edit
                </a>

                <a href="{{ route('tree-types.index') }}"
                   class="border border-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-50 transition-colors">
                    Back
                </a>
            </div>
        </div>

        <div class="border border-gray-200 rounded-lg p-5 bg-gray-50">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500 mb-3">Description</h2>

            @if($treeType->description)
                <div class="prose max-w-none text-gray-700">
                    {!! nl2br(e($treeType->description)) !!}
                </div>
            @else
                <p class="text-gray-500">No description added.</p>
            @endif
        </div>

        @if($treeType->wood_density_kg_m3 || $treeType->carbon_fraction)
            <div class="border border-gray-200 rounded-lg p-5 bg-gray-50 mt-6">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500 mb-3">Growth / Carbon Reference Data</h2>

                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                    @if($treeType->wood_density_kg_m3)
                        <div>
                            <dt class="text-gray-500">Wood Density</dt>
                            <dd class="text-gray-800 font-medium">{{ $treeType->wood_density_kg_m3 }} kg/m³</dd>
                        </div>
                    @endif

                    @if($treeType->carbon_fraction)
                        <div>
                            <dt class="text-gray-500">Carbon Fraction</dt>
                            <dd class="text-gray-800 font-medium">{{ $treeType->carbon_fraction }}</dd>
                        </div>
                    @endif
                </dl>

                @if($treeType->source_reference)
                    <p class="mt-3 text-xs text-gray-500">Source: {{ $treeType->source_reference }}</p>
                @endif
            </div>
        @endif

        <div class="mt-6 text-sm text-gray-500">
            Created: {{ $treeType->created_at->format('d M Y H:i') }}<br>
            Updated: {{ $treeType->updated_at->format('d M Y H:i') }}
        </div>
    </div>
</x-app-layout>
