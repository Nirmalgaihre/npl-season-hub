
<form method="POST"
      action="{{ $action }}"
      enctype="multipart/form-data"
      class="mt-7 space-y-6 border border-slate-200 bg-white p-5 sm:p-8"
      id="playerForm">
    @csrf

    @if ($method !== 'POST')
        @method($method)
    @endif

    @if ($errors->any())
        <div class="border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            Please correct the errors below.
        </div>
    @endif

    {{-- Player name --}}
    <div>
        <label for="name" class="mb-2 block text-sm font-bold text-slate-700">
            Player name *
        </label>
        <input id="name" name="name" required maxlength="150"
               value="{{ old('name', $player?->name) }}"
               class="w-full border border-slate-300 px-4 py-3 outline-none focus:border-[#1d0647]">
        @error('name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Team --}}
    <div>
        <label for="team_id" class="mb-2 block text-sm font-bold text-slate-700">
            Team
        </label>
        <select id="team_id" name="team_id"
                class="w-full border border-slate-300 px-4 py-3 outline-none focus:border-[#1d0647]">
            <option value="">No team assigned</option>
            @foreach ($teams as $team)
                <option value="{{ $team->id }}"
                    @selected((string) old('team_id', $player?->team_id) === (string) $team->id)>
                    {{ $team->name }}
                </option>
            @endforeach
        </select>
        @error('team_id')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Role and nationality --}}
    <div class="grid gap-5 sm:grid-cols-2">
        <div>
            <label for="role" class="mb-2 block text-sm font-bold text-slate-700">
                Playing role
            </label>
            <select id="role" name="role"
                    class="w-full border border-slate-300 px-4 py-3 outline-none focus:border-[#1d0647]">
                <option value="">Select role</option>
                @foreach ([
                    'batter' => 'Batter',
                    'bowler' => 'Bowler',
                    'all_rounder' => 'All-rounder',
                    'wicket_keeper' => 'Wicket-keeper',
                ] as $value => $label)
                    <option value="{{ $value }}"
                        @selected(old('role', $player?->role) === $value)>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
            @error('role')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="nationality" class="mb-2 block text-sm font-bold text-slate-700">
                Nationality
            </label>
            <input id="nationality" name="nationality" maxlength="100"
                   value="{{ old('nationality', $player?->nationality) }}"
                   placeholder="e.g. Nepal"
                   class="w-full border border-slate-300 px-4 py-3 outline-none focus:border-[#1d0647]">
            @error('nationality')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- Player image upload --}}
    <div>
        <div class="mb-3">
            <label class="block text-sm font-bold text-slate-700">
                Player photo
            </label>
            <p class="mt-1 text-xs text-slate-500">
                Upload a clear player portrait for the player profile and statistics cards.
            </p>
        </div>

        <div id="photoDropzone"
             role="button"
             tabindex="0"
             aria-label="Upload player photo. Drop an image here or press Enter to browse."
             class="group relative cursor-pointer border-2 border-dashed border-slate-300 bg-slate-50 px-5 py-8 text-center transition hover:border-[#1d0647] hover:bg-purple-50/40 sm:px-8">

            {{-- Upload instructions --}}
            <div id="photoPlaceholder" class="flex flex-col items-center">
                <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-full border border-slate-200 bg-white text-[#1d0647] shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg"
                         width="26" height="26" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="1.7"
                         stroke-linecap="round" stroke-linejoin="round"
                         aria-hidden="true">
                        <path d="M12 16V4"/>
                        <path d="m7 9 5-5 5 5"/>
                        <path d="M20 16.5v2A1.5 1.5 0 0 1 18.5 20h-13A1.5 1.5 0 0 1 4 18.5v-2"/>
                    </svg>
                </div>

                <p class="text-sm font-bold text-slate-800">
                    Drag and drop your player photo here
                </p>

                <p class="mt-2 text-sm text-slate-500">
                    or
                    <span class="font-bold text-[#1d0647] underline underline-offset-2">
                        browse files
                    </span>
                </p>

                <p class="mt-4 text-xs text-slate-500">
                    JPG, PNG or WebP · Maximum 3 MB
                </p>
            </div>

            {{-- Image preview --}}
            <div id="photoPreviewContainer" class="hidden">
                <img id="photoPreview"
                     src=""
                     alt="Selected player photo preview"
                     class="mx-auto h-36 w-36 rounded-full border-4 border-white object-cover shadow-md">

                <p id="photoFileName"
                   class="mx-auto mt-4 max-w-full break-all text-sm font-bold text-slate-800">
                </p>

                <p id="photoFileSize" class="mt-1 text-xs text-slate-500"></p>

                <div class="mt-4 flex flex-wrap items-center justify-center gap-3">
                    <button type="button"
                            id="changePhotoButton"
                            class="border border-slate-300 bg-white px-4 py-2 text-xs font-bold text-slate-700 hover:border-[#1d0647] hover:text-[#1d0647]">
                        Change photo
                    </button>

                    <button type="button"
                            id="removePhotoButton"
                            class="border border-red-200 bg-white px-4 py-2 text-xs font-bold text-red-600 hover:bg-red-50">
                        Remove photo
                    </button>
                </div>
            </div>

            {{-- Actual file input --}}
            <input type="file"
                   id="photo"
                   name="photo"
                   accept="image/jpeg,image/png,image/webp"
                   class="sr-only">
        </div>

        <p id="photoError"
           class="mt-2 hidden text-sm font-medium text-red-600"
           role="alert"></p>

        @error('photo')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror

        @if ($player?->photo_path)
            <div id="currentPhotoSection"
                 class="mt-4 flex items-center gap-4 border border-slate-200 bg-white p-4">
                <img src="{{ $player->photo_url }}"
                     alt="{{ $player->name }}"
                     class="h-16 w-16 rounded-full border border-slate-200 object-cover">

                <div class="min-w-0">
                    <p class="text-sm font-bold text-slate-800">
                        Current player photo
                    </p>
                    <p class="mt-1 text-xs text-slate-500">
                        Your current image will remain unchanged unless you upload a replacement.
                    </p>
                </div>
            </div>
        @endif
    </div>

    {{-- Active player --}}
    <label class="flex items-center gap-3 border border-slate-200 bg-slate-50 p-4">
        <input type="checkbox" name="is_active" value="1"
               @checked(old('is_active', $player?->is_active ?? true))>
        <span>
            <span class="block text-sm font-bold">Active player</span>
            <span class="mt-1 block text-xs text-slate-500">
                Active profiles can be matched to scorecard names.
            </span>
        </span>
    </label>

    {{-- Form actions --}}
    <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end">
        <a href="{{ route('admin.players.index') }}"
           class="border border-slate-300 px-5 py-3 text-center text-sm font-bold hover:bg-slate-50">
            Cancel
        </a>

        <button type="submit"
                class="bg-[#1d0647] px-6 py-3 text-sm font-bold text-white transition hover:bg-[#2d0b68]">
            {{ $player ? 'Save changes' : 'Create player' }}
        </button>
    </div>
</form>

{{-- Drag-and-drop upload behaviour --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const dropzone = document.getElementById('photoDropzone');
    const input = document.getElementById('photo');
    const placeholder = document.getElementById('photoPlaceholder');
    const previewContainer = document.getElementById('photoPreviewContainer');
    const preview = document.getElementById('photoPreview');
    const fileName = document.getElementById('photoFileName');
    const fileSize = document.getElementById('photoFileSize');
    const error = document.getElementById('photoError');
    const removeButton = document.getElementById('removePhotoButton');
    const changeButton = document.getElementById('changePhotoButton');
    const currentPhotoSection = document.getElementById('currentPhotoSection');

    if (!dropzone || !input) return;

    const maxSize = 3 * 1024 * 1024;
    const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    let previewUrl = null;

    function showError(message) {
        error.textContent = message;
        error.classList.remove('hidden');
    }

    function clearError() {
        error.textContent = '';
        error.classList.add('hidden');
    }

    function clearPreviewUrl() {
        if (previewUrl) {
            URL.revokeObjectURL(previewUrl);
            previewUrl = null;
        }
    }

    function formatSize(bytes) {
        return bytes < 1024 * 1024
            ? (bytes / 1024).toFixed(0) + ' KB'
            : (bytes / (1024 * 1024)).toFixed(2) + ' MB';
    }

    function showPreview(file) {
        clearError();

        if (!file) return;

        if (!allowedTypes.includes(file.type)) {
            input.value = '';
            showError('Please choose a JPG, PNG or WebP image.');
            return;
        }

        if (file.size > maxSize) {
            input.value = '';
            showError('The image must be 3 MB or smaller.');
            return;
        }

        if (file.size === 0) {
            input.value = '';
            showError('The selected image is empty. Please choose another file.');
            return;
        }

        clearPreviewUrl();
        previewUrl = URL.createObjectURL(file);

        preview.onload = function () {
            clearError();
        };

        preview.onerror = function () {
            clearPreviewUrl();
            input.value = '';
            preview.removeAttribute('src');
            showError('This image could not be previewed. Please choose another image.');
            previewContainer.classList.add('hidden');
            placeholder.classList.remove('hidden');
        };

        preview.src = previewUrl;
        fileName.textContent = file.name;
        fileSize.textContent = formatSize(file.size);

        placeholder.classList.add('hidden');
        previewContainer.classList.remove('hidden');

        if (currentPhotoSection) {
            currentPhotoSection.classList.add('hidden');
        }

        dropzone.classList.remove('border-slate-300');
        dropzone.classList.add('border-[#1d0647]', 'bg-purple-50/40');
    }

    function openFilePicker() {
        input.click();
    }

    dropzone.addEventListener('click', function (event) {
        if (event.target.closest('button')) return;
        openFilePicker();
    });

    dropzone.addEventListener('keydown', function (event) {
        if (event.target !== dropzone) return;

        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            openFilePicker();
        }
    });

    input.addEventListener('change', function () {
        const file = input.files && input.files[0];

        if (file) {
            showPreview(file);
        }
    });

    ['dragenter', 'dragover'].forEach(function (eventName) {
        dropzone.addEventListener(eventName, function (event) {
            event.preventDefault();
            event.stopPropagation();

            dropzone.classList.add('border-[#1d0647]', 'bg-purple-100/60');
        });
    });

    ['dragleave', 'dragend'].forEach(function (eventName) {
        dropzone.addEventListener(eventName, function (event) {
            event.preventDefault();
            event.stopPropagation();

            if (!dropzone.contains(event.relatedTarget)) {
                dropzone.classList.remove('bg-purple-100/60');

                if (!preview.src || previewContainer.classList.contains('hidden')) {
                    dropzone.classList.remove('border-[#1d0647]');
                }
            }
        });
    });

    dropzone.addEventListener('drop', function (event) {
        event.preventDefault();
        event.stopPropagation();

        dropzone.classList.remove('bg-purple-100/60');

        const file = event.dataTransfer.files &&
                     event.dataTransfer.files[0];

        if (!file) return;

        const transfer = new DataTransfer();
        transfer.items.add(file);
        input.files = transfer.files;

        showPreview(file);
    });

    changeButton.addEventListener('click', function (event) {
        event.stopPropagation();
        openFilePicker();
    });

    removeButton.addEventListener('click', function (event) {
        event.stopPropagation();

        input.value = '';
        clearPreviewUrl();
        preview.removeAttribute('src');

        previewContainer.classList.add('hidden');
        placeholder.classList.remove('hidden');

        dropzone.classList.remove(
            'border-[#1d0647]',
            'bg-purple-50/40',
            'bg-purple-100/60'
        );
        dropzone.classList.add('border-slate-300');

        clearError();

        if (currentPhotoSection) {
            currentPhotoSection.classList.remove('hidden');
        }
    });

    document.getElementById('playerForm').addEventListener('submit', function (event) {
        const file = input.files && input.files[0];

        if (!file) return;

        if (!allowedTypes.includes(file.type) || file.size > maxSize || file.size === 0) {
            event.preventDefault();
            showError('Please select a valid JPG, PNG or WebP image smaller than 3 MB.');
        }
    });

    window.addEventListener('beforeunload', clearPreviewUrl);
});
</script>
