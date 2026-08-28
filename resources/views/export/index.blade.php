<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <h1 class="text-4xl font-bold mb-4 text-center"><i class="fas fa-file-csv mr-2"></i>Export</h1>

        @php
            $nonGeoSources = array_diff(array_keys($sources), $geoFilterableSources);
            $nonGeoSourcesJs = collect($nonGeoSources)->map(fn ($s) => "'{$s}'")->implode(',');

            // What "search" actually matches per source — shown as the
            // search input's placeholder, swapped live via Alpine as the
            // Data Source dropdown changes, same mechanism as the
            // Division field's show/hide below.
            $searchPlaceholders = [
                'planting_locations'         => 'Search by location or division name...',
                'tree_plantings'             => 'Search by planting location or division name...',
                'inspections'                => 'Search by planting location or division name...',
                'tree_planting_measurements' => 'Search by planting location or division name...',
                'biochar_batches'            => 'Search by planting location or division name...',
                'tree_types'                 => 'Search by name or latin name...',
                'users'                      => 'Search by name...',
            ];
            $searchPlaceholdersJson = json_encode($searchPlaceholders);
        @endphp

        <div class="filter-container">
            <div class="filter-form-content">
                <form method="GET" action="{{ route('export.index') }}" class="filter-form" x-data="{ table: '{{ $source }}', placeholders: {{ $searchPlaceholdersJson }} }">
                    <div class="filter-grid filter-grid-3">
                        <div>
                            <label for="table" class="filter-label">Data Source</label>
                            <select name="table" id="table" x-model="table" class="filter-select">
                                @foreach($sources as $key => $label)
                                    <option value="{{ $key }}" {{ $source === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div x-show="![{{ $nonGeoSourcesJs }}].includes(table)">
                            <label for="division" class="filter-label">Division</label>
                            <select name="division" id="division" class="filter-select {{ $divisionId ? 'filter-active' : '' }}">
                                <option value="">All Divisions</option>
                                @foreach($divisions as $division)
                                    <option value="{{ $division->id }}" {{ (string) $divisionId === (string) $division->id ? 'selected' : '' }}>
                                        {{ $division->LGA_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="search" class="filter-label">Search</label>
                            <input type="text" name="search" id="search" value="{{ $search }}"
                                   x-bind:placeholder="placeholders[table]"
                                   class="filter-select {{ $search ? 'filter-active' : '' }}">
                        </div>
                    </div>

                    <div class="filter-actions">
                        <div class="filter-button-group">
                            <button type="submit" class="filter-btn-primary">
                                <i class="fas fa-search mr-1"></i>Preview
                            </button>
                            <button type="submit" formaction="{{ route('export.download') }}"
                                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                                <i class="fas fa-file-csv mr-1"></i>Export to CSV
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <p class="text-sm text-gray-500 mt-4 mb-2">
            Preview — showing up to {{ $rows->count() }} of {{ $paginator->total() }} matching row{{ $paginator->total() === 1 ? '' : 's' }}.
            "Export to CSV" downloads the full filtered result, not just this preview page.
        </p>

        <div class="overflow-x-auto">
            <table class="text-xs border-collapse w-full">
                <thead>
                    <tr>
                        @foreach($columnLabels as $label)
                            <th class="border border-gray-400 bg-gray-100 px-2 py-1 text-left whitespace-nowrap font-semibold">{{ $label }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            @foreach($row as $value)
                                <td class="border border-gray-300 px-2 py-1 whitespace-nowrap">{{ $value }}</td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($columnLabels) }}" class="border border-gray-300 px-2 py-4 text-center text-gray-500">
                                No rows match the current filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $paginator->links() }}
        </div>
    </div>
</x-app-layout>
