<x-app-layout>
    <x-slot name="header">
        {{-- Insert common header  --}}
    </x-slot>



    <div class="mx-auto w-full max-w-xl px-4">
        <x-map

            :markers="$markers"

        />
    </div>

    <div class="flex justify-center">
        <form method="GET" action="{{ route('dashboard') }}" class="mb-4">
            <label for="division_id" class="block mb-1 font-semibold">Filter by Division</label>
            <select name="division_id" id="division_id" class="border rounded p-2">
                <option value="">-- All Divisions --</option>
                @foreach (\App\Models\Division::orderBy('LGA_name')->get() as $division)
                    <option value="{{ $division->id }}" @selected(request('division_id') == $division->id)>
                        {{ $division->LGA_name }}
                    </option>
                @endforeach
            </select>

            <button type="submit" class="ml-2 px-4 py-2 bg-green-600 text-white rounded">Filter</button>
        </form>
    </div>



    <div class="bg-white p-6 rounded shadow max-w-3xl mx-auto mt-8">
        <h2 class="text-2xl font-bold mb-4">Welcome to the Tree Planting Dashboard</h2>

        <p class="mb-4">
            This dashboard gives you an overview of current tree planting activities in your assigned areas. As an Inspector or Field Worker, your role is essential in ensuring that each planting meets the standards for survival, documentation, and long-term sustainability.
        </p>

        <h3 class="text-xl font-semibold mt-6 mb-2">Your Responsibilities:</h3>
        <ul class="list-disc list-inside mb-4">
            <li>Review and verify newly planted trees in the field.</li>
            <li>Record accurate inspection results and upload supporting photos if required.</li>
            <li>Report any issues such as missing trees, poor soil conditions, or pest damage.</li>
            <li>Ensure that each planting location is properly geotagged and documented.</li>
            <li><strong>Always write down all key data on paper as a backup</strong>, in case of technical problems or poor network coverage.</li>
        </ul>

        <h3 class="text-xl font-semibold mt-6 mb-2">Getting Started:</h3>
        <ol class="list-decimal list-inside mb-4">
            <li>Use the navigation menu to access <strong>Tree Plantings</strong>, <strong>Inspections</strong>, or <strong>Planting Locations</strong>.</li>
            <li>Click on any record to view details, update status, or submit inspection notes.</li>
            <li>If you’re new, be sure to read the <a href="#" class="text-blue-600 underline">Field Manual</a> or ask your team leader for help.</li>
        </ol>

        <p class="text-sm text-gray-600">
            Thank you for your work in restoring our environment — one tree at a time.
        </p>
    </div>



</x-app-layout>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const map = L.map('map').setView([10.0, 6.5], 8);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);
    });
</script>
