<!-- Name -->
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1" for="name">
        <span>Name</span>
    </label>
    <input type="text" id="name" name="name"
           class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary"
           value="{{ old('name', $treeType->name ?? '') }}" required>

    @error('name')
    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
    @enderror
</div>

<!-- Latin Name -->
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1" for="latin_name">
        <span>Latin Name</span>
    </label>
    <input type="text" id="latin_name" name="latin_name"
           class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary"
           value="{{ old('latin_name', $treeType->latin_name ?? '') }}">

    @error('latin_name')
    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
    @enderror
</div>

<!-- Description -->
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1" for="description">
        <span>Description</span>
    </label>
    <textarea id="description" name="description" rows="5"
              class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary">{{ old('description', $treeType->description ?? '') }}</textarea>

    @error('description')
    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
    @enderror
</div>

<!-- Buttons -->
<div class="flex justify-end space-x-2 pt-4">
    <button type="submit"
            class="bg-primary text-white px-6 py-3 rounded hover:bg-green-700 transition-colors">
        {{ $buttonText }}
    </button>

    <a href="{{ route('tree-types.index') }}"
       class="border border-gray-300 text-gray-700 px-6 py-3 rounded hover:bg-gray-50 transition-colors">
        Cancel
    </a>
</div>
