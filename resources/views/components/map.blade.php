{{-- Include Leaflet (ideally only once globally, or wrap in @once) --}}
@once
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        .tree-label {
    border-radius: 5px;
    padding: 0px 1px;  /* Reduced vertical padding to 0px */
    font-weight: normal;
    white-space: nowrap;
    text-align: center;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    color: #32497e;
    background: #eff6ff;
    border: 2px solid #6e7da6;
    font-size: 13px;
    line-height: 1;  /* Add this to control vertical spacing */
    display: flex;   /* Add this for better vertical centering */
    align-items: center;  /* Add this for better vertical centering */
    height: 29px;    /* Add this to explicitly control height */
}
    </style>
@endonce

{{-- Map container --}}
<div id="{{ $id }}" style="height: {{ $height }}; width: {{ $width }};"></div>

{{-- Initialize map --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const map = L.map('{{ $id }}').setView([{{ $lat }}, {{ $lng }}], {{ $zoom }});

        L.control.scale({
            imperial: false,
            metric: true,
            maxWidth: 200
        }).addTo(map);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        const blueIcon = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        });

        const icons = {
            'blue': blueIcon
        };

        @foreach ($markers as $marker)
        // Add marker
        L.marker([{{ $marker['lat'] }}, {{ $marker['lng'] }}], {
            icon: icons['{{ $marker['markerType'] }}']
        })
        .addTo(map)
        .bindPopup(`{!! $marker['popup'] !!}`);

        // Add permanent label with tree count
        L.marker([{{ $marker['lat'] }}, {{ $marker['lng'] }}], {
            icon: L.divIcon({
                className: 'tree-label',
                html: '<div>{{ $marker['title'] }}<br>{{ $marker['totalTrees'] }}</div>',
                iconSize: null,
                iconAnchor: [0, 45]  // Position above the marker
            })
        }).addTo(map);

        @endforeach
    });
</script>
