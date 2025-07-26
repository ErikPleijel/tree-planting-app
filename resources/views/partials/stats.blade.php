{{-- Stats Section --}}
<section class="py-12 bg-base-100 text-center">
    <div class="max-w-4xl mx-auto">
        <h2 class="text-3xl font-bold mb-6">Our Progress</h2>
        <div class="stats stats-vertical md:stats-horizontal shadow">
            <div class="stat">
                <div class="stat-title">Trees Planted</div>
                <div class="stat-value text-green-600">{{ \App\Models\TreePlanting::sum('number_of_trees') }}</div>
                <div class="stat-desc">And counting!</div>
            </div>
            <div class="stat">
                <div class="stat-title">Field Workers</div>
                <div class="stat-value text-blue-500">{{ \App\Models\User::count() }}</div>
                <div class="stat-desc">Across Niger State</div>
            </div>
            <div class="stat">
                <div class="stat-title">Target for 2032</div>
                <div class="stat-value text-orange-500">25,000,000</div>
                <div class="stat-desc">Let's make it happen 🌳</div>
            </div>
        </div>
    </div>
</section>

<div class="mx-auto w-full max-w-xl px-4">
    <x-map

        :markers="$markers"

    />
</div>

<div class="flex justify-center mt-8">
    <table class="border border-gray-300 shadow-md text-left">
        <thead>
        <tr class="bg-gray-100">
            <th class="px-4 py-2 text-left">Tree Type</th>
            <th class="px-4 py-2 text-right">Total Trees Planted</th>
        </tr>
        </thead>
        <tbody>
        @forelse ($treeStats as $stat)
            <tr class="border-t hover:bg-gray-50">
                <td class="px-4 py-2">{{ $stat->type_name ?? 'Unknown' }}</td>
                <td class="px-4 py-2 text-right">{{ number_format($stat->total) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="2" class="px-4 py-2 text-center text-gray-500">
                    No planting data available.
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

{{-- About Section --}}
<section class="py-12 bg-base-200">
    <div class="max-w-3xl mx-auto px-4 text-center">
        <h2 class="text-3xl font-bold mb-4">About the Project</h2>
        <p class="text-lg opacity-90 leading-relaxed">
            Itacen Mu is a community-led tree 🌳 growing initiative across Niger state in North Central Nigeria 🇳🇬.
        </p>

        <h2 class="text-3xl font-bold mb-4 mt-10">OUR MISSION</h2>
        <p class="text-lg opacity-90 leading-relaxed">
            To combat desertification, improve soil quality, and support sustainable agriculture and livelihoods for future generations.
        </p>

        <h2 class="text-3xl font-bold mb-4 mt-10">PARTNERSHIP</h2>
        <p class="text-lg opacity-90 leading-relaxed">
            ITACEN-MU initiative is a collaboration between Kenya Puxin Renewable Energy Co. as the app developers and RCE-Minna as the local implementing partner, with technical assistance from the office of His Excellency The Hon. Governor Niger State. The initiative aims to bring together all communities in the 25 local governments with a target of 1 million trees grown per local government in the next 5 years, totaling 25 million trees across the entire state of Niger State.
        </p>
    </div>
</section>
