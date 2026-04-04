<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <!-- Email -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}
                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>
                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <!-- Telephone -->
        <div>
            <x-input-label for="telephone" :value="__('Telephone')" />
            <x-text-input id="telephone" name="telephone" type="tel" class="mt-1 block w-full" :value="old('telephone', $user->telephone)" autocomplete="tel" />
            <x-input-error class="mt-2" :messages="$errors->get('telephone')" />
        </div>

        <!-- Country -->
        <div>
            <x-input-label for="country" :value="__('Country')" />
            <x-text-input id="country" name="country" type="text" class="mt-1 block w-full" :value="old('country', $user->country)" autocomplete="country-name" />
            <x-input-error class="mt-2" :messages="$errors->get('country')" />
        </div>

        <!-- Profile Picture -->
        <div x-data="profilePicture()">
            <x-input-label :value="__('Profile Picture')" />

            <!-- Current picture -->
            @if ($user->profile_picture_path)
                <div class="mt-2">
                    <img src="{{ asset('storage/' . $user->profile_picture_path) }}"
                         alt="Current profile picture"
                         class="w-28 h-36 rounded-lg object-cover border border-gray-300">
                    <p class="text-xs text-gray-500 mt-1">Current picture — select a new one below to replace it.</p>
                </div>
            @endif

            <!-- Mode toggle -->
            <div class="flex mt-2 rounded-md border border-gray-300 overflow-hidden">
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
                <img id="uploadPreview" class="hidden w-24 h-24 rounded-full object-cover border">
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

            <x-input-error class="mt-2" :messages="$errors->get('profile_picture_file')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>

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
