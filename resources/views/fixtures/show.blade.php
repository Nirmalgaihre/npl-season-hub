@extends('layouts.app')

@section('title', 'Match Centre | NPL Season Hub')

@section('content')
@php
    $home = $fixture->homeTeam;
    $away = $fixture->awayTeam;

    $homeName = $home?->short_name ?: $home?->name ?: 'Home Team';
    $awayName = $away?->short_name ?: $away?->name ?: 'Away Team';

    $innings = $innings ?? collect();
    $scorecards = $scorecards ?? collect();

    $status = strtolower($fixture->status ?? 'scheduled');

    $format = strtoupper(str_replace(
        '_',
        ' ',
        $fixture->match_type ?? 'T20'
    ));

    $statusStyles = [
        'live' => 'bg-red-50 text-red-700 ring-red-200',
        'scheduled' => 'bg-blue-50 text-blue-700 ring-blue-200',
        'upcoming' => 'bg-blue-50 text-blue-700 ring-blue-200',
        'completed' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'postponed' => 'bg-amber-50 text-amber-800 ring-amber-200',
        'abandoned' => 'bg-slate-100 text-slate-700 ring-slate-200',
    ];

    $statusLabels = [
        'live' => 'LIVE',
        'scheduled' => 'UPCOMING',
        'upcoming' => 'UPCOMING',
        'completed' => 'COMPLETED',
        'postponed' => 'POSTPONED',
        'abandoned' => 'ABANDONED',
    ];

    $statusStyle = $statusStyles[$status]
        ?? 'bg-slate-100 text-slate-700 ring-slate-200';

    $statusLabel = $statusLabels[$status] ?? strtoupper($status);

    /*
    |--------------------------------------------------------------------------
    | Find innings using manual scorecard batting team IDs
    |--------------------------------------------------------------------------
    */

    $homeInning = $innings->first(
        fn ($item) =>
            (int) $item->batting_team_id === (int) $fixture->home_team_id
    );

    $awayInning = $innings->first(
        fn ($item) =>
            (int) $item->batting_team_id === (int) $fixture->away_team_id
    );

    /*
    |--------------------------------------------------------------------------
    | Manual scorecards are the only source of score figures
    |--------------------------------------------------------------------------
    */

    $hasInnings = $innings->isNotEmpty();

    $showHomeOfficialScore = false;
    $showAwayOfficialScore = false;
    $showOfficialScores = false;

    $homeScoreAvailable = $homeInning !== null;
    $awayScoreAvailable = $awayInning !== null;

    $homeOvers = $homeInning?->overs_display ?? '—';
    $awayOvers = $awayInning?->overs_display ?? '—';

    /*
    |--------------------------------------------------------------------------
    | Fixture information
    |--------------------------------------------------------------------------
    */

    $scheduledDate = $fixture->scheduled_at?->format('D, d M Y')
        ?? 'Date to be announced';

    $scheduledTime = $fixture->scheduled_at?->format('h:i A')
        ?? 'Time to be announced';

    $venue = $fixture->venue ?: 'Venue to be announced';

    $showNotStartedMessage = in_array(
        $status,
        ['scheduled', 'upcoming'],
        true
    ) && ! $hasInnings;
@endphp

