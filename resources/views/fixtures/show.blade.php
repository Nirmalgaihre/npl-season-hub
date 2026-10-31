
@extends('layouts.app')

@section('title', 'Match Centre | NPL Season Hub')

@section('content')
@php
    $home = $fixture->homeTeam;
    $away = $fixture->awayTeam;

    $homeName = $home?->name ?? 'Home Team';
    $awayName = $away?->name ?? 'Away Team';

    $innings = collect($innings ?? [])
        ->sortBy('innings_number')
        ->values();

    $scorecards = $scorecards ?? collect();
    $status = strtolower($fixture->status ?? 'scheduled');
    $scheduledAt = $fixture->scheduled_at;
    $venue = $fixture->venue ?: 'Venue TBA';

    $format = strtoupper(str_replace(
        '_',
        ' ',
        $fixture->match_type ?? 'T20'
    ));

    $homeInning = $innings->first(
        fn ($item) => (int) $item->batting_team_id ===
            (int) $fixture->home_team_id
    );

    $awayInning = $innings->first(
        fn ($item) => (int) $item->batting_team_id ===
            (int) $fixture->away_team_id
    );

    $hasInnings = $innings->isNotEmpty();

    $statusConfig = match ($status) {
        'live' => [
            'label' => 'LIVE',
            'color' => 'bg-red-600 text-white',
        ],
        'completed' => [
            'label' => 'COMPLETED',
            'color' => 'bg-emerald-600 text-white',
        ],
        'postponed' => [
            'label' => 'POSTPONED',
            'color' => 'bg-amber-400 text-slate-950',
        ],
        'abandoned' => [
            'label' => 'ABANDONED',
            'color' => 'bg-slate-500 text-white',
        ],
        default => [
            'label' => 'UPCOMING',
            'color' => 'bg-blue-600 text-white',
        ],
    };

    $homeLogo = $home?->logo
        ? asset('storage/' . ltrim($home->logo, '/'))
        : null;

    $awayLogo = $away?->logo
        ? asset('storage/' . ltrim($away->logo, '/'))
        : null;

    /*
    |--------------------------------------------------------------------------
    | Match result
    |--------------------------------------------------------------------------
    */

    $winnerTeam = $fixture->winner ?? null;
    $winnerTeamId = data_get($fixture, 'winner_team_id');
    $resultText = null;
    $isTie = false;

    if ($winnerTeamId) {
        if ((int) $winnerTeamId === (int) $fixture->home_team_id) {
            $winnerTeam = $home;
        } elseif (
            (int) $winnerTeamId === (int) $fixture->away_team_id
        ) {
            $winnerTeam = $away;
        }
    }

    $firstInning = $innings->first();
    $secondInning = $innings->get(1);

    if (
        $status === 'completed' &&
        $firstInning &&
        $secondInning
    ) {
        $firstRuns = (int) $firstInning->total_runs;
        $secondRuns = (int) $secondInning->total_runs;

        if ($firstRuns === $secondRuns) {
            $isTie = true;
            $resultText = 'Match tied';
        } elseif (! $winnerTeam) {
            if ($secondRuns > $firstRuns) {
                $winnerTeam = $secondInning->battingTeam
                    ?? (
                        (int) $secondInning->batting_team_id ===
                        (int) $fixture->home_team_id
                            ? $home
                            : $away
                    );

                $remainingWickets = max(
                    0,
                    10 - (int) $secondInning->wickets
                );

                $resultText = $winnerTeam
                    ? (
                        $remainingWickets > 0
                            ? $winnerTeam->name .
                                ' won by ' .
                                $remainingWickets .
                                ' wickets'
                            : $winnerTeam->name . ' won'
                    )
                    : null;
            } else {
                $winnerTeam = $firstInning->battingTeam
                    ?? (
                        (int) $firstInning->batting_team_id ===
                        (int) $fixture->home_team_id
                            ? $home
                            : $away
                    );

                $resultText = $winnerTeam
                    ? $winnerTeam->name .
                        ' won by ' .
                        ($firstRuns - $secondRuns) .
                        ' runs'
                    : null;
            }
        }
    }

    if ($status === 'completed' && $winnerTeam && ! $isTie) {
        $resultText = $resultText
            ?: $winnerTeam->name . ' won the match';
    }
