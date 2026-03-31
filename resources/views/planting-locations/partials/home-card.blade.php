@php
    $totalTrees = $plantingLocation->treePlantings->sum('number_of_trees');

    $latestPlanting = $plantingLocation->treePlantings
        ->sortByDesc('planting_date')
        ->first();

    $treeTypes = $plantingLocation->treePlantings
        ->filter(fn($p) => $p->treeType)
        ->groupBy('treeType.name')
        ->map(fn($group) => $group->sum('number_of_trees'))
        ->sortByDesc(fn($count) => $count);
@endphp

<div class="bg-white border-2 border-[#b6d89a] rounded-2xl shadow-md hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden h-full flex flex-col">
    <div class="bg-[#1a3319] text-[#e8f4df] px-5 py-4 relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-[2px] bg-gradient-to-r from-transparent via-[#6fcf55] to-transparent opacity-70"></div>

        <div class="relative z-10">
            <h3 class="text-2xl font-semibold leading-tight mb-1" style="font-family: 'Lora', serif;">
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

    <div class="p-5 flex-1 flex flex-col">
        <div class="mb-4">
            <div class="text-3xl font-bold text-[#2d6118] leading-none">
                {{ number_format($totalTrees) }}
            </div>
            <div class="text-sm text-[#6b8c5a] mt-1">
                Trees planted
            </div>
        </div>

        <div class="mb-4">
            <h4 class="text-sm font-semibold text-gray-700 mb-2">Tree types</h4>

            @if($treeTypes->isNotEmpty())
                <div class="flex flex-wrap gap-2">
                    @foreach($treeTypes->take(5) as $name => $count)
                        <span class="inline-flex items-center gap-2 bg-[#eaf4de] text-[#2d6118] border border-[#c0dd97] text-xs italic px-3 py-1 rounded-full">
                            <span class="not-italic font-semibold text-[#3a7020] bg-white/70 px-2 py-[1px] rounded-full">
                                {{ $count }}
                            </span>
                            {{ $name }}
                        </span>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-400">No tree types listed yet.</p>
            @endif
        </div>

        <div class="mt-auto pt-2">
            <a href="{{ route('public.planting-locations.show', $plantingLocation->public_code) }}"
               class="inline-block bg-primary text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                View location
            </a>
        </div>
    </div>
</div>
