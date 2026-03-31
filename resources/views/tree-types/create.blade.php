<x-app-layout>
    <div class="max-w-2xl mx-auto mt-10 p-6 bg-white shadow-md rounded-lg">
        <h1 class="text-2xl font-bold text-center text-gray-800 mb-6">Add Tree Type</h1>

        <form method="POST" action="{{ route('tree-types.store') }}" class="space-y-4">
            @csrf

            @include('tree-types.partials.form', [
                'treeType' => null,
                'buttonText' => 'Save Tree Type'
            ])
        </form>
    </div>
</x-app-layout>
