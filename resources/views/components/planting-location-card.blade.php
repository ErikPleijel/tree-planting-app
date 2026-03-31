@props(['plantingLocation'])

@php
    $totalTrees = $plantingLocation->treePlantings->sum('number_of_trees');

    $latestPlanting = $plantingLocation->treePlantings
        ->sortByDesc('planting_date')
        ->first();

    $contributorsText = trim(strip_tags($plantingLocation->contributors ?? ''));
@endphp

<div class="bg-white border border-[#d6e8cc] rounded-2xl shadow-sm overflow-hidden">
    <div class="bg-[#1a3319] text-[#e8f4df] px-5 py-5 relative overflow-hidden">
        <div class="relative z-10">
            <h3 class="font-serif text-2xl leading-tight mb-1">
                {{ $plantingLocation->location }}
            </h3>

            <p class="text-sm text-[#a8d48a]">
                {{ $plantingLocation->division->LGA_name ?? 'Unknown region' }}
            </p>

            @if($latestPlanting)
                <p class="text-sm text-[#b6d89a] mt-2">
                    Latest planting: {{ \Carbon\Carbon::parse($latestPlanting->planting_date)->format('d M Y') }}
                </p>
            @endif
        </div>

        <div class="absolute inset-0 pointer-events-none"
             style="background-image:
                radial-gradient(circle at 20% 80%, rgba(80,140,50,0.18) 0%, transparent 55%),
                radial-gradient(circle at 80% 20%, rgba(50,100,30,0.15) 0%, transparent 50%);">
        </div>
    </div>

    <div class="p-5">
        <div class="flex items-center justify-between gap-4 mb-4">
            <div>
                <div class="text-3xl font-semibold text-[#2d6118] leading-none">
                    {{ $totalTrees }}
                </div>
                <div class="text-sm text-[#6b8c5a]">
                    Trees planted
                </div>
            </div>

            <div class="text-right">
                <div class="inline-block bg-[#eaf4de] text-[#2d6118] border border-[#c0dd97] text-xs px-3 py-1 rounded-full">
                    {{ wordwrap($plantingLocation->public_code, 3, ' ', true) }}
                </div>
            </div>
        </div>

        @if($contributorsText)
            <div class="mb-4">
                <h4 class="text-sm font-semibold text-gray-700 mb-1">Contributors</h4>
                <p class="text-sm text-[#4a6640] leading-relaxed">
                    {{ \Illuminate\Support\Str::limit($contributorsText, 140) }}
                </p>
            </div>
        @endif

        <a href="{{ route('planting-locations.public.show', $plantingLocation->public_code) }}"
           class="inline-block bg-primary text-white px-4 py-2 rounded hover:bg-green-700 transition-colors">
            View public page
        </a>
    </div>
</div>
