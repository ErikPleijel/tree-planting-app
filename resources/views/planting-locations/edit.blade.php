<x-app-layout>
    <div class="max-w-2xl mx-auto mt-10 p-6 bg-white shadow-md rounded-lg">
        <h1 class="text-2xl font-bold text-center text-gray-800 mb-6">Edit Planting Location</h1>

        <form method="POST" action="{{ route('planting-locations.update', $plantingLocation) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <!-- Location Name -->
            <div>
                <label class="label" for="location">
                    <span class="label-text">Location Name</span>
                </label>
                <input type="text" id="location" name="location"
                       class="input input-bordered w-full"
                       value="{{ old('location', $plantingLocation->location) }}" required>
            </div>

            <!-- Division -->
            <div>
                <label class="label" for="division_id">
                    <span class="label-text">LGA</span>
                </label>
                <select id="division_id" name="division_id" class="select select-bordered w-full" required>
                    <option value="">-- Select Division --</option>
                    @foreach($divisions as $division)
                        <option value="{{ $division->id }}" @if(old('division_id', $plantingLocation->division_id)==$division->id) selected @endif>
                            {{ $division->LGA_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Status -->
            <div>
                <label class="label" for="status">
                    <span class="label-text">Status</span>
                </label>
                <select id="status" name="status" class="select select-bordered w-full" required>
                    <option value="">-- Select Status --</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status->id }}" @if(old('status', $plantingLocation->status)==$status->id) selected @endif>
                            {{ $status->planting_location_status }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Comment -->
            <div>
                <label class="label" for="comment">
                    <span class="label-text">Comment</span>
                </label>
                <textarea id="comment" name="comment"
                          class="textarea textarea-bordered w-full"
                          rows="3">{{ old('comment', $plantingLocation->comment) }}</textarea>
            </div>

            <!-- 📍 GPS Button -->
            <div class="flex justify-end">
                <button type="button" onclick="getLocation()" class="btn btn-sm btn-info mb-2">
                    📍 Use My Location
                </button>
            </div>
            {{-- TODO: Disable on desktop --}}

            <!-- Latitude & Longitude -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="label" for="latitude">
                        <span class="label-text">Latitude</span>
                    </label>
                    <input type="text" id="latitude" name="latitude"
                           class="input input-bordered w-full"
                           value="{{ old('latitude', $plantingLocation->latitude) }}">
                </div>

                <div>
                    <label class="label" for="longitude">
                        <span class="label-text">Longitude</span>
                    </label>
                    <input type="text" id="longitude" name="longitude"
                           class="input input-bordered w-full"
                           value="{{ old('longitude', $plantingLocation->longitude) }}">
                </div>
            </div>

            <!-- 🗺️ Map Preview -->
            <div class="mt-4">
                <label class="label"><span class="label-text font-semibold">Map Preview</span></label>
                <div id="map" class="rounded border" style="height: 300px;"></div>
            </div>

            <!-- Buttons -->
            <div class="flex justify-end space-x-2 pt-4">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="{{ route('planting-locations.index') }}" class="btn btn-outline">Cancel</a>
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
