<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $plantingLocation->location }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400;0,500;1,400&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'DM Sans', sans-serif;
            background-color: #f2f5ee;
            color: #1a2e1a;
        }

        .hero {
            background-color: #1a3319;
            color: #e8f4df;
            text-align: center;
            padding: 3.5rem 1.5rem 2.5rem;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: radial-gradient(circle at 20% 80%, rgba(80,140,50,0.18) 0%, transparent 55%),
            radial-gradient(circle at 80% 20%, rgba(50,100,30,0.15) 0%, transparent 50%);
            pointer-events: none;
        }

        .hero-inner {
            position: relative;
            z-index: 1;
            max-width: 600px;
            margin: 0 auto;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.18);
            color: #a8d48a;
            font-size: 11px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            padding: 5px 14px;
            border-radius: 20px;
            margin-bottom: 1.25rem;
        }

        .hero-badge .dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #6fcf55;
        }

        .hero h1 {
            font-family: 'Lora', serif;
            font-size: clamp(2.2rem, 5vw, 3.4rem);
            font-weight: 500;
            line-height: 1.15;
            margin-bottom: 0.4rem;
            color: #e8f4df;
        }

        .hero .region {
            font-size: 14px;
            color: #8fbe72;
            margin-bottom: 0.85rem;
        }

        .hero .comment {
            font-family: 'Lora', serif;
            font-style: italic;
            font-size: 15px;
            color: #b6d89a;
            max-width: 480px;
            margin: 0.5rem auto;
            line-height: 1.7;
        }

        .hero-species {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: center;
            margin: 1.1rem auto 1.25rem;
            max-width: 520px;
        }

        .hero-chip {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: rgba(255,255,255,0.1);

            color: #e8f4e1;
            font-size: 15px;
            font-style: italic;
            padding: 5px 12px;
            border-radius: 20px;
        }

        .hero-chip-count {
            font-style: normal;
            font-size: 15px;
            font-weight: 500;
            color: #8fbe72;
            background: rgba(0,0,0,0.2);
            padding: 1px 7px;
            border-radius: 10px;
        }

        .hero .code-pill {
            display: inline-block;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.16);
            color: #c5e8b0;
            font-family: monospace;
            font-size: 13px;
            letter-spacing: 0.12em;
            padding: 1px 6px;
            border-radius: 20px;
        }

        .hero-top-link {
            margin-bottom: 1rem;
        }

        .hero-top-link a {
            display: inline-block;
            font-size: 17px;
            letter-spacing: 0.08em;

            color: #a8d48a;
            text-decoration: underline;

            background: rgba(255,255,255,0.08);
            border: 2px solid rgba(255,255,255,0.18);
            padding: 6px 14px;
            border-radius: 10px;

            transition: all 0.2s ease;
        }

        .hero-top-link a:hover {
            background: rgba(255,255,255,0.16);
            color: #e8f4df;
            transform: translateY(-1px);
        }

        .page-content {
            max-width: 680px;
            margin: 0 auto;
            padding: 2rem 1rem 4rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 1.75rem;
        }

        .stat-card {
            background: #ffffff;
            border: 1px solid #d6e8cc;
            border-radius: 14px;
            padding: 1.25rem 1rem;
            text-align: center;
        }

        .stat-icon {
            width: 36px;
            height: 36px;
            background: #eaf4de;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
        }

        .stat-icon svg {
            width: 18px;
            height: 18px;
            stroke: #3a7020;
        }

        .stat-num {
            font-size: 2rem;
            font-weight: 500;
            color: white;
            line-height: 1;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 12px;
            color: #6b8c5a;
        }

        .section-card {
            background: #ffffff;
            border: 1px solid #d6e8cc;
            border-radius: 16px;
            padding: 1.75rem 1.5rem;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .section-card h2 {
            font-family: 'Lora', serif;
            font-size: 1.6rem;
            font-weight: 500;
            color: #1a3319;
            margin-bottom: 1rem;
        }

        .section-divider {
            width: 36px;
            height: 2px;
            background: #4a9030;
            border-radius: 2px;
            margin: 0 auto 1.25rem;
        }

        /* Map */
        .map-wrapper {
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #d6e8cc;
        }

        /* Planting history */
        .planting-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
            text-align: left;
        }

        .planting-item {
            background: #f5faf0;
            border: 1px solid #d6e8cc;
            border-radius: 10px;
            padding: 0.9rem 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .planting-date {
            font-size: 12px;
            color: #6b8c5a;
            margin-bottom: 3px;
        }

        .planting-tree {
            font-size: 15px;
            font-weight: 500;
            color: #1a3319;
            font-style: italic;
        }

        .planting-count-num {
            font-size: 1.75rem;
            font-weight: 500;
            color: #2d6118;
            line-height: 1;
        }

        .planting-count-unit {
            font-size: 11px;
            color: #6b8c5a;
            text-align: right;
        }

        /* Species chips */
        .species-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: center;
        }

        .chip {
            background: #eaf4de;
            color: #2d6118;
            border: 1px solid #c0dd97;
            font-size: 13px;
            font-style: italic;
            padding: 5px 14px;
            border-radius: 20px;
        }

        /* Photos */
        .photos-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 8px;
        }

        .photos-grid img {
            width: 100%;
            aspect-ratio: 1 / 1;
            object-fit: cover;
            border-radius: 10px;
            border: 1px solid #d6e8cc;
        }

        /* Contributors */
        .contributors-body {
            font-size: 18px;
            color: #4a6640;
            line-height: 1.8;
        }

        .contributors-body a {
            color: #3a7020;
            text-decoration: underline;
        }

        .empty-state {
            font-size: 13px;
            color: #8faa7e;
        }

        /* Footer */
        .page-footer {
            text-align: center;
            font-size: 12px;
            color: #8faa7e;
            padding-bottom: 2rem;
        }

        /* About the trees */
        .tree-info-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
            text-align: left;
        }

        .tree-info-item {
            background: #f5faf0;
            border: 1px solid #d6e8cc;
            border-radius: 12px;
            padding: 1rem 1rem 0.95rem;
        }

        .tree-info-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1a3319;
            margin-bottom: 0.2rem;
        }

        .tree-info-latin {
            font-size: 0.98rem;
            font-style: italic;
            color: #4f6f45;
            margin-bottom: 0.55rem;
        }

        .tree-info-description {
            font-size: 0.95rem;
            line-height: 1.7;
            color: #3f5538;
        }
    </style>