@endphp

<div class="npl-match-page min-h-screen bg-[#f3f5f9] text-slate-900">

    {{-- TOP NAVIGATION --}}
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">

            <a href="{{ route('fixtures.index') }}"
               class="inline-flex items-center gap-2 text-sm font-bold text-slate-600 transition hover:text-red-600">

                <svg class="h-5 w-5" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="m15 18-6-6 6-6"/>
                </svg>

                <span>All Matches</span>
            </a>

            <a href="{{ route('fixtures.index') }}"
               class="flex items-center gap-2">

                <span class="flex h-9 w-9 items-center justify-center
                             rounded-xl bg-red-600 text-sm font-black
                             italic text-white shadow-sm">
                    NPL
                </span>

                <span class="hidden text-sm font-black tracking-tight
                             text-slate-900 sm:block">
                    SEASON HUB
                </span>
            </a>

            <span class="inline-flex items-center gap-2 rounded-full
                         px-3 py-2 text-[10px] font-black tracking-wider
                         {{ $statusConfig['color'] }}">

                @if ($status === 'live')
                    <span class="h-2 w-2 animate-pulse rounded-full bg-white"></span>
                @endif

                {{ $statusConfig['label'] }}
            </span>

        </div>
    </header>

    <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 sm:py-10 lg:px-8">

        {{-- PAGE INTRO --}}
        <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">

            <div>
                <p class="text-xs font-black uppercase tracking-[0.2em] text-red-600">
                    {{ $fixture->season?->name ?? 'Nepal Premier League' }}
                </p>

                <h1 class="mt-2 text-3xl font-black tracking-tight
                           text-slate-950 sm:text-4xl">
                    Match Centre<span class="text-red-600">.</span>
                </h1>

                <p class="mt-2 text-sm text-slate-500">
                    Match {{ $fixture->match_number ?: '—' }}
                    <span class="mx-2 text-slate-300">/</span>
                    {{ $format }}
                    <span class="mx-2 text-slate-300">/</span>
                    {{ $scheduledAt?->format('d M Y') ?? 'Date TBA' }}
                </p>
            </div>

            <a href="#scorecard"
               class="inline-flex items-center justify-center gap-2
                      rounded-lg bg-slate-950 px-5 py-3 text-sm
                      font-bold text-white transition hover:bg-red-600">

                View Full Scorecard

                <svg class="h-4 w-4" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 5v14m-7-7 7 7 7-7"/>
                </svg>
            </a>
        </div>

        {{-- MAIN SCOREBOARD --}}
        <section class="overflow-hidden rounded-2xl border border-slate-800
                        bg-[#111827] text-white shadow-xl">

            {{-- SCOREBOARD HEADER --}}
            <div class="flex flex-col justify-between gap-3 border-b
                        border-white/10 px-5 py-4 sm:flex-row
                        sm:items-center sm:px-8">

                <div>
                    <p class="text-[10px] font-black uppercase
                              tracking-[0.2em] text-red-400">
                        Match {{ $fixture->match_number ?: '—' }}
                    </p>

                    <h2 class="mt-1 text-sm font-bold text-white sm:text-base">
                        {{ $homeName }}
                        <span class="mx-1 text-slate-500">vs</span>
                        {{ $awayName }}
                    </h2>
                </div>

                <div class="flex items-center gap-2 text-xs text-slate-400">
                    <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 8v4l3 2"/>
                        <circle cx="12" cy="12" r="9"/>
                    </svg>

                    <span>
                        {{ $scheduledAt?->format('d M Y, h:i A') ?? 'Time TBA' }}
                    </span>
                </div>
            </div>

            {{-- TEAMS AND SCORES --}}
            <div class="grid grid-cols-1 sm:grid-cols-[1fr_auto_1fr]">

                {{-- HOME TEAM --}}
                <div class="flex min-w-0 items-center gap-4 px-5 py-7
                            sm:gap-5 sm:px-8 sm:py-10">

                    <div class="flex h-[76px] w-[76px] shrink-0 items-center
                                justify-center rounded-2xl border border-white/10
                                bg-white p-2 sm:h-24 sm:w-24">

                        @if ($homeLogo)
                            <img src="{{ $homeLogo }}"
                                 alt="{{ $homeName }} logo"
                                 class="h-full w-full object-contain"
                                 onerror="this.style.display='none'">
                        @else
                            <span class="text-xl font-black text-slate-900">
                                {{ strtoupper(substr($homeName, 0, 3)) }}
                            </span>
                        @endif
                    </div>

                    <div class="min-w-0 flex-1">

                        <p class="text-[10px] font-bold uppercase
                                  tracking-[0.16em] text-slate-400">
                            Home Team
                        </p>

                        <h3 class="mt-1 break-words text-base font-black
                                   leading-tight text-white sm:text-lg">
                            {{ $homeName }}
                        </h3>

                        @if ($homeInning)
                            <div class="mt-3 flex flex-wrap items-baseline gap-x-2">
                                <span class="text-4xl font-black
                                             tracking-tight sm:text-5xl">
                                    {{ $homeInning->total_runs ?? 0 }}
                                </span>

                                <span class="text-2xl font-bold text-slate-500">
                                    /{{ $homeInning->wickets ?? 0 }}
                                </span>
                            </div>

                            <p class="mt-1 text-xs font-semibold text-slate-400">
                                {{ $homeInning->overs_display ?? '0.0' }} overs
                            </p>
                        @else
                            <p class="mt-3 text-sm font-semibold text-slate-400">
                                {{ in_array($status, ['scheduled', 'upcoming'], true)
                                    ? 'Yet to bat'
                                    : 'Score unavailable' }}
                            </p>
                        @endif
                    </div>
                </div>

                {{-- VS --}}
                <div class="flex items-center justify-center border-y
                            border-white/10 bg-white/[0.03] px-5 py-4
                            sm:border-x sm:border-y-0 sm:px-6">

                    <div class="text-center">
                        <span class="text-2xl font-black italic
                                     tracking-widest text-slate-600">
                            VS
                        </span>

                        <p class="mt-1 text-[9px] font-black uppercase
                                  tracking-widest
                                  {{ $status === 'live'
                                      ? 'text-red-400'
                                      : ($status === 'completed'
                                          ? 'text-emerald-400'
                                          : 'text-slate-500') }}">
                            {{ $status === 'live'
                                ? 'In Play'
                                : ($status === 'completed'
                                    ? 'Full Time'
                                    : 'Match Day') }}
                        </p>
                    </div>
                </div>

                {{-- AWAY TEAM --}}
                <div class="flex min-w-0 items-center gap-4 px-5 py-7
                            sm:gap-5 sm:px-8 sm:py-10">

                    <div class="flex h-[76px] w-[76px] shrink-0 items-center
                                justify-center rounded-2xl border border-white/10
                                bg-white p-2 sm:h-24 sm:w-24">

                        @if ($awayLogo)
                            <img src="{{ $awayLogo }}"
                                 alt="{{ $awayName }} logo"
                                 class="h-full w-full object-contain"
                                 onerror="this.style.display='none'">
                        @else
                            <span class="text-xl font-black text-slate-900">
                                {{ strtoupper(substr($awayName, 0, 3)) }}
                            </span>
                        @endif
                    </div>

                    <div class="min-w-0 flex-1">

                        <p class="text-[10px] font-bold uppercase
                                  tracking-[0.16em] text-slate-400">
                            Away Team
                        </p>

                        <h3 class="mt-1 break-words text-base font-black
                                   leading-tight text-white sm:text-lg">
                            {{ $awayName }}
                        </h3>

                        @if ($awayInning)
                            <div class="mt-3 flex flex-wrap items-baseline gap-x-2">
                                <span class="text-4xl font-black
                                             tracking-tight sm:text-5xl">
                                    {{ $awayInning->total_runs ?? 0 }}
                                </span>

                                <span class="text-2xl font-bold text-slate-500">
                                    /{{ $awayInning->wickets ?? 0 }}
                                </span>
                            </div>

                            <p class="mt-1 text-xs font-semibold text-slate-400">
                                {{ $awayInning->overs_display ?? '0.0' }} overs
                            </p>
                        @else
                            <p class="mt-3 text-sm font-semibold text-slate-400">
                                {{ in_array($status, ['scheduled', 'upcoming'], true)
                                    ? 'Yet to bat'
                                    : 'Score unavailable' }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- MATCH RESULT --}}
            @if ($status === 'completed')
                <div class="border-t border-white/10 bg-white/[0.04]
                            px-5 py-5 sm:px-8">

                    <div class="flex items-start gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center
                                    justify-center rounded-xl
                                    {{ $isTie
                                        ? 'bg-amber-400/15 text-amber-300'
                                        : 'bg-emerald-400/15 text-emerald-400' }}">

                            @if ($isTie)
                                <span class="text-xl font-black">=</span>
                            @else
                                <svg class="h-5 w-5" viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor"
                                     stroke-width="2.5">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          d="m5 12 4 4L19 6"/>
                                </svg>
                            @endif
                        </div>

                        <div>
                            <p class="text-[10px] font-black uppercase
                                      tracking-[0.18em] text-slate-400">
                                Official Match Result
                            </p>

                            <p class="mt-1 text-base font-black text-white sm:text-lg">
                                {{ $resultText ?? 'Match completed — result unavailable' }}
                            </p>
                        </div>
                    </div>
                </div>

            @elseif (in_array($status, ['scheduled', 'upcoming'], true)
                && ! $hasInnings)

                <div class="border-t border-white/10 bg-white/[0.04]
                            px-5 py-4 sm:px-8">

                    <p class="text-sm font-bold text-white">
                        Match not started
                    </p>

                    <p class="mt-1 text-xs text-slate-400">
                        {{ $scheduledAt
                            ? 'Scheduled for ' . $scheduledAt->format('d M Y, h:i A')
                            : 'Match date and time will be announced.' }}
                    </p>
                </div>
            @endif
        </section>

        {{-- MATCH INFORMATION --}}
        <section class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-3">

            <div class="flex items-start gap-3 rounded-xl border
                        border-slate-200 bg-white p-4">

                <div class="flex h-10 w-10 shrink-0 items-center
                            justify-center rounded-lg bg-red-50 text-red-600">

                    <svg class="h-5 w-5" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="1.8">
                        <rect x="3" y="5" width="18" height="16" rx="2"/>
                        <path stroke-linecap="round" d="M16 3v4M8 3v4M3 10h18"/>
                    </svg>
                </div>

                <div class="min-w-0">
                    <p class="text-[10px] font-black uppercase
                              tracking-widest text-slate-400">
                        Date & Time
                    </p>

                    <p class="mt-2 text-sm font-extrabold text-slate-900">
                        {{ $scheduledAt?->format('d M Y') ?? 'Date TBA' }}
                    </p>

                    <p class="mt-1 text-xs text-slate-500">
                        {{ $scheduledAt?->format('h:i A') ?? 'Time TBA' }}
                    </p>
                </div>
            </div>

            <div class="flex items-start gap-3 rounded-xl border
                        border-slate-200 bg-white p-4">

                <div class="flex h-10 w-10 shrink-0 items-center
                            justify-center rounded-lg bg-red-50 text-red-600">

                    <svg class="h-5 w-5" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"/>
                        <circle cx="12" cy="10" r="2.5"/>
                    </svg>
                </div>

                <div class="min-w-0">
                    <p class="text-[10px] font-black uppercase
                              tracking-widest text-slate-400">
                        Venue
                    </p>

                    <p class="mt-2 break-words text-sm font-extrabold text-slate-900">
                        {{ $venue }}
                    </p>

                    <p class="mt-1 text-xs text-slate-500">
                        Match location
                    </p>
                </div>
            </div>

            <div class="flex items-start gap-3 rounded-xl border
                        border-slate-200 bg-white p-4">

                <div class="flex h-10 w-10 shrink-0 items-center
                            justify-center rounded-lg bg-red-50 text-red-600">

                    <svg class="h-5 w-5" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="1.8">
                        <circle cx="12" cy="12" r="9"/>
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 7v5l3 2"/>
                    </svg>
                </div>

                <div class="min-w-0">
                    <p class="text-[10px] font-black uppercase
                              tracking-widest text-slate-400">
                        Match Format
                    </p>

                    <p class="mt-2 text-sm font-extrabold text-slate-900">
                        {{ $format }}
                    </p>

                    <p class="mt-1 text-xs text-slate-500">
                        Match {{ $fixture->match_number ?: '—' }}
                    </p>
                </div>
            </div>
        </section>

        {{-- SCORECARD --}}
        <section id="scorecard" class="mt-10 scroll-mt-6 sm:mt-12">

            <div class="mb-5 flex flex-col justify-between gap-4
                        sm:flex-row sm:items-end">

                <div>
                    <p class="text-[10px] font-black uppercase
                              tracking-[0.2em] text-red-600">
                        Match Statistics
                    </p>

                    <h2 class="mt-2 text-2xl font-black tracking-tight
                               text-slate-950 sm:text-3xl">
                        Full Scorecard<span class="text-red-600">.</span>
                    </h2>

                    <p class="mt-2 text-sm text-slate-500">
                        Batting, bowling and innings details.
                    </p>
                </div>

                @if ($innings->isNotEmpty())
                    <span class="inline-flex w-fit items-center gap-2
                                 rounded-lg border border-slate-200
                                 bg-white px-3 py-2 text-xs font-bold
                                 text-slate-600">

                        <span class="h-2 w-2 rounded-full bg-red-600"></span>

                        {{ $innings->count() }}
                        {{ \Illuminate\Support\Str::plural(
                            'Innings',
                            $innings->count()
                        ) }}
                    </span>
                @endif
            </div>

            @forelse ($innings as $inning)

                @php
                    $battingTeam = $inning->battingTeam
                        ?? (
                            (int) $inning->batting_team_id ===
                            (int) $fixture->home_team_id
                                ? $home
                                : $away
                        );

                    $legalBalls = (int) ($inning->legal_balls ?? 0);

                    $oversText = $inning->overs_display
                        ?? (
                            intdiv($legalBalls, 6) .
                            '.' .
                            ($legalBalls % 6)
                        );

                    $teamLogo = $battingTeam?->logo
                        ? asset(
                            'storage/' .
                            ltrim($battingTeam->logo, '/')
                        )
                        : null;
                @endphp

                <article class="mb-5 overflow-hidden rounded-2xl
                                border border-slate-200 bg-white
                                shadow-sm">

                    {{-- INNINGS HEADER --}}
                    <div class="flex flex-col justify-between gap-4
                                border-b border-slate-200 bg-white
                                px-5 py-5 sm:flex-row sm:items-center
                                sm:px-7">

                        <div class="flex min-w-0 items-center gap-3">

                            <span class="flex h-11 w-11 shrink-0
                                         items-center justify-center
                                         rounded-xl bg-slate-950
                                         text-sm font-black text-white">
                                {{ str_pad(
                                    (string) ($inning->innings_number ?? 1),
                                    2,
                                    '0',
                                    STR_PAD_LEFT
                                ) }}
                            </span>

                            @if ($teamLogo)
                                <img src="{{ $teamLogo }}"
                                     alt="{{ $battingTeam?->name ?? 'Team' }} logo"
                                     class="h-11 w-11 shrink-0 rounded-lg
                                            border border-slate-100
                                            bg-white p-1 object-contain"
                                     onerror="this.style.display='none'">
                            @endif

                            <div class="min-w-0">
                                <p class="text-[10px] font-black uppercase
                                          tracking-[0.15em] text-red-600">
                                    Innings {{ $inning->innings_number ?? 1 }}
                                </p>

                                <h3 class="mt-1 break-words text-base
                                           font-black text-slate-900">
                                    {{ $battingTeam?->name ?? 'Batting Team' }}
                                </h3>
                            </div>
                        </div>

                        <div class="sm:text-right">
                            <p class="text-3xl font-black tracking-tight
                                      text-slate-950">
                                {{ $inning->total_runs ?? 0 }}
                                <span class="text-slate-300">/</span>
                                {{ $inning->wickets ?? 0 }}
                            </p>

                            <p class="mt-1 text-xs font-semibold text-slate-500">
                                {{ $oversText }} overs
                            </p>
                        </div>
                    </div>

                    {{-- EXISTING SCORECARD PARTIAL --}}
                    @if ($scorecards->has($inning->id))

                        <div class="min-w-0">
                            @include('fixtures.partials.scorecard', [
                                'fixture' => $fixture,
                                'inning' => $inning,
                                'scorecards' => $scorecards,
                            ])
                        </div>

                    @else

                        <div class="px-5 py-12 text-center">

                            <div class="mx-auto flex h-12 w-12
                                        items-center justify-center
                                        rounded-xl bg-slate-100
                                        text-slate-400">

                                <svg class="h-6 w-6" viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor"
                                     stroke-width="1.7">
                                    <rect x="4" y="3" width="16" height="18" rx="2"/>
                                    <path stroke-linecap="round"
                                          d="M8 8h8M8 12h8M8 16h4"/>
                                </svg>
                            </div>

                            <h4 class="mt-4 text-base font-black text-slate-800">
                                Detailed scorecard unavailable
                            </h4>

                            <p class="mx-auto mt-2 max-w-md text-sm
                                      leading-6 text-slate-500">
                                Batting and bowling figures will appear here
                                once the innings statistics have been entered.
                            </p>
                        </div>

                    @endif
                </article>

            @empty

                <div class="rounded-2xl border border-dashed
                            border-slate-300 bg-white px-5 py-14 text-center">

                    <div class="mx-auto flex h-14 w-14 items-center
                                justify-center rounded-2xl
                                bg-slate-100 text-slate-500">

                        <svg class="h-7 w-7" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor"
                             stroke-width="1.7">
                            <rect x="4" y="3" width="16" height="18" rx="2"/>
                            <path stroke-linecap="round"
                                  d="M8 8h8M8 12h8M8 16h4"/>
                        </svg>
                    </div>

                    <h3 class="mt-4 text-lg font-black text-slate-900">
                        No scorecard available
                    </h3>

                    <p class="mx-auto mt-2 max-w-md text-sm
                              leading-6 text-slate-500">
                        Match statistics will appear here when innings
                        and player figures are available.
                    </p>
                </div>

            @endforelse

        </section>

        {{-- BOTTOM NAVIGATION --}}
        <footer class="mt-10 flex flex-col justify-between gap-4
                       border-t border-slate-200 py-6
                       sm:flex-row sm:items-center">

            <div>
                <p class="text-sm font-black text-slate-800">
                    NPL Season Hub
                </p>

                <p class="mt-1 text-xs text-slate-500">
                    Match information and cricket statistics.
                </p>
            </div>

            <a href="{{ route('fixtures.index') }}"
               class="inline-flex items-center gap-2 text-sm
                      font-bold text-slate-700 transition hover:text-red-600">

                <svg class="h-4 w-4" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="m15 18-6-6 6-6"/>
                </svg>

                Back to all matches
            </a>
        </footer>

    </main>
</div>
@endsection
