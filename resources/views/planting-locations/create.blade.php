<x-app-layout>
    <div class="max-w-2xl mx-auto mt-10 p-6 bg-white shadow-md rounded-lg">
        <h1 class="text-2xl font-bold text-center text-gray-800 mb-6">Add Planting Location</h1>

        <form method="POST" action="{{ route('planting-locations.store') }}" class="space-y-4">
            @csrf

            <!-- Location Name -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1" for="location">
                    <span>Location Name</span>
                </label>
                <input type="text" id="location" name="location"
                       class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary"
                       value="{{ old('location') }}" required>
            </div>

            <!-- Division -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1" for="division_id">
                    <span>LGA</span>
                </label>
                <select id="division_id" name="division_id" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary" required>
                    <option value="">-- Select Division --</option>
                    @foreach($divisions as $division)
                        <option value="{{ $division->id }}" @if(old('division_id')==$division->id) selected @endif>
                            {{ $division->LGA_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Add this after the Division field -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1" for="status">
                    <span>Status</span>
                </label>
                <select id="status" name="status" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary" required>
                    <option value="">-- Select Status --</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status->id }}" @if(old('status')==$status->id) selected @endif>
                            {{ $status->planting_location_status }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Comment -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1" for="comment">
                    <span>Describe the location (optional)</span>
                </label>
                <textarea id="comment" name="comment"
                          class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary"
                          rows="3">{{ old('comment') }}</textarea>
            </div>

            <!-- 🗺️ Map Preview -->
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-1"><span class="font-semibold">Map Preview</span></label>
                <div id="map" class="rounded border" style="height: 300px;"></div>
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
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="longitude">
                        <span>Longitude</span>
                    </label>
                    <input type="text" id="longitude" name="longitude"
                           class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary"
                           value="{{ old('longitude') }}">
                </div>
            </div>

            <!-- 📍 GPS Button -->
            <div class="flex justify-end">
                <button type="button" onclick="getLocation()" class="bg-blue-500 text-white px-4 py-2 text-sm rounded hover:bg-blue-600 transition-colors mb-2">
                    📍 Get location from phone GPS
                </button>
            </div>
            {{-- TODO: Disable on desktop --}}

            <!-- Buttons -->
            <div class="flex justify-end space-x-2 pt-4">
                <button type="submit" class="bg-primary text-white px-6 py-3 rounded hover:bg-green-700 transition-colors">Save Location</button>
                <a href="{{ route('planting-locations.index') }}" class="border border-gray-300 text-gray-700 px-6 py-3 rounded hover:bg-gray-50 transition-colors">Cancel</a>
            </div>
        </form>
    </div>

    <!-- Leaflet & Scripts -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        let map, marker;

        function initMap() {
            const lat = parseFloat(document.getElementById('latitude').value) || 9.0820;
            const lng = parseFloat(document.getElementById('longitude').value) || 8.6753;

            map = L.map('map').setView([lat, lng], 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            marker = L.marker([lat, lng]).addTo(map);
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
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;

                    document.getElementById('latitude').value = lat.toFixed(6);
                    document.getElementById('longitude').value = lng.toFixed(6);
                    updateMapMarker();
                },
                () => {
                    alert("Unable to retrieve your location.");
                }
            );
        }

        window.addEventListener('DOMContentLoaded', () => {
            initMap();

            document.getElementById('latitude').addEventListener('input', updateMapMarker);
            document.getElementById('longitude').addEventListener('input', updateMapMarker);
        });
    </script>
</x-app-layout>
