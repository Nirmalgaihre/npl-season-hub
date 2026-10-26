@extends('layouts.admin')
@section('title', 'Categories | NPL Season Hub')

@section('content')
<div class="mx-auto max-w-6xl px-4 py-8 sm:px-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="text-xs font-bold uppercase tracking-widest text-red-700">Content settings</p>
            <h1 class="mt-2 text-3xl font-black">Categories</h1>
            <p class="mt-2 text-sm text-slate-600">Organize articles into useful news topics.</p>
        </div>
        <a href="{{ route('admin.articles.index') }}" class="text-sm font-semibold text-red-700">← Articles</a>
    </div>

    @if (session('success'))
        <div class="my-5 rounded-lg bg-green-50 p-4 text-sm text-green-800">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="my-5 rounded-lg bg-red-50 p-4 text-sm text-red-800">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('admin.categories.store') }}" class="my-7 grid gap-4 rounded-xl border border-slate-200 bg-white p-5 sm:grid-cols-2">
        @csrf
        <h2 class="text-lg font-bold sm:col-span-2">Add a category</h2>
        <div>
            <label for="name" class="mb-2 block text-sm font-semibold">Name *</label>
            <input id="name" name="name" required maxlength="100" value="{{ old('name') }}" placeholder="e.g. Match Reports" class="w-full rounded-lg border border-slate-300 px-3 py-2.5">
        </div>
        <div>
            <label for="slug" class="mb-2 block text-sm font-semibold">Slug (optional)</label>
            <input id="slug" name="slug" maxlength="120" value="{{ old('slug') }}" placeholder="match-reports" class="w-full rounded-lg border border-slate-300 px-3 py-2.5">
        </div>
        <div class="sm:col-span-2">
            <label for="description" class="mb-2 block text-sm font-semibold">Description</label>
            <textarea id="description" name="description" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2.5">{{ old('description') }}</textarea>
        </div>
        <div><button class="rounded-lg bg-red-700 px-5 py-2.5 font-semibold text-white">Add category</button></div>
    </form>

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
        <table class="w-full min-w-[600px] text-left text-sm">
            <thead class="bg-slate-100 text-xs uppercase text-slate-500">
                <tr><th class="px-4 py-3">Category</th><th class="px-4 py-3">Articles</th><th class="px-4 py-3">Edit</th><th class="px-4 py-3">Delete</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($categories as $category)
                    <tr>
                        <td class="px-4 py-4">
                            <p class="font-bold">{{ $category->name }}</p>
                            <p class="text-xs text-slate-500">{{ $category->slug }}</p>
                        </td>
                        <td class="px-4 py-4">{{ $category->articles_count }}</td>
                        <td class="px-4 py-4">
                            <details>
                                <summary class="cursor-pointer font-semibold text-red-700">Edit</summary>
                                <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="mt-3 min-w-64 space-y-2">
                                    @csrf @method('PUT')
                                    <input name="name" required maxlength="100" value="{{ $category->name }}" class="w-full rounded border border-slate-300 p-2">
                                    <input name="slug" required maxlength="120" value="{{ $category->slug }}" class="w-full rounded border border-slate-300 p-2">
                                    <textarea name="description" class="w-full rounded border border-slate-300 p-2" placeholder="Description">{{ $category->description }}</textarea>
                                    <button class="rounded bg-slate-900 px-3 py-2 text-white">Save</button>
                                </form>
                            </details>
                        </td>
                        <td class="px-4 py-4">
                            <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" onsubmit="return confirm('Delete this category?')">
                                @csrf @method('DELETE')
                                <button class="font-semibold text-red-700" @disabled($category->articles_count > 0) title="{{ $category->articles_count > 0 ? 'Reassign articles first' : 'Delete category' }}">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-12 text-center text-slate-500">No categories yet. Add your first category above.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4">{{ $categories->links() }}</div>
    </div>
</div>
@endsection