</head>
<body>

<!-- Hero -->
<div class="hero">
    <div class="hero-inner">

        <div class="hero-top-link">
            <a href="https://ItacenMu.org" target="_blank" rel="noopener noreferrer">
                ItacenMu.org
            </a>
        </div>

        <h1 class="">{{ $plantingLocation->location }}</h1>

        <h2 class="">
            {{ $plantingLocation->division->LGA_name ?? 'N/A' }}
            <span class="text-xs ml-3">{{ wordwrap($plantingLocation->public_code, 3, ' ', true) }}</span>
        </h2>

        @if($plantingLocation->comment)
            <p class="comment">{{ $plantingLocation->comment }}</p>
        @endif



        <div class="stat-num">{{ $plantingLocation->treePlantings->sum('number_of_trees') }} Trees</div>

        @php
            $heroSpecies = $plantingLocation->treePlantings
                ->filter(fn($p) => $p->treeType)
                ->groupBy('treeType.name')
                ->map(fn($group) => $group->sum('number_of_trees'))
                ->sortByDesc(fn($count) => $count);
        @endphp

        @if($heroSpecies->isNotEmpty())
            <div class="hero-species">
                @foreach($heroSpecies as $name => $count)
                    <span class="hero-chip">
                            <span class="hero-chip-count">{{ $count }}</span>
                            {{ $name }}
                        </span>
                @endforeach
            </div>
        @endif
    </div>


</div>

