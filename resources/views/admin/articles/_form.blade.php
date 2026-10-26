
@csrf

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <div class="space-y-5 lg:col-span-2">
        <div>
            <label for="title" class="mb-2 block text-sm font-semibold">Article title *</label>
            <input id="title" name="title" required maxlength="200" value="{{ old('title', $article->title) }}" class="w-full rounded-lg border border-slate-300 px-4 py-3" placeholder="Enter a clear, descriptive headline">
            @error('title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="slug" class="mb-2 block text-sm font-semibold">URL slug (optional)</label>
            <input id="slug" name="slug" maxlength="220" value="{{ old('slug', $article->slug) }}" class="w-full rounded-lg border border-slate-300 px-4 py-3" placeholder="Leave blank to generate from the title">
            @error('slug') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="excerpt" class="mb-2 block text-sm font-semibold">Short summary</label>
            <textarea id="excerpt" name="excerpt" rows="3" maxlength="500" class="w-full rounded-lg border border-slate-300 px-4 py-3" placeholder="A short introduction shown on news cards">{{ old('excerpt', $article->excerpt) }}</textarea>
            @error('excerpt') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="body" class="mb-2 block text-sm font-semibold">Article content *</label>
            <textarea id="body" name="body" rows="18" required class="w-full rounded-lg border border-slate-300 px-4 py-3 leading-7" placeholder="Write the complete article here...">{{ old('body', $article->body) }}</textarea>
            <p class="mt-1 text-xs text-slate-500">Plain text is supported in this version. HTML is escaped on public pages.</p>
            @error('body') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>

    <aside class="space-y-5">
        <div class="rounded-xl border border-slate-200 p-4">
            <h2 class="mb-4 font-bold">Publication</h2>
            <label for="status" class="mb-2 block text-sm font-semibold">Status</label>
            <select id="status" name="status" class="w-full rounded-lg border border-slate-300 px-3 py-2.5">
                <option value="draft" @selected(old('status', $article->status ?: 'draft') === 'draft')>Draft</option>
                <option value="published" @selected(old('status', $article->status) === 'published')>Published</option>
            </select>
            @error('status') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror

            <label for="published_at" class="mb-2 mt-4 block text-sm font-semibold">Publish date/time</label>
            <input id="published_at" type="datetime-local" name="published_at" value="{{ old('published_at', $article->published_at?->format('Y-m-d\TH:i')) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2.5">
            @error('published_at') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            <p class="mt-2 text-xs text-slate-500">A future publication time keeps the article hidden until then.</p>
        </div>

        <div class="rounded-xl border border-slate-200 p-4">
            <label for="category_id" class="mb-2 block font-bold">Category</label>
            <select id="category_id" name="category_id" class="w-full rounded-lg border border-slate-300 px-3 py-2.5">
                <option value="">Uncategorized</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) old('category_id', $article->category_id) === (string) $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            @error('category_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="rounded-xl border border-slate-200 p-4">
            <p class="mb-3 font-bold">Tags</p>
            @forelse ($tags as $tag)
                <label class="mb-2 flex items-center gap-2 text-sm">
                    <input type="checkbox" name="tag_ids[]" value="{{ $tag->id }}" @checked(in_array($tag->id, old('tag_ids', $article->exists ? $article->tags->modelKeys() : []))) class="rounded border-slate-300 text-red-700">
                    {{ $tag->name }}
                </label>
            @empty
                <p class="text-sm text-slate-500">Create tags in Tag Management first.</p>
            @endforelse
            @error('tag_ids') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="rounded-xl border border-slate-200 p-4">
            <label for="cover_image" class="mb-2 block font-bold">Cover image</label>
            <input id="cover_image" type="file" name="cover_image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="w-full text-sm">
            <p class="mt-2 text-xs text-slate-500">JPG, PNG, or WebP. Maximum 4 MB.</p>
            @if ($article->cover_image)
                <img src="{{ asset('storage/'.$article->cover_image) }}" alt="Current article cover" class="mt-4 aspect-video w-full rounded-lg object-cover">
            @endif
            @error('cover_image') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
    </aside>
</div>

<div class="mt-7 flex flex-wrap gap-3 border-t border-slate-200 pt-5">
    <button class="rounded-lg bg-red-700 px-5 py-3 font-semibold text-white hover:bg-red-800">{{ $article->exists ? 'Save changes' : 'Create article' }}</button>
    <a href="{{ route('admin.articles.index') }}" class="rounded-lg border border-slate-300 px-5 py-3 font-semibold text-slate-700">Cancel</a>
</div>
