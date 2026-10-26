@extends('layouts.app')

@section('title', $article->title . ' | NPL Season Hub')

@section('content')
<article class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:py-12">
    <a href="{{ route('news.index') }}" class="text-sm font-semibold text-red-700">← All news</a>

    <header class="mt-7">
        @if ($article->category)
            <a href="{{ route('news.category', $article->category) }}" class="text-xs font-bold uppercase tracking-widest text-red-700">{{ $article->category->name }}</a>
        @endif
        <h1 class="mt-3 text-3xl font-black leading-tight tracking-tight text-slate-950 sm:text-5xl">{{ $article->title }}</h1>
        @if ($article->excerpt)
            <p class="mt-5 text-lg leading-8 text-slate-600">{{ $article->excerpt }}</p>
        @endif
        <div class="mt-5 flex flex-wrap gap-2 text-sm text-slate-500">
            <span>By {{ $article->author?->name ?? 'NPL Season Hub' }}</span>
            <span>·</span>
            <time datetime="{{ $article->published_at?->toIso8601String() }}">{{ $article->published_at?->format('d F Y, g:i A') }}</time>
        </div>
    </header>

    @if ($article->cover_image)
        <img src="{{ asset('storage/'.$article->cover_image) }}" alt="{{ $article->title }}" class="mt-8 max-h-[520px] w-full rounded-xl object-cover">
    @endif

    @if ($article->tags->isNotEmpty())
        <div class="mt-6 flex flex-wrap gap-2">
            @foreach ($article->tags as $tag)
                <a href="{{ route('news.tag', $tag) }}" class="rounded-full bg-slate-100 px-3 py-1.5 text-sm text-slate-700 hover:bg-red-50 hover:text-red-700">#{{ $tag->name }}</a>
            @endforeach
        </div>
    @endif

    <div class="mt-8 whitespace-pre-line break-words text-base leading-8 text-slate-800 sm:text-lg">{{ $article->body }}</div>

    @if ($related->isNotEmpty())
        <section class="mt-14 border-t border-slate-200 pt-8">
            <h2 class="text-2xl font-black text-slate-950">Related articles</h2>
            <div class="mt-5 grid gap-4 sm:grid-cols-3">
                @foreach ($related as $item)
                    <a href="{{ route('news.show', $item) }}" class="rounded-lg border border-slate-200 p-4 hover:border-red-300">
                        <p class="font-bold leading-snug">{{ $item->title }}</p>
                        <p class="mt-2 text-xs text-slate-500">{{ $item->published_at?->format('d M Y') }}</p>
                    </a>
                @endforeach
            </div>
        </section>
    @endif
</article>
@endsection