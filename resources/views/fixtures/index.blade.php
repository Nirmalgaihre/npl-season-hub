
@extends('layouts.app')

@section('title', 'Fixtures & Results | NPL Season Hub')

@section('meta_description', 'Explore Nepal Premier League fixtures, upcoming matches, match-day schedules and verified cricket results.')

@section('content')

@php
    $today = now(config('app.timezone'))->startOfDay();
@endphp

<div class="min-h-screen bg-[#F6F8FB]">

    {{-- Page heading --}}
    <section class="relative overflow-hidden bg-[#171D50] text-white">
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute -right-20 -top-32 h-80 w-80 rounded-full border border-white/10"></div>
            <div class="absolute -right-10 -top-20 h-60 w-60 rounded-full border border-white/10"></div>
            <div class="absolute bottom-0 right-1/4 h-40 w-40 rounded-full bg-[#F06445]/10 blur-3xl"></div>
        </div>

        <div class="relative mx-auto max-w-[1280px] px-5 py-12 sm:px-8 sm:py-16 lg:px-10">

            <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-[0.18em] text-white/60">
                <span class="h-2 w-2 rounded-full bg-[#F06445]"></span>
                Nepal Premier League
            </div>

            <div class="mt-5 flex flex-col justify-between gap-6 sm:flex-row sm:items-end">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight sm:text-4xl lg:text-5xl">
                        Fixtures <span class="text-[#F06445]">&</span> Results
                    </h1>

                    <p class="mt-4 max-w-2xl text-sm leading-7 text-white/65 sm:text-base">
                        Every match, every result, every moment.
                        Follow the NPL season from the first ball to the final score.
                    </p>
                </div>

                <div class="inline-flex w-fit items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white/80">
                    <svg class="h-4 w-4 text-[#F06445]" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="1.8"
                         aria-hidden="true">
                        <rect x="3" y="5" width="18" height="16" rx="2"/>
                        <path d="M16 3v4M8 3v4M3 10h18"/>
                    </svg>

                    <span>{{ now(config('app.timezone'))->format('D, d M Y') }}</span>
                </div>
            </div>
        </div>
    </section>

    <div class="mx-auto max-w-[1280px] px-5 py-8 sm:px-8 sm:py-10 lg:px-10">

        {{-- Filters --}}
        <section class="mb-8 rounded-2xl border border-[#E5E9F2] bg-white p-5 shadow-sm sm:p-6">

            <div class="mb-5 flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#F0F2FF] text-[#202766]">
                    <svg class="h-5 w-5" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="1.8"
                         aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M4 7h16M7 12h10m-7 5h4"/>
                    </svg>
                </div>

                <div>
                    <h2 class="text-sm font-extrabold text-slate-900">
                        Find a match
                    </h2>
                    <p class="mt-1 text-xs text-slate-500">
                        Filter fixtures by season and match status.
                    </p>
                </div>
            </div>

            <form method="GET" action="{{ route('fixtures.index') }}"
                  class="grid gap-3 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_auto_auto]">

                <div>
                    <label for="season"
                           class="mb-2 block text-xs font-bold text-slate-600">
                        Season
                    </label>

                    <select
                        id="season"
                        name="season"
                        class="min-h-12 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-700 outline-none transition focus:border-[#202766] focus:ring-2 focus:ring-[#202766]/10"
                    >
                        <option value="">All seasons</option>

                        @foreach ($seasons as $season)
                            <option
                                value="{{ $season->id }}"
                                @selected((string) request('season') === (string) $season->id)
                            >
                                {{ $season->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="status"
                           class="mb-2 block text-xs font-bold text-slate-600">
                        Match status
                    </label>

                    <select
                        id="status"
                        name="status"
                        class="min-h-12 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-700 outline-none transition focus:border-[#202766] focus:ring-2 focus:ring-[#202766]/10"
                    >
                        <option value="">All matches</option>
                        <option value="upcoming" @selected(request('status') === 'upcoming')>
                            Upcoming
                        </option>
                        <option value="live" @selected(request('status') === 'live')>
                            Live
                        </option>
                        <option value="completed" @selected(request('status') === 'completed')>
                            Completed
                        </option>
                    </select>
                </div>

                <div class="flex items-end">
                    <button
                        type="submit"
                        class="flex min-h-12 w-full items-center justify-center gap-2 rounded-xl bg-[#202766] px-5 text-sm font-bold text-white transition hover:bg-[#171D50] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#F06445]"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2"
                             aria-hidden="true">
                            <circle cx="11" cy="11" r="7"/>
                            <path d="m16 16 4 4"/>
                        </svg>
                        Apply filters
                    </button>
                </div>

                @if (request()->filled('season') || request()->filled('status'))
                    <div class="flex items-end">
                        <a
                            href="{{ route('fixtures.index') }}"
                            class="flex min-h-12 w-full items-center justify-center rounded-xl border border-slate-200 px-4 text-sm font-semibold text-slate-600 transition hover:bg-slate-50"
                        >
                            Clear filters
                        </a>
                    </div>
                @endif

            </form>
        </section>

        {{-- Section title --}}
        <div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#F06445]">
                    Match centre
                </p>

                <h2 class="mt-2 text-xl font-extrabold tracking-tight text-slate-900 sm:text-2xl">
                    Season fixtures
                </h2>

                <p class="mt-2 text-sm text-slate-500">
                    Check schedules, team matchups and confirmed results.
                </p>
            </div>

            <p class="text-sm font-medium text-slate-500">
                {{ $fixtures->total() }} {{ \Illuminate\Support\Str::plural('match', $fixtures->total()) }} found
            </p>
        </div>

        {{-- Match cards --}}
        <div class="grid items-stretch gap-5 lg:grid-cols-2">

            @forelse ($fixtures as $fixture)

                @php
                    $matchDate = $fixture->scheduled_at
                        ? $fixture->scheduled_at
                            ->copy()
                            ->timezone(config('app.timezone'))
                            ->startOfDay()
                        : null;

                    $isToday = $matchDate && $matchDate->equalTo($today);
                    $isPast = $matchDate && $matchDate->lt($today);
                    $isFuture = $matchDate && $matchDate->gt($today);

                    $actualStatus = strtolower($fixture->status ?? 'scheduled');

                    $isVerified = (bool) $fixture->is_result_verified;

                    /*
                     * Never mark an unverified past match as a confirmed
                     * completed result. The date controls the date label;
                     * recorded match status and verification control the
                     * result state.
                     */
                    if (in_array($actualStatus, ['postponed', 'abandoned'])) {
                        $displayStatus = ucfirst($actualStatus);
                    } elseif ($actualStatus === 'live') {
                        $displayStatus = 'LIVE';
                    } elseif ($actualStatus === 'completed') {
                        $displayStatus = 'COMPLETED';
                    } elseif ($isPast && $isVerified) {
                        $displayStatus = 'COMPLETED';
                    } elseif ($isPast) {
                        $displayStatus = 'RESULT PENDING';
                    } elseif ($isToday) {
                        $displayStatus = 'TODAY';
                    } elseif ($isFuture) {
                        $displayStatus = 'UPCOMING';
                    } else {
                        $displayStatus = 'DATE TBA';
                    }

                    $statusClass = match ($displayStatus) {
                        'LIVE' => 'bg-red-50 text-red-700 ring-red-100',
                        'COMPLETED' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
                        'TODAY' => 'bg-orange-50 text-orange-700 ring-orange-100',
                        'UPCOMING' => 'bg-blue-50 text-blue-700 ring-blue-100',
                        'RESULT PENDING' => 'bg-amber-50 text-amber-800 ring-amber-100',
                        'Postponed', 'Abandoned' => 'bg-slate-100 text-slate-700 ring-slate-200',
                        default => 'bg-slate-100 text-slate-600 ring-slate-200',
                    };

                    $homeTeamName = $fixture->homeTeam->short_name
                        ?: $fixture->homeTeam->name;

                    $awayTeamName = $fixture->awayTeam->short_name
                        ?: $fixture->awayTeam->name;

                    $hasResult = $isVerified && $actualStatus === 'completed';

                    $dateLabel = $fixture->scheduled_at
                        ? $fixture->scheduled_at
                            ->copy()
                            ->timezone(config('app.timezone'))
                            ->format('D, d M Y')
                        : 'Date to be announced';

                    $timeLabel = $fixture->scheduled_at
                        ? $fixture->scheduled_at
                            ->copy()
                            ->timezone(config('app.timezone'))
                            ->format('h:i A')
                        : null;
                @endphp

                <a
                    href="{{ route('fixtures.show', $fixture) }}"
                    class="group flex min-w-0 flex-col overflow-hidden rounded-2xl border border-[#E5E9F2] bg-white shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-[#C9CEE8] hover:shadow-lg focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#F06445]"
                >

                    {{-- Card header --}}
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4 sm:px-6">

                        <div class="min-w-0">
                            <p class="text-xs font-extrabold uppercase tracking-wider text-[#202766]">
                                {{ $fixture->season?->name ?? 'NPL Season' }}
                            </p>

                            <p class="mt-1 text-xs text-slate-500">
                                Match {{ $fixture->match_number ?? '—' }}
                            </p>
                        </div>

                        <span class="inline-flex shrink-0 items-center gap-2 rounded-full px-3 py-1.5 text-[10px] font-extrabold uppercase tracking-wider ring-1 ring-inset {{ $statusClass }}">

                            @if ($displayStatus === 'LIVE')
                                <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-red-600"></span>
                            @elseif ($displayStatus === 'TODAY')
                                <span class="h-1.5 w-1.5 rounded-full bg-orange-500"></span>
                            @endif

                            {{ $displayStatus }}
                        </span>

                    </div>

                    {{-- Teams and scores --}}
                    <div class="flex-1 px-5 py-6 sm:px-6 sm:py-7">

                        <div class="grid grid-cols-[minmax(0,1fr)_auto_minmax(0,1fr)] items-center gap-3 sm:gap-5">

                            {{-- Home team --}}
                            <div class="min-w-0 text-center">
                                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl border border-slate-100 bg-slate-50 text-sm font-black text-[#202766] sm:h-16 sm:w-16 sm:text-base">
                                    {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($homeTeamName, 0, 3)) }}
                                </div>

                                <h3 class="mt-3 break-words text-sm font-extrabold leading-5 text-slate-900 sm:text-base">
                                    {{ $homeTeamName }}
                                </h3>

                                @if ($hasResult)
                                    <p class="mt-3 text-2xl font-black tracking-tight text-[#202766] sm:text-3xl">
                                        {{ $fixture->home_runs ?? 0 }}<span class="text-slate-400">/</span>{{ $fixture->home_wickets ?? 0 }}
                                    </p>

                                    <p class="mt-1 text-xs font-medium text-slate-500">
                                        {{ $fixture->home_overs ?? '—' }} overs
                                    </p>
                                @endif
                            </div>

                            {{-- Versus / result --}}
                            <div class="flex min-w-[64px] flex-col items-center justify-center">
                                @if ($hasResult)
                                    <span class="rounded-lg bg-emerald-50 px-2.5 py-2 text-[10px] font-extrabold uppercase tracking-wider text-emerald-700">
                                        Result
                                    </span>
                                @elseif ($displayStatus === 'LIVE')
                                    <span class="rounded-lg bg-red-50 px-2.5 py-2 text-[10px] font-extrabold uppercase tracking-wider text-red-700">
                                        Live
                                    </span>
                                @else
                                    <span class="text-xs font-black uppercase tracking-[0.18em] text-slate-300">
                                        VS
                                    </span>
                                @endif
                            </div>

                            {{-- Away team --}}
                            <div class="min-w-0 text-center">
                                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl border border-slate-100 bg-slate-50 text-sm font-black text-[#202766] sm:h-16 sm:w-16 sm:text-base">
                                    {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($awayTeamName, 0, 3)) }}
                                </div>

                                <h3 class="mt-3 break-words text-sm font-extrabold leading-5 text-slate-900 sm:text-base">
                                    {{ $awayTeamName }}
                                </h3>

                                @if ($hasResult)
                                    <p class="mt-3 text-2xl font-black tracking-tight text-[#202766] sm:text-3xl">
                                        {{ $fixture->away_runs ?? 0 }}<span class="text-slate-400">/</span>{{ $fixture->away_wickets ?? 0 }}
                                    </p>

                                    <p class="mt-1 text-xs font-medium text-slate-500">
                                        {{ $fixture->away_overs ?? '—' }} overs
                                    </p>
                                @endif
                            </div>

                        </div>

                        {{-- Verified result summary --}}
                        @if ($hasResult && $fixture->result_summary)
                            <div class="mt-6 flex items-start gap-3 rounded-xl border border-emerald-100 bg-emerald-50/70 p-3.5">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-emerald-700"
                                     viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor" stroke-width="2"
                                     aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="m5 12 4 4L19 6"/>
                                </svg>

                                <p class="text-xs font-semibold leading-5 text-emerald-800 sm:text-sm">
                                    {{ $fixture->result_summary }}
                                </p>
                            </div>
                        @elseif ($isPast && !$isVerified && !in_array($actualStatus, ['postponed', 'abandoned']))
                            <div class="mt-6 rounded-xl border border-amber-100 bg-amber-50/70 px-4 py-3">
                                <p class="text-xs font-medium leading-5 text-amber-800">
                                    The match date has passed. The confirmed result is not yet available.
                                </p>
                            </div>
                        @endif

                    </div>

                    {{-- Match details --}}
                    <div class="border-t border-slate-100 bg-slate-50/70 px-5 py-4 sm:px-6">

                        <div class="flex flex-wrap items-center justify-between gap-4">

                            <div class="flex min-w-0 items-start gap-3">

                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white text-slate-500 ring-1 ring-slate-200/70">
                                    <svg class="h-4 w-4"
                                         viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor" stroke-width="1.8"
                                         aria-hidden="true">
                                        <rect x="3" y="5" width="18" height="16" rx="2"/>
                                        <path d="M16 3v4M8 3v4M3 10h18"/>
                                    </svg>
                                </div>

                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-slate-800">
                                        {{ $dateLabel }}
                                    </p>

                                    <p class="mt-1 text-xs text-slate-500">
                                        @if ($timeLabel)
                                            {{ $timeLabel }}
                                        @else
                                            Time to be announced
                                        @endif
                                    </p>

                                    <p class="mt-2 break-words text-xs text-slate-500">
                                        {{ $fixture->venue ?: 'Venue to be announced' }}
                                    </p>
                                </div>

                            </div>

                            <span class="inline-flex shrink-0 items-center gap-1.5 text-xs font-bold text-[#202766] transition group-hover:text-[#F06445]">
                                View match

                                <svg class="h-4 w-4 transition-transform group-hover:translate-x-1"
                                     viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor" stroke-width="2"
                                     aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M5 12h14m-6-6 6 6-6 6"/>
                                </svg>
                            </span>

                        </div>
                    </div>

                </a>

            @empty

                {{-- Empty state --}}
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center lg:col-span-2">

                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-[#F0F2FF] text-[#202766]">
                        <svg class="h-7 w-7"
                             viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="1.6"
                             aria-hidden="true">
                            <rect x="3" y="5" width="18" height="16" rx="2"/>
                            <path d="M16 3v4M8 3v4M3 10h18"/>
                        </svg>
                    </div>

                    <h3 class="mt-5 text-lg font-extrabold text-slate-900">
                        No matches found
                    </h3>

                    <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500">
                        There are no fixtures matching your selected filters.
                        Try another season or clear your filters to see all matches.
                    </p>

                    <a
                        href="{{ route('fixtures.index') }}"
                        class="mt-6 inline-flex min-h-11 items-center justify-center rounded-xl bg-[#202766] px-5 text-sm font-bold text-white transition hover:bg-[#171D50]"
                    >
                        View all fixtures
                    </a>
                </div>

            @endforelse

        </div>

        {{-- Pagination --}}
        @if ($fixtures->hasPages())
            <div class="mt-8 rounded-2xl border border-[#E5E9F2] bg-white px-4 py-4 sm:px-6">
                {{ $fixtures->withQueryString()->links() }}
            </div>
        @endif

        {{-- Small disclaimer --}}
        <p class="mt-6 text-center text-xs leading-5 text-slate-400">
            Match results are displayed according to the information recorded
            and verified by the site's administrators.
        </p>

    </div>
</div>

@endsection
