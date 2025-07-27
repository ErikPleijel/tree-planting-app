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
            imperial: false,
            metric: true,
            maxWidth: 200
        }).addTo(map);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Define custom icons
        const yellowIcon = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-yellow.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        });

        const greenIcon = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        });

        const blueIcon = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        });

        const icons = {
            'yellow': yellowIcon,
            'green': greenIcon,
            'blue': blueIcon
        };

        @foreach ($markers as $marker)
        L.marker([{{ $marker['lat'] }}, {{ $marker['lng'] }}], {
            icon: icons['{{ $marker['markerType'] }}']
        })
            .addTo(map)
            .bindPopup(`{!! $marker['popup'] !!}`);
        @endforeach

        // Add legend
        const legend = L.control({position: 'bottomright'});
        legend.onAdd = function (map) {
            const div = L.DomUtil.create('div', 'info legend');
            div.innerHTML = `
                <div style="background: white; padding: 10px; border-radius: 5px; border: 1px solid #ccc;">
                    <h4 class="font-bold mb-2">Tree Status</h4>
                    <div style="margin-bottom: 5px;">
                        <img src="https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-yellow.png" style="width: 12px; vertical-align: middle;">
                        <span style="margin-left: 5px;">Verified</span>
                    </div>
                    <div style="margin-bottom: 5px;">
                        <img src="https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png" style="width: 12px; vertical-align: middle;">
                        <span style="margin-left: 5px;">Planted</span>
                    </div>

                </div>
            `;
            return div;
        };
        legend.addTo(map);
    });
</script>