<div class="min-h-screen bg-[#f4f6fa] text-slate-900">

    {{-- TOP NAVIGATION --}}
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">

            <div class="min-w-0">
                <a
                    href="{{ route('fixtures.index') }}"
                    class="inline-flex items-center gap-2 text-sm font-bold text-slate-500 transition hover:text-[#202766]"
                >
                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-4 w-4"
                         viewBox="0 0 24 24"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6"/>
                    </svg>
                    All fixtures
                </a>

                <nav aria-label="Breadcrumb"
                     class="mt-2 flex flex-wrap items-center gap-2 text-xs text-slate-400">
                    <a href="{{ route('home') }}" class="transition hover:text-slate-700">
                        Home
                    </a>

                    <span>/</span>

                    <a href="{{ route('fixtures.index') }}" class="transition hover:text-slate-700">
                        Fixtures
                    </a>

                    <span>/</span>

                    <span class="font-bold text-slate-600">
                        Match {{ $fixture->match_number }}
                    </span>
                </nav>
            </div>

            <span class="inline-flex shrink-0 items-center gap-2 rounded-full px-3 py-2 text-[10px] font-black tracking-[0.12em] ring-1 {{ $statusStyle }}">
                @if ($status === 'live')
                    <span class="relative flex h-2 w-2">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-red-500 opacity-60"></span>
                        <span class="relative inline-flex h-2 w-2 rounded-full bg-red-600"></span>
                    </span>
                @else
                    <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                @endif

                {{ $statusLabel }}
            </span>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 sm:py-9 lg:px-8">

        {{-- PAGE HEADING --}}
        <section class="mb-6 sm:mb-8">
            <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-md bg-orange-50 px-2.5 py-1.5 text-[10px] font-black uppercase tracking-[0.16em] text-[#e65d43]">
                        <span class="h-1.5 w-1.5 rounded-full bg-[#e65d43]"></span>
                        {{ $fixture->season?->name ?? 'Nepal Premier League' }}
                    </div>

                    <h1 class="mt-3 text-3xl font-black tracking-tight text-[#202766] sm:text-4xl">
                        Match Centre
                    </h1>

                    <p class="mt-2 text-sm leading-6 text-slate-500">
                        Match {{ $fixture->match_number }}
                        <span class="mx-1.5 text-slate-300">/</span>
                        {{ $format }}
                        <span class="mx-1.5 text-slate-300">/</span>
                        {{ $fixture->scheduled_at?->format('d M Y') ?? 'Date TBA' }}
                    </p>
                </div>

                <a
                    href="#scorecard"
                    class="inline-flex items-center justify-center gap-2 self-start rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 shadow-sm transition hover:border-[#202766] hover:text-[#202766] sm:self-auto"
                >
                    View scorecard

                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-4 w-4"
                         viewBox="0 0 24 24"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m-7-7 7 7 7-7"/>
                    </svg>
                </a>
            </div>
        </section>

        {{-- MAIN SCOREBOARD --}}
        <section class="overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-slate-200">

            {{-- SCOREBOARD HEADER --}}
            <div class="relative overflow-hidden bg-[#202766] px-5 py-5 text-white sm:px-8">
                <div class="pointer-events-none absolute -right-12 -top-20 h-48 w-48 rounded-full border-[24px] border-white/[0.04]"></div>
                <div class="pointer-events-none absolute -right-2 -top-8 h-32 w-32 rounded-full border-[16px] border-white/[0.04]"></div>

                <div class="relative flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-indigo-200">
                            {{ $fixture->season?->name ?? 'NPL Season Hub' }}
                        </p>

                        <h2 class="mt-1 text-lg font-extrabold sm:text-xl">
                            Match {{ $fixture->match_number }}
                        </h2>

                        <div class="mt-2 flex flex-wrap items-center gap-2 text-xs text-indigo-200">
                            <span>{{ $format }} match</span>
                            <span class="text-indigo-400">•</span>
                            <span>{{ $fixture->scheduled_at?->format('d M Y') ?? 'Date TBA' }}</span>
                        </div>
                    </div>

                    <div class="sm:text-right">
                        <div class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/[0.07] px-3 py-2">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="h-4 w-4 text-orange-300"
                                 viewBox="0 0 24 24"
                                 fill="none"
                                 stroke="currentColor"
                                 stroke-width="1.8">
                                <circle cx="12" cy="12" r="9"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 2"/>
                            </svg>

                            <span class="text-xs font-bold text-white">
                                {{ $scheduledTime }}
                            </span>
                        </div>

                        <p class="mt-2 text-xs text-indigo-200">
                            {{ $venue }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- TEAM COMPARISON --}}
            <div class="px-4 py-8 sm:px-8 sm:py-10">
                <div class="grid grid-cols-[minmax(0,1fr)_auto_minmax(0,1fr)] items-center gap-2 sm:gap-8">

                    {{-- HOME TEAM --}}
                    <div class="min-w-0 text-center">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl border border-slate-100 bg-slate-50 p-2 sm:h-24 sm:w-24 sm:rounded-3xl sm:p-3">
                            @if ($home?->logo)
                                <img
                                    src="{{ asset('storage/' . $home->logo) }}"
                                    alt="{{ $homeName }} logo"
                                    class="h-full w-full object-contain"
                                    loading="lazy"
                                >
                            @else
                                <span class="text-base font-black tracking-tight text-[#202766] sm:text-2xl">
                                    {{ strtoupper(substr($homeName, 0, 3)) }}
                                </span>
                            @endif
                        </div>

                        <p class="mt-3 text-[9px] font-black uppercase tracking-[0.16em] text-slate-400 sm:text-[10px]">
                            Home team
                        </p>

                        <h3 class="mx-auto mt-1 max-w-40 break-words text-sm font-black leading-5 text-slate-800 sm:max-w-xs sm:text-xl sm:leading-7">
                            {{ $home?->name ?? 'Home Team' }}
                        </h3>

                        @if ($homeInning)
                            <p class="mt-4 whitespace-nowrap text-3xl font-black tracking-tight text-[#202766] sm:text-5xl">
                                {{ $homeInning->total_runs ?? 0 }}<span class="text-slate-300">/</span>{{ $homeInning->wickets ?? 0 }}
                            </p>

                            <p class="mt-1 text-xs font-semibold text-slate-500 sm:text-sm">
                                {{ $homeOvers }} overs
                            </p>
                        @else
                            <p class="mt-4 text-xs font-semibold text-slate-400 sm:text-sm">
                                {{ in_array($status, ['scheduled', 'upcoming'], true) ? 'Yet to bat' : 'Score unavailable' }}
                            </p>
                        @endif
                    </div>

                    {{-- VS --}}
                    <div class="flex flex-col items-center justify-center">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-[10px] font-black tracking-wider text-slate-400 shadow-sm sm:h-14 sm:w-14 sm:text-xs">
                            VS
                        </div>

                        <span class="mt-2 text-[8px] font-black uppercase tracking-[0.14em] text-slate-400 sm:text-[9px]">
                            {{ $status === 'completed' ? 'Full time' : ($status === 'live' ? 'In play' : 'Fixture') }}
                        </span>
                    </div>

                    {{-- AWAY TEAM --}}
                    <div class="min-w-0 text-center">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl border border-slate-100 bg-slate-50 p-2 sm:h-24 sm:w-24 sm:rounded-3xl sm:p-3">
                            @if ($away?->logo)
                                <img
                                    src="{{ asset('storage/' . $away->logo) }}"
                                    alt="{{ $awayName }} logo"
                                    class="h-full w-full object-contain"
                                    loading="lazy"
                                >
                            @else
                                <span class="text-base font-black tracking-tight text-[#e65d43] sm:text-2xl">
                                    {{ strtoupper(substr($awayName, 0, 3)) }}
                                </span>
                            @endif
                        </div>

                        <p class="mt-3 text-[9px] font-black uppercase tracking-[0.16em] text-slate-400 sm:text-[10px]">
                            Away team
                        </p>

                        <h3 class="mx-auto mt-1 max-w-40 break-words text-sm font-black leading-5 text-slate-800 sm:max-w-xs sm:text-xl sm:leading-7">
                            {{ $away?->name ?? 'Away Team' }}
                        </h3>

                        @if ($awayInning)
                            <p class="mt-4 whitespace-nowrap text-3xl font-black tracking-tight text-[#202766] sm:text-5xl">
                                {{ $awayInning->total_runs ?? 0 }}<span class="text-slate-300">/</span>{{ $awayInning->wickets ?? 0 }}
                            </p>

                            <p class="mt-1 text-xs font-semibold text-slate-500 sm:text-sm">
                                {{ $awayOvers }} overs
                            </p>
                        @else
                            <p class="mt-4 text-xs font-semibold text-slate-400 sm:text-sm">
                                {{ in_array($status, ['scheduled', 'upcoming'], true) ? 'Yet to bat' : 'Score unavailable' }}
                            </p>
                        @endif
                    </div>
                </div>

                {{-- MATCH STATUS / RESULT --}}
                @if ($status === 'completed' && $hasInnings)
                    <div class="mt-8 flex gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 sm:p-5">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="h-5 w-5"
                                 viewBox="0 0 24 24"
                                 fill="none"
                                 stroke="currentColor"
                                 stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6"/>
                            </svg>
                        </div>

                        <div class="min-w-0">
                            <p class="text-[10px] font-black uppercase tracking-[0.15em] text-emerald-700">
                                Match completed
                            </p>

                            <p class="mt-1 text-sm leading-6 text-slate-600">
                                The scorecard below shows the manually entered innings and player statistics.
                            </p>
                        </div>
                    </div>

                @elseif ($showNotStartedMessage)
                    <div class="mt-8 flex flex-col items-center rounded-2xl border border-slate-200 bg-slate-50 px-4 py-5 text-center sm:flex-row sm:items-center sm:gap-4 sm:text-left">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white text-[#202766] ring-1 ring-slate-200">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="h-5 w-5"
                                 viewBox="0 0 24 24"
                                 fill="none"
                                 stroke="currentColor"
                                 stroke-width="1.7">
                                <circle cx="12" cy="12" r="9"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 2"/>
                            </svg>
                        </div>

                        <div class="mt-3 sm:mt-0">
                            <p class="font-extrabold text-slate-800">
                                Match not started
                            </p>

                            <p class="mt-1 text-sm leading-6 text-slate-500">
                                {{ $fixture->scheduled_at
                                    ? 'Scheduled for ' . $fixture->scheduled_at->format('d M Y, h:i A')
                                    : 'The match date and time have not been announced.' }}
                            </p>
                        </div>
                    </div>

                @elseif ($status === 'completed' && ! $hasInnings)
                    <div class="mt-8 rounded-2xl border border-amber-200 bg-amber-50 p-4 sm:p-5">
                        <p class="font-extrabold text-amber-900">
                            Scorecard not available
                        </p>

                        <p class="mt-1 text-sm leading-6 text-amber-800">
                            No manual innings have been entered for this fixture yet.
                        </p>
                    </div>

                @elseif (in_array($status, ['postponed', 'abandoned'], true))
                    <div class="mt-8 rounded-2xl border border-slate-200 bg-slate-50 p-4 sm:p-5">
                        <p class="font-extrabold text-slate-800">
                            {{ $status === 'postponed' ? 'Match postponed' : 'Match abandoned' }}
                        </p>

                        <p class="mt-1 text-sm leading-6 text-slate-500">
                            Check the fixtures page for match updates.
                        </p>
                    </div>
                @endif
            </div>

            {{-- SCOREBOARD BOTTOM INFORMATION --}}
            <div class="grid grid-cols-1 border-t border-slate-100 bg-slate-50 sm:grid-cols-3">

                <div class="flex items-center gap-3 border-b border-slate-100 px-5 py-4 sm:border-b-0 sm:border-r sm:px-6">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white text-[#202766] ring-1 ring-slate-200">
                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="h-4 w-4"
                             viewBox="0 0 24 24"
                             fill="none"
                             stroke="currentColor"
                             stroke-width="1.7">
                            <rect x="3" y="5" width="18" height="16" rx="2"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 3v4M8 3v4M3 10h18"/>
                        </svg>
                    </div>

                    <div class="min-w-0">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                            Match date
                        </p>
                        <p class="mt-1 text-sm font-extrabold text-slate-800">
                            {{ $fixture->scheduled_at?->format('d M Y') ?? 'To be announced' }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3 border-b border-slate-100 px-5 py-4 sm:border-b-0 sm:border-r sm:px-6">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white text-[#202766] ring-1 ring-slate-200">
                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="h-4 w-4"
                             viewBox="0 0 24 24"
                             fill="none"
                             stroke="currentColor"
                             stroke-width="1.7">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"/>
                            <circle cx="12" cy="10" r="2.5"/>
                        </svg>
                    </div>

                    <div class="min-w-0">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                            Venue
                        </p>
                        <p class="mt-1 break-words text-sm font-extrabold text-slate-800">
                            {{ $venue }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3 px-5 py-4 sm:px-6">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white text-[#202766] ring-1 ring-slate-200">
                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="h-4 w-4"
                             viewBox="0 0 24 24"
                             fill="none"
                             stroke="currentColor"
                             stroke-width="1.7">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6.5h16M4 12h16M4 17.5h16"/>
                        </svg>
                    </div>

                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                            Match format
                        </p>
                        <p class="mt-1 text-sm font-extrabold text-slate-800">
                            {{ $format }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- SCORECARD SECTION --}}
        <section id="scorecard" class="mt-10 scroll-mt-6 sm:mt-12">

            <div class="mb-5 flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-[#e65d43]">
                        Match statistics
                    </p>

                    <h2 class="mt-2 text-2xl font-black tracking-tight text-[#202766] sm:text-3xl">
                        Scorecard
                    </h2>

                    <p class="mt-2 text-sm leading-6 text-slate-500">
                        Batting, bowling and innings statistics.
                    </p>
                </div>

                @if ($innings->isNotEmpty())
                    <span class="inline-flex items-center gap-2 self-start rounded-lg bg-white px-3 py-2 text-xs font-bold text-slate-600 ring-1 ring-slate-200 sm:self-auto">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                        {{ $innings->count() }}
                        {{ \Illuminate\Support\Str::plural('innings', $innings->count()) }}
                    </span>
                @endif
            </div>

            @forelse ($innings as $inning)
                @php
                    $battingTeam = $inning->battingTeam
                        ?? (
                            (int) $inning->batting_team_id === (int) $fixture->home_team_id
                                ? $home
                                : $away
                        );

                    $legalBalls = (int) ($inning->legal_balls ?? 0);

                    $oversText = $inning->overs_display
                        ?? (
                            intdiv($legalBalls, 6)
                            . '.'
                            . ($legalBalls % 6)
                        );
                @endphp

                <div class="mb-6 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">

                    {{-- INNINGS SUMMARY HEADER --}}
                    <div class="flex flex-col justify-between gap-4 border-b border-slate-100 px-4 py-5 sm:flex-row sm:items-center sm:px-6">
                        <div class="flex min-w-0 items-center gap-3">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#202766] text-xs font-black text-white">
                                {{ str_pad((string) ($inning->innings_number ?? 1), 2, '0', STR_PAD_LEFT) }}
                            </div>

                            <div class="min-w-0">
                                <p class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-400">
                                    Innings {{ $inning->innings_number ?? 1 }}
                                </p>

                                <h3 class="mt-1 break-words text-base font-black text-slate-800 sm:text-lg">
                                    {{ $battingTeam?->name ?? 'Batting team' }}
                                </h3>

                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $oversText }} overs
                                </p>
                            </div>
                        </div>

                        <div class="border-t border-slate-100 pt-4 sm:border-0 sm:pt-0 sm:text-right">
                            <p class="text-3xl font-black tracking-tight text-[#202766] sm:text-4xl">
                                {{ $inning->total_runs ?? 0 }}<span class="text-slate-300">/</span>{{ $inning->wickets ?? 0 }}
                            </p>

                            <p class="mt-1 text-xs font-medium text-slate-500">
                                {{ $legalBalls }} legal balls
                            </p>
                        </div>
                    </div>

                    {{-- DETAILED MANUAL SCORECARD --}}
                    @if ($scorecards->has($inning->id))
                        @include('fixtures.partials.scorecard', [
                            'fixture' => $fixture,
                            'inning' => $inning,
                            'scorecards' => $scorecards,
                        ])
                    @else
                        <div class="px-5 py-10 text-center">
                            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-slate-50 text-slate-400 ring-1 ring-slate-200">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="h-5 w-5"
                                     viewBox="0 0 24 24"
                                     fill="none"
                                     stroke="currentColor"
                                     stroke-width="1.7">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 4.75h14A1.25 1.25 0 0 1 20.25 6v12A1.25 1.25 0 0 1 19 19.25H5A1.25 1.25 0 0 1 3.75 18V6A1.25 1.25 0 0 1 5 4.75Z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 9h8M8 12h8M8 15h4"/>
                                </svg>
                            </div>

                            <h4 class="mt-3 text-sm font-extrabold text-slate-800">
                                Detailed scorecard unavailable
                            </h4>

                            <p class="mx-auto mt-1 max-w-sm text-sm leading-6 text-slate-500">
                                Player-wise batting and bowling figures are not available for this innings yet.
                            </p>
                        </div>
                    @endif
                </div>
            @empty
                <div class="rounded-2xl bg-white px-5 py-12 text-center shadow-sm ring-1 ring-slate-200 sm:py-16">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-50 text-[#202766]">
                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="h-7 w-7"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor"
                             stroke-width="1.7">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 4.75h14A1.25 1.25 0 0 1 20.25 6v12A1.25 1.25 0 0 1 19 19.25H5A1.25 1.25 0 0 1 3.75 18V6A1.25 1.25 0 0 1 5 4.75Z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 9h8M8 12h8M8 15h4"/>
                        </svg>
                    </div>

                    <h3 class="mt-4 text-lg font-black text-slate-800">
                        Scorecard not available yet
                    </h3>

                    <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500">
                        Match innings, batting figures and bowling statistics will appear here when manual scorecard data is available.
                    </p>

                    @if (in_array($status, ['scheduled', 'upcoming'], true))
                        <p class="mt-3 text-xs font-semibold text-slate-400">
                            Match scheduled: {{ $scheduledDate }}
                        </p>
                    @endif
                </div>
            @endforelse
        </section>

        {{-- MATCH INFORMATION --}}
        <section class="mt-10 sm:mt-12">
            <div class="mb-5">
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-[#e65d43]">
                    Fixture information
                </p>

                <h2 class="mt-2 text-2xl font-black tracking-tight text-[#202766]">
                    Match details
                </h2>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">

                {{-- DATE --}}
                <article class="group rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 transition hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-400">
                                Date &amp; time
                            </p>

                            <p class="mt-3 font-extrabold text-slate-800">
                                {{ $fixture->scheduled_at?->format('d M Y') ?? 'To be announced' }}
                            </p>

                            <p class="mt-1 text-sm text-slate-500">
                                {{ $fixture->scheduled_at?->format('h:i A') ?? 'Time TBA' }}
                            </p>
                        </div>

                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-[#202766] transition group-hover:bg-[#202766] group-hover:text-white">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="h-5 w-5"
                                 viewBox="0 0 24 24"
                                 fill="none"
                                 stroke="currentColor"
                                 stroke-width="1.7">
                                <rect x="3" y="5" width="18" height="16" rx="2"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 3v4M8 3v4M3 10h18"/>
                            </svg>
                        </div>
                    </div>
                </article>

                {{-- VENUE --}}
                <article class="group rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 transition hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-400">
                                Venue
                            </p>

                            <p class="mt-3 break-words font-extrabold leading-6 text-slate-800">
                                {{ $venue }}
                            </p>

                            <p class="mt-1 text-sm text-slate-500">
                                Match location
                            </p>
                        </div>

                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-orange-50 text-[#e65d43] transition group-hover:bg-[#e65d43] group-hover:text-white">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="h-5 w-5"
                                 viewBox="0 0 24 24"
                                 fill="none"
                                 stroke="currentColor"
                                 stroke-width="1.7">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"/>
                                <circle cx="12" cy="10" r="2.5"/>
                            </svg>
                        </div>
                    </div>
                </article>

                {{-- FORMAT --}}
                <article class="group rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 transition hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-400">
                                Match format
                            </p>

                            <p class="mt-3 font-extrabold text-slate-800">
                                {{ $format }}
                            </p>

                            <p class="mt-1 text-sm text-slate-500">
                                Match {{ $fixture->match_number }}
                            </p>
                        </div>

                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-[#202766] transition group-hover:bg-[#202766] group-hover:text-white">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="h-5 w-5"
                                 viewBox="0 0 24 24"
                                 fill="none"
                                 stroke="currentColor"
                                 stroke-width="1.7">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6.5h16M4 12h16M4 17.5h16"/>
                            </svg>
                        </div>
                    </div>
                </article>
            </div>
        </section>

        {{-- BOTTOM NAVIGATION --}}
        <footer class="mt-10 border-t border-slate-200 pt-6 sm:mt-12">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <a
                    href="{{ route('fixtures.index') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#202766] px-5 py-3.5 text-sm font-extrabold text-white shadow-sm transition hover:bg-[#171d50] focus:outline-none focus:ring-2 focus:ring-[#202766] focus:ring-offset-2"
                >
                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-4 w-4"
                         viewBox="0 0 24 24"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6"/>
                    </svg>

                    All fixtures
                </a>

                <a
                    href="{{ route('home') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-3.5 text-sm font-bold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-300 focus:ring-offset-2"
                >
                    Back to homepage

                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-4 w-4"
                         viewBox="0 0 24 24"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 18l6-6-6-6"/>
                    </svg>
                </a>
            </div>

            <p class="mt-6 text-center text-xs text-slate-400">
                NPL Season Hub
                <span class="mx-1">·</span>
                Match information and scorecards
            </p>
        </footer>

    </main>
</div>
@endsection