@role('Admin|SuperAdmin|Monitor|Grower')
<div class="text-center  mt-2">
    <a href="{{ route('planting-locations.show', $plantingLocation) }}" class="bg-yellow-500 text-white px-3 py-1 text-lg rounded hover:bg-yellow-600 transition-colors">Edit</a>
</div>
@endrole

<div class="page-content">



    <!-- Planting history -->
    <div class="section-card">
        <h2>Planting history</h2>
        <div class="section-divider"></div>

        @if($plantingLocation->treePlantings->isEmpty())
            <p class="empty-state">No planting records yet.</p>
        @else
            <div class="planting-list">
                @foreach($plantingLocation->treePlantings()->orderBy('planting_date', 'desc')->get() as $planting)
                    <div class="planting-item">
                        <div>
                            <div class="planting-date">
                                {{ \Carbon\Carbon::parse($planting->planting_date)->format('d M Y') }}
                                &nbsp;·&nbsp;
                                {{ number_format(\Carbon\Carbon::parse($planting->planting_date)->diffInDays(now()) / 365.25, 1) }} yrs ago
                            </div>
                            <div class="planting-tree">{{ $planting->treeType->name ?? 'Unknown species' }}</div>
                        </div>
                        <div style="text-align:right; flex-shrink:0; margin-left:1rem;">
                            <div class="planting-count-num">{{ $planting->number_of_trees }}</div>
                            <div class="planting-count-unit">trees</div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    @php
        $treeTypesAtLocation = $plantingLocation->treePlantings
            ->filter(fn($planting) => $planting->treeType)
            ->pluck('treeType')
            ->unique('id')
            ->values();
    @endphp

    <div class="section-card">
        <h2>About the trees</h2>
        <div class="section-divider"></div>

        @if($treeTypesAtLocation->isEmpty())
            <p class="empty-state">No tree type information available yet.</p>
        @else
            <div class="tree-info-list">
                @foreach($treeTypesAtLocation as $treeType)
                    <div class="tree-info-item">
                        <div class="tree-info-name">{{ $treeType->name }}</div>

                        @if($treeType->latin_name)
                            <div class="tree-info-latin">{{ $treeType->latin_name }}</div>
                        @endif

                        @if($treeType->description)
                            <div class="tree-info-description">
                                {{ $treeType->description }}
                            </div>
                        @else
                            <div class="empty-state">No description available.</div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Photos -->
    <div class="section-card">
        <h2>Photos</h2>
        <div class="section-divider"></div>

        @if($plantingLocation->pictures->isEmpty())
            <p class="empty-state">No photos uploaded yet.</p>
        @else
            <div class="photos-grid">
                @foreach($plantingLocation->pictures as $pic)
                    <img
                        src="{{ asset('storage/' . $pic->path) }}"
                        alt="Photo of {{ $plantingLocation->location }}"
                    >
                @endforeach
            </div>
        @endif
    </div>

    <!-- Contributors -->
    @if($plantingLocation->contributors)
        <div class="section-card">
            <h2>Contributors</h2>
            <div class="section-divider"></div>
            <div class="contributors-body">
                {!! $plantingLocation->contributors !!}
            </div>
        </div>
    @endif



    <!-- Map -->
    @if($plantingLocation->latitude && $plantingLocation->longitude)

        <p class="region text-center">
            {{ $plantingLocation->division->LGA_name ?? 'N/A' }}
            @if($plantingLocation->latitude && $plantingLocation->longitude)
                &nbsp;·&nbsp; {{ number_format($plantingLocation->latitude, 4) }}°,
                {{ number_format($plantingLocation->longitude, 4) }}°
            @endif
        </p>

        <div class="section-card">
            <h2>Location</h2>
            <div class="section-divider"></div>
            <div class="map-wrapper">
                <x-map2
                    lat="{{ $plantingLocation->latitude }}"
                    lng="{{ $plantingLocation->longitude }}"
                    :zoom="10"
                    :markers="$markers"
                />
            </div>
        </div>
    @endif

    <!-- Footer -->
    <div class="page-footer">
        This page is publicly accessible via its unique site code.
    </div>

</div>

</body>
</html>
