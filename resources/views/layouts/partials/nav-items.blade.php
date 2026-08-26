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
    HOME
</x-dynamic-component>

@role('Admin|SuperAdmin|Monitor|Grower')
<x-dynamic-component :component="$linkComponent" :href="route('dashboard')" :active="request()->routeIs('dashboard')">
    Dashboard
</x-dynamic-component>
@endrole

<x-dynamic-component :component="$linkComponent" :href="route('stats.map')" :active="request()->routeIs('stats.map')">
    Map
</x-dynamic-component>

<x-dynamic-component :component="$linkComponent" :href="route('stats.stats1')" :active="request()->routeIs('stats.stats1')">
    Statistics
</x-dynamic-component>

@role('Admin|SuperAdmin|Monitor|Grower')
<x-dynamic-component :component="$linkComponent" :href="route('planting-locations.index')" :active="request()->routeIs('planting-locations.*')">
    Locations
</x-dynamic-component>
@endrole

@role('Admin|SuperAdmin|Monitor')
<x-dynamic-component :component="$linkComponent" :href="route('tree-plantings.index')" :active="request()->routeIs('tree-plantings.*')">
    Plantings
</x-dynamic-component>
@endrole

@role('Admin|SuperAdmin|Monitor')
<x-dynamic-component :component="$linkComponent" :href="route('inspections.index')" :active="request()->routeIs('inspections.*')">
    Inspections
</x-dynamic-component>
@endrole

<x-dynamic-component :component="$linkComponent" :href="route('tree-types.index')" :active="request()->routeIs('tree-types.*')">
    Tree Types
</x-dynamic-component>

@role('Admin|SuperAdmin')
<x-dynamic-component :component="$linkComponent" :href="route('users.report')" :active="request()->routeIs('users.report')">
    Authorizations
</x-dynamic-component>
@endrole

@auth
<x-dynamic-component :component="$linkComponent" :href="route('team.index')" :active="request()->routeIs('team.index')">
    The Team
</x-dynamic-component>
@endauth
