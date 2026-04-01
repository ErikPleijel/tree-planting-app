<x-app-layout>
    <div class="max-w-2xl mx-auto mt-8 px-4 pb-12">

        {{-- Header --}}
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Upload Photos</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $plantingLocation->location }}</p>
        </div>

        {{-- Server-side validation errors --}}
        @if($errors->any())
            <div class="bg-red-50 border border-red-300 text-red-700 rounded-lg px-4 py-3 mb-6 text-sm">
                <p class="font-semibold mb-1">Please fix the following:</p>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Client-side error list --}}
        <ul id="clientErrors" class="mb-4 space-y-1 text-sm text-red-600 hidden"></ul>

        {{-- The form posts the hidden file input that we keep in sync --}}
        <form
            action="{{ route('pictures.upload.store', $plantingLocation) }}"
            method="POST"
            enctype="multipart/form-data"
            id="uploadForm"
        >
            @csrf

            {{-- This is the ONLY file input that gets submitted.
                 We keep it hidden and sync it from our DataTransfer object. --}}
            <input
                type="file"
                id="hiddenFileInput"
                name="photos[]"
                multiple
                accept="image/*"
                class="hidden"
                tabindex="-1"
                aria-hidden="true"
            >

            {{-- Drop zone / visible picker trigger --}}
            <div
                id="dropZone"
                class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center
                       cursor-pointer hover:border-green-500 hover:bg-green-50 transition-colors"
            >
                {{-- Separate visible input purely for triggering the picker UI --}}
                <input
                    type="file"
                    id="pickerInput"
                    accept="image/*"
                    multiple
                    class="hidden"
                >

                <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                          stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <p class="text-gray-600 font-medium">Tap to choose photos</p>
                <p class="text-gray-400 text-sm mt-1">or drag &amp; drop here</p>
                <p class="text-gray-400 text-xs mt-2">
                    JPG, PNG, WEBP, GIF &middot; Max 8 MB per image &middot; Up to 10 images
                </p>
            </div>

            {{-- Preview grid --}}
            <div id="previewGrid" class="mt-5 grid grid-cols-2 sm:grid-cols-3 gap-3 hidden"></div>

            {{-- Show on welcome toggle --}}
            <div class="mt-5 flex items-center gap-3 bg-gray-50 border border-gray-200 rounded-lg px-4 py-3">
                <input
                    type="checkbox"
                    id="showOnWelcome"
                    name="show_on_welcome"
                    value="1"
                    checked
                    class="w-4 h-4 accent-green-600 cursor-pointer"
                >
                <label for="showOnWelcome" class="text-sm text-gray-700 cursor-pointer select-none">
                    Show uploaded photos on the Welcome page
                </label>
            </div>

            {{-- Actions --}}
            <div class="mt-6 flex flex-col sm:flex-row gap-3">
                <button
                    type="submit"
                    id="submitBtn"
                    disabled
                    class="flex-1 bg-primary text-white py-2.5 px-6 rounded-lg font-semibold
                           hover:bg-green-700 transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                >
                    Upload Photos
                </button>
                <a
                    href="{{ route('planting-locations.show', $plantingLocation) }}"
                    class="flex-1 text-center bg-gray-100 text-gray-700 py-2.5 px-6 rounded-lg
                           font-semibold hover:bg-gray-200 transition-colors"
                >
                    Cancel
                </a>
            </div>

            {{-- Progress bar --}}
            <div id="progressWrap" class="mt-4 hidden">
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div id="progressBar"
                         class="bg-green-500 h-2 rounded-full transition-all duration-300"
                         style="width:0%">
                    </div>
                </div>
                <p class="text-xs text-gray-500 text-center mt-1" id="progressLabel">Uploading…</p>
            </div>

        </form>
    </div>

    <script>
        (() => {
            const MAX_BYTES     = 8 * 1024 * 1024;   // 8 MB
            const MAX_FILES     = 10;
            const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

            const dropZone        = document.getElementById('dropZone');
            const pickerInput     = document.getElementById('pickerInput');   // triggers UI picker
            const hiddenFileInput = document.getElementById('hiddenFileInput'); // submitted with form
            const previewGrid     = document.getElementById('previewGrid');
            const submitBtn       = document.getElementById('submitBtn');
            const errorList       = document.getElementById('clientErrors');
            const form            = document.getElementById('uploadForm');
            const progressWrap    = document.getElementById('progressWrap');
            const progressBar     = document.getElementById('progressBar');
            const progressLbl     = document.getElementById('progressLabel');

            // ── Single source of truth: a DataTransfer object ──────────────────
            // We keep dt.files in sync with hiddenFileInput.files at all times.
            // pickerInput is never submitted — it only feeds new files into dt.
            let dt = new DataTransfer();

            // ── Open picker when drop zone is clicked ──────────────────────────
            dropZone.addEventListener('click', () => pickerInput.click());

            // ── Picker selection ───────────────────────────────────────────────
            pickerInput.addEventListener('change', () => {
                addFiles(Array.from(pickerInput.files));
                pickerInput.value = ''; // reset so the same file can be picked again
            });

            // ── Drag & drop ────────────────────────────────────────────────────
            dropZone.addEventListener('dragover', e => {
                e.preventDefault();
                dropZone.classList.add('border-green-500', 'bg-green-50');
            });
            ['dragleave', 'dragend'].forEach(evt =>
                dropZone.addEventListener(evt, () =>
                    dropZone.classList.remove('border-green-500', 'bg-green-50')
                )
            );
            dropZone.addEventListener('drop', e => {
                e.preventDefault();
                dropZone.classList.remove('border-green-500', 'bg-green-50');
                addFiles(Array.from(e.dataTransfer.files));
            });

            // ── Add files into the DataTransfer ───────────────────────────────
            function addFiles(incoming) {
                const errors = [];

                for (const file of incoming) {
                    if (!ALLOWED_TYPES.includes(file.type)) {
                        errors.push(`"${file.name}" is not a supported image type (JPG, PNG, WEBP, GIF).`);
                        continue;
                    }
                    if (file.size > MAX_BYTES) {
                        const mb = (file.size / 1024 / 1024).toFixed(1);
                        errors.push(`"${file.name}" is ${mb} MB — maximum is 8 MB.`);
                        continue;
                    }
                    if (dt.files.length >= MAX_FILES) {
                        errors.push(`Maximum ${MAX_FILES} images allowed.`);
                        break;
                    }
                    // Deduplicate by name + size
                    let dupe = false;
                    for (const existing of dt.files) {
                        if (existing.name === file.name && existing.size === file.size) {
                            dupe = true; break;
                        }
                    }
                    if (!dupe) dt.items.add(file);
                }

                showErrors(errors);
                syncAndRender();
            }

            // ── Remove a file by index ─────────────────────────────────────────
            function removeFile(idx) {
                const fresh = new DataTransfer();
                Array.from(dt.files).forEach((f, i) => {
                    if (i !== idx) fresh.items.add(f);
                });
                dt = fresh;
                syncAndRender();
            }

            // ── Push dt → hiddenFileInput, then re-render previews ─────────────
            function syncAndRender() {
                // This is the critical step: assign dt.files to the hidden input
                // that actually gets submitted with the form.
                hiddenFileInput.files = dt.files;

                renderPreviews();

                submitBtn.disabled = dt.files.length === 0;
            }

            // ── Render preview cards ───────────────────────────────────────────
            function renderPreviews() {
                previewGrid.innerHTML = '';

                if (dt.files.length === 0) {
                    previewGrid.classList.add('hidden');
                    return;
                }

                previewGrid.classList.remove('hidden');

                Array.from(dt.files).forEach((file, idx) => {
                    const card = document.createElement('div');
                    card.className = 'relative rounded-lg overflow-hidden border shadow-sm bg-white flex flex-col';

                    // Placeholder while FileReader loads
                    card.innerHTML = `
                    <div class="w-full h-32 bg-gray-100 animate-pulse"></div>
                    <div class="px-2 py-1 text-xs text-gray-500 truncate">${escHtml(file.name)}</div>
                    <div class="px-2 pb-1 text-xs text-gray-400">${(file.size / 1024 / 1024).toFixed(2)} MB</div>
                    <button type="button"
                            class="removeBtn absolute top-1 right-1 bg-red-500 text-white
                                   rounded-full w-6 h-6 flex items-center justify-center
                                   text-xs font-bold leading-none hover:bg-red-600 transition-colors"
                            title="Remove">✕</button>
                `;

                    card.querySelector('.removeBtn').addEventListener('click', () => removeFile(idx));

                    previewGrid.appendChild(card);

                    // Load preview image async
                    const reader = new FileReader();
                    reader.onload = e => {
                        const placeholder = card.querySelector('div.animate-pulse');
                        if (placeholder) {
                            const img = document.createElement('img');
                            img.src = e.target.result;
                            img.alt = file.name;
                            img.className = 'w-full h-32 object-cover';
                            placeholder.replaceWith(img);
                        }
                    };
                    reader.readAsDataURL(file);
                });
            }

            // ── Error display ──────────────────────────────────────────────────
            function showErrors(errors) {
                errorList.innerHTML = '';
                if (!errors.length) { errorList.classList.add('hidden'); return; }
                errors.forEach(msg => {
                    const li = document.createElement('li');
                    li.textContent = msg;
                    errorList.appendChild(li);
                });
                errorList.classList.remove('hidden');
            }

            // ── HTML escape helper ─────────────────────────────────────────────
            function escHtml(str) {
                return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
            }

            // ── Progress animation on submit ───────────────────────────────────
            form.addEventListener('submit', e => {
                if (hiddenFileInput.files.length === 0) {
                    e.preventDefault();
                    showErrors(['Please select at least one photo.']);
                    return;
                }
                submitBtn.disabled = true;
                progressWrap.classList.remove('hidden');
                const total = hiddenFileInput.files.length;
                let pct = 0;
                const tick = setInterval(() => {
                    pct = Math.min(pct + Math.random() * 12, 90);
                    progressBar.style.width = pct + '%';
                    progressLbl.textContent = `Uploading ${total} photo${total > 1 ? 's' : ''}…`;
                }, 300);
            });

        })();
    </script>
</x-app-layout>
