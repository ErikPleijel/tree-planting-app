<x-app-layout>
    @guest
        <div class="w-full mt-10 px-10">
            <div class="text-center">
                <p class="text-3xl font-semibold">{{-- Welcome --}}</p>
            </div>
        </div>
    @endguest
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

    {{-- You Can Contribute Section --}}
    <section class="py-16 bg-gradient-to-b from-base-100 to-base-200">
        <div class="max-w-6xl mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12">How You Can Contribute</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="card bg-base-100 shadow-xl hover:shadow-2xl transition-shadow duration-300">
                    <div class="card-body items-center text-center">
                        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-600" fill="none"
                                 viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                        </div>
                        <h3 class="card-title mb-2">Become a Grower</h3>
                        <p class="text-base-content/80">Join our community of tree planters and help grow the future.
                            Every tree counts!</p>
                        @guest
                            <div class="card-actions mt-4 hidden">
                                <a href="{{ route('register') }}" class="btn btn-primary">Join as Planter</a>
                            </div>
                        @endguest
                        <div class="mt-4 text-sm text-base-content/70 border-t pt-4 w-full">
                            <p class="font-semibold mb-1">For more information:</p>
                            <p><i class="fas fa-phone mr-2"></i>+234 800 555 0123</p>
                            <p><i class="fas fa-envelope mr-2"></i>growers@itacenmu.org</p>
                        </div>
                    </div>
                </div>

                <div class="card bg-base-100 shadow-xl hover:shadow-2xl transition-shadow duration-300">
                    <div class="card-body items-center text-center">
                        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600" fill="none"
                                 viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </div>
                        <h3 class="card-title mb-2">Become a Monitor</h3>
                        <p class="text-base-content/80">Help verify and monitor tree planting progress across Niger
                            State.</p>
                        @guest
                            <div class="card-actions mt-4 hidden">
                                <a href="{{ route('register') }}" class="btn btn-primary">Join as Monitor</a>
                            </div>
                        @endguest
                        <div class="mt-4 text-sm text-base-content/70 border-t pt-4 w-full">
                            <p class="font-semibold mb-1">Contact our Monitor team:</p>
                            <p><i class="fas fa-phone mr-2"></i>+234 800 555 0124</p>
                            <p><i class="fas fa-envelope mr-2"></i>monitors@itacenmu.org</p>
                        </div>
                    </div>
                </div>

                <div class="card bg-base-100 shadow-xl hover:shadow-2xl transition-shadow duration-300">
                    <div class="card-body items-center text-center">
                        <div class="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-orange-600" fill="none"
                                 viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                            </svg>
                        </div>
                        <h3 class="card-title mb-2">Spread the Word</h3>
                        <p class="text-base-content/80">Share our mission with your community and help us grow our
                            impact.</p>
                        <p class="text-xl font-bold mt-4 mb-2">ItacenMu.org</p>
                        <div class="card-actions">
                            <button onclick="copyLink()" class="btn btn-primary gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/>
                                </svg>
                                Copy Link
                            </button>
                            <div id="copyNotification" class="hidden">
                                <span class="text-success text-sm">Link copied!</span>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                    function copyLink() {
                        navigator.clipboard.writeText('https://ItacenMu.org').then(() => {
                            const notification = document.getElementById('copyNotification');
                            notification.classList.remove('hidden');
                            setTimeout(() => {
                                notification.classList.add('hidden');
                            }, 2000);
                        });
                    }
                </script>
            </div>
        </div>
    </section>

    {{-- Explore Data Section --}}
    <section class="py-16 bg-base-200">
        <div class="max-w-4xl mx-auto px-4 text-center">
            <h2 class="text-3xl font-bold mb-8">Explore Our Impact</h2>
            <div class="flex flex-col md:flex-row gap-6 justify-center mb-8">
                <div class="card bg-base-100 shadow-xl flex-1">
                    <div class="card-body">
                        <h3 class="card-title justify-center mb-4">Map</h3>
                        <p class="mb-6 text-base-content/80">Discover tree planting locations across Niger State and see
                            our growing impact in real-time.</p>
                        <!-- Replace the existing View Map button with this: -->
                        <div class="card-actions justify-center">
                            <a href="{{ route('stats.map') }}" class="btn btn-primary gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                </svg>
                                View Map
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card bg-base-100 shadow-xl flex-1">
                    <div class="card-body">
                        <h3 class="card-title justify-center mb-4">Statistics</h3>
                        <p class="mb-6 text-base-content/80">Access comprehensive data about our tree planting progress,
                            species distribution, and regional achievements.</p>
                        <div class="card-actions justify-center">
                            <a href="{{ route('stats.stats1') }}" class="btn btn-primary gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                                View Statistics
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <p class="text-base-content/70 max-w-2xl mx-auto">
                Our transparent data visualization tools help you understand the impact of every tree planted.
                Track our progress, identify key planting areas, and see how we're working towards our goal of 25
                million trees.
            </p>
        </div>
    </section>

    {{-- About Section --}}
    <section class="py-12 bg-base-200">
        <div class="max-w-3xl mx-auto px-4 text-center">
            <h2 class="text-3xl font-bold mb-4">About the Project</h2>
            <p class="text-lg opacity-90 leading-relaxed">
                Itacen Mu is a community-led tree 🌳 growing initiative across Niger state in North Central Nigeria 🇳🇬.
            </p>

            <h2 class="text-3xl font-bold mb-4 mt-10">OUR MISSION</h2>
            <p class="text-lg opacity-90 leading-relaxed">
                To combat desertification, improve soil quality, and support sustainable agriculture and livelihoods for
                future generations.
            </p>

            <h2 class="text-3xl font-bold mb-4 mt-10">PARTNERSHIP</h2>
            <p class="text-lg opacity-90 leading-relaxed">
                ITACEN-MU initiative is a collaboration between Kenya Puxin Renewable Energy Co. as the app developers
                and RCE-Minna as the local implementing partner, with technical assistance from the office of His
                Excellency The Hon. Governor Niger State. The initiative aims to bring together all communities in the
                25 local governments with a target of 1 million trees grown per local government in the next 5 years,
                totaling 25 million trees across the entire state of Niger State.
            </p>
        </div>
    </section>
</x-app-layout>
