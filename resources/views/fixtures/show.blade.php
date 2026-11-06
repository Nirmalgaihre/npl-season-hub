@extends('layouts.app')

@section('title', 'Match Centre | Nepal Premier League')

@section('meta_description', 'Live scorecard, match details and full statistics for the Nepal Premier League.')

@section('content')
@php
$home = $fixture->homeTeam;
$away = $fixture->awayTeam;

$homeName = $home?->name ?? 'Home Team';
$awayName = $away?->name ?? 'Away Team';

$innings = collect($innings ?? [])->sortBy('innings_number')->values();
$scorecards = $scorecards ?? collect();

$status = strtolower($fixture->status ?? 'scheduled');
$isLive = in_array($status, ['live', 'in_progress', 'innings_break']);
$isResult = in_array($status, ['completed', 'finished', 'result']);

$scheduledAt = $fixture->scheduled_at;
$venue = $fixture->venue ?: 'Venue TBA';
$format = strtoupper(str_replace('_', ' ', $fixture->match_type ?? 'T20'));

$homeInning = $innings->first(fn ($item) => (int) $item->batting_team_id === (int) $fixture->home_team_id);
$awayInning = $innings->first(fn ($item) => (int) $item->batting_team_id === (int) $fixture->away_team_id);
$hasInnings = $innings->isNotEmpty();

$statusConfig = match (true) {
$isLive => ['label' => 'LIVE', 'color' => 'bg-[#d93025] text-white', 'dot' => 'bg-white'],
$isResult => ['label' => 'COMPLETED', 'color' => 'bg-emerald-600 text-white', 'dot' => 'bg-white'],
str_contains($status, 'postpon') => ['label' => 'POSTPONED', 'color' => 'bg-amber-400 text-slate-900', 'dot' =>
'bg-slate-900'],
str_contains($status, 'abandon') => ['label' => 'ABANDONED', 'color' => 'bg-slate-500 text-white', 'dot' => 'bg-white'],
default => ['label' => 'UPCOMING', 'color' => 'bg-blue-600 text-white', 'dot' => 'bg-white'],
};

$homeLogo = $home?->logo ? asset('storage/' . ltrim($home->logo, '/')) : null;
$awayLogo = $away?->logo ? asset('storage/' . ltrim($away->logo, '/')) : null;

// Result Logic (Who Won)
$winnerTeam = $fixture->winner ?? null;
$winnerTeamId = data_get($fixture, 'winner_team_id');
$resultText = null;
$isTie = false;

if ($winnerTeamId) {
if ((int) $winnerTeamId === (int) $fixture->home_team_id) $winnerTeam = $home;
elseif ((int) $winnerTeamId === (int) $fixture->away_team_id) $winnerTeam = $away;
}

$firstInning = $innings->first();
$secondInning = $innings->get(1);

if ($isResult && $firstInning && $secondInning) {
$firstRuns = (int) $firstInning->total_runs;
$secondRuns = (int) $secondInning->total_runs;

if ($firstRuns === $secondRuns) {
$isTie = true;
$resultText = 'Match tied';
} elseif (! $winnerTeam) {
if ($secondRuns > $firstRuns) {
$winnerTeam = $secondInning->battingTeam ?? ((int) $secondInning->batting_team_id === (int) $fixture->home_team_id ?
$home : $away);
$remainingWickets = max(0, 10 - (int) $secondInning->wickets);
$resultText = $winnerTeam ? ($remainingWickets > 0 ? $winnerTeam->name . ' won by ' . $remainingWickets . ' wickets' :
$winnerTeam->name . ' won') : null;
} else {
$winnerTeam = $firstInning->battingTeam ?? ((int) $firstInning->batting_team_id === (int) $fixture->home_team_id ? $home
: $away);
$resultText = $winnerTeam ? $winnerTeam->name . ' won by ' . ($firstRuns - $secondRuns) . ' runs' : null;
}
}
}

if ($isResult && $winnerTeam && ! $isTie) {
$resultText = $resultText ?: $winnerTeam->name . ' won the match';
}

