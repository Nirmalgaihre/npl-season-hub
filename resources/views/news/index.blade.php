
@extends('layouts.app')

@section('title', ($heading ?? 'Latest Cricket News') . ' | NPL Season Hub')

@section('content')
<div class="min-h-screen bg-slate-50">

    {{-- Hero --}}
    <section class="relative isolate overflow-hidden bg-slate-950 text-white">
        <div class="absolute inset-0 -z-10 overflow-hidden">
            <div class="absolute -right-20 -top-32 h-96 w-96 rounded-full bg-red-600/25 blur-3xl"></div>
            <div class="absolute -bottom-40 left-1/4 h-96 w-96 rounded-full bg-amber-400/15 blur-3xl"></div>
            <div class="absolute inset-0 opacity-[0.06]"
                 style="background-image:linear-gradient(45deg,white 1px,transparent 1px);background-size:35px 35px;"></div>
        </div>

        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 sm:py-16 lg:py-20">
            <div class="grid items-center gap-10 lg:grid-cols-[1fr_auto]">
                <div class="max-w-3xl">
                    <div class="inline-flex items-center gap-2 rounded-full border border-red-400/30 bg-red-500/10 px-4 py-2 text-xs font-bold uppercase tracking-[0.2em] text-red-300">
                        <span class="h-2 w-2 animate-pulse rounded-full bg-red-400"></span>
                        Nepal Premier League
                    </div>

                    <h1 class="mt-5 text-4xl font-black leading-tight tracking-tight sm:text-5xl lg:text-6xl">
                        {{ $heading ?? 'Latest Cricket News' }}
                    </h1>

                    <p class="mt-5 max-w-2xl text-base leading-7 text-slate-300 sm:text-lg">
                        Match reports, breaking updates, team news, player performances, and every big moment from the NPL season.
                    </p>

                    <form action="{{ route('news.index') }}" method="GET"
                          class="mt-8 flex max-w-2xl flex-col gap-3 rounded-2xl border border-white/10 bg-white/10 p-2 backdrop-blur-sm sm:flex-row">
                        <div class="flex min-w-0 flex-1 items-center gap-3 px-3">
                            <svg class="h-5 w-5 shrink-0 text-slate-300" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <circle cx="11" cy="11" r="7"></circle>
                                <path d="m20 20-4-4"></path>
                            </svg>
                            <input
                                type="search"
                                name="search"
                                value="{{ request('search') }}"
                                placeholder="Search teams, players or cricket news..."
                                aria-label="Search cricket news"
                                class="w-full border-0 bg-transparent py-3 text-sm text-white outline-none placeholder:text-slate-400 focus:ring-0"
                            >
                        </div>

                        <button type="submit"
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-600 px-6 py-3 text-sm font-bold text-white transition hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-300">
                            Search News
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="M5 12h14M12 5l7 7-7 7"></path>
                            </svg>
                        </button>
                    </form>

                    @if (request('search'))
                        <p class="mt-4 text-sm text-slate-300">
                            Results for:
                            <span class="font-semibold text-white">“{{ request('search') }}”</span>
                            <a href="{{ route('news.index') }}"
                               class="ml-2 font-semibold text-red-300 underline underline-offset-4 hover:text-white">
                                Clear search
                            </a>
                        </p>
                    @endif
                </div>

                {{-- Your exact NPL logo --}}
                <div class="hidden lg:flex lg:justify-center">
                    <div class="relative flex h-56 w-56 items-center justify-center rounded-full border border-white/10 bg-white/[0.04] p-5 shadow-2xl shadow-red-950/30">
                        <div class="absolute inset-3 rounded-full border border-dashed border-amber-300/30"></div>
                        <div class="absolute inset-0 rounded-full bg-red-500/10 blur-2xl"></div>
                        <img
                            src="https://blog.nirmalgaihre.com.np/images/npl.png"
                            alt="NPL Season Hub logo"
                            class="relative z-10 max-h-36 max-w-36 object-contain"
                        >
                        <span class="absolute -bottom-2 rounded-full border border-amber-300/30 bg-slate-900 px-4 py-2 text-xs font-black uppercase tracking-[0.2em] text-amber-300">
                            Cricket Central
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="h-1 bg-gradient-to-r from-red-600 via-amber-400 to-red-600"></div>
    </section>

    {{-- News cards --}}
    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 sm:py-14">

        <div class="mb-8 flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-red-700">
                    Around the League
                </p>
                <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-950 sm:text-3xl">
                    {{ request('search') ? 'Search Results' : 'Latest Stories' }}
                </h2>
                <p class="mt-2 text-sm text-slate-500">
                    Stay updated with the latest action from the Nepal Premier League.
                </p>
            </div>

            @if ($articles->count())
                <div class="inline-flex w-fit items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 shadow-sm">
                    <span class="h-2 w-2 rounded-full bg-red-600"></span>
                    {{ $articles->total() }} {{ \Illuminate\Support\Str::plural('story', $articles->total()) }}
                </div>
            @endif
        </div>

        @if ($articles->count())
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">

                @foreach ($articles as $article)
                    <article class="group flex h-full flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:border-red-200 hover:shadow-xl hover:shadow-slate-900/5">

                        {{-- Cover image or branded fallback --}}
                        <a href="{{ route('news.show', $article) }}"
                           class="relative block overflow-hidden focus:outline-none focus:ring-2 focus:ring-inset focus:ring-red-500"
                           aria-label="Read {{ $article->title }}">

                            @if ($article->cover_image)
                                <img
                                    src="{{ asset('storage/' . $article->cover_image) }}"
                                    alt="{{ $article->title }}"
                                    loading="lazy"
                                    class="aspect-[16/10] w-full object-cover transition duration-500 group-hover:scale-105"
                                >
                            @else
                                <div class="relative flex aspect-[16/10] w-full items-center justify-center overflow-hidden bg-gradient-to-br from-red-700 via-red-600 to-slate-950">

                                    {{-- Decorative circles --}}
                                    <div class="absolute -right-12 -top-16 h-48 w-48 rounded-full border-[22px] border-amber-300/20"></div>
                                    <div class="absolute -bottom-20 -left-10 h-48 w-48 rounded-full border-[25px] border-white/10"></div>
                                    <div class="absolute inset-0 opacity-20"
                                         style="background-image:linear-gradient(135deg,transparent 45%,white 45%,white 46%,transparent 46%);background-size:24px 24px;"></div>

                                    {{-- Logo --}}
                                    <div class="relative z-10 flex flex-col items-center justify-center text-center">
                                        <div class="flex h-24 w-24 items-center justify-center rounded-2xl border border-white/30 bg-white p-3 shadow-2xl sm:h-28 sm:w-28">
                                            <img
                                                src="https://blog.nirmalgaihre.com.np/images/npl.png"
                                                alt="NPL Season Hub"
                                                loading="lazy"
                                                class="max-h-full max-w-full object-contain"
                                            >
                                        </div>

                                        <span class="mt-4 rounded-full border border-white/25 bg-black/20 px-4 py-1.5 text-[10px] font-black uppercase tracking-[0.25em] text-white backdrop-blur-sm">
                                            NPL Season Hub
                                        </span>
                                    </div>

                                    <div class="absolute bottom-0 left-0 right-0 h-1.5 bg-gradient-to-r from-amber-300 via-white to-amber-300"></div>
                                </div>
                            @endif

                            <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-slate-950/50 via-transparent to-transparent opacity-70 transition group-hover:opacity-100"></div>

                            @if ($article->category)
                                <span class="absolute left-4 top-4 rounded-full border border-white/20 bg-red-700 px-3 py-1.5 text-[10px] font-extrabold uppercase tracking-wider text-white shadow-lg">
                                    {{ $article->category->name }}
                                </span>
                            @endif

                            <span class="absolute bottom-4 right-4 inline-flex h-9 w-9 translate-y-2 items-center justify-center rounded-full bg-white text-red-700 opacity-0 shadow-lg transition duration-300 group-hover:translate-y-0 group-hover:opacity-100">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                    <path d="M5 12h14M12 5l7 7-7 7"></path>
                                </svg>
                            </span>
                        </a>

                        {{-- Article details --}}
                        <div class="flex flex-1 flex-col p-5 sm:p-6">
                            <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-slate-500">
                                <svg class="h-4 w-4 text-red-600" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <rect x="3" y="5" width="18" height="16" rx="2"></rect>
                                    <path d="M16 3v4M8 3v4M3 11h18"></path>
                                </svg>
                                <span>{{ $article->published_at?->format('d M Y') ?? 'Latest Update' }}</span>
                                <span class="text-slate-300">•</span>
                                <span class="max-w-[150px] truncate">
                                    {{ $article->author?->name ?? 'NPL Season Hub' }}
                                </span>
                            </div>

                            <h3 class="mt-4 text-xl font-extrabold leading-snug tracking-tight text-slate-950 transition group-hover:text-red-700">
                                <a href="{{ route('news.show', $article) }}">
                                    {{ $article->title }}
                                </a>
                            </h3>

                            @if ($article->excerpt)
                                <p class="mt-3 line-clamp-3 text-sm leading-6 text-slate-600">
                                    {{ $article->excerpt }}
                                </p>
                            @endif

                            @if ($article->tags->isNotEmpty())
                                <div class="mt-4 flex flex-wrap gap-2">
                                    @foreach ($article->tags->take(3) as $tag)
                                        <a
                                            href="{{ route('news.tag', $tag) }}"
                                            class="rounded-full bg-slate-100 px-3 py-1.5 text-[11px] font-semibold text-slate-600 transition hover:bg-red-50 hover:text-red-700"
                                        >
                                            #{{ $tag->name }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif

                            <div class="mt-auto pt-6">
                                <div class="mb-4 h-px bg-slate-100"></div>
                                <a href="{{ route('news.show', $article) }}"
                                   class="inline-flex items-center gap-2 text-sm font-extrabold text-red-700 transition hover:gap-3 hover:text-red-800">
                                    Read Full Story
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                         stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                        <path d="M5 12h14M12 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach

            </div>

            {{-- Pagination --}}
            @if ($articles->hasPages())
                <div class="mt-12 rounded-2xl border border-slate-200 bg-white px-4 py-5 shadow-sm sm:px-6">
                    {{ $articles->withQueryString()->links() }}
                </div>
            @endif

        @else
            {{-- Empty state --}}
            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="bg-gradient-to-br from-slate-950 via-red-950 to-red-800 px-6 py-12 text-center text-white sm:py-16">
                    <div class="mx-auto flex h-24 w-24 items-center justify-center rounded-2xl bg-white p-3 shadow-lg">
                        <img
                            src="https://blog.nirmalgaihre.com.np/images/npl.png"
                            alt="NPL Season Hub logo"
                            class="max-h-full max-w-full object-contain"
                        >
                    </div>

                    <h3 class="mt-6 text-2xl font-black">No stories found</h3>
                    <p class="mx-auto mt-3 max-w-md text-sm leading-6 text-slate-200">
                        We couldn't find any published articles matching your search. Try different keywords or return to the latest cricket news.
                    </p>

                    <a href="{{ route('news.index') }}"
                       class="mt-6 inline-flex items-center gap-2 rounded-xl bg-white px-5 py-3 text-sm font-extrabold text-red-700 transition hover:bg-amber-100">
                        View All News
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="m15 18-6-6 6-6"></path>
                        </svg>
                    </a>
                </div>
            </div>
        @endif

    </section>

    {{-- Bottom banner --}}
    <section class="border-t border-slate-200 bg-white">
        <div class="mx-auto flex max-w-7xl flex-col gap-5 px-4 py-8 sm:px-6 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-red-700">
                    The Game. The Stories. The NPL.
                </p>
                <p class="mt-2 text-lg font-black text-slate-950">Every moment matters.</p>
                <p class="mt-1 text-sm text-slate-500">
                    Follow the latest cricket stories in one place.
                </p>
            </div>

            <a href="{{ route('news.index') }}"
               class="inline-flex w-fit items-center gap-2 rounded-xl bg-slate-950 px-5 py-3 text-sm font-bold text-white transition hover:bg-red-700">
                Explore Latest News
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M5 12h14M12 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>
    </section>

</div>
@endsection
