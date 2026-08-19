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

<!-- Wood Density -->
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1" for="wood_density_kg_m3">
        <span>Wood Density (kg/m³)</span>
    </label>
    <input type="number" step="0.01" min="0" id="wood_density_kg_m3" name="wood_density_kg_m3"
           class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary"
           value="{{ old('wood_density_kg_m3', $treeType->wood_density_kg_m3 ?? '') }}">

    @error('wood_density_kg_m3')
    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
    @enderror
</div>

<!-- Carbon Fraction -->
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1" for="carbon_fraction">
        <span>Carbon Fraction (0–1, e.g. 0.47)</span>
    </label>
    <input type="number" step="0.001" min="0" max="1" id="carbon_fraction" name="carbon_fraction"
           class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary"
           value="{{ old('carbon_fraction', $treeType->carbon_fraction ?? '') }}">

    @error('carbon_fraction')
    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
    @enderror
</div>

<!-- Source Reference -->
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1" for="source_reference">
        <span>Source / Citation <span class="text-gray-400 font-normal">(required if either value above is set)</span></span>
    </label>
    <textarea id="source_reference" name="source_reference" rows="3"
              class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary"
              placeholder="e.g. IPCC 2006 Guidelines for National Greenhouse Gas Inventories, Vol. 4, Table 4.14">{{ old('source_reference', $treeType->source_reference ?? '') }}</textarea>

    @error('source_reference')
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
