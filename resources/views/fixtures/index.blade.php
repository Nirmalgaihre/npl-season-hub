@extends('layouts.app')

@section('title', 'Fixtures & Results | NPL Season Hub')

@section('meta_description', 'Explore Nepal Premier League fixtures, upcoming matches, team logos, match schedules and verified cricket results.')

@section('content')

@php
    $today = now(config('app.timezone'))->startOfDay();
    $totalFixtures = $fixtures->total();
@endphp

<div class="min-h-screen bg-[#F5F7FC] text-[#172554]">

    {{-- Hero --}}
    <section class="relative isolate overflow-hidden bg-[#111A45] text-white">
        <div class="pointer-events-none absolute inset-0 overflow-hidden">
            <div class="absolute -right-20 -top-36 h-96 w-96 rounded-full border border-white/[0.08]"></div>
            <div class="absolute -right-8 -top-24 h-72 w-72 rounded-full border border-white/[0.08]"></div>
            <div class="absolute -bottom-32 left-1/3 h-80 w-80 rounded-full bg-[#F06445]/10 blur-3xl"></div>
            <div class="absolute bottom-0 left-0 h-px w-full bg-gradient-to-r from-transparent via-white/20 to-transparent"></div>
        </div>

        <div class="relative mx-auto max-w-[1280px] px-5 py-12 sm:px-8 sm:py-16 lg:px-10 lg:py-20">

            <div class="flex items-center gap-2.5 text-[11px] font-extrabold uppercase tracking-[0.22em] text-white/60">
                <span class="h-2 w-2 rounded-full bg-[#F06445]"></span>
                Nepal Premier League
                <span class="text-white/25">/</span>
                Match centre
            </div>

            <div class="mt-6 flex flex-col justify-between gap-8 lg:flex-row lg:items-end">

                <div class="max-w-3xl">
                    <h1 class="text-4xl font-black tracking-tight sm:text-5xl lg:text-6xl">
                        Fixtures <span class="text-[#F06445]">&</span>
                        <br class="hidden sm:block">
                        Results<span class="text-[#F06445]">.</span>
                    </h1>

                    <p class="mt-5 max-w-xl text-sm leading-7 text-white/65 sm:text-base">
                        Every matchup has a story. Explore upcoming games, check
                        confirmed scores and follow the Nepal Premier League
                        throughout the season.
                    </p>

                    <a href="#fixtures"
                       class="mt-7 inline-flex min-h-12 items-center gap-3 rounded-xl bg-[#F06445] px-5 text-sm font-extrabold text-white transition hover:bg-[#e55336]">
                        Explore matches
                        <svg class="h-4 w-4" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2"
                             aria-hidden="true">
                            <path d="M5 12h14m-6-6 6 6-6 6"/>
                        </svg>
                    </a>
                </div>

                <div class="flex w-fit items-center gap-4 rounded-2xl border border-white/10 bg-white/[0.05] p-4 backdrop-blur-sm sm:p-5">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white/10 text-[#FF8A70]">
                        <svg class="h-6 w-6" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="1.7"
                             aria-hidden="true">
                            <rect x="3" y="5" width="18" height="16" rx="2"/>
                            <path d="M16 3v4M8 3v4M3 10h18"/>
                        </svg>
                    </div>

                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-white/50">
                            Today's date
                        </p>
                        <p class="mt-1 text-sm font-extrabold">
                            {{ now(config('app.timezone'))->format('D, d M Y') }}
                        </p>
                        <p class="mt-1 text-xs text-white/50">
                            {{ number_format($totalFixtures) }}
                            {{ \Illuminate\Support\Str::plural('fixture', $totalFixtures) }} listed
                        </p>
                    </div>
                </div>

            </div>

        </div>
    </section>

    <div class="mx-auto max-w-[1280px] px-5 py-8 sm:px-8 sm:py-10 lg:px-10">

        {{-- Filters --}}
        <section class="mb-10 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-7">

            <div class="mb-6 flex items-start gap-3">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#EEF1FF] text-[#202766]">
                    <svg class="h-5 w-5" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="1.8"
                         aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M4 7h16M7 12h10m-7 5h4"/>
                    </svg>
                </div>

                <div>
                    <h2 class="text-base font-extrabold text-slate-900">
                        Find your match
                    </h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Narrow down the schedule by season and match status.
                    </p>
                </div>
            </div>

            <form method="GET"
                  action="{{ route('fixtures.index') }}"
                  class="grid gap-4 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_auto_auto]">

                <div>
                    <label for="season" class="mb-2 block text-xs font-bold text-slate-600">
                        Competition season
                    </label>

                    <select id="season"
                            name="season"
                            class="min-h-12 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm text-slate-700 outline-none transition focus:border-[#202766] focus:ring-4 focus:ring-[#202766]/10">
                        <option value="">All seasons</option>

                        @foreach ($seasons as $season)
                            <option value="{{ $season->id }}"
                                @selected((string) request('season') === (string) $season->id)>
                                {{ $season->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="status" class="mb-2 block text-xs font-bold text-slate-600">
                        Match status
                    </label>

                    <select id="status"
                            name="status"
                            class="min-h-12 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm text-slate-700 outline-none transition focus:border-[#202766] focus:ring-4 focus:ring-[#202766]/10">
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
                    <button type="submit"
                            class="flex min-h-12 w-full items-center justify-center gap-2 rounded-xl bg-[#202766] px-5 text-sm font-bold text-white transition hover:bg-[#171D50] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#F06445]">
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
                        <a href="{{ route('fixtures.index') }}"
                           class="flex min-h-12 w-full items-center justify-center rounded-xl border border-slate-200 px-4 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                            Clear filters
                        </a>
                    </div>
                @endif

            </form>
        </section>

        {{-- Fixture heading --}}
        <section id="fixtures" class="scroll-mt-6">

            <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">

                <div>
                    <p class="text-[11px] font-extrabold uppercase tracking-[0.2em] text-[#F06445]">
                        The competition
                    </p>

                    <h2 class="mt-2 text-2xl font-black tracking-tight text-[#172554] sm:text-3xl">
                        Match schedule
                    </h2>

                    <p class="mt-2 text-sm text-slate-500">
                        Matchups, kickoff times and official recorded results.
                    </p>
                </div>

                <div class="flex items-center gap-2 self-start rounded-xl border border-slate-200 bg-white px-4 py-3 sm:self-auto">
                    <span class="h-2 w-2 rounded-full bg-[#F06445]"></span>
                    <span class="text-sm font-bold text-slate-700">
                        {{ number_format($totalFixtures) }}
                        {{ \Illuminate\Support\Str::plural('match', $totalFixtures) }}
                    </span>
                </div>

            </div>

            {{-- Match cards --}}
            <div class="grid items-stretch gap-5 lg:grid-cols-2">

                @forelse ($fixtures as $fixture)

                    @php
                        $matchDate = $fixture->scheduled_at
                            ? $fixture->scheduled_at->copy()
                                ->timezone(config('app.timezone'))
                                ->startOfDay()
                            : null;

                        $isToday = $matchDate && $matchDate->equalTo($today);
                        $isPast = $matchDate && $matchDate->lt($today);
                        $isFuture = $matchDate && $matchDate->gt($today);

                        $actualStatus = strtolower($fixture->status ?? 'scheduled');
                        $isVerified = (bool) ($fixture->is_result_verified ?? false);

                        $isSpecialStatus = in_array(
                            $actualStatus,
                            ['postponed', 'abandoned']
                        );

                        if ($isSpecialStatus) {
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

                        /*
                         * Use the actual team logo stored in the teams table.
                         * This assumes logo paths are stored relative to
                         * storage/app/public, for example teams/logo.png.
                         */
                        $homeTeam = $fixture->homeTeam;
                        $awayTeam = $fixture->awayTeam;

                        $homeTeamName = $homeTeam?->short_name
                            ?: $homeTeam?->name
                            ?: 'Home team';

                        $awayTeamName = $awayTeam?->short_name
                            ?: $awayTeam?->name
                            ?: 'Away team';

                        $homeLogo = $homeTeam?->logo
                            ? asset('storage/' . ltrim($homeTeam->logo, '/'))
                            : null;

                        $awayLogo = $awayTeam?->logo
                            ? asset('storage/' . ltrim($awayTeam->logo, '/'))
                            : null;

                        $homeInitials = \Illuminate\Support\Str::upper(
                            \Illuminate\Support\Str::substr($homeTeamName, 0, 3)
                        );

                        $awayInitials = \Illuminate\Support\Str::upper(
                            \Illuminate\Support\Str::substr($awayTeamName, 0, 3)
                        );

                        $hasResult = $isVerified && $actualStatus === 'completed';

                        $dateLabel = $fixture->scheduled_at
                            ? $fixture->scheduled_at->copy()
                                ->timezone(config('app.timezone'))
                                ->format('D, d M Y')
                            : 'Date to be announced';

                        $timeLabel = $fixture->scheduled_at
                            ? $fixture->scheduled_at->copy()
                                ->timezone(config('app.timezone'))
                                ->format('h:i A')
                            : null;

                        $matchHeadline = $fixture->result_summary
                            ?: ($hasResult ? 'Official result confirmed' : null);
                    @endphp

                    <a href="{{ route('fixtures.show', $fixture) }}"
                       class="group flex min-w-0 flex-col overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:border-[#C9CEE8] hover:shadow-xl focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#F06445]">

                        {{-- Match header --}}
                        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4 sm:px-6">

                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-xs font-extrabold uppercase tracking-wider text-[#202766]">
                                        {{ $fixture->season?->name ?? 'NPL Season' }}
                                    </span>

                                    @if ($fixture->match_number)
                                        <span class="text-slate-300">/</span>
                                        <span class="text-xs font-medium text-slate-500">
                                            Match {{ $fixture->match_number }}
                                        </span>
                                    @endif
                                </div>

                                <p class="mt-1.5 text-[11px] text-slate-400">
                                    {{ $fixture->venue ?: 'Venue TBA' }}
                                </p>
                            </div>

                            <span class="inline-flex shrink-0 items-center gap-2 rounded-full px-3 py-1.5 text-[10px] font-extrabold uppercase tracking-wider ring-1 ring-inset {{ $statusClass }}">

                                @if ($displayStatus === 'LIVE')
                                    <span class="relative flex h-2 w-2">
                                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-red-500 opacity-50"></span>
                                        <span class="relative inline-flex h-2 w-2 rounded-full bg-red-600"></span>
                                    </span>
                                @elseif ($displayStatus === 'TODAY')
                                    <span class="h-1.5 w-1.5 rounded-full bg-orange-500"></span>
                                @elseif ($displayStatus === 'COMPLETED')
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor" stroke-width="2.5"
                                         aria-hidden="true">
                                        <path d="m5 12 4 4L19 6"/>
                                    </svg>
                                @endif

                                {{ $displayStatus }}
                            </span>

                        </div>

                        {{-- Team matchup --}}
                        <div class="flex-1 px-4 py-7 sm:px-6 sm:py-8">

                            <div class="grid grid-cols-[minmax(0,1fr)_auto_minmax(0,1fr)] items-start gap-2 sm:gap-4">

                                {{-- Home team --}}
                                <div class="min-w-0 text-center">

                                    <div class="relative mx-auto flex h-24 w-24 max-w-full items-center justify-center rounded-2xl border border-slate-100 bg-gradient-to-br from-slate-50 to-white p-3 transition duration-300 group-hover:border-[#DCE1F5] sm:h-28 sm:w-28 sm:p-4">

                                        @if ($homeLogo)
                                            <img
                                                src="{{ $homeLogo }}"
                                                alt="{{ $homeTeam?->name ?? $homeTeamName }} logo"
                                                loading="lazy"
                                                decoding="async"
                                                class="h-full w-full object-contain transition duration-300 group-hover:scale-105"
                                                onerror="this.style.display='none';this.nextElementSibling.classList.remove('hidden');"
                                            >

                                            <span class="hidden text-xl font-black tracking-tight text-[#202766]">
                                                {{ $homeInitials }}
                                            </span>
                                        @else
                                            <span class="text-xl font-black tracking-tight text-[#202766]">
                                                {{ $homeInitials }}
                                            </span>
                                        @endif

                                    </div>

                                    <h3 class="mx-auto mt-4 max-w-[180px] break-words text-sm font-extrabold leading-5 text-slate-900 sm:text-base">
                                        {{ $homeTeamName }}
                                    </h3>

                                    @if ($hasResult)
                                        <div class="mt-4">
                                            <p class="text-2xl font-black tracking-tight text-[#172554] sm:text-3xl">
                                                {{ $fixture->home_runs ?? 0 }}<span class="text-slate-300">/</span>{{ $fixture->home_wickets ?? 0 }}
                                            </p>

                                            <p class="mt-1 text-[11px] font-medium text-slate-400">
                                                {{ $fixture->home_overs ?? '—' }} overs
                                            </p>
                                        </div>
                                    @endif

                                </div>

                                {{-- VS / Result --}}
                                <div class="flex min-w-[48px] flex-col items-center justify-center pt-9 sm:min-w-[60px]">

                                    @if ($hasResult)
                                        <span class="rounded-lg bg-emerald-50 px-2.5 py-2 text-[9px] font-extrabold uppercase tracking-wider text-emerald-700 sm:text-[10px]">
                                            Result
                                        </span>
                                    @elseif ($displayStatus === 'LIVE')
                                        <span class="rounded-lg bg-red-50 px-2.5 py-2 text-[9px] font-extrabold uppercase tracking-wider text-red-700 sm:text-[10px]">
                                            Live
                                        </span>
                                    @else
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full border border-slate-100 bg-slate-50">
                                            <span class="text-[10px] font-black tracking-wider text-slate-400">
                                                VS
                                            </span>
                                        </div>
                                    @endif

                                </div>

                                {{-- Away team --}}
                                <div class="min-w-0 text-center">

                                    <div class="relative mx-auto flex h-24 w-24 max-w-full items-center justify-center rounded-2xl border border-slate-100 bg-gradient-to-br from-slate-50 to-white p-3 transition duration-300 group-hover:border-[#DCE1F5] sm:h-28 sm:w-28 sm:p-4">

                                        @if ($awayLogo)
                                            <img
                                                src="{{ $awayLogo }}"
                                                alt="{{ $awayTeam?->name ?? $awayTeamName }} logo"
                                                loading="lazy"
                                                decoding="async"
                                                class="h-full w-full object-contain transition duration-300 group-hover:scale-105"
                                                onerror="this.style.display='none';this.nextElementSibling.classList.remove('hidden');"
                                            >

                                            <span class="hidden text-xl font-black tracking-tight text-[#202766]">
                                                {{ $awayInitials }}
                                            </span>
                                        @else
                                            <span class="text-xl font-black tracking-tight text-[#202766]">
                                                {{ $awayInitials }}
                                            </span>
                                        @endif

                                    </div>

                                    <h3 class="mx-auto mt-4 max-w-[180px] break-words text-sm font-extrabold leading-5 text-slate-900 sm:text-base">
                                        {{ $awayTeamName }}
                                    </h3>

                                    @if ($hasResult)
                                        <div class="mt-4">
                                            <p class="text-2xl font-black tracking-tight text-[#172554] sm:text-3xl">
                                                {{ $fixture->away_runs ?? 0 }}<span class="text-slate-300">/</span>{{ $fixture->away_wickets ?? 0 }}
                                            </p>

                                            <p class="mt-1 text-[11px] font-medium text-slate-400">
                                                {{ $fixture->away_overs ?? '—' }} overs
                                            </p>
                                        </div>
                                    @endif

                                </div>

                            </div>

                            {{-- Match outcome --}}
                            @if ($hasResult && $matchHeadline)
                                <div class="mt-7 flex items-start gap-3 rounded-xl border border-emerald-100 bg-emerald-50/70 p-4">
                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24"
                                             fill="none" stroke="currentColor" stroke-width="2.2"
                                             aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="m5 12 4 4L19 6"/>
                                        </svg>
                                    </div>

                                    <div class="min-w-0">
                                        <p class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-700">
                                            Match outcome
                                        </p>

                                        <p class="mt-1 break-words text-sm font-semibold leading-6 text-emerald-900">
                                            {{ $matchHeadline }}
                                        </p>
                                    </div>
                                </div>

                            @elseif ($isPast && !$isVerified && !$isSpecialStatus)
                                <div class="mt-6 flex items-start gap-3 rounded-xl border border-amber-100 bg-amber-50/70 p-4">
                                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-amber-700"
                                         viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor" stroke-width="1.8"
                                         aria-hidden="true">
                                        <circle cx="12" cy="12" r="9"/>
                                        <path d="M12 8v4m0 4h.01"/>
                                    </svg>

                                    <div>
                                        <p class="text-xs font-bold text-amber-900">
                                            Result awaiting confirmation
                                        </p>

                                        <p class="mt-1 text-xs leading-5 text-amber-800">
                                            The scheduled date has passed, but a verified result is not available yet.
                                        </p>
                                    </div>
                                </div>
                            @endif

                            {{-- Postponement and abandonment details --}}
                            @if ($isSpecialStatus)
                                <div class="mt-6 rounded-xl border border-slate-200 bg-slate-50 p-4">
                                    <p class="text-sm font-bold text-slate-800">
                                        Match {{ strtolower($displayStatus) }}
                                    </p>

                                    <p class="mt-1 text-xs leading-5 text-slate-500">
                                        Check the match centre for the latest available scheduling information.
                                    </p>
                                </div>
                            @endif

                        </div>

                        {{-- Match metadata footer --}}
                        <div class="border-t border-slate-100 bg-[#FAFBFE] px-5 py-4 sm:px-6">

                            <div class="flex flex-wrap items-center justify-between gap-4">

                                <div class="flex min-w-0 items-start gap-3">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-100 bg-white text-[#202766]">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24"
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
                                            {{ $timeLabel ?: 'Time to be announced' }}
                                        </p>

                                        <div class="mt-2 flex items-start gap-1.5 text-xs text-slate-500">
                                            <svg class="mt-0.5 h-3.5 w-3.5 shrink-0"
                                                 viewBox="0 0 24 24"
                                                 fill="none" stroke="currentColor" stroke-width="1.8"
                                                 aria-hidden="true">
                                                <path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"/>
                                                <circle cx="12" cy="10" r="2.5"/>
                                            </svg>

                                            <span class="break-words">
                                                {{ $fixture->venue ?: 'Venue to be announced' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <span class="inline-flex shrink-0 items-center gap-2 text-xs font-extrabold text-[#202766] transition group-hover:text-[#F06445]">
                                    Match details

                                    <svg class="h-4 w-4 transition-transform duration-200 group-hover:translate-x-1"
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

                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-[#EEF1FF] text-[#202766]">
                            <svg class="h-7 w-7" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="1.6"
                                 aria-hidden="true">
                                <rect x="3" y="5" width="18" height="16" rx="2"/>
                                <path d="M16 3v4M8 3v4M3 10h18"/>
                            </svg>
                        </div>

                        <h3 class="mt-5 text-xl font-extrabold text-slate-900">
                            No matches found
                        </h3>

                        <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500">
                            No fixtures match your current filters. Try a different
                            season or reset your filters to see all available matches.
                        </p>

                        <a href="{{ route('fixtures.index') }}"
                           class="mt-6 inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-[#202766] px-5 text-sm font-bold text-white transition hover:bg-[#171D50]">
                            <svg class="h-4 w-4" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="2"
                                 aria-hidden="true">
                                <path d="M3 12a9 9 0 1 0 3-6.7L3 8"/>
                                <path d="M3 3v5h5"/>
                            </svg>
                            Reset filters
                        </a>

                    </div>

                @endforelse

            </div>

        </section>

        {{-- Pagination --}}
        @if ($fixtures->hasPages())
            <div class="mt-8 rounded-2xl border border-slate-200 bg-white px-4 py-5 shadow-sm sm:px-6">
                {{ $fixtures->withQueryString()->links() }}
            </div>
        @endif

        {{-- Information footer --}}
        <div class="mt-8 flex items-start gap-3 rounded-xl border border-slate-200/80 bg-white p-4 sm:p-5">
            <svg class="mt-0.5 h-5 w-5 shrink-0 text-slate-400"
                 viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="1.8"
                 aria-hidden="true">
                <circle cx="12" cy="12" r="9"/>
                <path d="M12 11v5m0-8h.01"/>
            </svg>

            <p class="text-xs leading-6 text-slate-500">
                Fixtures, scores and match outcomes are displayed using information
                recorded by the site's administrators. A result is shown as confirmed
                only when it has been verified and the fixture status is completed.
            </p>
        </div>

    </div>
</div>

@endsection
