
@extends('layouts.app')

@section('title', 'Seasons | NPL Season Hub')
@section('meta_description', 'Explore Nepal Premier League seasons, tournament archives, schedules, and cricket results.')

@section('content')
<section class="relative overflow-hidden bg-[#171D50] text-white">
    <div class="pointer-events-none absolute -right-20 -top-24 h-80 w-80 rounded-full border border-white/10"></div>
    <div class="pointer-events-none absolute -right-8 -top-12 h-56 w-56 rounded-full border border-white/10"></div>
    <div class="pointer-events-none absolute -bottom-20 left-1/3 h-64 w-64 rounded-full bg-[#F06445]/10 blur-3xl"></div>

    <div class="relative mx-auto max-w-7xl px-4 py-14 sm:px-6 sm:py-20 lg:px-8">
        <div class="max-w-3xl">
            <div class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/[0.07] px-4 py-2 text-xs font-bold uppercase tracking-[0.18em] text-slate-200">
                <span class="h-2 w-2 rounded-full bg-[#F06445]"></span>
                Nepal Premier League
            </div>

            <h1 class="mt-6 text-4xl font-black leading-[1.08] tracking-tight sm:text-5xl lg:text-7xl">
                Every season.
                <span class="block text-[#F06445]">Every story.</span>
            </h1>

            <p class="mt-5 max-w-2xl text-base leading-7 text-slate-300 sm:text-lg sm:leading-8">
                Explore tournament seasons, revisit past campaigns, and follow the history of Nepal Premier League cricket.
            </p>

            <div class="mt-8 flex flex-wrap items-center gap-3">
                <a href="{{ route('fixtures.index') }}"
                   class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-[#F06445] px-5 py-3 text-sm font-extrabold text-white transition hover:bg-[#e75335]">
                    Browse fixtures
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                </a>

                <a href="{{ route('teams.index') }}"
                   class="inline-flex min-h-12 items-center justify-center rounded-xl border border-white/20 bg-white/[0.05] px-5 py-3 text-sm font-bold text-white transition hover:bg-white/10">
                    Explore teams
                </a>
            </div>
        </div>
    </div>
</section>

<section class="bg-[#F6F8FB] py-12 sm:py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

        {{-- Archive heading --}}
        <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-[#F06445]">
                    Tournament history
                </p>
                <h2 class="mt-2 text-2xl font-black tracking-tight text-[#202766] sm:text-3xl">
                    Season archive
                </h2>
                <p class="mt-2 text-sm leading-6 text-slate-500">
                    Select a season to explore its tournament information.
                </p>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white px-4 py-3">
                <p class="text-2xl font-black tabular-nums text-[#202766]">
                    {{ $seasons->total() }}
                </p>
                <p class="text-xs font-semibold text-slate-500">
                    {{ $seasons->total() === 1 ? 'Season recorded' : 'Seasons recorded' }}
                </p>
            </div>
        </div>

        @if($seasons->count())
            <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                @foreach($seasons as $season)
                    <article class="group relative flex h-full flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition duration-200 hover:-translate-y-1 hover:border-[#202766]/25 hover:shadow-lg">
                        {{-- Top accent --}}
                        <div class="h-1.5 bg-[#202766] transition-colors group-hover:bg-[#F06445]"></div>

                        <div class="flex flex-1 flex-col p-5 sm:p-6">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[#202766] text-white transition group-hover:bg-[#F06445]">
                                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                        <path d="M8 4h8v4a4 4 0 0 1-8 0V4Z"/>
                                        <path d="M8 6H4v2a4 4 0 0 0 4 4M16 6h4v2a4 4 0 0 1-4 4M12 12v5M8 21h8M10 17h4"/>
                                    </svg>
                                </div>

                                @if($season->status === 'active')
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1.5 text-[10px] font-extrabold uppercase tracking-wide text-emerald-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                        Active season
                                    </span>
                                @elseif($season->status === 'completed')
                                    <span class="rounded-full bg-slate-100 px-3 py-1.5 text-[10px] font-extrabold uppercase tracking-wide text-slate-600">
                                        Completed
                                    </span>
                                @else
                                    <span class="rounded-full bg-orange-50 px-3 py-1.5 text-[10px] font-extrabold uppercase tracking-wide text-orange-700">
                                        {{ ucfirst($season->status ?? 'Season') }}
                                    </span>
                                @endif
                            </div>

                            <p class="mt-6 text-xs font-extrabold uppercase tracking-[0.16em] text-[#F06445]">
                                @if($season->season_number !== null)
                                    Season {{ $season->season_number }}
                                @else
                                    Tournament season
                                @endif
                            </p>

                            <h3 class="mt-2 text-xl font-black leading-snug text-[#202766] sm:text-2xl">
                                {{ $season->name }}
                            </h3>

                            <div class="mt-4 flex items-start gap-3 rounded-xl bg-[#F6F8FB] p-3.5">
                                <svg class="mt-0.5 h-5 w-5 shrink-0 text-[#202766]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <rect x="3" y="5" width="18" height="16" rx="2"/>
                                    <path d="M16 3v4M8 3v4M3 10h18"/>
                                </svg>

                                <div class="min-w-0">
                                    <p class="text-xs font-bold uppercase tracking-wide text-slate-500">
                                        Tournament dates
                                    </p>

                                    <p class="mt-1 text-sm font-bold leading-6 text-[#202766]">
                                        @if($season->start_date)
                                            {{ $season->start_date->format('d M Y') }}
                                        @else
                                            Date to be announced
                                        @endif

                                        @if($season->end_date)
                                            <span class="text-slate-400"> — </span>
                                            {{ $season->end_date->format('d M Y') }}
                                        @endif
                                    </p>
                                </div>
                            </div>

                            @if($season->description)
                                <p class="mt-4 flex-1 text-sm leading-7 text-slate-600">
                                    {{ \Illuminate\Support\Str::limit($season->description, 150) }}
                                </p>
                            @else
                                <div class="flex-1"></div>
                            @endif

                            <div class="mt-6 border-t border-slate-100 pt-5">
                                <a href="{{ route('seasons.show', $season) }}"
                                   class="inline-flex w-full items-center justify-between gap-3 rounded-xl bg-[#202766] px-4 py-3.5 text-sm font-extrabold text-white transition hover:bg-[#171D50]">
                                    <span>Explore season</span>
                                    <svg class="h-4 w-4 transition group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <path d="M5 12h14M12 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            {{-- Pagination --}}
            @if($seasons->hasPages())
                <div class="mt-10 rounded-2xl border border-slate-200 bg-white p-4 sm:p-5">
                    {{ $seasons->onEachSide(1)->links() }}
                </div>
            @endif
        @else
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center sm:py-16">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-[#F6F8FB] text-[#202766]">
                    <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                        <path d="M8 4h8v4a4 4 0 0 1-8 0V4Z"/>
                        <path d="M8 6H4v2a4 4 0 0 0 4 4M16 6h4v2a4 4 0 0 1-4 4M12 12v5M8 21h8M10 17h4"/>
                    </svg>
                </div>

                <h3 class="mt-5 text-xl font-black text-[#202766]">
                    No seasons available yet
                </h3>

                <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500">
                    There are no seasons available in the public archive at the moment. Published seasons will appear here when they are added.
                </p>

                <a href="{{ route('fixtures.index') }}"
                   class="mt-6 inline-flex items-center gap-2 rounded-xl bg-[#202766] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#171D50]">
                    Browse fixtures
                    <span aria-hidden="true">→</span>
                </a>
            </div>
        @endif

    </div>
</section>
@endsection
