@props(['active' => false])

@php
$classes = $active
            ? 'flex items-center px-3 py-2 rounded-md text-sm font-medium bg-primary text-primary-content'
            : 'flex items-center px-3 py-2 rounded-md text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
