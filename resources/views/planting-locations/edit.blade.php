<x-app-layout>
    <div class="max-w-2xl mx-auto mt-10 p-6 bg-white shadow-md rounded-lg">
        <h1 class="text-2xl font-bold text-center text-gray-800 mb-6">Edit Planting Location</h1>

        <form id="edit-location-form" method="POST" action="{{ route('planting-locations.update', $plantingLocation) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <!-- Location Name -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1" for="location">
                    <span>Location Name</span>
                </label>
                <input type="text" id="location" name="location"
                       class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary"
                       value="{{ old('location', $plantingLocation->location) }}" required>

                @error('location')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Division -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1" for="division_id">
                    <span>Region</span>
                </label>
                <select id="division_id" name="division_id"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary"
                        required>
                    <option value="">-- Select Division --</option>
                    @foreach($divisions as $division)
                        <option value="{{ $division->id }}" @selected(old('division_id', $plantingLocation->division_id) == $division->id)>
                            {{ $division->LGA_name }}
                        </option>
                    @endforeach
                </select>

                @error('division_id')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Status -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1" for="status_id">
                    <span>Status</span>
                </label>
                <select id="status_id" name="status_id"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary"
                        required>
                    <option value="">-- Select Status --</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status->id }}" @selected(old('status_id', $plantingLocation->status_id) == $status->id)>
                            {{ $status->planting_location_status }}
                        </option>
                    @endforeach
                </select>

                @error('status_id')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Contributors -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    <span>Contributors</span>
                </label>

                {{-- Holds existing DB value for JS to read after Quill init --}}
                <input type="hidden" id="contributors-existing"
                       value="{{ old('contributors', $plantingLocation->contributors) }}">

                {{-- Quill renders here — must be empty, JS loads content after init --}}
                <div id="contributors-editor"
                     class="bg-white border border-gray-300 rounded-md"
                     style="height: 200px;"></div>

                {{-- Carries the value on form submit --}}
                <input type="hidden" name="contributors" id="contributors-input">

                @error('contributors')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Comment -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1" for="comment">
                    <span>Describe the location (optional)</span>
                </label>
                <textarea id="comment" name="comment"
                          class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary"
                          rows="3">{{ old('comment', $plantingLocation->comment) }}</textarea>

                @error('comment')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- 🗺️ Map Preview -->
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    <span class="font-semibold">Map Preview</span>
                </label>
                <div id="map" class="rounded border" style="height: 300px;"></div>
            </div>

            <!-- 🔷 Site Boundary (optional) -->
            <div class="mt-2">
                <div class="flex items-center justify-between mb-1">
                    <label class="block text-sm font-medium text-gray-700">
                        <span class="font-semibold">Site Boundary (optional)</span>
                    </label>
                    <button type="button" onclick="clearBoundary()"
                            class="text-xs text-red-600 hover:text-red-800 underline">
                        Clear Boundary
                    </button>
                </div>
                <p class="text-xs text-gray-500 mb-1">
                    Use the polygon tool (⬠) in the map's top-right corner to trace or reshape the site's boundary. Optional — leave blank if you only want the point above.
                </p>
                <input type="hidden" id="boundary-existing"
                       value="{{ old('boundary_geojson') ? '' : json_encode($plantingLocation->boundary_geojson) }}">
                <input type="hidden" name="boundary_geojson" id="boundary_geojson" value="{{ old('boundary_geojson') }}">
                @error('boundary_geojson')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Latitude & Longitude -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="latitude">
                        <span>Latitude</span>
                    </label>
                    <input type="text" id="latitude" name="latitude"
                           class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary"
                           value="{{ old('latitude', $plantingLocation->latitude) }}">

                    @error('latitude')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="longitude">
                        <span>Longitude</span>
                    </label>
                    <input type="text" id="longitude" name="longitude"
                           class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary"
                           value="{{ old('longitude', $plantingLocation->longitude) }}">

                    @error('longitude')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Coordinate provenance (not user-facing — set by JS below) -->
            <input type="hidden" name="capture_method" id="capture_method" value="manual">
            <input type="hidden" name="gps_accuracy_meters" id="gps_accuracy_meters" value="">

            <!-- 📍 GPS Button -->
            <div class="flex justify-end">
                <button type="button" onclick="getLocation()"
                        class="bg-blue-500 text-white px-4 py-2 text-sm rounded hover:bg-blue-600 transition-colors mb-2">
                    📍 Get location from phone GPS
                </button>
            </div>

            <!-- Buttons -->
            <div class="flex justify-end space-x-2 pt-4">
                <button type="submit"
                        class="bg-primary text-white px-6 py-3 rounded hover:bg-green-700 transition-colors">
                    Save Changes
                </button>
                <a href="{{ route('planting-locations.show', $plantingLocation) }}"
                   class="border border-gray-300 text-gray-700 px-6 py-3 rounded hover:bg-gray-50 transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>

    <!-- Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <!-- Leaflet.draw (polygon boundary editor) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.css" />
    <!-- Quill -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.js"></script>
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

    <script>
        let map, marker, quill;
        let drawnItems, drawControl;
        // True only while getLocation() is assigning lat/lng itself, so
        // the input listener below can tell "GPS button set this" apart
        // from a genuine keystroke and not stomp on the method it just set.
        let settingViaGps = false;

        function initMap() {
            const lat = parseFloat(document.getElementById('latitude').value) || 9.0820;
            const lng = parseFloat(document.getElementById('longitude').value) || 8.6753;

            map = L.map('map').setView([lat, lng], 13);

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

            marker = L.marker([lat, lng]).addTo(map);

            initBoundaryDrawing();
            renderNeighbors();
        }

        {{-- Adjacent PlantingLocations (dimmed context, not the current
             location) — admin-only, this edit page and the show page's
             map2.blade.php only, not create.blade.php or the public page.
             This file already duplicates its own Leaflet setup rather than
             sharing map2.blade.php's, so this mirrors that same pattern
             rather than reusing map2's rendering code. --}}
        @php
            $neighborsForJs = $neighbors->map(fn ($n) => [
                'id'               => $n->id,
                'location'         => $n->location,
                'latitude'         => $n->latitude,
                'longitude'        => $n->longitude,
                'boundary_geojson' => $n->boundary_geojson,
                'url'              => route('planting-locations.show', $n->id),
            ]);
        @endphp
        const neighbors = @json($neighborsForJs);

        function escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }

        function renderNeighbors() {
            if (!neighbors || neighbors.length === 0) {
                return;
            }

            const neighborIcon = L.icon({
                iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-grey.png',
                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowSize: [41, 41]
            });

            neighbors.forEach(function (neighbor) {
                const popupHtml = '<a href="' + neighbor.url + '">' + escapeHtml(neighbor.location) + '</a>';

                L.marker([neighbor.latitude, neighbor.longitude], { icon: neighborIcon })
                    .bindPopup(popupHtml)
                    .addTo(map);

                // Same blue/fill as the current location's own boundary
                // style ('#3388ff', matched to Leaflet.draw's default
                // elsewhere on this page), but a faint stroke opacity so it
                // reads as background context rather than the shape being
                // edited.
                if (neighbor.boundary_geojson) {
                    L.geoJSON(neighbor.boundary_geojson, {
                        style: { color: '#3388ff', weight: 2.5, opacity: 0.35, fillColor: '#3388ff', fillOpacity: 0.2 }
                    }).bindPopup(popupHtml).addTo(map);
                }
            });
        }

        // Exactly one polygon per location: CREATED clears any prior
        // drawn shape before adding the new one, so a second polygon
        // never coexists with the first. If a boundary already exists
        // for this location, it's loaded onto the map as an editable
        // layer so the user can reshape or replace it.
        function initBoundaryDrawing() {
            drawnItems = new L.FeatureGroup();
            map.addLayer(drawnItems);

            drawControl = new L.Control.Draw({
                draw: {
                    polygon: {
                        allowIntersection: false,
                        showArea: true,
                    },
                    polyline: false,
                    rectangle: false,
                    circle: false,
                    circlemarker: false,
                    marker: false,
                },
                edit: {
                    featureGroup: drawnItems,
                    remove: true,
                },
            });
            map.addControl(drawControl);

            map.on(L.Draw.Event.CREATED, function (event) {
                drawnItems.clearLayers();
                drawnItems.addLayer(event.layer);
                syncBoundaryField();
            });

            map.on(L.Draw.Event.EDITED, syncBoundaryField);
            map.on(L.Draw.Event.DELETED, syncBoundaryField);

            loadExistingBoundary();
        }

        function loadExistingBoundary() {
            const raw = document.getElementById('boundary-existing').value;

            if (!raw || raw.trim() === '' || raw.trim() === 'null') {
                return;
            }

            let geometry;
            try {
                geometry = JSON.parse(raw);
            } catch (e) {
                return;
            }

            if (!geometry) {
                return;
            }

            const layer = L.geoJSON(geometry).getLayers()[0];
            if (layer) {
                drawnItems.addLayer(layer);
                syncBoundaryField();
                map.fitBounds(layer.getBounds());
            }
        }

        function syncBoundaryField() {
            const layers = drawnItems.getLayers();
            const field = document.getElementById('boundary_geojson');

            if (layers.length === 0) {
                field.value = '';
                return;
            }

            field.value = JSON.stringify(layers[0].toGeoJSON().geometry);
        }

        function clearBoundary() {
            drawnItems.clearLayers();
            document.getElementById('boundary_geojson').value = '';
        }

        function initQuill() {
            quill = new Quill('#contributors-editor', {
                theme: 'snow',
                placeholder: 'List partners and contributors...',
                modules: {
                    toolbar: [
                        ['bold', 'italic', 'underline'],
                        [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                        ['link'],
                        ['clean']
                    ]
                }
            });

            const existing = document.getElementById('contributors-existing').value;
            if (existing && existing.trim() !== '') {
                quill.clipboard.dangerouslyPasteHTML(existing);
            }
        }

        function updateMapMarker() {
            const lat = parseFloat(document.getElementById('latitude').value);
            const lng = parseFloat(document.getElementById('longitude').value);

            if (!isNaN(lat) && !isNaN(lng)) {
                marker.setLatLng([lat, lng]);
                map.setView([lat, lng], 13);
            }
        }

        function getLocation() {
            if (!navigator.geolocation) {
                alert("Geolocation is not supported by your browser.");
                return;
            }

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    settingViaGps = true;

                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;

                    document.getElementById('latitude').value = lat.toFixed(6);
                    document.getElementById('longitude').value = lng.toFixed(6);
                    document.getElementById('capture_method').value = 'gps_button';
                    document.getElementById('gps_accuracy_meters').value = position.coords.accuracy;

                    updateMapMarker();

                    settingViaGps = false;
                },
                () => {
                    alert("Unable to retrieve your location.");
                }
            );
        }

        // Fires on both a genuine keystroke and (in principle) any
        // script-dispatched 'input' event on these fields. If the GPS
        // button is what's changing the value, settingViaGps is already
        // true and this leaves capture_method/accuracy alone; otherwise
        // it's a real correction, so the method reverts to manual.
        function handleCoordinateInput() {
            if (!settingViaGps) {
                document.getElementById('capture_method').value = 'manual';
                document.getElementById('gps_accuracy_meters').value = '';
            }

            updateMapMarker();
        }

        window.addEventListener('DOMContentLoaded', () => {
            initMap();
            initQuill();

            // Target form directly by id to avoid selecting navbar logout form
            document.getElementById('edit-location-form').addEventListener('submit', function () {
                document.getElementById('contributors-input').value = quill.root.innerHTML;
            });

            document.getElementById('latitude').addEventListener('input', handleCoordinateInput);
            document.getElementById('longitude').addEventListener('input', handleCoordinateInput);
        });
    </script>
</x-app-layout>
