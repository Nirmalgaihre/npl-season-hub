@extends('layouts.admin')

@section('title', 'Tags | NPL Season Hub')

@section('content')
<div class="mx-auto max-w-6xl px-4 py-8 sm:px-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="text-xs font-bold uppercase tracking-widest text-red-700">Content settings</p>
            <h1 class="mt-2 text-3xl font-black">Tags</h1>
            <p class="mt-2 text-sm text-slate-600">Use tags to connect related articles.</p>
        </div>
        <a href="{{ route('admin.articles.index') }}" class="text-sm font-semibold text-red-700">← Articles</a>
    </div>

    @if (session('success'))
        <div class="my-5 rounded-lg bg-green-50 p-4 text-sm text-green-800">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="my-5 rounded-lg bg-red-50 p-4 text-sm text-red-800">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('admin.tags.store') }}" class="my-7 grid gap-4 rounded-xl border border-slate-200 bg-white p-5 sm:grid-cols-2">
        @csrf
        <h2 class="text-lg font-bold sm:col-span-2">Add a tag</h2>
        <div>
            <label for="name" class="mb-2 block text-sm font-semibold">Name *</label>
            <input id="name" name="name" required maxlength="80" value="{{ old('name') }}" placeholder="e.g. Kathmandu Kings" class="w-full rounded-lg border border-slate-300 px-3 py-2.5">
        </div>
        <div>
            <label for="slug" class="mb-2 block text-sm font-semibold">Slug (optional)</label>
            <input id="slug" name="slug" maxlength="100" value="{{ old('slug') }}" placeholder="kathmandu-kings" class="w-full rounded-lg border border-slate-300 px-3 py-2.5">
        </div>
        <div><button class="rounded-lg bg-red-700 px-5 py-2.5 font-semibold text-white">Add tag</button></div>
    </form>

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
        <table class="w-full min-w-[480px] text-left text-sm">
            <thead class="bg-slate-100 text-xs uppercase text-slate-500">
                <tr><th class="px-4 py-3">Tag</th><th class="px-4 py-3">Articles</th><th class="px-4 py-3">Action</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($tags as $tag)
                    <tr>
                        <td class="px-4 py-4">
                            <p class="font-bold">{{ $tag->name }}</p>
                            <p class="text-xs text-slate-500">{{ $tag->slug }}</p>
                        </td>
                        <td class="px-4 py-4">{{ $tag->articles_count }}</td>
                        <td class="px-4 py-4">
                            <form method="POST" action="{{ route('admin.tags.destroy', $tag) }}" onsubmit="return confirm('Delete this tag? It will be removed from associated articles.')">
                                @csrf @method('DELETE')
                                <button class="font-semibold text-red-700">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-4 py-12 text-center text-slate-500">No tags yet. Add your first tag above.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4">{{ $tags->links() }}</div>
    </div>
</div>
@endsection