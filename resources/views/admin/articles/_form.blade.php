
@php
    $article = $article ?? new \App\Models\Article();

    $selectedTags = old(
        'tags',
        isset($article) ? $article->tags->pluck('id')->map(fn ($id) => (string) $id)->all() : []
    );

    $selectedTags = array_map('strval', (array) $selectedTags);

    $publishedAt = old(
        'published_at',
        $article->published_at
            ? $article->published_at->format('Y-m-d\TH:i')
            : ''
    );

    $publishedDate = old(
        'published_date',
        $publishedAt ? \Illuminate\Support\Carbon::parse($publishedAt)->format('Y-m-d') : ''
    );

    $publishedTime = old(
        'published_time',
        $publishedAt ? \Illuminate\Support\Carbon::parse($publishedAt)->format('H:i') : ''
    );

    $isEditing = $article->exists;
@endphp

@csrf

<link
    href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css"
    rel="stylesheet"
>

<style>
    .article-form {
        color: #0f172a;
    }

    .article-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 22px;
        box-shadow: 0 2px 8px rgb(15 23 42 / 3%);
    }

    .article-label {
        display: block;
        margin-bottom: 8px;
        font-size: 13px;
        font-weight: 700;
        color: #334155;
    }

    .article-input,
    .article-select,
    .article-textarea {
        width: 100%;
        border: 1px solid #cbd5e1;
        border-radius: 9px;
        background: #fff;
        padding: 11px 13px;
        font-size: 14px;
        color: #0f172a;
        outline: none;
        transition: border-color .2s, box-shadow .2s;
    }

    .article-input:focus,
    .article-select:focus,
    .article-textarea:focus {
        border-color: #dc2626;
        box-shadow: 0 0 0 3px rgb(220 38 38 / 10%);
    }

    .article-input-error {
        margin-top: 6px;
        font-size: 12px;
        color: #dc2626;
    }

    .article-help {
        margin-top: 6px;
        font-size: 12px;
        color: #64748b;
    }

    .article-editor .ql-toolbar.ql-snow {
        border: 1px solid #cbd5e1;
        border-radius: 10px 10px 0 0;
        background: #f8fafc;
        padding: 10px;
    }

    .article-editor .ql-container.ql-snow {
        border: 1px solid #cbd5e1;
        border-top: 0;
        border-radius: 0 0 10px 10px;
        font-family: inherit;
        font-size: 16px;
    }

    .article-editor .ql-editor {
        min-height: 360px;
        padding: 20px;
        line-height: 1.85;
        color: #1e293b;
    }

    .article-editor .ql-editor.ql-blank::before {
        color: #94a3b8;
        font-style: normal;
    }

    .article-editor .ql-editor h1 {
        font-size: 2rem;
        line-height: 1.3;
        font-weight: 800;
        margin: 18px 0 12px;
    }

    .article-editor .ql-editor h2 {
        font-size: 1.5rem;
        line-height: 1.4;
        font-weight: 750;
        margin: 16px 0 10px;
    }

    .article-editor .ql-editor h3 {
        font-size: 1.25rem;
        font-weight: 700;
        margin: 14px 0 8px;
    }

    .article-editor .ql-editor a {
        color: #dc2626;
        text-decoration: underline;
    }

    .publish-now-panel {
        border: 1px solid #fecaca;
        border-radius: 12px;
        background: #fff7f7;
        padding: 15px;
    }

    .article-tag {
        display: flex;
        align-items: center;
        gap: 9px;
        padding: 9px 11px;
        border: 1px solid #e2e8f0;
        border-radius: 9px;
        cursor: pointer;
        font-size: 13px;
    }

    .article-tag:has(input:checked) {
        border-color: #fca5a5;
        background: #fff7f7;
    }

    .article-tag input {
        accent-color: #dc2626;
    }

    .article-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border-radius: 9px;
        padding: 11px 17px;
        font-size: 14px;
        font-weight: 700;
        text-decoration: none;
        cursor: pointer;
        transition: .2s;
    }

    .article-btn-primary {
        border: 1px solid #b91c1c;
        background: #dc2626;
        color: white;
    }

    .article-btn-primary:hover {
        background: #b91c1c;
    }

    .article-btn-secondary {
        border: 1px solid #cbd5e1;
        background: white;
        color: #334155;
    }

    .article-btn-secondary:hover {
        background: #f8fafc;
    }

    #cover-preview {
        display: none;
        width: 100%;
        max-height: 250px;
        object-fit: cover;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        margin-top: 12px;
    }

    @media (max-width: 640px) {
        .article-card {
            padding: 16px;
        }

        .article-editor .ql-editor {
            min-height: 280px;
            padding: 14px;
        }

        .article-editor .ql-toolbar.ql-snow {
            padding: 7px;
        }
    }
