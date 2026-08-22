<x-app-layout>
    <div class="max-w-2xl mx-auto mt-10 p-6 bg-white shadow-md rounded-lg">
        <h1 class="text-2xl font-bold text-center text-gray-800 mb-6">Add Planting Location</h1>

        <form id="add-location-form" method="POST" action="{{ route('planting-locations.store') }}" class="space-y-4">
            @csrf

            <!-- Location Name -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1" for="location">
                    <span>Location Name</span>
                </label>
                <input type="text" id="location" name="location"
                       class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary"
                       value="{{ old('location') }}" required>

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
                        <option value="{{ $division->id }}" @selected(old('division_id') == $division->id)>
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
                        <option value="{{ $status->id }}" @selected(old('status_id') == $status->id)>
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
                <div id="contributors-editor"
                     class="bg-white border border-gray-300 rounded-md"
                     style="height: 200px;">
                    {!! old('contributors') !!}
                </div>
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
                          rows="3">{{ old('comment') }}</textarea>

                @error('comment')
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
                           value="{{ old('latitude') }}">

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
                           value="{{ old('longitude') }}">

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

            <!-- 🗺️ Map Preview -->
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    <span class="font-semibold">Map Preview</span>
                </label>

                {{-- relative wrapper so the fixed crosshair can be absolutely
                     positioned over the map during "adjust position" mode --}}
                <div class="relative">
                    <div id="map" class="rounded border" style="height: 459px;"></div>

                    {{-- Fixed pin, always dead-center over the map. Only
                         shown while positioningMode is active. The tip of
                         the pin (not its center) marks the true coordinate,
                         so it's anchored bottom-center via the transform. --}}
                    <div id="position-crosshair"
                         class="pointer-events-none"
                         style="display: none; position: absolute; left: 50%; top: 50%; transform: translate(-50%, -100%); z-index: 1000; font-size: 2.25rem; line-height: 1; filter: drop-shadow(0 2px 2px rgba(0,0,0,0.45));">
                        📍
                    </div>
                </div>

                {{-- Out-of-bounds warning for the live "my position" tracker.
                     Only shown while tracking is active and the live fix
                     falls outside the current map view. --}}
                <div id="my-position-warning"
                     style="display: none; align-items: center; justify-content: space-between; gap: 0.5rem;"
                     class="mt-2 text-xs text-amber-800 bg-amber-50 border border-amber-200 rounded px-3 py-2">
                    <span id="my-position-warning-text"></span>
                    <button type="button" onclick="centerMapOnMyPosition()"
                            class="text-indigo-600 hover:text-indigo-800 underline whitespace-nowrap">
                        Center map here
                    </button>
                </div>

                <div class="flex flex-wrap items-center justify-end gap-2 mt-2">
                    <button type="button" id="my-position-btn" onclick="toggleMyPositionTracking()"
                            class="bg-indigo-500 text-white px-4 py-2 text-sm rounded hover:bg-indigo-600 transition-colors">
                        🧭 Show my position
                    </button>

                    <button type="button" id="adjust-position-btn" onclick="enterPositionMode()"
                            class="bg-blue-500 text-white px-4 py-2 text-sm rounded hover:bg-blue-600 transition-colors">
                        🎯 Adjust marker position
                    </button>

                    <div id="positioning-controls" style="display: none;" class="items-center gap-2">
                        <span class="text-xs text-gray-500">Pan the map so the pin marks the spot, then:</span>
                        <button type="button" onclick="confirmPosition()"
                                class="bg-green-600 text-white px-4 py-2 text-sm rounded hover:bg-green-700 transition-colors">
                            ✅ Confirm position
                        </button>
                        <button type="button" onclick="cancelPositionMode()"
                                class="border border-gray-300 text-gray-700 px-4 py-2 text-sm rounded hover:bg-gray-50 transition-colors">
                            ✖ Cancel
                        </button>
                    </div>
                </div>
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
                    Use the polygon tool (⬠) in the map's top-right corner to trace the site's boundary. Optional — leave blank if you only want the point above.
                </p>
                <input type="hidden" name="boundary_geojson" id="boundary_geojson" value="">
                @error('boundary_geojson')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Buttons -->
            <div class="flex justify-end space-x-2 pt-4">
                <button type="submit" id="submit-btn"
                        class="bg-primary text-white px-6 py-3 rounded hover:bg-green-700 transition-colors">
                    Save Location
                </button>
                <a href="{{ route('planting-locations.index') }}"
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

        // "Adjust marker position" mode state. While active, the real
        // marker is hidden and a fixed crosshair sits at the map's visual
        // center; panning the map moves the effective point. Confirming
        // reads map.getCenter() and writes it back to the marker/inputs;
        // cancelling restores whatever was there before the mode started.
        let positioningMode = false;
        let prePositionState = null;

        // "Show my position" live tracker state. myPositionMarker is a
        // separate marker from the location `marker` above and from the
        // #position-crosshair element — purely informational, never
        // written back to the latitude/longitude inputs or capture_method.
        let myPositionWatchId = null;
        let myPositionMarker = null;
        let lastMyPosition = null;

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
        }

        // Exactly one polygon per location: CREATED clears any prior
        // drawn shape before adding the new one, so a second polygon
        // never coexists with the first.
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

            map.on(L.Draw.Event.EDITED, function () {
                syncBoundaryField();
            });

            map.on(L.Draw.Event.DELETED, function () {
                syncBoundaryField();
            });
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

        // ------------------------------------------------------------
        // Feature 1: "Adjust marker position" — pan-the-map-under-a-
        // fixed-pin flow, similar to Google Maps' pin-drop UX.
        // ------------------------------------------------------------

        function enterPositionMode() {
            if (positioningMode) return;
            positioningMode = true;

            prePositionState = {
                latitude: document.getElementById('latitude').value,
                longitude: document.getElementById('longitude').value,
                captureMethod: document.getElementById('capture_method').value,
                gpsAccuracy: document.getElementById('gps_accuracy_meters').value,
            };

            marker.setOpacity(0);
            document.getElementById('position-crosshair').style.display = 'block';
            document.getElementById('adjust-position-btn').style.display = 'none';
            document.getElementById('positioning-controls').style.display = 'flex';

            const submitBtn = document.getElementById('submit-btn');
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');

            // Start the pin exactly where the real marker currently is,
            // so "adjust" begins from the existing position rather than
            // wherever the map happened to be scrolled/zoomed to.
            map.panTo(marker.getLatLng());
        }

        function confirmPosition() {
            const center = map.getCenter();

            document.getElementById('latitude').value = center.lat.toFixed(6);
            document.getElementById('longitude').value = center.lng.toFixed(6);
            document.getElementById('capture_method').value = 'map_center_adjust';
            document.getElementById('gps_accuracy_meters').value = '';

            marker.setLatLng(center);
            exitPositionMode();
        }

        function cancelPositionMode() {
            if (prePositionState) {
                document.getElementById('latitude').value = prePositionState.latitude;
                document.getElementById('longitude').value = prePositionState.longitude;
                document.getElementById('capture_method').value = prePositionState.captureMethod;
                document.getElementById('gps_accuracy_meters').value = prePositionState.gpsAccuracy;

                const lat = parseFloat(prePositionState.latitude);
                const lng = parseFloat(prePositionState.longitude);
                if (!isNaN(lat) && !isNaN(lng)) {
                    marker.setLatLng([lat, lng]);
                }
            }

            exitPositionMode();
        }

        function exitPositionMode() {
            positioningMode = false;
            prePositionState = null;

            marker.setOpacity(1);
            document.getElementById('position-crosshair').style.display = 'none';
            document.getElementById('adjust-position-btn').style.display = '';
            document.getElementById('positioning-controls').style.display = 'none';

            const submitBtn = document.getElementById('submit-btn');
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        }

        // ------------------------------------------------------------
        // Feature 3: "Show my position" — live GPS tracker for walking
        // a site boundary outdoors. Uses watchPosition (not
        // getCurrentPosition) so the marker updates continuously. Never
        // auto-pans the map except via the explicit "Center map here"
        // link in the out-of-bounds warning, and never touches the
        // latitude/longitude inputs or capture_method — it's read-only
        // display. The marker is non-interactive so it can't swallow
        // map clicks that Leaflet.draw relies on for drawing/editing.
        // ------------------------------------------------------------

        function myPositionIcon() {
            return L.divIcon({
                className: 'my-position-marker',
                html: '<div style="width:14px;height:14px;border-radius:50%;background:#2563eb;border:2px solid #fff;box-shadow:0 0 0 2px #2563eb,0 1px 3px rgba(0,0,0,0.5);"></div>',
                iconSize: [14, 14],
                iconAnchor: [7, 7],
            });
        }

        function toggleMyPositionTracking() {
            if (myPositionWatchId !== null) {
                stopMyPositionTracking();
            } else {
                startMyPositionTracking();
            }
        }

        function startMyPositionTracking() {
            if (!navigator.geolocation) {
                alert("Geolocation is not supported by your browser.");
                return;
            }

            document.getElementById('my-position-btn').textContent = '⏹ Stop tracking';

            myPositionWatchId = navigator.geolocation.watchPosition(
                (position) => {
                    const latlng = L.latLng(position.coords.latitude, position.coords.longitude);

                    if (!myPositionMarker) {
                        myPositionMarker = L.marker(latlng, {
                            icon: myPositionIcon(),
                            interactive: false,
                            keyboard: false,
                        }).addTo(map);
                    } else {
                        myPositionMarker.setLatLng(latlng);
                    }

                    updateMyPositionWarning(latlng);
                },
                () => {
                    stopMyPositionTracking();
                    alert("Unable to track your location.");
                },
                { enableHighAccuracy: true }
            );
        }

        function stopMyPositionTracking() {
            if (myPositionWatchId !== null) {
                navigator.geolocation.clearWatch(myPositionWatchId);
                myPositionWatchId = null;
            }

            if (myPositionMarker) {
                map.removeLayer(myPositionMarker);
                myPositionMarker = null;
            }

            hideMyPositionWarning();

            const btn = document.getElementById('my-position-btn');
            if (btn) btn.textContent = '🧭 Show my position';
        }

        function updateMyPositionWarning(latlng) {
            if (map.getBounds().contains(latlng)) {
                hideMyPositionWarning();
                return;
            }

            const center = map.getCenter();
            const distance = haversineDistanceMeters(center, latlng);
            const direction = compassDirection(center, latlng);

            const distanceLabel = distance < 1000
                ? (Math.round(distance / 10) * 10) + ' m'
                : (distance / 1000).toFixed(1) + ' km';

            lastMyPosition = latlng;

            document.getElementById('my-position-warning-text').textContent =
                'Your position is ' + distanceLabel + ' ' + direction + ' of map area.';
            document.getElementById('my-position-warning').style.display = 'flex';
        }

        function hideMyPositionWarning() {
            document.getElementById('my-position-warning').style.display = 'none';
            lastMyPosition = null;
        }

        // The only place in this feature that moves the map — an
        // explicit user action from the warning, never automatic.
        function centerMapOnMyPosition() {
            if (lastMyPosition) {
                map.panTo(lastMyPosition);
            }
        }

        function haversineDistanceMeters(a, b) {
            const R = 6371000;
            const toRad = (deg) => deg * Math.PI / 180;
            const dLat = toRad(b.lat - a.lat);
            const dLng = toRad(b.lng - a.lng);
            const lat1 = toRad(a.lat);
            const lat2 = toRad(b.lat);

            const h = Math.sin(dLat / 2) ** 2 + Math.cos(lat1) * Math.cos(lat2) * Math.sin(dLng / 2) ** 2;
            return R * 2 * Math.atan2(Math.sqrt(h), Math.sqrt(1 - h));
        }

        function compassDirection(from, to) {
            const toRad = (deg) => deg * Math.PI / 180;
            const toDeg = (rad) => rad * 180 / Math.PI;

            const lat1 = toRad(from.lat);
            const lat2 = toRad(to.lat);
            const dLng = toRad(to.lng - from.lng);

            const y = Math.sin(dLng) * Math.cos(lat2);
            const x = Math.cos(lat1) * Math.sin(lat2) - Math.sin(lat1) * Math.cos(lat2) * Math.cos(dLng);
            const bearing = (toDeg(Math.atan2(y, x)) + 360) % 360;

            const directions = ['N', 'NE', 'E', 'SE', 'S', 'SW', 'W', 'NW'];
            return directions[Math.round(bearing / 45) % 8];
        }

        window.addEventListener('DOMContentLoaded', () => {
            initMap();
            initQuill();

            document.getElementById('add-location-form').addEventListener('submit', function () {
                document.getElementById('contributors-input').value = quill.root.innerHTML;
            });

            document.getElementById('latitude').addEventListener('input', handleCoordinateInput);
            document.getElementById('longitude').addEventListener('input', handleCoordinateInput);
        });
    </script>
</x-app-layout>
