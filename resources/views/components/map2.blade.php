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

        {{-- Boundary overlay: supplements the point marker(s) above, doesn't replace them --}}
        @if(isset($boundary) && $boundary)
        L.geoJSON(@json($boundary), {
            style: { color: '#2d6118', weight: 2, fillColor: '#4a9030', fillOpacity: 0.15 }
        }).addTo(map);
        @endif
    });
</script>
