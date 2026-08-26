<!-- Division Name -->
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1" for="LGA_name">
        <span>Division Name</span>
    </label>
    <input type="text" id="LGA_name" name="LGA_name"
           class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary"
           value="{{ old('LGA_name', $division->LGA_name ?? '') }}" required>

    @error('LGA_name')
    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
    @enderror
</div>

<!-- Latitude -->
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1" for="latitude">
        <span>Latitude</span>
    </label>
    <input type="number" step="0.0000001" min="-90" max="90" id="latitude" name="latitude"
           class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary"
           value="{{ old('latitude', $division->latitude ?? '') }}">

    @error('latitude')
    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
    @enderror
</div>

<!-- Longitude -->
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1" for="longitude">
        <span>Longitude</span>
    </label>
    <input type="number" step="0.0000001" min="-180" max="180" id="longitude" name="longitude"
           class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary"
           value="{{ old('longitude', $division->longitude ?? '') }}">

    @error('longitude')
    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
    @enderror
</div>

<!-- Buttons -->
<div class="flex justify-end space-x-2 pt-4">
    <button type="submit"
            class="bg-primary text-white px-6 py-3 rounded hover:bg-green-700 transition-colors">
        {{ $buttonText }}
    </button>

    <a href="{{ route('divisions.index') }}"
       class="border border-gray-300 text-gray-700 px-6 py-3 rounded hover:bg-gray-50 transition-colors">
        Cancel
    </a>
</div>
