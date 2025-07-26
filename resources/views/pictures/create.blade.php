<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">📸 Upload Photo for: {{ $plantingLocation->name }}</h2>
    </x-slot>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex justify-center items-center min-h-screen bg-gray-50 px-4">
        <form id="photoForm" action="{{ route('pictures.store') }}" method="POST" enctype="multipart/form-data"
              class="bg-white shadow-md rounded-lg p-6 w-full max-w-md space-y-4">
            @csrf
            <input type="hidden" name="planting_location_id" value="{{ $plantingLocation->id }}">
            <input type="hidden" name="image_data" id="image_data">

            {{-- Live Preview --}}
            <div>
                <label class="block mb-2 text-sm font-medium text-gray-700">Live Camera</label>
                <video id="camera" autoplay playsinline class="border rounded w-full"></video>
            </div>

            {{-- Capture Button --}}
            <div class="text-center">
                <button type="button" onclick="capturePhoto()"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded">
                    📸 Take Photo
                </button>
            </div>

            {{-- Captured Preview --}}
            <div>
                <label class="block mb-2 text-sm font-medium text-gray-700">Captured Preview</label>
                <canvas id="previewCanvas" class="hidden border rounded w-full"></canvas>
            </div>

            {{-- Submit --}}
            <div class="text-center">
                <button type="submit"
                        class="bg-green-600 hover:bg-green-700 text-white font-semibold px-4 py-2 rounded">
                    Upload Photo
                </button>
            </div>
        </form>
    </div>



    <a href="{{ route('planting-locations.show', $plantingLocation->id) }}" class="inline-block mb-6">
        <button class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded">
            ← Back to {{ $plantingLocation->name }}
        </button>
    </a>

    {{-- Thumbnails --}}
    <h3 class="text-lg font-semibold mb-2">Uploaded Photos</h3>
    <div x-data="{ showModal: false, modalImage: '' }">
        {{-- Thumbnail Grid --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach($pictures as $pic)
                <div class="border p-2 rounded shadow cursor-pointer"
                     @click="modalImage='{{ asset('storage/' . $pic->path) }}'; showModal=true">
                    <img
                        src="{{ asset('storage/' . $pic->thumbnail) }}"
                        alt="Thumbnail"
                        class="w-full h-auto rounded"
                    >
                    <p class="text-xs text-gray-600 mt-1">Uploaded: {{ $pic->created_at->format('Y-m-d H:i') }}</p>
                </div>
            @endforeach
        </div>

        {{-- Modal --}}
        <div
            x-show="showModal"
            x-cloak
            class="fixed inset-0 bg-black bg-opacity-80 flex items-center justify-center z-50"
            @click.self="showModal = false"
        >
            <div class="relative max-w-3xl w-full p-4">
                <button
                    @click="showModal = false"
                    class="absolute top-2 right-2 text-white text-2xl font-bold"
                    aria-label="Close"
                >
                    &times;
                </button>
                <img :src="modalImage" alt="Full Image" class="rounded shadow-xl max-h-[90vh] mx-auto">
            </div>
        </div>
    </div>

    @if($pictures->count())
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach($pictures as $pic)
                <div class="border p-2 rounded shadow">
                    <img
                        src="{{ asset('storage/' . $pic->thumbnail) }}"
                        alt="Photo"
                        class="w-full h-auto rounded"
                    >
                    <p class="text-xs text-gray-600 mt-1">Uploaded: {{ $pic->created_at->format('Y-m-d H:i') }}</p>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-gray-500">No photos uploaded yet.</p>
    @endif
    <script>
        const video = document.getElementById('camera');
        const canvas = document.getElementById('previewCanvas');
        const imageDataInput = document.getElementById('image_data');

        // Start camera stream
        navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" } })
            .then(stream => {
                video.srcObject = stream;
            })
            .catch(err => {
                alert("Camera access denied or not available.");
                console.error(err);
            });

        function capturePhoto() {
            const context = canvas.getContext('2d');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            context.drawImage(video, 0, 0);
            canvas.classList.remove('hidden');

            // Convert to base64 and store in hidden input
            imageDataInput.value = canvas.toDataURL('image/jpeg');
        }
    </script>

</x-app-layout>
