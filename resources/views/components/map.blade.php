{{-- Include Leaflet (ideally only once globally, or wrap in @once) --}}
@once
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@endonce

{{-- Map container --}}
<div id="{{ $id }}" style="height: {{ $height }}; width: {{ $width }};"></div>

{{-- Initialize map --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const map = L.map('{{ $id }}').setView([{{ $lat }}, {{ $lng }}], {{ $zoom }});

        L.control.scale({
            imperial: false,  // disable miles
            metric: true,     // enable km
            maxWidth: 200     // optional: controls how wide the scale can be
        }).addTo(map);


        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        @foreach ($markers as $marker)
        L.marker([{{ $marker['lat'] }}, {{ $marker['lng'] }}])
            .addTo(map)
            .bindPopup(`{!! $marker['popup'] ?? '' !!}`);
        @endforeach
    });
</script>