</style>

<div class="article-form space-y-6">

    {{-- Validation summary --}}
    @if ($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4">
            <h3 class="font-bold text-red-800">
                Please correct the following errors:
            </h3>

            <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-red-700">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Header --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">
                {{ $isEditing ? 'Edit Article' : 'Create Article' }}
            </h1>

            <p class="mt-1 text-sm text-slate-500">
                Write, format and publish your news article.
            </p>
        </div>

        <span class="inline-flex w-fit items-center rounded-full bg-red-50 px-3 py-1.5 text-xs font-bold text-red-700">
            <span class="mr-2 h-2 w-2 rounded-full bg-red-500"></span>
            Article Editor
        </span>
    </div>

    <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-3">

        {{-- Main content --}}
        <div class="space-y-6 lg:col-span-2">

            {{-- Article information --}}
            <section class="article-card">
                <div class="mb-5">
                    <h2 class="text-lg font-bold text-slate-900">
                        Article Information
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Add the headline, URL slug and short introduction.
                    </p>
                </div>

                <div class="space-y-5">

                    <div>
                        <label for="title" class="article-label">
                            Article Title <span class="text-red-600">*</span>
                        </label>

                        <input
                            id="title"
                            name="title"
                            type="text"
                            required
                            maxlength="200"
                            value="{{ old('title', $article->title) }}"
                            class="article-input"
                            placeholder="Enter a clear and engaging headline"
                        >

                        @error('title')
                            <p class="article-input-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="slug" class="article-label">
                            URL Slug
                        </label>

                        <input
                            id="slug"
                            name="slug"
                            type="text"
                            maxlength="220"
                            value="{{ old('slug', $article->slug) }}"
                            class="article-input"
                            placeholder="article-url-slug"
                        >

                        <p class="article-help">
                            Leave blank if your controller automatically generates a slug.
                        </p>

                        @error('slug')
                            <p class="article-input-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="excerpt" class="article-label">
                            Short Description / Excerpt
                        </label>

                        <textarea
                            id="excerpt"
                            name="excerpt"
                            rows="3"
                            maxlength="500"
                            class="article-textarea"
                            placeholder="Write a short summary for article cards and search previews..."
                        >{{ old('excerpt', $article->excerpt) }}</textarea>

                        <p class="article-help">
                            Keep it concise and explain what the article is about.
                        </p>

                        @error('excerpt')
                            <p class="article-input-error">{{ $message }}</p>
                        @enderror
                    </div>

                </div>
            </section>

            {{-- Rich text description --}}
            <section class="article-card">
                <div class="mb-5">
                    <h2 class="text-lg font-bold text-slate-900">
                        Article Description
                        <span class="text-red-600">*</span>
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Format your story with headings, bold text, colors, links and lists.
                    </p>
                </div>

                <div class="article-editor">
                    <div id="article-editor"></div>
                </div>

                {{-- The editor's HTML is submitted through this field. --}}
                <textarea
                    id="body"
                    name="body"
                    class="hidden"
                    required
                    aria-label="Article body"
                >{{ old('body', $article->body) }}</textarea>

                @error('body')
                    <p class="article-input-error">{{ $message }}</p>
                @enderror

                <div class="mt-3 flex flex-wrap items-center justify-between gap-2">
                    <p class="text-xs text-slate-500">
                        Select text and use the toolbar to format it.
                    </p>

                    <p class="text-xs text-slate-500">
                        <strong id="word-count">0</strong> words
                    </p>
                </div>

                <div class="mt-4 rounded-lg bg-slate-50 p-3 text-xs leading-6 text-slate-600">
                    <strong class="text-slate-800">Formatting available:</strong>
                    headings, bold, italic, underline, text color, highlight,
                    hyperlinks, ordered lists, bullet lists, alignment and blockquotes.
                </div>
            </section>

        </div>

        {{-- Sidebar --}}
        <aside class="space-y-6">

            {{-- Publishing controls --}}
            <section class="article-card">
                <div class="mb-5">
                    <h2 class="text-lg font-bold text-slate-900">
                        Publishing
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Set the article status and publication time.
                    </p>
                </div>

                <div class="space-y-5">

                    <div>
                        <label for="status" class="article-label">
                            Article Status
                        </label>

                        <select
                            id="status"
                            name="status"
                            required
                            class="article-select"
                        >
                            <option
                                value="draft"
                                @selected(old('status', $article->status ?? 'draft') === 'draft')
                            >
                                Draft
                            </option>

                            <option
                                value="published"
                                @selected(old('status', $article->status ?? 'draft') === 'published')
                            >
                                Published
                            </option>

                            <option
                                value="scheduled"
                                @selected(old('status', $article->status ?? 'draft') === 'scheduled')
                            >
                                Scheduled
                            </option>
                        </select>

                        @error('status')
                            <p class="article-input-error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Publish immediately --}}
                    <div class="publish-now-panel">
                        <label for="publish_now" class="flex cursor-pointer items-start gap-3">
                            <input
                                id="publish_now"
                                name="publish_now"
                                type="checkbox"
                                value="1"
                                class="mt-1 h-4 w-4 shrink-0 accent-red-600"
                                @checked(old('publish_now', false))
                            >

                            <span>
                                <span class="block text-sm font-extrabold text-red-800">
                                    Publish Now
                                </span>

                                <span class="mt-1 block text-xs leading-5 text-red-700">
                                    Publish this article immediately using the current server time.
                                </span>
                            </span>
                        </label>
                    </div>

                    <div>
                        <label for="published_date" class="article-label">
                            Publication Date
                        </label>

                        <input
                            id="published_date"
                            name="published_date"
                            type="date"
                            value="{{ $publishedDate }}"
                            class="article-input"
                        >

                        @error('published_date')
                            <p class="article-input-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="published_time" class="article-label">
                            Publication Time
                        </label>

                        <input
                            id="published_time"
                            name="published_time"
                            type="time"
                            value="{{ $publishedTime }}"
                            class="article-input"
                        >

                        @error('published_time')
                            <p class="article-input-error">{{ $message }}</p>
                        @enderror

                        <p class="article-help">
                            Used when Publish Now is not selected.
                        </p>
                    </div>

                    <div id="publish-now-notice" class="hidden rounded-lg bg-red-50 p-3 text-xs leading-5 text-red-700">
                        Publish Now is enabled. The controller must set the status to published and use the current server time.
                    </div>

                </div>
            </section>

            {{-- Category --}}
            <section class="article-card">
                <div class="mb-4">
                    <h2 class="text-lg font-bold text-slate-900">
                        Category
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Choose the article category.
                    </p>
                </div>

                <label for="category_id" class="article-label">
                    Article Category
                </label>

                <select
                    id="category_id"
                    name="category_id"
                    class="article-select"
                >
                    <option value="">Select category</option>

                    @foreach (($categories ?? collect()) as $category)
                        <option
                            value="{{ $category->id }}"
                            @selected((string) old('category_id', $article->category_id) === (string) $category->id)
                        >
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>

                @error('category_id')
                    <p class="article-input-error">{{ $message }}</p>
                @enderror
            </section>

            {{-- Tags --}}
            <section class="article-card">
                <div class="mb-4">
                    <h2 class="text-lg font-bold text-slate-900">
                        Tags
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Select relevant tags for your article.
                    </p>
                </div>

                @if (isset($tags) && $tags->count())
                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-1">
                        @foreach ($tags as $tag)
                            <label class="article-tag">
                                <input
                                    type="checkbox"
                                    name="tags[]"
                                    value="{{ $tag->id }}"
                                    @checked(in_array((string) $tag->id, $selectedTags, true))
                                >

                                <span>{{ $tag->name }}</span>
                            </label>
                        @endforeach
                    </div>
                @else
                    <p class="rounded-lg bg-slate-50 p-3 text-sm text-slate-500">
                        No tags available. Add tags first if your application supports them.
                    </p>
                @endif

                @error('tags')
                    <p class="article-input-error">{{ $message }}</p>
                @enderror

                @error('tags.*')
                    <p class="article-input-error">{{ $message }}</p>
                @enderror
            </section>

            {{-- Cover image --}}
            <section class="article-card">
                <div class="mb-4">
                    <h2 class="text-lg font-bold text-slate-900">
                        Cover Image
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Upload the main image for your article.
                    </p>
                </div>

                <label for="cover_image" class="article-label">
                    Choose Image
                </label>

                <input
                    id="cover_image"
                    name="cover_image"
                    type="file"
                    accept="image/jpeg,image/png,image/webp,image/gif"
                    class="article-input"
                >

                <p class="article-help">
                    Supported formats: JPG, PNG, WEBP and GIF. Maximum 4 MB.
                </p>

                @error('cover_image')
                    <p class="article-input-error">{{ $message }}</p>
                @enderror

                <img
                    id="cover-preview"
                    src=""
                    alt="Selected cover image preview"
                >

                @if ($article->exists && $article->cover_image)
                    <div id="current-cover-wrapper" class="mt-4">
                        <p class="mb-2 text-xs font-semibold text-slate-600">
                            Current cover image
                        </p>

                        <img
                            src="{{ \Illuminate\Support\Facades\Storage::url($article->cover_image) }}"
                            alt="Current article cover"
                            class="w-full rounded-lg border border-slate-200 object-cover"
                            style="max-height: 220px"
                        >
                    </div>
                @endif
            </section>

        </aside>
    </div>

    {{-- Form actions --}}
    <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:items-center sm:justify-between">
        <a
            href="{{ route('admin.articles.index') }}"
            class="article-btn article-btn-secondary"
        >
            Cancel
        </a>

        <button
            id="article-submit-button"
            type="submit"
            class="article-btn article-btn-primary"
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                width="17"
                height="17"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
                aria-hidden="true"
            >
                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                <polyline points="17 21 17 13 7 13 7 21"/>
                <polyline points="7 3 7 8 15 8"/>
            </svg>

            <span id="submit-button-text">
                {{ $isEditing ? 'Save Changes' : 'Create Article' }}
            </span>
        </button>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const editorElement = document.getElementById('article-editor');
    const bodyField = document.getElementById('body');
    const form = editorElement ? editorElement.closest('form') : null;

    if (!editorElement || !bodyField || typeof Quill === 'undefined') {
        console.error('Article editor could not initialize. Check that Quill loaded correctly.');
        return;
    }

    const toolbarOptions = [
        [{ header: [1, 2, 3, false] }],
        ['bold', 'italic', 'underline', 'strike'],
        [{ color: [] }, { background: [] }],
        [{ font: [] }, { size: ['small', false, 'large', 'huge'] }],
        [{ align: [] }],
        [{ list: 'ordered' }, { list: 'bullet' }],
        ['blockquote'],
        ['link'],
        ['clean']
    ];

    const quill = new Quill('#article-editor', {
        theme: 'snow',
        placeholder: 'Start writing your article here...',
        modules: {
            toolbar: toolbarOptions
        }
    });

    /*
     * Load existing article HTML into the editor.
     * Content must still be sanitized on the server before public rendering.
     */
    const initialContent = bodyField.value.trim();

    if (initialContent) {
        quill.clipboard.dangerouslyPasteHTML(initialContent);
    }

    const wordCountElement = document.getElementById('word-count');
    const publishNowCheckbox = document.getElementById('publish_now');
    const publishDateField = document.getElementById('published_date');
    const publishTimeField = document.getElementById('published_time');
    const publishNotice = document.getElementById('publish-now-notice');
    const submitButtonText = document.getElementById('submit-button-text');
    const coverInput = document.getElementById('cover_image');
    const coverPreview = document.getElementById('cover-preview');

    const defaultButtonText = @json($isEditing ? 'Save Changes' : 'Create Article');

    function syncEditorContent() {
        bodyField.value = quill.root.innerHTML;

        const plainText = quill.getText().trim();
        const words = plainText.length
            ? plainText.split(/\s+/).filter(Boolean).length
            : 0;

        if (wordCountElement) {
            wordCountElement.textContent = words;
        }
    }

    function updatePublishNowUI() {
        const publishImmediately = Boolean(
            publishNowCheckbox && publishNowCheckbox.checked
        );

        /*
         * Keep status enabled because Laravel validation needs it.
         * The controller should override status to "published"
         * whenever publish_now is true.
         */
        if (publishDateField) {
            publishDateField.disabled = publishImmediately;
            publishDateField.classList.toggle('opacity-50', publishImmediately);
        }

        if (publishTimeField) {
            publishTimeField.disabled = publishImmediately;
            publishTimeField.classList.toggle('opacity-50', publishImmediately);
        }

        if (publishNotice) {
            publishNotice.classList.toggle('hidden', !publishImmediately);
        }

        if (submitButtonText) {
            submitButtonText.textContent = publishImmediately
                ? 'Publish Now'
                : defaultButtonText;
        }
    }

    quill.on('text-change', syncEditorContent);

    if (publishNowCheckbox) {
        publishNowCheckbox.addEventListener('change', updatePublishNowUI);
    }

    if (coverInput && coverPreview) {
        coverInput.addEventListener('change', function () {
            const file = coverInput.files && coverInput.files[0];

            if (!file) {
                coverPreview.removeAttribute('src');
                coverPreview.style.display = 'none';
                return;
            }

            const allowedTypes = [
                'image/jpeg',
                'image/png',
                'image/webp',
                'image/gif'
            ];

            if (!allowedTypes.includes(file.type)) {
                alert('Please choose a JPG, PNG, WEBP or GIF image.');
                coverInput.value = '';
                coverPreview.style.display = 'none';
                return;
            }

            if (file.size > 4 * 1024 * 1024) {
                alert('The cover image must not exceed 4 MB.');
                coverInput.value = '';
                coverPreview.style.display = 'none';
                return;
            }

            const reader = new FileReader();

            reader.onload = function (event) {
                coverPreview.src = event.target.result;
                coverPreview.style.display = 'block';
            };

            reader.readAsDataURL(file);
        });
    }

    if (form) {
        form.addEventListener('submit', function (event) {
            syncEditorContent();

            const articleText = quill.getText().trim();

            if (!articleText) {
                event.preventDefault();
                alert('Please enter the article description.');
                quill.focus();
                return;
            }

            const submitButton = document.getElementById('article-submit-button');

            if (submitButton) {
                submitButton.disabled = true;
                submitButton.classList.add('opacity-60', 'cursor-not-allowed');
            }
        });
    }

    syncEditorContent();
    updatePublishNowUI();
});
</script>
