{{-- Include Leaflet (ideally only once globally, or wrap in @once) --}}
@once
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
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

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

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
    });
</script>
