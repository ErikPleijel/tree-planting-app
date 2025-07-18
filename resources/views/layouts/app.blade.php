<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="emerald">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        {{-- Leaflet CSS --}}
        <!-- Leaflet CSS -->
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

        <!-- Leaflet JS -->
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">

    @include('partials.header')



    <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>

    <footer class="bg-neutral text-neutral-content p-6 mt-12">
        <div class="max-w-4xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Address -->
            <div>
                <h3 class="font-bold mb-2">Main Office</h3>
                <p>
                    ItacenMu.org<br>
                    123 Green Street<br>
                    Tree City, TX 75001<br>
                    Niger state
                </p>
            </div>

            <!-- Contact -->
            <div>
                <h3 class="font-bold mb-2">Contact</h3>
                <p>Email: <a href="mailto:info@itacenmu.org" class="link link-hover">info@itacenmu.org</a></p>
                <p>Phone: <a href="tel:+1234567890" class="link link-hover">+1 (234) 567-890</a></p>
            </div>

            <!-- Credits -->
            <div>
                <h3 class="font-bold mb-2">Credits</h3>
                <p>Designed by Erik Pleijel & Martha Kagiri</p>
                <p>Email: epost@erikpleijel.se</p>
                <p>Email: marthapleijel@yahoo.se</p>
            </div>
        </div>
    </footer>

    </body>
</html>
