<x-guest-layout>
    <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Telephone -->
        <div class="mt-4">
            <x-input-label for="telephone" :value="__('Telephone')" />
            <x-text-input id="telephone" class="block mt-1 w-full" type="tel" name="telephone" :value="old('telephone')" autocomplete="tel" />
            <x-input-error :messages="$errors->get('telephone')" class="mt-2" />
        </div>

        <!-- Country -->
        <div class="mt-4">
            <x-input-label for="country" :value="__('Country')" />
            <x-text-input id="country" class="block mt-1 w-full" type="text" name="country" :value="old('country')" autocomplete="country-name" />
            <x-input-error :messages="$errors->get('country')" class="mt-2" />
        </div>

        <!-- Profile Picture -->
        <div class="mt-4" x-data="profilePicture()">
            <x-input-label :value="__('Profile Picture (optional)')" />

            <!-- Mode toggle -->
            <div class="flex mt-1 rounded-md border border-gray-300 overflow-hidden">
                <button type="button" @click="setMode('upload')"
                    :class="mode === 'upload' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'"
                    class="flex-1 py-2 text-sm font-medium transition-colors">
                    Upload Photo
                </button>
                <button type="button" @click="setMode('selfie')"
                    :class="mode === 'selfie' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'"
                    class="flex-1 py-2 text-sm font-medium transition-colors border-l border-gray-300">
                    Take Selfie
                </button>
            </div>

            <!-- Upload mode -->
            <div x-show="mode === 'upload'" class="mt-2 space-y-2">
                <input type="file" name="profile_picture_file" id="profilePictureFile" accept="image/*"
                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                    @change="previewUpload($event)">
                <img id="uploadPreview" class="hidden w-full max-h-48 object-cover rounded border">
            </div>

            <!-- Selfie mode -->
            <div x-show="mode === 'selfie'" class="mt-2 space-y-2">
                <video id="selfieVideo" autoplay playsinline class="w-full rounded border" x-show="!selfieCaptured"></video>
                <canvas id="selfieCanvas" class="w-full rounded border" x-show="selfieCaptured"></canvas>
                <div class="flex gap-2">
                    <button type="button" @click="captureSelfie()" x-show="!selfieCaptured"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 px-4 rounded">
                        Capture
                    </button>
                    <button type="button" @click="retakeSelfie()" x-show="selfieCaptured"
                        class="w-full bg-gray-500 hover:bg-gray-600 text-white text-sm font-medium py-2 px-4 rounded">
                        Retake
                    </button>
                </div>
                <input type="hidden" name="profile_picture_data" id="selfieData">
            </div>

            <x-input-error :messages="$errors->get('profile_picture_file')" class="mt-2" />
        </div>

        <!-- Invitation Code -->
        <div class="mt-4">
            <x-input-label for="invitation_code" :value="__('Invitation Code')" />
            <x-text-input id="invitation_code" class="block mt-1 w-full" type="text" name="invitation_code" required autocomplete="off" />
            <x-input-error :messages="$errors->get('invitation_code')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>

    <script>
        function profilePicture() {
            return {
                mode: 'upload',
                selfieCaptured: false,
                stream: null,

                setMode(newMode) {
                    if (newMode === this.mode) return;
                    this.mode = newMode;
                    if (newMode === 'selfie') {
                        this.$nextTick(() => this.startCamera());
                    } else {
                        this.stopCamera();
                        this.selfieCaptured = false;
                        document.getElementById('selfieData').value = '';
                    }
                },

                startCamera() {
                    navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } })
                        .then(stream => {
                            this.stream = stream;
                            document.getElementById('selfieVideo').srcObject = stream;
                        })
                        .catch(() => alert('Camera access denied or not available.'));
                },

                stopCamera() {
                    if (this.stream) {
                        this.stream.getTracks().forEach(t => t.stop());
                        this.stream = null;
                    }
                },

                captureSelfie() {
                    const video  = document.getElementById('selfieVideo');
                    const canvas = document.getElementById('selfieCanvas');
                    canvas.width  = video.videoWidth;
                    canvas.height = video.videoHeight;
                    canvas.getContext('2d').drawImage(video, 0, 0);
                    document.getElementById('selfieData').value = canvas.toDataURL('image/jpeg');
                    this.selfieCaptured = true;
                    this.stopCamera();
                },

                retakeSelfie() {
                    document.getElementById('selfieData').value = '';
                    this.selfieCaptured = false;
                    this.startCamera();
                },

                previewUpload(event) {
                    const file = event.target.files[0];
                    if (!file) return;
                    const preview = document.getElementById('uploadPreview');
                    preview.src = URL.createObjectURL(file);
                    preview.classList.remove('hidden');
                },
            };
        }
    </script>
</x-guest-layout>