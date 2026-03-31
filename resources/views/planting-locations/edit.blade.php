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
    <!-- Quill -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

    <script>
        let map, marker, quill;

        function initMap() {
            const lat = parseFloat(document.getElementById('latitude').value) || 9.0820;
            const lng = parseFloat(document.getElementById('longitude').value) || 8.6753;

            map = L.map('map').setView([lat, lng], 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            marker = L.marker([lat, lng]).addTo(map);
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
            initQuill();

            // Target form directly by id to avoid selecting navbar logout form
            document.getElementById('edit-location-form').addEventListener('submit', function () {
                document.getElementById('contributors-input').value = quill.root.innerHTML;
            });

            document.getElementById('latitude').addEventListener('input', updateMapMarker);
            document.getElementById('longitude').addEventListener('input', updateMapMarker);
        });
    </script>
</x-app-layout>
