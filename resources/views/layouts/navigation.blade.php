{{--
    Orchestrates two renderings of the same nav data (see
    layouts/partials/nav-items.blade.php and layouts/partials/user-menu.blade.php):
      - sm:+  a persistent vertical sidebar (<aside>), sticky while scrolling.
      - <sm   a mobile top bar with a left-aligned hamburger that opens a
              left-sliding off-canvas drawer.
    x-data is scoped to this whole block so the hamburger button and the
    drawer/backdrop share the same `open` state.
--}}
<div x-data="{ open: false }">
    {{-- Mobile top bar: hamburger on the LEFT, compact Login button (guests only) on the right --}}
    <div class="flex items-center justify-between h-14 px-4 bg-white border-b border-gray-100 sm:hidden">
        <button @click="open = true" aria-label="Open navigation menu"
                class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
            <svg class="h-7 w-7" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>

        @guest
            <a href="{{ route('login') }}"
               class="inline-flex items-center px-3 py-1.5 rounded-md text-xs font-semibold bg-primary text-primary-content hover:bg-primary/90 transition duration-150 ease-in-out">
                <i class="fas fa-right-to-bracket mr-2"></i>{{ __('Log in') }}
            </a>
        @endguest
    </div>

    {{-- Desktop sidebar (sm:+), sticky for the height of the viewport --}}
    <aside class="hidden sm:flex sm:flex-col sm:w-64 sm:shrink-0 sm:sticky sm:top-0 sm:h-screen sm:overflow-y-auto bg-white border-r border-gray-100 px-3 py-4">
        <nav class="space-y-1">
            @include('layouts.partials.nav-items', ['linkComponent' => 'sidebar-link'])
        </nav>

        @include('layouts.partials.user-menu', ['linkComponent' => 'sidebar-link'])
    </aside>

    {{-- Mobile off-canvas drawer + backdrop --}}
    <div x-show="open" class="fixed inset-0 z-40 sm:hidden" style="display: none;" x-cloak>
        <div x-show="open"
             x-transition:enter="transition-opacity ease-linear duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black/50"
             @click="open = false"
             style="display: none;">
        </div>

        <div x-show="open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="-translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="-translate-x-full"
             class="fixed inset-y-0 left-0 z-40 w-64 bg-white shadow-lg flex flex-col px-3 py-4 overflow-y-auto"
             style="display: none;">
            <div class="flex items-center justify-between mb-4 px-1">
                <span class="font-semibold text-gray-700">Menu</span>
                <button @click="open = false" aria-label="Close navigation menu"
                        class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <nav class="space-y-1" @click="open = false">
                @include('layouts.partials.nav-items', ['linkComponent' => 'responsive-nav-link'])
            </nav>

            @include('layouts.partials.user-menu', ['linkComponent' => 'responsive-nav-link', 'showGuestLinks' => false])
        </div>
    </div>
</div>
