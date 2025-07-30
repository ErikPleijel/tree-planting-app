<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h2 class="text-2xl font-bold mb-6 text-center">Tree Planting Locations Map</h2>

                <!-- Map Component -->
                <div class="w-full mb-6">
                    <x-map
                        :markers="$markers"
                        :zoom="8"
                        :lat="9.75"
                        :lng="5.6"
                    />

                </div>

                <!-- Map Stats -->
                <div class="mt-6 flex justify-around items-center">
                    <div class="bg-blue-50 p-4 rounded-lg w-64">
                        <h3 class="font-semibold text-blue-700">Total Locations</h3>
                        <p class="text-2xl font-bold text-blue-800">{{ $totalLocations }}</p>
                    </div>
                    <div class="bg-blue-50 p-4 rounded-lg w-64">
                        <h3 class="font-semibold text-blue-700">Total Trees</h3>
                        <p class="text-2xl font-bold text-blue-800">{{ $totalTrees }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
