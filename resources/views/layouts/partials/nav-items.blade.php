{{--
    Shared nav link list — included by both the desktop sidebar and the
    mobile drawer in layouts/navigation.blade.php, so the two surfaces
    can never drift out of sync on items, order, or role gating.

    Expects a $linkComponent variable ('sidebar-link' or
    'responsive-nav-link' — no 'x-' prefix, since <x-dynamic-component>
    takes the bare component name) naming which Blade component to render
    each link with, since the two surfaces use different link styling.
--}}
<x-dynamic-component :component="$linkComponent" :href="route('home')" :active="request()->routeIs('home')">
    <i class="fas fa-house mr-2"></i>HOME
</x-dynamic-component>

@role('Admin|SuperAdmin|Monitor|Grower')
<x-dynamic-component :component="$linkComponent" :href="route('dashboard')" :active="request()->routeIs('dashboard')">
    <i class="fas fa-gauge-high mr-2"></i>Dashboard
</x-dynamic-component>
@endrole

<x-dynamic-component :component="$linkComponent" :href="route('stats.map')" :active="request()->routeIs('stats.map')">
    <i class="fas fa-map mr-2"></i>Map
</x-dynamic-component>

<x-dynamic-component :component="$linkComponent" :href="route('stats.stats1')" :active="request()->routeIs('stats.stats1')">
    <i class="fas fa-chart-column mr-2"></i>Statistics
</x-dynamic-component>

@role('Admin|SuperAdmin|Monitor|Grower')
<x-dynamic-component :component="$linkComponent" :href="route('planting-locations.index')" :active="request()->routeIs('planting-locations.*')">
    <i class="fas fa-location-dot mr-2"></i>Locations
</x-dynamic-component>
@endrole

@role('Admin|SuperAdmin|Monitor')
<x-dynamic-component :component="$linkComponent" :href="route('tree-plantings.index')" :active="request()->routeIs('tree-plantings.*')">
    <i class="fas fa-seedling mr-2"></i>Plantings
</x-dynamic-component>
@endrole

@role('Admin|SuperAdmin|Monitor')
<x-dynamic-component :component="$linkComponent" :href="route('inspections.index')" :active="request()->routeIs('inspections.*')">
    <i class="fas fa-clipboard-check mr-2"></i>Inspections
</x-dynamic-component>
@endrole

<x-dynamic-component :component="$linkComponent" :href="route('tree-types.index')" :active="request()->routeIs('tree-types.*')">
    <i class="fas fa-tree mr-2"></i>Tree Types
</x-dynamic-component>

@role('Admin|SuperAdmin')
<x-dynamic-component :component="$linkComponent" :href="route('users.report')" :active="request()->routeIs('users.report')">
    <i class="fas fa-user-shield mr-2"></i>Authorizations
</x-dynamic-component>
@endrole

@auth
<x-dynamic-component :component="$linkComponent" :href="route('team.index')" :active="request()->routeIs('team.index')">
    <i class="fas fa-users mr-2"></i>The Team
</x-dynamic-component>
@endauth
