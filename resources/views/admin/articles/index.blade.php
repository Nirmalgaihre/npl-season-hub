
@extends('layouts.admin')
@section('title', 'Articles | NPL Season Hub')

@section('content')
<div class="min-h-screen bg-slate-50 px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-red-700">Content management</p>
                <h1 class="mt-2 text-3xl font-black text-slate-900">News & Articles</h1>
                <p class="mt-2 text-sm text-slate-600">Manage your NPL news coverage and match reports.</p>
            </div>
            <a href="{{ route('admin.articles.create') }}" class="rounded-lg bg-red-700 px-5 py-3 font-semibold text-white hover:bg-red-800">+ Write article</a>
        </div>

        <div class="my-6 grid grid-cols-1 gap-3 sm:grid-cols-3">
            @foreach ([
                ['All articles', \App\Models\Article::count()],
                ['Published', \App\Models\Article::where('status', 'published')->count()],
                ['Drafts', \App\Models\Article::where('status', 'draft')->count()],
            ] as $stat)
                <div class="rounded-xl border border-slate-200 bg-white p-5">
                    <p class="text-sm text-slate-500">{{ $stat[0] }}</p>
                    <p class="mt-2 text-3xl font-black text-slate-900">{{ $stat[1] }}</p>
                </div>
            @endforeach
        </div>

        <form class="mb-5 flex flex-wrap gap-3 rounded-xl border border-slate-200 bg-white p-4" method="GET">
            <input name="search" value="{{ request('search') }}" placeholder="Search article title..." class="min-w-0 flex-1 rounded-lg border border-slate-300 px-3 py-2">
            <select name="status" class="rounded-lg border border-slate-300 px-3 py-2">
                <option value="">All statuses</option>
                <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                <option value="published" @selected(request('status') === 'published')>Published</option>
            </select>
            <button class="rounded-lg bg-slate-900 px-4 py-2 font-semibold text-white">Filter</button>
            <a href="{{ route('admin.articles.index') }}" class="rounded-lg border border-slate-300 px-4 py-2">Reset</a>
        </form>

        @if ($errors->any())
            <div class="mb-4 rounded-lg bg-red-50 p-4 text-sm text-red-800">{{ $errors->first() }}</div>
        @endif
        @if (session('success'))
            <div class="mb-4 rounded-lg bg-green-50 p-4 text-sm text-green-800">{{ session('success') }}</div>
        @endif

        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
            <table class="w-full min-w-[720px] text-left text-sm">
                <thead class="bg-slate-100 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-4">Article</th>
                        <th class="px-5 py-4">Category</th>
                        <th class="px-5 py-4">Status</th>
                        <th class="px-5 py-4">Updated</th>
                        <th class="px-5 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($articles as $article)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-4">
                                <p class="font-bold text-slate-900">{{ $article->title }}</p>
                                <p class="mt-1 text-xs text-slate-500">By {{ $article->author?->name ?? 'Former author' }}</p>
                            </td>
                            <td class="px-5 py-4 text-slate-600">{{ $article->category?->name ?? 'Uncategorized' }}</td>
                            <td class="px-5 py-4">
                                <span class="rounded-full px-3 py-1 text-xs font-bold {{ $article->status === 'published' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ ucfirst($article->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-slate-500">{{ $article->updated_at->format('d M Y') }}</td>
                            <td class="px-5 py-4 text-right">
                                <a class="font-semibold text-red-700 hover:underline" href="{{ route('admin.articles.edit', $article) }}">Edit</a>
                                <form class="ml-3 inline" method="POST" action="{{ route('admin.articles.destroy', $article) }}" onsubmit="return confirm('Delete this article permanently?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="font-semibold text-slate-600 hover:text-red-700">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-16 text-center">
                            <p class="text-lg font-bold text-slate-800">No articles yet</p>
                            <p class="mt-2 text-slate-500">Your real articles will appear here once you create them.</p>
                            <a href="{{ route('admin.articles.create') }}" class="mt-4 inline-block font-semibold text-red-700">Write your first article →</a>
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="border-t border-slate-200 p-4">{{ $articles->links() }}</div>
        </div>
    </div>
</div>
@endsection
