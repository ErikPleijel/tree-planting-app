<x-app-layout>
    <div class="max-w-xl mx-auto mt-8 p-6 bg-white rounded-lg shadow">
        <h1 class="text-xl font-bold text-center mb-6 text-gray-800">Add Planting Location</h1>

        <form method="POST" action="{{ route('planting-locations.store') }}" class="space-y-4">
            @csrf

            <!-- Location Name -->
            <div>
                <label class="label">
                    <span class="label-text font-semibold">Location Name</span>
                </label>
                <input type="text" name="location" class="input input-bordered w-full"
                       value="{{ old('location') }}" required>
            </div>

            <!-- Division -->
            <div>
                <label class="label">
                    <span class="label-text font-semibold">Division</span>
                </label>
                <select name="division_id" class="select select-bordered w-full" required>
                    <option value="">-- Select Division --</option>
                    @foreach($divisions as $division)
                        <option value="{{ $division->id }}" @if(old('division_id')==$division->id) selected @endif>
                            {{ $division->LGA_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Status -->
            <div>
                <label class="label">
                    <span class="label-text font-semibold">Status</span>
                </label>
                <select name="status" class="select select-bordered w-full" required>
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
                <label class="label">
                    <span class="label-text font-semibold">Comment</span>
                </label>
                <textarea name="comment" rows="2" class="textarea textarea-bordered w-full">{{ old('comment') }}</textarea>
            </div>

            <!-- Coordinates -->
            <div class="flex justify-end">
                <button type="button" onclick="getLocation()" class="btn btn-sm btn-info mb-2">
                    📍 Use My Location
                </button>
            </div>
            {{-- TODO: Disable on desktop --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- GPS Button -->


                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Latitude</span>
                    </label>
                    <input id="latitude" type="text" name="latitude" class="input input-bordered w-full"
                           value="{{ old('latitude') }}">
                </div>
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Longitude</span>
                    </label>
                    <input id="longitude" type="text" name="longitude" class="input input-bordered w-full"
                           value="{{ old('longitude') }}">
                </div>

            </div>
            <div class="mt-4">
                <label class="label"><span class="label-text font-semibold">Map Preview</span></label>
                <div id="map" class="rounded border" style="height: 300px;"></div>
            </div>

            <!-- Buttons -->
            <div class="flex justify-end space-x-2 pt-4">
                <button class="btn btn-primary btn-sm">Save Location</button>
                <a href="{{ route('planting-locations.index') }}" class="btn btn-outline btn-sm">Cancel</a>
            </div>
        </form>
    </div>

</x-app-layout>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>


<script>
    let map, marker;

    function initMap() {
        const lat = parseFloat(document.getElementById('latitude').value);
        const lng = parseFloat(document.getElementById('longitude').value);

        const initialLat = !isNaN(lat) ? lat : 9.0820;  // Nigeria center
        const initialLng = !isNaN(lng) ? lng : 8.6753;

        map = L.map('map').setView([initialLat, initialLng], 6);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        if (!isNaN(lat) && !isNaN(lng)) {
            marker = L.marker([lat, lng]).addTo(map);
        } else {
            marker = L.marker([initialLat, initialLng]).addTo(map).setOpacity(0); // hidden
        }
    }

    function updateMapMarker() {
        const lat = parseFloat(document.getElementById('latitude').value);
        const lng = parseFloat(document.getElementById('longitude').value);

        if (!isNaN(lat) && !isNaN(lng)) {
            marker.setLatLng([lat, lng]).setOpacity(1);
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

