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

    $format = strtoupper(str_replace(
        '_',
        ' ',
        $fixture->match_type ?? 'T20'
    ));

    $homeInning = $innings->first(
        fn ($item) => (int) $item->batting_team_id === (int) $fixture->home_team_id
    );

    $awayInning = $innings->first(
        fn ($item) => (int) $item->batting_team_id === (int) $fixture->away_team_id
    );

    $hasInnings = $innings->isNotEmpty();

    $scheduledAt = $fixture->scheduled_at;
    $venue = $fixture->venue ?: 'Venue TBA';

    $statusConfig = match ($status) {
        'live' => [
            'label' => 'LIVE',
            'class' => 'bg-red-50 text-red-700 border-red-200',
        ],
        'completed' => [
            'label' => 'COMPLETED',
            'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        ],
        'postponed' => [
            'label' => 'POSTPONED',
            'class' => 'bg-amber-50 text-amber-800 border-amber-200',
        ],
        'abandoned' => [
            'label' => 'ABANDONED',
            'class' => 'bg-slate-100 text-slate-600 border-slate-200',
        ],
        default => [
            'label' => 'UPCOMING',
            'class' => 'bg-blue-50 text-blue-700 border-blue-200',
        ],
    };

    /*
    |--------------------------------------------------------------------------
    | Winner calculation
    |--------------------------------------------------------------------------
    | Prefer a saved winner. Otherwise calculate only when both innings exist.
    */

    $winnerTeam = $fixture->winner ?? null;
    $winnerText = null;
    $resultText = null;
    $isTie = false;

    $winnerTeamId = data_get($fixture, 'winner_team_id');

    if ($winnerTeamId) {
        if ((int) $winnerTeamId === (int) $fixture->home_team_id) {
            $winnerTeam = $home;
        } elseif ((int) $winnerTeamId === (int) $fixture->away_team_id) {
            $winnerTeam = $away;
        }
    }

    $firstInning = $innings->first();
    $secondInning = $innings->count() >= 2
        ? $innings->get(1)
        : null;

    if ($status === 'completed' && $firstInning && $secondInning) {
        $firstRuns = (int) $firstInning->total_runs;
        $secondRuns = (int) $secondInning->total_runs;

        if ($firstRuns === $secondRuns) {
            $isTie = true;
            $resultText = 'Match tied';
        } elseif (! $winnerTeam) {
            if ($secondRuns > $firstRuns) {
                $winnerTeam = $secondInning->battingTeam
                    ?? ((int) $secondInning->batting_team_id === (int) $fixture->home_team_id
                        ? $home
                        : $away);

                $wicketsRemaining = max(
                    0,
                    10 - (int) $secondInning->wickets
                );

                $resultText = $wicketsRemaining > 0
                    ? $winnerTeam->name . ' won by ' . $wicketsRemaining . ' wickets'
                    : $winnerTeam->name . ' won';
            } else {
                $winnerTeam = $firstInning->battingTeam
                    ?? ((int) $firstInning->batting_team_id === (int) $fixture->home_team_id
                        ? $home
                        : $away);

                $runMargin = $firstRuns - $secondRuns;

                $resultText = $winnerTeam->name . ' won by ' . $runMargin . ' runs';
            }
        }
    }

    if ($status === 'completed' && $winnerTeam) {
        $winnerText = $winnerTeam->name . ' won the match';
        $resultText = $resultText ?: $winnerText;
    }

    $homeLogo = $home?->logo
        ? asset('storage/' . ltrim($home->logo, '/'))
        : null;

    $awayLogo = $away?->logo
        ? asset('storage/' . ltrim($away->logo, '/'))
        : null;
@endphp

<div class="min-h-screen bg-[#f5f7fb] text-slate-900">

    {{-- TOP BAR --}}
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
            <a href="{{ route('fixtures.index') }}"
               class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 transition hover:text-[#202766]">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6"/>
                </svg>
                Fixtures
            </a>

            <div class="flex items-center gap-2 text-xs font-bold text-slate-500">
                <span class="hidden sm:inline">NPL SEASON HUB</span>
                <span class="h-1 w-1 rounded-full bg-slate-300"></span>
                <span class="rounded-md border px-2.5 py-1.5 {{ $statusConfig['class'] }}">
                    {{ $statusConfig['label'] }}
                </span>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 sm:py-9 lg:px-8">

        {{-- PAGE TITLE --}}
        <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#e65d43]">
                    {{ $fixture->season?->name ?? 'Nepal Premier League' }}
                </p>

                <h1 class="mt-2 text-3xl font-black tracking-tight text-[#202766] sm:text-4xl">
                    Match Centre
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
               class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#202766] px-4 py-3 text-sm font-bold text-white transition hover:bg-[#171d50]">
                View scorecard
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m-7-7 7 7 7-7"/>
                </svg>
            </a>
        </div>

        {{-- MAIN MATCH CARD --}}
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

            {{-- CARD HEADER --}}
            <div class="flex flex-col justify-between gap-3 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:px-7">
                <div>
                    <p class="text-sm font-extrabold text-[#202766]">
                        {{ $home?->short_name ?: $homeName }}
                        <span class="mx-1 text-slate-300">vs</span>
                        {{ $away?->short_name ?: $awayName }}
                    </p>
                    <p class="mt-1 text-xs text-slate-500">
                        {{ $venue }}
                    </p>
                </div>

                <div class="flex items-center gap-2 text-xs font-semibold text-slate-500">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="1.7">
                        <circle cx="12" cy="12" r="9"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 2"/>
                    </svg>
                    {{ $scheduledAt?->format('d M Y, h:i A') ?? 'Time TBA' }}
                </div>
            </div>

            {{-- TEAM SCOREBOARD --}}
            <div class="grid grid-cols-1 divide-y divide-slate-100 sm:grid-cols-[1fr_auto_1fr] sm:divide-x sm:divide-y-0">

                {{-- HOME TEAM --}}
                <div class="flex items-center gap-4 px-5 py-6 sm:px-7 sm:py-8">
                    <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-xl border border-slate-100 bg-slate-50 p-2 sm:h-20 sm:w-20">
                        @if ($homeLogo)
                            <img src="{{ $homeLogo }}"
                                 alt="{{ $homeName }} logo"
                                 class="h-full w-full object-contain"
                                 onerror="this.style.display='none'">
                        @else
                            <span class="text-lg font-black text-[#202766]">
                                {{ strtoupper(substr($homeName, 0, 3)) }}
                            </span>
                        @endif
                    </div>

                    <div class="min-w-0 flex-1">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                            Home
                        </p>
                        <h2 class="mt-1 break-words text-base font-extrabold text-slate-800 sm:text-lg">
                            {{ $homeName }}
                        </h2>

                        @if ($homeInning)
                            <p class="mt-2 text-3xl font-black tracking-tight text-[#202766] sm:text-4xl">
                                {{ $homeInning->total_runs ?? 0 }}<span class="text-slate-300">/</span>{{ $homeInning->wickets ?? 0 }}
                            </p>
                            <p class="mt-1 text-xs font-medium text-slate-500">
                                {{ $homeInning->overs_display ?? '0.0' }} overs
                            </p>
                        @else
                            <p class="mt-3 text-sm font-semibold text-slate-400">
                                {{ in_array($status, ['scheduled', 'upcoming'], true) ? 'Yet to bat' : 'Score unavailable' }}
                            </p>
                        @endif
                    </div>
                </div>

                {{-- VS COLUMN --}}
                <div class="flex items-center justify-center bg-slate-50 px-5 py-3 sm:bg-white sm:px-6">
                    <div class="text-center">
                        <span class="text-xs font-black tracking-widest text-slate-300">VS</span>
                        @if ($status === 'live')
                            <p class="mt-1 text-[10px] font-black uppercase tracking-wider text-red-600">
                                In play
                            </p>
                        @elseif ($status === 'completed')
                            <p class="mt-1 text-[10px] font-black uppercase tracking-wider text-emerald-600">
                                Full time
                            </p>
                        @else
                            <p class="mt-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                Fixture
                            </p>
                        @endif
                    </div>
                </div>

                {{-- AWAY TEAM --}}
                <div class="flex items-center gap-4 px-5 py-6 sm:px-7 sm:py-8">
                    <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-xl border border-slate-100 bg-slate-50 p-2 sm:h-20 sm:w-20">
                        @if ($awayLogo)
                            <img src="{{ $awayLogo }}"
                                 alt="{{ $awayName }} logo"
                                 class="h-full w-full object-contain"
                                 onerror="this.style.display='none'">
                        @else
                            <span class="text-lg font-black text-[#e65d43]">
                                {{ strtoupper(substr($awayName, 0, 3)) }}
                            </span>
                        @endif
                    </div>

                    <div class="min-w-0 flex-1">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                            Away
                        </p>
                        <h2 class="mt-1 break-words text-base font-extrabold text-slate-800 sm:text-lg">
                            {{ $awayName }}
                        </h2>

                        @if ($awayInning)
                            <p class="mt-2 text-3xl font-black tracking-tight text-[#202766] sm:text-4xl">
                                {{ $awayInning->total_runs ?? 0 }}<span class="text-slate-300">/</span>{{ $awayInning->wickets ?? 0 }}
                            </p>
                            <p class="mt-1 text-xs font-medium text-slate-500">
                                {{ $awayInning->overs_display ?? '0.0' }} overs
                            </p>
                        @else
                            <p class="mt-3 text-sm font-semibold text-slate-400">
                                {{ in_array($status, ['scheduled', 'upcoming'], true) ? 'Yet to bat' : 'Score unavailable' }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- RESULT BANNER --}}
            @if ($status === 'completed' && ($resultText || $isTie))
                <div class="border-t border-emerald-100 bg-emerald-50 px-5 py-4 sm:px-7">
                    <div class="flex items-start gap-3">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                            @if ($isTie)
                                <span class="text-lg font-black">=</span>
                            @else
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="2.2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6"/>
                                </svg>
                            @endif
                        </div>

                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.15em] text-emerald-700">
                                Match result
                            </p>
                            <p class="mt-1 text-base font-black text-emerald-950 sm:text-lg">
                                {{ $resultText }}
                            </p>
                        </div>
                    </div>
                </div>
            @elseif ($status === 'completed')
                <div class="border-t border-amber-200 bg-amber-50 px-5 py-4 sm:px-7">
                    <p class="text-sm font-bold text-amber-900">
                        Match completed — result unavailable
                    </p>
                    <p class="mt-1 text-sm text-amber-800">
                        Add the official winner or enter both innings to display the result.
                    </p>
                </div>
            @elseif (in_array($status, ['scheduled', 'upcoming'], true) && ! $hasInnings)
                <div class="border-t border-slate-100 bg-slate-50 px-5 py-4 sm:px-7">
                    <p class="text-sm font-bold text-slate-700">Match not started</p>
                    <p class="mt-1 text-sm text-slate-500">
                        {{ $scheduledAt
                            ? 'Scheduled for ' . $scheduledAt->format('d M Y, h:i A')
                            : 'Match date and time will be announced.' }}
                    </p>
                </div>
            @endif
        </section>

        {{-- MATCH DETAILS --}}
        <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-3">
            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Date & time</p>
                <p class="mt-2 text-sm font-extrabold text-slate-800">
                    {{ $scheduledAt?->format('d M Y') ?? 'To be announced' }}
                </p>
                <p class="mt-1 text-xs text-slate-500">
                    {{ $scheduledAt?->format('h:i A') ?? 'Time TBA' }}
                </p>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Venue</p>
                <p class="mt-2 text-sm font-extrabold text-slate-800">{{ $venue }}</p>
                <p class="mt-1 text-xs text-slate-500">Match location</p>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Match format</p>
                <p class="mt-2 text-sm font-extrabold text-slate-800">{{ $format }}</p>
                <p class="mt-1 text-xs text-slate-500">Match {{ $fixture->match_number ?: '—' }}</p>
            </div>
        </div>

        {{-- SCORECARD --}}
        <section id="scorecard" class="mt-10 scroll-mt-6">
            <div class="mb-5 flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-[#e65d43]">
                        Match statistics
                    </p>
                    <h2 class="mt-1 text-2xl font-black tracking-tight text-[#202766] sm:text-3xl">
                        Scorecard
                    </h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Innings-by-innings batting and bowling figures.
                    </p>
                </div>

                @if ($innings->isNotEmpty())
                    <span class="self-start rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-600">
                        {{ $innings->count() }} {{ \Illuminate\Support\Str::plural('innings', $innings->count()) }}
                    </span>
                @endif
            </div>

            @forelse ($innings as $inning)
                @php
                    $battingTeam = $inning->battingTeam
                        ?? ((int) $inning->batting_team_id === (int) $fixture->home_team_id
                            ? $home
                            : $away);

                    $legalBalls = (int) ($inning->legal_balls ?? 0);

                    $oversText = $inning->overs_display
                        ?? (intdiv($legalBalls, 6) . '.' . ($legalBalls % 6));
                @endphp

                <article class="mb-5 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                    <div class="flex flex-col justify-between gap-4 border-b border-slate-100 px-5 py-5 sm:flex-row sm:items-center sm:px-6">
                        <div class="flex items-center gap-3">
                            <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#202766] text-xs font-black text-white">
                                {{ str_pad((string) ($inning->innings_number ?? 1), 2, '0', STR_PAD_LEFT) }}
                            </span>

                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                    Innings {{ $inning->innings_number ?? 1 }}
                                </p>
                                <h3 class="mt-1 text-base font-extrabold text-slate-800">
                                    {{ $battingTeam?->name ?? 'Batting team' }}
                                </h3>
                            </div>
                        </div>

                        <div class="sm:text-right">
                            <p class="text-3xl font-black tracking-tight text-[#202766]">
                                {{ $inning->total_runs ?? 0 }}<span class="text-slate-300">/</span>{{ $inning->wickets ?? 0 }}
                            </p>
                            <p class="mt-1 text-xs font-medium text-slate-500">
                                {{ $oversText }} overs
                            </p>
                        </div>
                    </div>

                    @if ($scorecards->has($inning->id))
                        @include('fixtures.partials.scorecard', [
                            'fixture' => $fixture,
                            'inning' => $inning,
                            'scorecards' => $scorecards,
                        ])
                    @else
                        <div class="px-5 py-10 text-center">
                            <p class="text-sm font-bold text-slate-700">
                                Detailed scorecard unavailable
                            </p>
                            <p class="mt-1 text-sm text-slate-500">
                                Player batting and bowling figures have not been entered for this innings.
                            </p>
                        </div>
                    @endif
                </article>
            @empty
                <div class="rounded-2xl border border-slate-200 bg-white px-5 py-12 text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-slate-100 text-slate-400">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="1.7">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 4.75h14A1.25 1.25 0 0 1 20.25 6v12A1.25 1.25 0 0 1 19 19.25H5A1.25 1.25 0 0 1 3.75 18V6A1.25 1.25 0 0 1 5 4.75Z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 9h8M8 12h8M8 15h4"/>
                        </svg>
                    </div>
                    <h3 class="mt-4 text-lg font-extrabold text-slate-800">
                        No scorecard available
                    </h3>
                    <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500">
                        Manual innings and player statistics will appear here once entered by the administrator.
                    </p>
                </div>
            @endforelse
        </section>

        {{-- FOOTER --}}
        <footer class="mt-10 flex flex-col justify-between gap-4 border-t border-slate-200 py-6 sm:flex-row sm:items-center">
            <p class="text-xs text-slate-400">
                NPL Season Hub · Match information and scorecards
            </p>

            <a href="{{ route('fixtures.index') }}"
               class="inline-flex items-center gap-2 text-sm font-bold text-[#202766] hover:text-[#e65d43]">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6"/>
                </svg>
                Back to fixtures
            </a>
        </footer>
    </main>
</div>
@endsection
