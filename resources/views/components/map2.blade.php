{{-- Include Leaflet (ideally only once globally, or wrap in @once) --}}
@once
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    {{-- This component is also rendered standalone on the public
         /p/{public_code} page, which does not extend layouts/app.blade.php
         and so never receives that layout's token injection — set it here
         too so satellite view works there as well. Same value either way,
         so re-setting it when app.blade.php already has is harmless. --}}
    <script>
        window.mapboxAccessToken = @json(config('services.mapbox.access_token'));
    </script>
@endonce

{{-- Map container --}}
<div id="{{ $id ?? 'map' }}" style="height: {{ $height ?? '400px' }}; width: {{ $width ?? '100%' }};"></div>

{{-- Initialize map --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const map = L.map('{{ $id ?? 'map' }}').setView([{{ $lat }}, {{ $lng }}], {{ $zoom ?? 13 }});

        L.control.scale({
            imperial: false,
            metric: true,
            maxWidth: 200
        }).addTo(map);

        const osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        if (window.mapboxAccessToken) {
            const satelliteLayer = L.tileLayer('https://api.mapbox.com/styles/v1/mapbox/satellite-streets-v12/tiles/{z}/{x}/{y}{r}?access_token=' + window.mapboxAccessToken, {
                attribution: '© <a href="https://www.mapbox.com/about/maps/">Mapbox</a> © <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                tileSize: 512,
                zoomOffset: -1,
                maxZoom: 20
            });

            L.control.layers({ 'Street': osmLayer, 'Satellite': satelliteLayer }).addTo(map);
        }

        // Define single marker icon
        const defaultIcon = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        });

        @if(isset($markers))
        @foreach ($markers as $marker)
        L.marker([{{ $marker['lat'] }}, {{ $marker['lng'] }}], {
            icon: defaultIcon
        })
        @if(isset($marker['popup']))
            .bindPopup(`{!! $marker['popup'] !!}`)
            @endif
            .addTo(map);
        @endforeach
        @endif

        {{-- Boundary overlay: supplements the point marker(s) above, doesn't replace them.
             Matches the edit page's polygon appearance for visual consistency: '#3388ff'
             is Leaflet's own core default Path color, and is also leaflet-draw@1.0.4's
             shapeOptions.color default (confirmed against both libraries' source, the
             same versions loaded via CDN in create/edit.blade.php) — so it's what the
             edit page's boundary already renders as, whether freshly drawn or loaded from
             an existing geometry. Also confirmed high-contrast against both basemaps. --}}
        @if(isset($boundary) && $boundary)
        L.geoJSON(@json($boundary), {
            style: { color: '#3388ff', weight: 3, opacity: 1, fillColor: '#3388ff', fillOpacity: 0.2 }
        }).addTo(map);
        @endif

        {{-- Photo capture-location markers. Rendered on BOTH the admin show
             page and the public /p/{public_code} page — see DECISIONS.md,
             "Photo capture-location markers now public on both pages" — so
             this only cares whether a non-empty :photos prop was passed,
             not which controller built it. Red for a photo inside the
             boundary, or where there's no boundary to check against at
             all; violet for one flagged outside — a visually-distinct
             color chosen specifically to be spottable across the whole map
             at a glance, not just after clicking each marker individually. --}}
        @if(isset($photos) && count($photos))
        const photoIconInside = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        });

        const photoIconOutside = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-violet.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        });

        @foreach($photos as $photo)
        L.marker([{{ $photo['lat'] }}, {{ $photo['lng'] }}], {
            icon: {{ $photo['inside_boundary'] === false ? 'photoIconOutside' : 'photoIconInside' }}
        }).bindPopup(`
            <div style="min-width:150px">
                <a href="{{ $photo['full_url'] }}" target="_blank" rel="noopener">
                    <img src="{{ $photo['thumb_url'] }}" style="width:100%;max-width:150px;display:block;margin-bottom:6px;border-radius:4px;">
                </a>
                <div style="font-size:12px;">{{ $photo['captured_at'] ?? 'Unknown date' }}</div>
                @if($photo['capture_source_label'])
                <div style="font-size:12px;color:#555;">{{ $photo['capture_source_label'] }}</div>
                @endif
                @if($photo['inside_boundary'] === false)
                <div style="font-size:12px;color:#b91c1c;font-weight:600;margin-top:6px;">⚠️ This photo's location is outside the mapped site boundary</div>
                @endif
            </div>
        `).addTo(map);
        @endforeach
        @endif

        {{-- Adjacent PlantingLocations (dimmed context, not the current
             location). Admin-only — only ever present when the caller
             passes a non-empty :neighbors prop (the public page never
             does). Gray marker (kept distinct from the current location's
             own green marker); boundary uses the same blue/fill as the
             current location's own '#3388ff' boundary above, but with a
             faint stroke opacity so it reads as background context rather
             than the shape being edited. Popup is just the neighbor's
             name, linked to its own show page. --}}
        @if(isset($neighbors) && count($neighbors))
        const neighborIcon = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-grey.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        });

        @foreach($neighbors as $neighbor)
        L.marker([{{ $neighbor['latitude'] }}, {{ $neighbor['longitude'] }}], {
            icon: neighborIcon
        }).bindPopup(`<a href="{{ route('planting-locations.show', $neighbor['id']) }}">{{ $neighbor['location'] }}</a>`)
          .addTo(map);

        @if(!empty($neighbor['boundary_geojson']))
        L.geoJSON(@json($neighbor['boundary_geojson']), {
            style: { color: '#3388ff', weight: 2.5, opacity: 0.35, fillColor: '#3388ff', fillOpacity: 0.2 }
        }).bindPopup(`<a href="{{ route('planting-locations.show', $neighbor['id']) }}">{{ $neighbor['location'] }}</a>`)
          .addTo(map);
        @endif
        @endforeach
        @endif
    });
</script>
