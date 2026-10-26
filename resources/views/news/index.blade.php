
@extends('layouts.app')

@section('title', ($heading ?? 'Latest Cricket News') . ' | NPL Season Hub')

@section('content')
<div class="min-h-screen bg-white">
    <section class="border-b border-slate-200 bg-slate-50">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:py-16">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-red-700">Nepal Premier League</p>
            <h1 class="mt-3 text-4xl font-black tracking-tight text-slate-950 sm:text-5xl">{{ $heading ?? 'Latest Cricket News' }}</h1>
            <p class="mt-4 max-w-2xl text-base leading-7 text-slate-600">News, match reports, team updates, and stories from the NPL cricket season.</p>

            <form action="{{ route('news.index') }}" method="GET" class="mt-7 flex max-w-xl gap-2">
                <input name="search" value="{{ request('search') }}" placeholder="Search cricket news..." class="min-w-0 flex-1 rounded-lg border border-slate-300 bg-white px-4 py-3">
                <button class="rounded-lg bg-red-700 px-5 font-semibold text-white hover:bg-red-800">Search</button>
            </form>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6">
        @if ($articles->count())
            <div class="grid grid-cols-1 gap-x-7 gap-y-10 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($articles as $article)
                    <article class="group overflow-hidden rounded-xl border border-slate-200 bg-white transition hover:-translate-y-1 hover:shadow-lg">
                        <a href="{{ route('news.show', $article) }}" class="block">
                            @if ($article->cover_image)
                                <img src="{{ asset('storage/'.$article->cover_image) }}" alt="{{ $article->title }}" loading="lazy" class="aspect-[16/10] w-full object-cover">
                            @else
                                <div class="flex aspect-[16/10] items-center justify-center bg-slate-100 text-4xl text-red-700">
                                    <span aria-hidden="true">🏏</span>
                                </div>
                            @endif
                        </a>

                        <div class="p-5">
                            @if ($article->category)
                                <a href="{{ route('news.category', $article->category) }}" class="text-xs font-bold uppercase tracking-wider text-red-700">{{ $article->category->name }}</a>
                            @endif
                            <h2 class="mt-3 text-xl font-bold leading-snug text-slate-950 group-hover:text-red-700">
                                <a href="{{ route('news.show', $article) }}">{{ $article->title }}</a>
                            </h2>
                            @if ($article->excerpt)
                                <p class="mt-3 line-clamp-3 text-sm leading-6 text-slate-600">{{ $article->excerpt }}</p>
                            @endif
                            <div class="mt-5 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-slate-500">
                                <span>{{ $article->published_at?->format('d M Y') }}</span>
                                <span>·</span>
                                <span>{{ $article->author?->name ?? 'NPL Season Hub' }}</span>
                            </div>
                            @if ($article->tags->isNotEmpty())
                                <div class="mt-4 flex flex-wrap gap-2">
                                    @foreach ($article->tags as $tag)
                                        <a href="{{ route('news.tag', $tag) }}" class="rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-600 hover:bg-red-50 hover:text-red-700">#{{ $tag->name }}</a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
            <div class="mt-10">{{ $articles->withQueryString()->links() }}</div>
        @else
            <div class="rounded-xl border border-dashed border-slate-300 px-5 py-16 text-center">
                <p class="text-lg font-bold text-slate-900">No published articles found</p>
                <p class="mt-2 text-sm text-slate-500">Try a different search, or check back when new news is published.</p>
            </div>
        @endif
    </section>
</div>
@endsection
