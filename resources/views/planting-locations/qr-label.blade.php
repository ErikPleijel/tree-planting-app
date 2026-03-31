<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Label - {{ $plantingLocation->location }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background: white !important;
            }
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-900">

<div class="no-print max-w-3xl mx-auto px-4 pt-6">
    <div class="flex gap-3 mb-4">
        <button onclick="window.print()"
                class="bg-primary text-white px-4 py-2 rounded hover:bg-green-700 transition-colors">
            Print
        </button>

        <a href="{{ route('planting-locations.show', $plantingLocation) }}"
           class="border border-gray-300 bg-white px-4 py-2 rounded hover:bg-gray-50 transition-colors">
            Back
        </a>
    </div>
</div>

<div class="max-w-3xl mx-auto px-4 pb-8">
    <div class="bg-white shadow-md rounded-xl border p-8 print:shadow-none print:border-black">

        <!-- Logo + Website -->
        <div class="flex items-center justify-center gap-4 mb-6">
            <img src="{{ asset('images/TreePlantingImage.png') }}"
                 alt="ItacenMu Logo"
                 class="h-16 object-contain">

            <span class="text-3xl font-semibold tracking-wide text-gray-700">
                ItacenMu.org
             </span>
        </div>

        <!-- Heading -->
        <div class="text-center mb-6">
            <h1 class="text-2xl md:text-3xl font-bold uppercase tracking-wide break-words">
                {{ $plantingLocation->location }}
            </h1>
        </div>

        <!-- QR code -->
        <div class="flex justify-center mb-6">
            <div class="p-4 border border-gray-300 rounded-lg">
                {!! QrCode::size(280)->generate(route('public.planting-locations.show', $plantingLocation->public_code)) !!}
            </div>
        </div>

        <!-- Public code -->
        <div class="text-center mb-4">
            <p class="text-sm text-gray-600 mb-1">Location Code</p>
            <p class="text-2xl font-bold tracking-[0.25em]">
                {{ $plantingLocation->public_code }}
            </p>
        </div>

        <!-- Optional scan text -->
        <div class="text-center mt-6">
            <p class="text-lg font-medium">
                Scan to view planting information for this location
            </p>
        </div>
    </div>
</div>

</body>
</html>
