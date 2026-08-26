{{--
    Shared profile/logout footer — the single consolidated implementation
    used by both the desktop sidebar and the mobile drawer (previously two
    separate implementations: a dropdown on desktop, an always-expanded
    block on mobile). Name/role display now lives in partials/header.blade.php
    instead (desktop only).

    Expects a $linkComponent variable ('sidebar-link' or
    'responsive-nav-link') naming which Blade component to render each
    link with, matching layouts/partials/nav-items.blade.php's convention.

    Optionally accepts $showGuestLinks (default true) to suppress the
    guest Login/Register block per-caller — the mobile drawer passes
    false since guests get a compact Login button in the mobile top bar
    instead (see layouts/navigation.blade.php).
--}}
@auth
    <div class="border-t border-gray-200 pt-4 space-y-1">
        <x-dynamic-component :component="$linkComponent" :href="route('profile.edit')">
            <i class="fas fa-user mr-2"></i>{{ __('Profile') }}
        </x-dynamic-component>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-dynamic-component :component="$linkComponent" :href="route('logout')"
                             onclick="event.preventDefault(); this.closest('form').submit();">
                <i class="fas fa-right-from-bracket mr-2"></i>{{ __('Log Out') }}
            </x-dynamic-component>
        </form>
    </div>
@else
    @if($showGuestLinks ?? true)
        <div class="border-t border-gray-200 pt-4 px-3 space-y-1">
            <x-dynamic-component :component="$linkComponent" :href="route('login')">
                <i class="fas fa-right-to-bracket mr-2"></i>{{ __('Log in') }}
            </x-dynamic-component>
            @if (Route::has('register'))
                <x-dynamic-component :component="$linkComponent" :href="route('register')">
                    <i class="fas fa-user-plus mr-2"></i>{{ __('Register') }}
                </x-dynamic-component>
            @endif
        </div>
    @endif
@endauth
