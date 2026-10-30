
@extends('layouts.app')

@section('title', $article->title . ' | NPL Season Hub')

@section('meta_description', $article->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($article->body), 155))

@section('content')

@php
    use Illuminate\Support\Str;

    $publishedAt = $article->published_at;
    $authorName = $article->author?->name ?? 'NPL Season Hub';

    $coverImage = $article->cover_image
        ? asset('storage/' . $article->cover_image)
        : null;
@endphp

<div class="min-h-screen bg-[#f5f7fb] text-slate-900">

    {{-- News breadcrumb --}}
    <div class="border-b border-slate-200 bg-white">
        <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
            <nav aria-label="Breadcrumb"
                 class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
                <a href="{{ route('home') }}"
                   class="transition hover:text-red-700">
                    Home
                </a>

                <span>/</span>

                <a href="{{ route('news.index') }}"
                   class="transition hover:text-red-700">
                    News
                </a>

                @if ($article->category)
                    <span>/</span>
                    <a href="{{ route('news.category', $article->category) }}"
                       class="font-medium transition hover:text-red-700">
                        {{ $article->category->name }}
                    </a>
                @endif
            </nav>
        </div>
    </div>

    <main class="mx-auto max-w-7xl px-4 py-7 sm:px-6 sm:py-10 lg:px-8">

        <div class="grid items-start gap-8 lg:grid-cols-[minmax(0,1fr)_280px]">

            {{-- Main article --}}
            <article class="min-w-0 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                {{-- Article heading --}}
                <header class="px-5 pb-6 pt-7 sm:px-8 sm:pb-8 sm:pt-9 lg:px-10">

                    @if ($article->category)
                        <a href="{{ route('news.category', $article->category) }}"
                           class="inline-flex items-center gap-2 rounded-full bg-red-50 px-3 py-1.5 text-xs font-extrabold uppercase tracking-wider text-red-700 transition hover:bg-red-100">
                            <span class="h-1.5 w-1.5 rounded-full bg-red-600"></span>
                            {{ $article->category->name }}
                        </a>
                    @endif

                    <h1 class="mt-5 text-3xl font-black leading-tight tracking-tight text-slate-950 sm:text-4xl lg:text-[2.7rem]">
                        {{ $article->title }}
                    </h1>

                    @if ($article->excerpt)
                        <p class="mt-5 text-base leading-8 text-slate-600 sm:text-lg">
                            {{ $article->excerpt }}
                        </p>
                    @endif

                    {{-- Author and publication details --}}
                    <div class="mt-7 flex flex-wrap items-center gap-x-5 gap-y-3 border-t border-slate-100 pt-5">

                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#202766] text-sm font-black text-white">
                                {{ Str::upper(Str::substr($authorName, 0, 1)) }}
                            </div>

                            <div>
                                <p class="text-sm font-bold text-slate-900">
                                    {{ $authorName }}
                                </p>
                                <p class="mt-0.5 text-xs text-slate-500">
                                    NPL Season Hub
                                </p>
                            </div>
                        </div>

                        @if ($publishedAt)
                            <div class="text-sm text-slate-500">
                                <time datetime="{{ $publishedAt->toIso8601String() }}">
                                    {{ $publishedAt->format('d F Y') }}
                                </time>

                                <span class="mx-1.5 text-slate-300">·</span>

                                <span>
                                    {{ $publishedAt->format('g:i A') }}
                                </span>
                            </div>
                        @endif
                    </div>
                </header>

                {{-- Cover image --}}
                @if ($coverImage)
                    <figure class="px-4 sm:px-8 lg:px-10">
                        <img
                            src="{{ $coverImage }}"
                            alt="{{ $article->title }}"
                            fetchpriority="high"
                            class="max-h-[560px] w-full rounded-xl bg-slate-100 object-cover"
                        >
                    </figure>
                @endif

                {{-- Article body --}}
                <div class="px-5 pb-8 pt-7 sm:px-8 sm:pb-10 sm:pt-9 lg:px-10">

                    {{-- Article content styles for HTML saved by Quill --}}
                    <style>
                        .npl-article-content {
                            color: #334155;
                            font-size: 17px;
                            line-height: 1.95;
                            overflow-wrap: anywhere;
                        }

                        .npl-article-content p {
                            margin: 0 0 1.45rem;
                        }

                        .npl-article-content p:last-child {
                            margin-bottom: 0;
                        }

                        .npl-article-content .ql-align-center {
                            text-align: center;
                        }

                        .npl-article-content .ql-align-right {
                            text-align: right;
                        }

                        .npl-article-content .ql-align-justify {
                            text-align: justify;
                        }

                        .npl-article-content h1,
                        .npl-article-content h2,
                        .npl-article-content h3 {
                            color: #0f172a;
                            font-weight: 850;
                            line-height: 1.3;
                            letter-spacing: -0.025em;
                            margin: 2.2rem 0 1rem;
                        }

                        .npl-article-content h1 {
                            font-size: 2rem;
                        }

                        .npl-article-content h2 {
                            font-size: 1.65rem;
                        }

                        .npl-article-content h3 {
                            font-size: 1.3rem;
                        }

                        .npl-article-content strong,
                        .npl-article-content b {
                            color: #172554;
                            font-weight: 800;
                        }

                        .npl-article-content a {
                            color: #b91c1c;
                            font-weight: 650;
                            text-decoration: underline;
                            text-underline-offset: 3px;
                        }

                        .npl-article-content ul,
                        .npl-article-content ol {
                            margin: 1.25rem 0 1.75rem;
                            padding-left: 1.75rem;
                        }

                        .npl-article-content ul {
                            list-style: disc;
                        }

                        .npl-article-content ol {
                            list-style: decimal;
                        }

                        .npl-article-content li {
                            margin: 0.45rem 0;
                            padding-left: 0.25rem;
                        }

                        .npl-article-content blockquote {
                            margin: 1.75rem 0;
                            border-left: 4px solid #dc2626;
                            border-radius: 0 10px 10px 0;
                            background: #f8fafc;
                            padding: 1.1rem 1.25rem;
                            color: #475569;
                            font-size: 1.05rem;
                            font-style: italic;
                        }

                        .npl-article-content img {
                            display: block;
                            max-width: 100%;
                            height: auto;
                            margin: 1.75rem auto;
                            border-radius: 12px;
                        }

                        .npl-article-content iframe,
                        .npl-article-content video {
                            max-width: 100%;
                        }

                        .npl-article-content pre {
                            overflow-x: auto;
                            border-radius: 10px;
                            background: #0f172a;
                            padding: 1rem;
                            color: #f8fafc;
                        }

                        .npl-article-content hr {
                            margin: 2rem 0;
                            border-color: #e2e8f0;
                        }

                        @media (max-width: 640px) {
                            .npl-article-content {
                                font-size: 16px;
                                line-height: 1.85;
                            }

                            .npl-article-content h1 {
                                font-size: 1.65rem;
                            }

                            .npl-article-content h2 {
                                font-size: 1.4rem;
                            }

                            .npl-article-content h3 {
                                font-size: 1.2rem;
                            }
                        }
                    </style>

                    <div class="npl-article-content">
                        {{-- Only render trusted, sanitized HTML here. --}}
                        {!! $article->body !!}
                    </div>

                    {{-- Article tags --}}
                    @if ($article->tags->isNotEmpty())
                        <div class="mt-9 border-t border-slate-100 pt-6">
                            <p class="mb-3 text-xs font-extrabold uppercase tracking-widest text-slate-500">
                                Topics
                            </p>

                            <div class="flex flex-wrap gap-2">
                                @foreach ($article->tags as $tag)
                                    <a
                                        href="{{ route('news.tag', $tag) }}"
                                        class="inline-flex rounded-full border border-slate-200 bg-slate-50 px-3.5 py-2 text-sm font-semibold text-slate-600 transition hover:border-red-200 hover:bg-red-50 hover:text-red-700"
                                    >
                                        #{{ $tag->name }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Back to news --}}
                    <div class="mt-9 border-t border-slate-100 pt-6">
                        <a
                            href="{{ route('news.index') }}"
                            class="inline-flex items-center gap-2 rounded-lg bg-[#202766] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#171d50]"
                        >
                            <span aria-hidden="true">←</span>
                            Back to all news
                        </a>
                    </div>
                </div>
            </article>

            {{-- Sidebar --}}
            <aside class="space-y-6">

                {{-- News brand panel --}}
                <section class="overflow-hidden rounded-2xl bg-[#202766] p-6 text-white shadow-sm">
                    <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-red-300">
                        NPL Season Hub
                    </p>

                    <h2 class="mt-3 text-2xl font-black leading-tight">
                        Every match.
                        <span class="text-red-300">Every story.</span>
                    </h2>

                    <p class="mt-3 text-sm leading-6 text-slate-200">
                        Follow the latest Nepal Premier League news, match updates and cricket stories.
                    </p>

                    <a
                        href="{{ route('news.index') }}"
                        class="mt-5 inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2.5 text-sm font-extrabold text-[#202766] transition hover:bg-red-50"
                    >
                        Explore news <span aria-hidden="true">→</span>
                    </a>
                </section>

                {{-- Related articles --}}
                @if (isset($related) && $related->isNotEmpty())
                    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                        <div class="border-b border-slate-100 px-5 py-4">
                            <p class="text-xs font-extrabold uppercase tracking-widest text-red-700">
                                Keep reading
                            </p>
                            <h2 class="mt-1 text-xl font-black text-slate-950">
                                Related stories
                            </h2>
                        </div>

                        <div class="divide-y divide-slate-100">
                            @foreach ($related as $item)
                                <a
                                    href="{{ route('news.show', $item) }}"
                                    class="group block p-5 transition hover:bg-slate-50"
                                >
                                    @if ($item->cover_image)
                                        <img
                                            src="{{ asset('storage/' . $item->cover_image) }}"
                                            alt="{{ $item->title }}"
                                            loading="lazy"
                                            class="mb-3 aspect-[16/9] w-full rounded-lg bg-slate-100 object-cover"
                                        >
                                    @endif

                                    @if ($item->category)
                                        <p class="text-[10px] font-extrabold uppercase tracking-widest text-red-700">
                                            {{ $item->category->name }}
                                        </p>
                                    @endif

                                    <h3 class="mt-1.5 text-sm font-bold leading-6 text-slate-800 transition group-hover:text-red-700">
                                        {{ $item->title }}
                                    </h3>

                                    @if ($item->published_at)
                                        <p class="mt-2 text-xs text-slate-500">
                                            {{ $item->published_at->format('d M Y') }}
                                        </p>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif

            </aside>
        </div>
    </main>
</div>

@endsection
