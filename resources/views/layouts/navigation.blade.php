<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <!-- Insert logo here -->
                </div>

                <!-- Navigation Links -->
                <div class="hidden sm:block [&>*]:text-base">
                    <x-nav-link :href="route('home')" :active="request()->routeIs('home')">
                        HOME
                    </x-nav-link>

                    <x-nav-link :href="route('stats.map')" :active="request()->routeIs('stats.map')">
                        Map
                    </x-nav-link>

                    <x-nav-link :href="route('stats.stats1')" :active="request()->routeIs('stats.stats1')">
                        Statistics
                    </x-nav-link>


                    @role('Admin|SuperAdmin|Inspector|TreePlanter')
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        Dashboard
                    </x-nav-link>
                    @endrole

                    @role('Admin|SuperAdmin|Inspector')
                        <x-nav-link :href="route('planting-locations.index')" :active="request()->routeIs('planting-locations.*')">
                            Planting Locations
                        </x-nav-link>

                        <x-nav-link :href="route('tree-plantings.index')" :active="request()->routeIs('tree-plantings.*')">
                            Tree Plantings
                        </x-nav-link>
                    @endrole

                    @role('Admin|SuperAdmin|Inspector|TreePlanter')
                        <x-nav-link :href="route('inspections.index')" :active="request()->routeIs('inspections.*')">
                            Inspections
                        </x-nav-link>
                    @endrole

                    @role('Admin|SuperAdmin')
                        <x-nav-link :href="route('users.report')" :active="request()->routeIs('users.report')">
                            Users Report
                        </x-nav-link>
                    @endrole
                </div>
            </div>

            <div class="flex items-center">
                <!-- Settings Dropdown -->
                <div class="hidden sm:flex sm:items-center sm:ms-6">
    <div x-data="{ open: false }" @click.away="open = false" @close.stop="open = false" class="relative">
        <div>
            <button @click="open = !open" class="inline-flex items-center px-2 py-0.5 border border-gray-400 text-sm leading-none font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">

                @auth
                    <div><b>{{ Auth::user()->name }}</b>&nbsp; &nbsp;</div>
                    <div>Your role:
                        <strong>{{ Auth::user()->getRoleNames()->first() ?? 'None' }}</strong>
                    </div>
                @else
                    <div class="py-2">Guest</div>
                @endauth

                <div class="ms-1">
                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </div>
            </button>
        </div>

        <div x-show="open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="transform opacity-0 scale-95"
             x-transition:enter-end="transform opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-75"
             x-transition:leave-start="transform opacity-100 scale-100"
             x-transition:leave-end="transform opacity-0 scale-95"
             class="absolute z-50 mt-2 w-48 rounded-md shadow-lg origin-top-right right-0"
             style="display: none;">
            <div class="rounded-md ring-1 ring-black ring-opacity-5 py-1 bg-white">
                @auth
                    <!-- Profile Link -->
                    <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out">
                        {{ __('Profile') }}
                    </a>

                    <!-- Logout -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <a href="{{ route('logout') }}"
                           onclick="event.preventDefault(); this.closest('form').submit();"
                           class="block px-4 py-2 text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out">
                            {{ __('Log Out') }}
                        </a>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="block px-4 py-2 text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out">
                        {{ __('Log in') }}
                    </a>
                    <a href="{{ route('register') }}" class="block px-4 py-2 text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out">
                        {{ __('Register') }}
                    </a>
                @endauth
            </div>
        </div>
    </div>
</div>

                <!-- Hamburger -->
                <div class="flex items-center sm:hidden">
                    <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                        <svg class="h-8 w-8" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <!-- Responsive Navigation Menu -->
        <!-- Responsive Navigation Menu -->
        <div class="pt-2 pb-3 space-y-1">
    <x-responsive-nav-link :href="route('home')" :active="request()->routeIs('home')">
        HOME
    </x-responsive-nav-link>

    <x-responsive-nav-link :href="route('stats.map')" :active="request()->routeIs('stats.map')">
        Map
    </x-responsive-nav-link>

    <x-responsive-nav-link :href="route('stats.stats1')" :active="request()->routeIs('stats.stats1')">
        Statistics
    </x-responsive-nav-link>

    @role('Admin|SuperAdmin|Inspector|TreePlanter')
        <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
            Dashboard
        </x-responsive-nav-link>
    @endrole

    @role('Admin|SuperAdmin|Inspector')
        <x-responsive-nav-link :href="route('planting-locations.index')" :active="request()->routeIs('planting-locations.*')">
            Planting Locations
        </x-responsive-nav-link>

        <x-responsive-nav-link :href="route('tree-plantings.index')" :active="request()->routeIs('tree-plantings.*')">
            Tree Plantings
        </x-responsive-nav-link>
    @endrole

    @role('Admin|SuperAdmin|Inspector|TreePlanter')
        <x-responsive-nav-link :href="route('inspections.index')" :active="request()->routeIs('inspections.*')">
            Inspections
        </x-responsive-nav-link>
    @endrole

    @role('Admin|SuperAdmin')
        <x-responsive-nav-link :href="route('users.report')" :active="request()->routeIs('users.report')">
            Users Report
        </x-responsive-nav-link>
    @endrole
</div>



<!-- Responsive Settings Options -->
@auth
    <div class="pt-4 pb-1 border-t border-gray-200">
        <div class="px-4">
            <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
            <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            <div class="font-medium text-sm text-gray-500">
                Role: {{ Auth::user()->getRoleNames()->first() ?? 'None' }}
            </div>
        </div>

        <div class="mt-3 space-y-1">
            <x-responsive-nav-link :href="route('profile.edit')">
                {{ __('Profile') }}
            </x-responsive-nav-link>

            <!-- Authentication -->
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <x-responsive-nav-link :href="route('logout')"
                                     onclick="event.preventDefault(); this.closest('form').submit();">
                    {{ __('Log Out') }}
                </x-responsive-nav-link>
            </form>
        </div>
    </div>
@else
    <div class="pt-4 pb-1 border-t border-gray-200">
        <div class="px-4 space-y-1">
            <x-responsive-nav-link :href="route('login')">
                {{ __('Log in') }}
            </x-responsive-nav-link>
            @if (Route::has('register'))
                <x-responsive-nav-link :href="route('register')">
                    {{ __('Register') }}
                </x-responsive-nav-link>
            @endif
        </div>
    </div>
@endauth
</div>
</nav>