$homeWon = $winnerTeam && (int)$winnerTeam->id === (int)$fixture->home_team_id;
$awayWon = $winnerTeam && (int)$winnerTeam->id === (int)$fixture->away_team_id;

$nplLogo = 'https://blog.nirmalgaihre.com.np/images/npl.png';
@endphp

<div class="bg-[#f8f9fa] text-[#202124] font-sans min-h-screen">

    {{-- ============================================================
         PREMIUM OFFICIAL PAGE HEADER
    ============================================================ --}}
    <section class="relative overflow-hidden bg-npl-navy text-white">
        {{-- Abstract Background Pattern --}}
        <div class="absolute inset-0 opacity-[0.04]"
            style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 24px 24px;">
        </div>

        {{-- Accent Gradients --}}
        <div
            class="absolute -top-32 -right-32 h-[500px] w-[500px] rounded-full bg-npl-red/20 blur-3xl pointer-events-none">
        </div>
        <div
            class="absolute -bottom-32 -left-32 h-[400px] w-[400px] rounded-full bg-npl-gold/10 blur-3xl pointer-events-none">
        </div>

        <div class="npl-container relative py-8 sm:py-10">
            <div class="flex flex-col items-start gap-6 sm:flex-row sm:items-center sm:justify-between">

                {{-- Brand Block --}}
                <div class="flex flex-col items-start gap-5 sm:flex-row sm:items-center sm:gap-6">
                    {{-- NPL Crest (Floating with Glow) --}}
                    <div class="relative shrink-0 hidden sm:block">
                        <div class="absolute inset-0 rounded-xl bg-white/20 blur-md transform scale-105"></div>
                        <div
                            class="relative flex h-20 w-20 items-center justify-center rounded-xl bg-white p-2 shadow-2xl ring-1 ring-white/10">
                            <img src="{{ $nplLogo }}" alt="Nepal Premier League" class="h-full w-full object-contain">
                        </div>
                    </div>

                    {{-- Title & Kicker --}}
                    <div class="min-w-0">
                        <a href="{{ route('fixtures.index') }}"
                            class="mb-2 inline-flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-widest text-white/60 hover:text-white transition">
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                            </svg>
                            All Matches
                        </a>

                        <h1 class="text-3xl font-black uppercase leading-none tracking-tight text-white sm:text-4xl">
                            Match Centre<span class="text-[#d93025]">.</span>
                        </h1>

                        <p class="mt-2 text-sm font-medium text-white/70">
                            {{ $fixture->season?->name ?? 'Nepal Premier League' }}
                            <span class="mx-1.5 text-white/30">/</span>
                            Match {{ $fixture->match_number ?: '—' }}
                            <span class="mx-1.5 text-white/30">/</span>
                            {{ $format }}
                        </p>
                    </div>
                </div>

                {{-- Status Badge --}}
                <div class="flex items-center gap-3">
                    <span
                        class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-[11px] font-black tracking-widest shadow-lg {{ $statusConfig['color'] }}">
                        @if($isLive)
                        <span class="h-2 w-2 animate-pulse rounded-full {{ $statusConfig['dot'] }}"></span>
                        @endif
                        {{ $statusConfig['label'] }}
                    </span>
                </div>

            </div>
        </div>
    </section>

    {{-- ============================================================
         MAIN SCOREBOARD (Broadcast Style)
    ============================================================ --}}
    <div class="max-w-[1000px] mx-auto px-4 -mt-6 sm:-mt-8 relative z-10">

        <section class="overflow-hidden rounded-2xl border border-slate-800 bg-[#111827] text-white shadow-2xl">

            {{-- Scoreboard Header --}}
            <div
                class="flex flex-col justify-between gap-3 border-b border-white/10 px-5 py-4 sm:flex-row sm:items-center sm:px-8">
                <div class="flex items-center gap-2">
                    <span class="text-[10px] font-black uppercase tracking-[0.2em] text-[#d93025]">Official
                        Scorecard</span>
                </div>
                <div class="flex items-center gap-2 text-xs font-medium text-slate-400">
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 2" />
                        <circle cx="12" cy="12" r="9" />
                    </svg>
                    {{ $scheduledAt?->format('d M Y, h:i A') ?? 'Time TBA' }} NPT
                </div>
            </div>

            {{-- Teams & Scores --}}
            <div class="grid grid-cols-1 sm:grid-cols-[1fr_auto_1fr]">

                {{-- Home Team --}}
                <div
                    class="flex min-w-0 flex-col items-center sm:flex-row sm:items-center gap-4 px-5 py-8 sm:px-8 text-center sm:text-left">
                    <div
                        class="flex h-20 w-20 shrink-0 items-center justify-center rounded-2xl border border-white/10 bg-white p-2 shadow-sm sm:h-24 sm:w-24">
                        @if ($homeLogo)
                        <img src="{{ $homeLogo }}" alt="{{ $homeName }}" class="h-full w-full object-contain"
                            onerror="this.style.display='none'">
                        @else
                        <span class="text-xl font-black text-slate-900">{{ strtoupper(substr($homeName, 0, 3)) }}</span>
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-slate-400">Home Team</p>
                        <h3 @class([ 'mt-1 break-words text-lg leading-tight sm:text-xl transition-colors'
                            , 'font-black text-white'=> $homeWon || !$isResult,
                            'font-semibold text-slate-300' => !$homeWon && $isResult,
                            ])>
                            {{ $homeName }}
                        </h3>
                        @if ($homeInning)
                        <div class="mt-3 flex flex-wrap items-baseline justify-center sm:justify-start gap-x-2">
                            <span @class([ 'text-4xl font-black tracking-tight sm:text-5xl' , 'text-white'=> $homeWon ||
                                !$isResult,
                                'text-slate-400' => !$homeWon && $isResult,
                                ])>{{ $homeInning->total_runs ?? 0 }}</span>
                            <span class="text-2xl font-bold text-slate-500">/{{ $homeInning->wickets ?? 0 }}</span>
                        </div>
                        <p class="mt-1 text-xs font-semibold text-slate-400">{{ $homeInning->overs_display ?? '0.0' }}
                            overs</p>
                        @else
                        <p class="mt-3 text-sm font-semibold text-slate-400">
                            {{ in_array($status, ['scheduled', 'upcoming'], true) ? 'Yet to bat' : 'Score unavailable' }}
                        </p>
                        @endif
                    </div>
                </div>

                {{-- VS Divider --}}
                <div
                    class="flex items-center justify-center border-y border-white/10 bg-white/[0.03] px-5 py-4 sm:border-x sm:border-y-0 sm:px-6">
                    <div class="text-center">
                        <span class="text-2xl font-black italic tracking-widest text-slate-600">VS</span>
                        <p @class([ 'mt-1 text-[9px] font-black uppercase tracking-widest' , 'text-[#d93025]'=> $isLive,
                            'text-emerald-400' => $isResult,
                            'text-slate-500' => !$isLive && !$isResult,
                            ])>
                            {{ $isLive ? 'In Play' : ($isResult ? 'Full Time' : 'Match Day') }}
                        </p>
                    </div>
                </div>

                {{-- Away Team --}}
                <div
                    class="flex min-w-0 flex-col items-center sm:flex-row sm:items-center gap-4 px-5 py-8 sm:px-8 text-center sm:text-left">
                    <div
                        class="flex h-20 w-20 shrink-0 items-center justify-center rounded-2xl border border-white/10 bg-white p-2 shadow-sm sm:h-24 sm:w-24">
                        @if ($awayLogo)
                        <img src="{{ $awayLogo }}" alt="{{ $awayName }}" class="h-full w-full object-contain"
                            onerror="this.style.display='none'">
                        @else
                        <span class="text-xl font-black text-slate-900">{{ strtoupper(substr($awayName, 0, 3)) }}</span>
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-slate-400">Away Team</p>
                        <h3 @class([ 'mt-1 break-words text-lg leading-tight sm:text-xl transition-colors'
                            , 'font-black text-white'=> $awayWon || !$isResult,
                            'font-semibold text-slate-300' => !$awayWon && $isResult,
                            ])>
                            {{ $awayName }}
                        </h3>
                        @if ($awayInning)
                        <div class="mt-3 flex flex-wrap items-baseline justify-center sm:justify-start gap-x-2">
                            <span @class([ 'text-4xl font-black tracking-tight sm:text-5xl' , 'text-white'=> $awayWon ||
                                !$isResult,
                                'text-slate-400' => !$awayWon && $isResult,
                                ])>{{ $awayInning->total_runs ?? 0 }}</span>
                            <span class="text-2xl font-bold text-slate-500">/{{ $awayInning->wickets ?? 0 }}</span>
                        </div>
                        <p class="mt-1 text-xs font-semibold text-slate-400">{{ $awayInning->overs_display ?? '0.0' }}
                            overs</p>
                        @else
                        <p class="mt-3 text-sm font-semibold text-slate-400">
                            {{ in_array($status, ['scheduled', 'upcoming'], true) ? 'Yet to bat' : 'Score unavailable' }}
                        </p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Match Result --}}
            @if ($isResult)
            <div class="border-t border-white/10 bg-white/[0.04] px-5 py-5 sm:px-8">
                <div class="flex items-start gap-3">
                    <div @class([ 'flex h-10 w-10 shrink-0 items-center justify-center rounded-xl'
                        , 'bg-amber-400/15 text-amber-300'=> $isTie,
                        'bg-emerald-400/15 text-emerald-400' => !$isTie,
                        ])>
                        @if ($isTie)
                        <span class="text-xl font-black">=</span>
                        @else
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                        </svg>
                        @endif
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Official Match
                            Result</p>
                        <p class="mt-1 text-base font-black text-white sm:text-lg">
                            {{ $resultText ?? 'Match completed — result unavailable' }}
                        </p>
                    </div>
                </div>
            </div>
            @elseif (in_array($status, ['scheduled', 'upcoming'], true) && ! $hasInnings)
            <div class="border-t border-white/10 bg-white/[0.04] px-5 py-4 sm:px-8">
                <p class="text-sm font-bold text-white">Match not started</p>
                <p class="mt-1 text-xs text-slate-400">
                    {{ $scheduledAt ? 'Scheduled for ' . $scheduledAt->format('d M Y, h:i A') : 'Match date and time will be announced.' }}
                </p>
            </div>
            @endif
        </section>

        {{-- ============================================================
             MATCH INFO CARDS
        ============================================================ --}}
        <section class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-3">
            <div class="flex items-start gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-[#e8f0fe] text-[#1a73e8]">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <rect x="3" y="5" width="18" height="16" rx="2" />
                        <path stroke-linecap="round" d="M16 3v4M8 3v4M3 10h18" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Date & Time</p>
                    <p class="mt-1.5 text-sm font-extrabold text-[#202124]">
                        {{ $scheduledAt?->format('d M Y') ?? 'Date TBA' }}</p>
                    <p class="mt-0.5 text-xs text-gray-500">{{ $scheduledAt?->format('h:i A') ?? 'Time TBA' }}</p>
                </div>
            </div>

            <div class="flex items-start gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-[#e8f0fe] text-[#1a73e8]">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z" />
                        <circle cx="12" cy="10" r="2.5" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Venue</p>
                    <p class="mt-1.5 break-words text-sm font-extrabold text-[#202124]">{{ $venue }}</p>
                    <p class="mt-0.5 text-xs text-gray-500">Match location</p>
                </div>
            </div>

            <div class="flex items-start gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-[#e8f0fe] text-[#1a73e8]">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <circle cx="12" cy="12" r="9" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 2" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Match Format</p>
                    <p class="mt-1.5 text-sm font-extrabold text-[#202124]">{{ $format }}</p>
                    <p class="mt-0.5 text-xs text-gray-500">Match {{ $fixture->match_number ?: '—' }}</p>
                </div>
            </div>
        </section>

        {{-- ============================================================
             FULL SCORECARD (Innings Breakdown)
        ============================================================ --}}
        <section id="scorecard" class="mt-10 scroll-mt-6 sm:mt-12 mb-16">
            <div class="mb-5 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-[#d93025]">Match Statistics</p>
                    <h2 class="mt-2 text-2xl font-black tracking-tight text-[#202124] sm:text-3xl">Full Scorecard<span
                            class="text-[#d93025]">.</span></h2>
                </div>
                @if ($innings->isNotEmpty())
                <span
                    class="inline-flex w-fit items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-bold text-gray-600 shadow-sm">
                    <span class="h-2 w-2 rounded-full bg-[#d93025]"></span>
                    {{ $innings->count() }} {{ \Illuminate\Support\Str::plural('Innings', $innings->count()) }}
                </span>
                @endif
            </div>

            @forelse ($innings as $inning)
            @php
            $battingTeam = $inning->battingTeam ?? ((int) $inning->batting_team_id === (int) $fixture->home_team_id ?
            $home : $away);
            $legalBalls = (int) ($inning->legal_balls ?? 0);
            $oversText = $inning->overs_display ?? (intdiv($legalBalls, 6) . '.' . ($legalBalls % 6));
            $teamLogo = $battingTeam?->logo ? asset('storage/' . ltrim($battingTeam->logo, '/')) : null;
            @endphp

            <article class="mb-5 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                {{-- Innings Header --}}
                <div
                    class="flex flex-col justify-between gap-4 border-b border-gray-100 bg-[#f8f9fa] px-5 py-4 sm:flex-row sm:items-center sm:px-6">
                    <div class="flex min-w-0 items-center gap-3">
                        <span
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#202124] text-sm font-black text-white">
                            {{ str_pad((string) ($inning->innings_number ?? 1), 2, '0', STR_PAD_LEFT) }}
                        </span>
                        @if ($teamLogo)
                        <img src="{{ $teamLogo }}" alt="{{ $battingTeam?->name ?? 'Team' }} logo"
                            class="h-10 w-10 shrink-0 rounded-lg border border-gray-200 bg-white p-1 object-contain"
                            onerror="this.style.display='none'">
                        @endif
                        <div class="min-w-0">
                            <p class="text-[10px] font-black uppercase tracking-wider text-[#d93025]">Innings
                                {{ $inning->innings_number ?? 1 }}</p>
                            <h3 class="mt-0.5 break-words text-base font-black text-[#202124]">
                                {{ $battingTeam?->name ?? 'Batting Team' }}</h3>
                        </div>
                    </div>
                    <div class="sm:text-right">
                        <p class="text-2xl font-black tracking-tight text-[#202124]">
                            {{ $inning->total_runs ?? 0 }} <span class="text-gray-300">/</span>
                            {{ $inning->wickets ?? 0 }}
                        </p>
                        <p class="mt-0.5 text-xs font-semibold text-gray-500">{{ $oversText }} overs</p>
                    </div>
                </div>

                {{-- Scorecard Partial --}}
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
                    <div
                        class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-gray-100 text-gray-400">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                            <rect x="4" y="3" width="16" height="18" rx="2" />
                            <path stroke-linecap="round" d="M8 8h8M8 12h8M8 16h4" />
                        </svg>
                    </div>
                    <h4 class="mt-4 text-base font-black text-[#202124]">Detailed scorecard unavailable</h4>
                    <p class="mx-auto mt-1.5 max-w-md text-sm leading-6 text-gray-500">Batting and bowling figures will
                        appear here once the innings statistics have been entered.</p>
                </div>
                @endif
            </article>
            @empty
            <div class="rounded-2xl border border-dashed border-gray-300 bg-white px-5 py-14 text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-100 text-gray-500">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                        <rect x="4" y="3" width="16" height="18" rx="2" />
                        <path stroke-linecap="round" d="M8 8h8M8 12h8M8 16h4" />
                    </svg>
                </div>
                <h3 class="mt-4 text-lg font-black text-[#202124]">No scorecard available</h3>
                <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-gray-500">Match statistics will appear here when
                    innings and player figures are available.</p>
            </div>
            @endforelse
        </section>
    </div>
</div>
@endsection