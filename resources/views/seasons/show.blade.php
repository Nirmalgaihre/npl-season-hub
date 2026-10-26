
@extends('layouts.app')

@section('title', $season->name . ' | NPL Season Hub')

@section('meta_description', $season->description
    ? \Illuminate\Support\Str::limit($season->description, 155)
    : 'Explore ' . $season->name . ' on NPL Season Hub.')

@section('content')
@php
    $seasonStatus = $season->status ?? 'upcoming';
@endphp

<section class="bg-[#171D50] text-white">
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
        <a href="{{ route('seasons.index') }}"
           class="inline-flex items-center gap-2 text-sm font-semibold text-slate-300 transition hover:text-white">
            <span aria-hidden="true">←</span>
            All seasons
        </a>

        <div class="mt-8 grid gap-8 lg:grid-cols-[1fr_320px] lg:items-end">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-[#F7A08D]">
                    Nepal Premier League
                    @if($season->season_number !== null)
                        <span class="mx-1 text-white/40">/</span>
                        Season {{ $season->season_number }}
                    @endif
                </p>

                <h1 class="mt-4 text-4xl font-black leading-tight tracking-tight sm:text-5xl lg:text-6xl">
                    {{ $season->name }}
                </h1>

                <p class="mt-5 max-w-2xl text-base leading-7 text-slate-300">
                    Explore tournament information, match schedules, participating teams, and published updates for this season.
                </p>

                <div class="mt-7 flex flex-wrap gap-3">
                    <a href="{{ route('fixtures.index') }}"
                       class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-[#F06445] px-5 py-3 text-sm font-extrabold text-white transition hover:bg-[#e75335]">
                        Browse fixtures
                        <span aria-hidden="true">→</span>
                    </a>

                    <a href="{{ route('teams.index') }}"
                       class="inline-flex min-h-12 items-center justify-center rounded-xl border border-white/20 px-5 py-3 text-sm font-bold text-white transition hover:bg-white/10">
                        Explore teams
                    </a>
                </div>
            </div>

            <div class="rounded-2xl border border-white/10 bg-white/[0.07] p-6">
                <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-[#F7A08D]">
                    Season information
                </p>

                <div class="mt-5">
                    <p class="text-xs font-semibold text-slate-400">Start date</p>
                    <p class="mt-1 text-sm font-bold text-white">
                        {{ $season->start_date?->format('d F Y') ?? 'To be announced' }}
                    </p>
                </div>

                <div class="mt-4 border-t border-white/10 pt-4">
                    <p class="text-xs font-semibold text-slate-400">End date</p>
                    <p class="mt-1 text-sm font-bold text-white">
                        {{ $season->end_date?->format('d F Y') ?? 'To be announced' }}
                    </p>
                </div>

                <div class="mt-4 border-t border-white/10 pt-4">
                    <p class="text-xs font-semibold text-slate-400">Status</p>

                    <div class="mt-2">
                        @if($seasonStatus === 'active')
                            <span class="inline-flex items-center gap-2 rounded-full bg-emerald-400/10 px-3 py-1.5 text-xs font-bold text-emerald-300">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                                Active season
                            </span>
                        @elseif($seasonStatus === 'completed')
                            <span class="inline-flex rounded-full bg-white/10 px-3 py-1.5 text-xs font-bold text-slate-200">
                                Completed
                            </span>
                        @else
                            <span class="inline-flex rounded-full bg-orange-400/10 px-3 py-1.5 text-xs font-bold text-orange-200">
                                {{ ucfirst(str_replace('_', ' ', $seasonStatus)) }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="bg-[#F6F8FB] py-10 sm:py-14">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-6 lg:grid-cols-[1fr_320px]">

            <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-[#F06445]">
                    About the tournament
                </p>

                <h2 class="mt-2 text-2xl font-black tracking-tight text-[#202766]">
                    Season overview
                </h2>

                @if($season->description)
                    <div class="mt-5 whitespace-pre-line break-words text-base leading-8 text-slate-600">{{ $season->description }}</div>
                @else
                    <p class="mt-5 text-sm leading-7 text-slate-500">
                        A description for this season has not been published yet.
                    </p>
                @endif
            </article>

            <aside class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-[#F06445]">
                    Quick links
                </p>

                <h2 class="mt-2 text-xl font-black text-[#202766]">
                    Keep exploring
                </h2>

                <div class="mt-5 space-y-3">
                    <a href="{{ route('fixtures.index') }}"
                       class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 p-4 text-sm font-bold text-[#202766] transition hover:border-[#202766]/30 hover:bg-slate-50">
                        Fixtures & results
                        <span aria-hidden="true">→</span>
                    </a>

                    <a href="{{ route('teams.index') }}"
                       class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 p-4 text-sm font-bold text-[#202766] transition hover:border-[#202766]/30 hover:bg-slate-50">
                        Teams
                        <span aria-hidden="true">→</span>
                    </a>

                    <a href="{{ route('news.index') }}"
                       class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 p-4 text-sm font-bold text-[#202766] transition hover:border-[#202766]/30 hover:bg-slate-50">
                        Latest news
                        <span aria-hidden="true">→</span>
                    </a>

                    <a href="{{ route('seasons.index') }}"
                       class="flex items-center justify-between gap-3 rounded-xl bg-[#202766] p-4 text-sm font-bold text-white transition hover:bg-[#171D50]">
                        All seasons
                        <span aria-hidden="true">→</span>
                    </a>
                </div>
            </aside>
        </div>

        <div class="mt-8 rounded-2xl border border-slate-200 bg-white p-6 sm:p-8">
            <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-[#F06445]">
                Season coverage
            </p>

            <h2 class="mt-2 text-2xl font-black tracking-tight text-[#202766]">
                Follow the action
            </h2>

            <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-500">
                Browse published fixtures, explore team profiles, and read available league updates. Season-specific match reports and news can be connected here when those records are available.
            </p>

            <div class="mt-6">
                <a href="{{ route('fixtures.index') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-[#F06445] px-5 py-3 text-sm font-extrabold text-white transition hover:bg-[#e75335]">
                    View fixtures
                    <span aria-hidden="true">→</span>
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
