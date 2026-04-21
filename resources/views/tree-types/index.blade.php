<x-app-layout>
    <div class="max-w-5xl mx-auto mt-10 p-6 bg-white shadow-md rounded-lg">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Tree Types</h1>

            @role('Admin|SuperAdmin')
            <a href="{{ route('tree-types.create') }}"
               class="inline-block bg-primary text-white px-5 py-3 rounded hover:bg-green-700 transition-colors">
                + Add Tree Type
            </a>
            @endrole
        </div>

        @if(session('success'))
            <div class="mb-4 rounded-md bg-green-100 border border-green-300 text-green-800 px-4 py-3">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 rounded-md bg-red-100 border border-red-300 text-red-800 px-4 py-3">
                {{ session('error') }}
            </div>
        @endif

        @if($treeTypes->count())
            <div class="overflow-x-auto">
                <table class="min-w-full border border-gray-200 rounded-md overflow-hidden">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 border-b">Name</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 border-b">Latin Name</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 border-b">Description</th>
                        <th class="px-4 py-3 text-right text-sm font-semibold text-gray-700 border-b">Total Planted</th>
                        @role('Admin|SuperAdmin')
                        <th class="px-4 py-3 text-right text-sm font-semibold text-gray-700 border-b">Actions</th>
                        @endrole
                    </tr>
                    </thead>
                    <tbody class="bg-white">
                    @foreach($treeTypes as $treeType)
                        <tr class="border-b last:border-b-0">
                            <td class="px-4 py-3 text-gray-800 font-medium">
                                {{ $treeType->name }}
                            </td>
                            <td class="px-4 py-3 text-gray-700 italic">
                                {{ $treeType->latin_name ?: '—' }}
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ \Illuminate\Support\Str::limit(strip_tags($treeType->description), 200) ?: '—' }}
                            </td>
                            <td class="px-4 py-3 text-right text-gray-800 font-medium">
                                {{ number_format($treeType->tree_plantings_sum_number_of_trees ?? 0) }}
                            </td>
                            @role('Admin|SuperAdmin')
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('tree-types.show', $treeType) }}"
                                       class="px-3 py-2 text-sm rounded border border-gray-300 text-gray-700 hover:bg-gray-50">
                                        View
                                    </a>

                                    <a href="{{ route('tree-types.edit', $treeType) }}"
                                       class="px-3 py-2 text-sm rounded bg-yellow-500 text-white hover:bg-yellow-600 transition-colors">
                                        Edit
                                    </a>

                                    <form action="{{ route('tree-types.destroy', $treeType) }}" method="POST"
                                          onsubmit="return confirm('Delete this tree type?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="px-3 py-2 text-sm rounded bg-red-600 text-white hover:bg-red-700 transition-colors">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                            @endrole
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $treeTypes->links() }}
            </div>
        @else
            <p class="text-gray-600">No tree types found yet.</p>
        @endif
    </div>
</x-app-layout>
