<x-app-layout>
    <div class="max-w-2xl mx-auto mt-10 p-6 bg-white shadow-md rounded-lg">
        <h1 class="text-2xl font-bold text-center text-gray-800 mb-6">Edit Contributor</h1>

        <form method="POST" action="{{ route('contributors.update', $contributor) }}" class="space-y-4">
            @csrf
            @method('PUT')

            @include('contributors.partials.form', [
                'contributor' => $contributor,
                'buttonText' => 'Update Contributor'
            ])
        </form>
    </div>
</x-app-layout>
