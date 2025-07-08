{{-- resources/views/home.blade.php --}}

    <!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-gray-900 antialiased">

@include('partials.header')

@guest
    <div class="w-full mt-10 px-10 flex justify-between items-center">

        <div class="flex-1 text-center">
            <p class="text-3xl font-semibold">{{-- Welcome --}}</p>
        </div>

        {{-- Right-aligned question and buttons --}}
        <div class="flex flex-col items-end text-right">
            <p class="text-lg">Involved in this project?</p>

            <div class="flex gap-4 mt-2">
                <a href="{{ route('login') }}"
                   class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                    Log In
                </a>

                <a href="{{ route('register') }}"
                   class="px-6 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">
                    Register
                </a>
            </div>
        </div>
    </div>
@endguest
@include('partials.stats')

</body>
</html>


