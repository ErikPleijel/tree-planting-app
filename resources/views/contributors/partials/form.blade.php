<!-- Name -->
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1" for="name">
        <span>Organization Name</span>
    </label>
    <input type="text" id="name" name="name"
           class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary"
           value="{{ old('name', $contributor->name ?? '') }}" required>

    @error('name')
    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
    @enderror
</div>

<!-- Website -->
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1" for="website">
        <span>Website</span>
    </label>
    <input type="url" id="website" name="website"
           class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary"
           placeholder="https://example.org"
           value="{{ old('website', $contributor->website ?? '') }}">

    @error('website')
    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
    @enderror
</div>

<!-- Contact Email -->
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1" for="contact_email">
        <span>Contact Email</span>
    </label>
    <input type="email" id="contact_email" name="contact_email"
           class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary"
           value="{{ old('contact_email', $contributor->contact_email ?? '') }}">

    @error('contact_email')
    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
    @enderror
</div>

<!-- Buttons -->
<div class="flex justify-end space-x-2 pt-4">
    <button type="submit"
            class="bg-primary text-white px-6 py-3 rounded hover:bg-green-700 transition-colors">
        {{ $buttonText }}
    </button>

    <a href="{{ route('contributors.index') }}"
       class="border border-gray-300 text-gray-700 px-6 py-3 rounded hover:bg-gray-50 transition-colors">
        Cancel
    </a>
</div>
