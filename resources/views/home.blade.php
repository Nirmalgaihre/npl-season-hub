{{-- resources/views/home.blade.php --}}

@extends('layouts.app')

@php
    $liveMatches = $liveMatches ?? collect();
    $teams = $teams ?? collect();
    $recentResults = $recentResults ?? collect();
    $latestNews = $latestNews ?? collect();
    $liveScoreByFixture = $liveScoreByFixture ?? collect();
@endphp

@section('content')

<style>
    .hero-pattern {
        background-image:
            radial-gradient(circle at 85% 15%, rgba(240, 100, 69, .18), transparent 26%),
            radial-gradient(circle at 8% 100%, rgba(255, 255, 255, .08), transparent 32%);
    }

    .live-dot {
        width: 8px;
        height: 8px;
        display: inline-block;
        background: #F06445;
        border-radius: 50%;
        animation: livePulse 1.5s infinite;
    }

    @keyframes livePulse {
        0%, 100% {
            opacity: 1;
            box-shadow: 0 0 0 0 rgba(240, 100, 69, .4);
        }

        50% {
            opacity: .75;
            box-shadow: 0 0 0 6px rgba(240, 100, 69, 0);
        }
    }

    .section-anchor {
        scroll-margin-top: 100px;
    }

    .team-logo {
        width: 52px;
        height: 52px;
        object-fit: contain;
    }

    .news-image {
        width: 100%;
        height: 190px;
        object-fit: cover;
    }

    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .line-clamp-3 {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    @media (prefers-reduced-motion: reduce) {
        .live-dot {
            animation: none;
        }

        *,
        *::before,
        *::after {
            scroll-behavior: auto !important;
            animation-duration: .01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: .01ms !important;
        }
    }
</style>

<main class="antialiased bg-[#F6F8FB] text-[#202766]">

    {{-- Hero --}}
    <section class="hero-pattern overflow-hidden bg-[#202766] text-white">
        <div class="mx-auto grid max-w-7xl items-center gap-10 px-4 py-14 sm:px-6 sm:py-20 lg:grid-cols-2 lg:px-8 lg:py-24">

            <div>
                <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3.5 py-2 text-xs font-bold uppercase tracking-wider text-white/90">
                    <span class="h-2 w-2 rounded-full bg-[#F06445]"></span>

                    Nepal Premier League

                    @if(isset($season) && $season)
                        <span class="text-white/50">/</span>
                        <span>{{ $season->name }}</span>
                    @endif
                </div>

                <h1 class="max-w-2xl text-4xl font-extrabold leading-tight tracking-tight sm:text-5xl lg:text-6xl">
                    Every Match.
                    <span class="block text-[#F06445]">Every Moment.</span>
                    All in One Place.
                </h1>

                <p class="mt-6 max-w-xl text-base leading-8 text-slate-200 sm:text-lg">
                    Follow Nepal's cricket action with match fixtures, live scores,
                    verified results, team information and the latest league news.
                </p>

                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('fixtures.index') }}"
                       class="inline-flex items-center gap-2 rounded-xl bg-[#F06445] px-6 py-3.5 text-sm font-bold text-white transition hover:bg-orange-600">
                        Explore Fixtures
                        <span aria-hidden="true">&rarr;</span>
                    </a>

                    <a href="#live-scores"
                       class="inline-flex items-center gap-2 rounded-xl border border-white/25 bg-white/5 px-6 py-3.5 text-sm font-bold text-white transition hover:bg-white/10">
                        View Live Scores
                    </a>
                </div>

                <div class="mt-9 flex flex-wrap items-center gap-x-6 gap-y-3 text-sm text-slate-300">
                    <div class="flex items-center gap-2">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-white/10 text-[#F06445]">
                            &#10003;
                        </span>
                        Match information
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-white/10 text-[#F06445]">
                            &#10003;
                        </span>
                        Verified results
                    </div>
                </div>
            </div>

            {{-- Featured upcoming match --}}
            <div class="relative">
                <div class="absolute -inset-4 rounded-3xl bg-white/5 blur-2xl"></div>

                <div class="relative overflow-hidden rounded-3xl border border-white/15 bg-white p-5 text-[#202766] shadow-2xl sm:p-7">

                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-xs font-extrabold uppercase tracking-[.15em] text-slate-500">
                                Match Centre
                            </p>

                            <h2 class="mt-1 text-xl font-extrabold">
                                Upcoming Match
                            </h2>
                        </div>

                        <span class="rounded-full bg-orange-50 px-3 py-1.5 text-xs font-bold text-[#F06445]">
                            Upcoming
                        </span>
                    </div>

                    @if(isset($nextMatch) && $nextMatch)
                        <div class="mt-6 rounded-2xl bg-[#F6F8FB] p-5 sm:p-6">

                            <div class="text-center">
                                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                                    {{ $nextMatch->season->name ?? 'Nepal Premier League' }}
                                </p>

                                @if($nextMatch->match_number)
                                    <p class="mt-1 text-sm font-semibold text-slate-600">
                                        Match {{ $nextMatch->match_number }}
                                    </p>
                                @endif
                            </div>

                            <div class="mt-6 grid grid-cols-[1fr_auto_1fr] items-center gap-3">

                                {{-- Home team --}}
                                <div class="min-w-0 text-center">
                                    @if($nextMatch->homeTeam?->logo)
                                        <img
                                            src="{{ asset('storage/' . ltrim($nextMatch->homeTeam->logo, '/')) }}"
                                            alt="{{ $nextMatch->homeTeam->name }} logo"
                                            class="team-logo mx-auto"
                                            loading="lazy"
                                        >
                                    @else
                                        <div class="mx-auto flex items-center justify-center rounded-2xl bg-white text-xl font-extrabold shadow-sm"
                                             style="width:52px;height:52px">
                                            {{ strtoupper(substr($nextMatch->homeTeam->name ?? 'H', 0, 1)) }}
                                        </div>
                                    @endif

                                    <p class="mt-3 break-words text-sm font-extrabold sm:text-base">
                                        {{ $nextMatch->homeTeam->name ?? 'Home Team' }}
                                    </p>

                                    <p class="mt-1 text-xs text-slate-500">Home</p>
                                </div>

                                <span class="text-xl font-extrabold text-[#F06445]">VS</span>

                                {{-- Away team --}}
                                <div class="min-w-0 text-center">
                                    @if($nextMatch->awayTeam?->logo)
                                        <img
                                            src="{{ asset('storage/' . ltrim($nextMatch->awayTeam->logo, '/')) }}"
                                            alt="{{ $nextMatch->awayTeam->name }} logo"
                                            class="team-logo mx-auto"
                                            loading="lazy"
                                        >
                                    @else
                                        <div class="mx-auto flex items-center justify-center rounded-2xl bg-white text-xl font-extrabold shadow-sm"
                                             style="width:52px;height:52px">
                                            {{ strtoupper(substr($nextMatch->awayTeam->name ?? 'A', 0, 1)) }}
                                        </div>
                                    @endif

                                    <p class="mt-3 break-words text-sm font-extrabold sm:text-base">
                                        {{ $nextMatch->awayTeam->name ?? 'Away Team' }}
                                    </p>

                                    <p class="mt-1 text-xs text-slate-500">Away</p>
                                </div>
                            </div>

                            <div class="mt-6 border-t border-slate-200 pt-5 text-center">
                                @if($nextMatch->scheduled_at)
                                    <p class="text-sm font-bold">
                                        {{ \Illuminate\Support\Carbon::parse($nextMatch->scheduled_at)->format('D, d M Y') }}
                                    </p>

                                    <p class="mt-1 text-sm text-slate-500">
                                        {{ \Illuminate\Support\Carbon::parse($nextMatch->scheduled_at)->format('h:i A') }}
                                    </p>
                                @else
                                    <p class="text-sm font-semibold text-slate-500">
                                        Match schedule to be announced
                                    </p>
                                @endif
                            </div>
                        </div>

                        <a href="{{ route('fixtures.show', $nextMatch) }}"
                           class="mt-5 flex w-full items-center justify-center gap-2 rounded-xl bg-[#202766] px-5 py-3.5 text-sm font-bold text-white transition hover:bg-[#171D50]">
                            View Match Details
                            <span aria-hidden="true">&rarr;</span>
                        </a>
                    @else
                        <div class="mt-6 rounded-2xl bg-[#F6F8FB] px-5 py-12 text-center">
                            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-2xl text-[#F06445] shadow-sm">
                                &#128197;
                            </div>

                            <h3 class="mt-4 font-extrabold">
                                No Upcoming Match Scheduled
                            </h3>

                            <p class="mx-auto mt-2 max-w-sm text-sm leading-6 text-slate-500">
                                There are currently no upcoming fixtures available for the selected season.
                                Check the fixtures page for updates.
                            </p>

                            <a href="{{ route('fixtures.index') }}"
                               class="mt-5 inline-flex items-center gap-2 text-sm font-bold text-[#F06445] hover:text-[#202766]">
                                Browse fixtures &rarr;
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- Live scores --}}
    <section id="live-scores" class="section-anchor py-14 sm:py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            <div class="mb-7 flex flex-wrap items-end justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 text-xs font-extrabold uppercase tracking-[.16em] text-[#F06445]">
                        <span class="live-dot"></span>
                        Match updates
                    </div>

                    <h2 class="mt-2 text-2xl font-extrabold tracking-tight sm:text-3xl">
                        Live Scores
                    </h2>

                    <p class="mt-2 text-sm text-slate-500">
                        Scores recorded for matches currently marked live.
                    </p>
                </div>

                <a href="{{ route('fixtures.index') }}"
                   class="inline-flex items-center gap-2 text-sm font-bold text-[#202766] transition hover:text-[#F06445]">
                    All Fixtures &rarr;
                </a>
            </div>

            @if($liveMatches->isNotEmpty())
                <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                    @foreach($liveMatches as $match)
                        @php
                            $inningsForMatch = collect($liveScoreByFixture->get($match->id, collect()));

                            $currentInnings = $inningsForMatch->firstWhere('is_current', true)
                                ?? $inningsForMatch->sortByDesc('innings_number')->first();

                            $homeTeamId = $match->home_team_id;
                            $awayTeamId = $match->away_team_id;

                            $homeRuns = $homeWickets = $homeOvers = null;
                            $awayRuns = $awayWickets = $awayOvers = null;

                            foreach ($inningsForMatch as $innings) {
                                $runs = (int) $innings->total_runs;
                                $wickets = (int) $innings->wickets;
                                $legalBalls = (int) $innings->legal_balls;
                                $overs = intdiv($legalBalls, 6) . '.' . ($legalBalls % 6);

                                if ((string) $innings->batting_team_id === (string) $homeTeamId) {
                                    $homeRuns = $runs;
                                    $homeWickets = $wickets;
                                    $homeOvers = $overs;
                                }

                                if ((string) $innings->batting_team_id === (string) $awayTeamId) {
                                    $awayRuns = $runs;
                                    $awayWickets = $wickets;
                                    $awayOvers = $overs;
                                }
                            }

                            $currentRuns = (int) ($currentInnings?->total_runs ?? 0);
                            $currentWickets = (int) ($currentInnings?->wickets ?? 0);
                            $currentLegalBalls = (int) ($currentInnings?->legal_balls ?? 0);
                            $currentOvers = intdiv($currentLegalBalls, 6) . '.' . ($currentLegalBalls % 6);
                        @endphp

                        <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg">

                            <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-4">
                                <div class="flex items-center gap-2">
                                    <span class="live-dot"></span>
                                    <span class="text-xs font-extrabold uppercase tracking-wider text-[#F06445]">
                                        Live
                                    </span>
                                </div>

                                <span class="text-xs font-semibold text-slate-500">
                                    {{ $match->match_number ? 'Match ' . $match->match_number : 'NPL Match' }}
                                </span>
                            </div>

                            <div class="p-5">
                                @if($match->season)
                                    <p class="mb-5 text-xs font-semibold text-slate-500">
                                        {{ $match->season->name }}
                                    </p>
                                @endif

                                <div class="space-y-5">
                                    {{-- Home team --}}
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="flex min-w-0 items-center gap-3">
                                            @if($match->homeTeam?->logo)
                                                <img src="{{ asset('storage/' . ltrim($match->homeTeam->logo, '/')) }}"
                                                     alt="{{ $match->homeTeam->name }} logo"
                                                     class="h-10 w-10 shrink-0 object-contain"
                                                     loading="lazy">
                                            @else
                                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#F6F8FB] font-extrabold">
                                                    {{ strtoupper(substr($match->homeTeam->name ?? 'H', 0, 1)) }}
                                                </div>
                                            @endif

                                            <div class="min-w-0">
                                                <p class="break-words text-sm font-extrabold">
                                                    {{ $match->homeTeam->name ?? 'Home Team' }}
                                                </p>

                                                @if($currentInnings && (string) $currentInnings->batting_team_id === (string) $homeTeamId)
                                                    <p class="mt-1 text-xs font-semibold text-[#F06445]">Batting</p>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="shrink-0 text-right">
                                            @if($homeRuns !== null)
                                                <p class="text-xl font-extrabold">{{ $homeRuns }}/{{ $homeWickets }}</p>
                                                <p class="mt-1 text-xs text-slate-500">{{ $homeOvers }} overs</p>
                                            @else
                                                <p class="text-sm font-bold text-slate-400">Yet to bat</p>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="border-t border-dashed border-slate-200"></div>

                                    {{-- Away team --}}
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="flex min-w-0 items-center gap-3">
                                            @if($match->awayTeam?->logo)
                                                <img src="{{ asset('storage/' . ltrim($match->awayTeam->logo, '/')) }}"
                                                     alt="{{ $match->awayTeam->name }} logo"
                                                     class="h-10 w-10 shrink-0 object-contain"
                                                     loading="lazy">
                                            @else
                                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#F6F8FB] font-extrabold">
                                                    {{ strtoupper(substr($match->awayTeam->name ?? 'A', 0, 1)) }}
                                                </div>
                                            @endif

                                            <div class="min-w-0">
                                                <p class="break-words text-sm font-extrabold">
                                                    {{ $match->awayTeam->name ?? 'Away Team' }}
                                                </p>

                                                @if($currentInnings && (string) $currentInnings->batting_team_id === (string) $awayTeamId)
                                                    <p class="mt-1 text-xs font-semibold text-[#F06445]">Batting</p>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="shrink-0 text-right">
                                            @if($awayRuns !== null)
                                                <p class="text-xl font-extrabold">{{ $awayRuns }}/{{ $awayWickets }}</p>
                                                <p class="mt-1 text-xs text-slate-500">{{ $awayOvers }} overs</p>
                                            @else
                                                <p class="text-sm font-bold text-slate-400">Yet to bat</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                @if($currentInnings)
                                    <div class="mt-5 rounded-xl bg-orange-50 px-4 py-3">
                                        <div class="flex items-center justify-between gap-3">
                                            <div>
                                                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                                                    Current innings
                                                </p>
                                                <p class="mt-1 text-sm font-bold text-[#202766]">
                                                    {{ $currentInnings->battingTeam->name ?? 'Batting team' }}
                                                </p>
                                            </div>

                                            <div class="text-right">
                                                <p class="text-lg font-black text-[#F06445]">
                                                    {{ $currentRuns }}/{{ $currentWickets }}
                                                </p>
                                                <p class="text-xs text-slate-500">{{ $currentOvers }} overs</p>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="mt-5 rounded-xl bg-[#F6F8FB] px-4 py-3">
                                        <p class="text-sm font-semibold text-slate-600">
                                            Live match — score details are not available yet.
                                        </p>
                                    </div>
                                @endif

                                <a href="{{ route('fixtures.show', $match) }}"
                                   class="mt-5 flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 px-4 py-3 text-sm font-bold text-[#202766] transition hover:border-[#F06445] hover:text-[#F06445]">
                                    View Scorecard &rarr;
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="rounded-2xl border border-slate-200 bg-white px-6 py-12 text-center shadow-sm">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-orange-50 text-2xl text-[#F06445]">
                        &#127951;
                    </div>

                    <h3 class="mt-4 text-lg font-extrabold">No Live Matches Right Now</h3>

                    <p class="mx-auto mt-2 max-w-lg text-sm leading-6 text-slate-500">
                        There are currently no fixtures marked as live.
                        Check the schedule for upcoming matches and confirmed results.
                    </p>

                    <a href="{{ route('fixtures.index') }}"
                       class="mt-5 inline-flex items-center gap-2 rounded-xl bg-[#202766] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#171D50]">
                        View Fixtures &rarr;
                    </a>
                </div>
            @endif
        </div>
    </section>

    {{-- Teams --}}
    <section id="teams" class="section-anchor border-y border-slate-200/70 bg-white py-14 sm:py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-[.16em] text-[#F06445]">
                        The competition
                    </p>

                    <h2 class="mt-2 text-2xl font-extrabold tracking-tight sm:text-3xl">
                        Teams
                    </h2>

                    <p class="mt-2 text-sm text-slate-500">
                        Explore the teams participating in the selected season.
                    </p>
                </div>

                <a href="{{ route('teams.index') }}"
                   class="inline-flex items-center gap-2 text-sm font-bold text-[#202766] transition hover:text-[#F06445]">
                    All Teams &rarr;
                </a>
            </div>

            @if($teams->isNotEmpty())
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                    @foreach($teams as $team)
                        <a href="{{ route('teams.show', $team) }}"
                           class="group rounded-2xl border border-slate-200 bg-white p-5 text-center transition hover:-translate-y-1 hover:border-orange-200 hover:shadow-lg sm:p-6">

                            @if($team->logo)
                                <img src="{{ asset('storage/' . ltrim($team->logo, '/')) }}"
                                     alt="{{ $team->name }} logo"
                                     class="mx-auto h-14 w-14 object-contain transition group-hover:scale-105"
                                     loading="lazy">
                            @else
                                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-[#F6F8FB] text-xl font-extrabold text-[#202766] transition group-hover:bg-orange-50 group-hover:text-[#F06445]">
                                    {{ strtoupper(substr($team->name, 0, 1)) }}
                                </div>
                            @endif

                            <h3 class="mt-4 break-words text-sm font-extrabold text-[#202766] sm:text-base">
                                {{ $team->name }}
                            </h3>

                            @if(!empty($team->short_name))
                                <p class="mt-1 text-xs font-semibold text-slate-500">
                                    {{ $team->short_name }}
                                </p>
                            @endif

                            <p class="mt-3 text-xs font-bold text-[#F06445] opacity-0 transition group-hover:opacity-100">
                                View Team &rarr;
                            </p>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="rounded-2xl border border-slate-200 bg-[#F6F8FB] px-6 py-10 text-center">
                    <h3 class="font-extrabold">No Teams Available</h3>
                    <p class="mt-2 text-sm text-slate-500">
                        Active teams will appear here when available in the database.
                    </p>
                </div>
            @endif
        </div>
    </section>

    {{-- Recent results --}}
    <section id="results" class="section-anchor py-14 sm:py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-[.16em] text-[#F06445]">
                        The final score
                    </p>

                    <h2 class="mt-2 text-2xl font-extrabold tracking-tight sm:text-3xl">
                        Recent Results
                    </h2>

                    <p class="mt-2 text-sm text-slate-500">
                        Completed matches with verified results.
                    </p>
                </div>

                <a href="{{ route('fixtures.index') }}"
                   class="inline-flex items-center gap-2 text-sm font-bold text-[#202766] transition hover:text-[#F06445]">
                    View All Matches &rarr;
                </a>
            </div>

            @if($recentResults->isNotEmpty())
                <div class="grid gap-4 md:grid-cols-2">
                    @foreach($recentResults as $result)
                        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:shadow-lg sm:p-6">

                            <div class="flex items-center justify-between gap-3">
                                <span class="rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700">
                                    Completed
                                </span>

                                <span class="text-xs font-semibold text-slate-500">
                                    {{ $result->match_number ? 'Match ' . $result->match_number : 'Final Result' }}
                                </span>
                            </div>

                            <div class="mt-6 grid grid-cols-[1fr_auto_1fr] items-center gap-3">
                                <div class="min-w-0 text-center">
                                    @if($result->homeTeam?->logo)
                                        <img src="{{ asset('storage/' . ltrim($result->homeTeam->logo, '/')) }}"
                                             alt="{{ $result->homeTeam->name }} logo"
                                             class="mx-auto h-11 w-11 object-contain"
                                             loading="lazy">
                                    @else
                                        <div class="mx-auto flex h-11 w-11 items-center justify-center rounded-xl bg-[#F6F8FB] font-extrabold">
                                            {{ strtoupper(substr($result->homeTeam->name ?? 'H', 0, 1)) }}
                                        </div>
                                    @endif

                                    <p class="mt-3 break-words text-sm font-extrabold">
                                        {{ $result->homeTeam->name ?? 'Home Team' }}
                                    </p>
                                </div>

                                <span class="text-sm font-extrabold text-slate-400">VS</span>

                                <div class="min-w-0 text-center">
                                    @if($result->awayTeam?->logo)
                                        <img src="{{ asset('storage/' . ltrim($result->awayTeam->logo, '/')) }}"
                                             alt="{{ $result->awayTeam->name }} logo"
                                             class="mx-auto h-11 w-11 object-contain"
                                             loading="lazy">
                                    @else
                                        <div class="mx-auto flex h-11 w-11 items-center justify-center rounded-xl bg-[#F6F8FB] font-extrabold">
                                            {{ strtoupper(substr($result->awayTeam->name ?? 'A', 0, 1)) }}
                                        </div>
                                    @endif

                                    <p class="mt-3 break-words text-sm font-extrabold">
                                        {{ $result->awayTeam->name ?? 'Away Team' }}
                                    </p>
                                </div>
                            </div>

                            <div class="mt-5 rounded-xl bg-[#F6F8FB] px-4 py-3 text-center">
                                @if($result->winner)
                                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                                        Match Winner
                                    </p>
                                    <p class="mt-1 text-sm font-extrabold text-[#202766]">
                                        {{ $result->winner->name }}
                                    </p>
                                @else
                                    <p class="text-sm font-semibold text-slate-600">
                                        Verified match result
                                    </p>
                                @endif
                            </div>

                            @if($result->scheduled_at)
                                <p class="mt-4 text-center text-xs text-slate-500">
                                    {{ \Illuminate\Support\Carbon::parse($result->scheduled_at)->format('d M Y') }}
                                </p>
                            @endif

                            <a href="{{ route('fixtures.show', $result) }}"
                               class="mt-4 flex items-center justify-center gap-2 text-sm font-bold text-[#F06445] transition hover:text-[#202766]">
                                Match Details &rarr;
                            </a>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="rounded-2xl border border-slate-200 bg-white px-6 py-10 text-center shadow-sm">
                    <h3 class="font-extrabold">No Verified Results Yet</h3>
                    <p class="mt-2 text-sm text-slate-500">
                        Verified completed match results will appear here when available.
                    </p>
                </div>
            @endif
        </div>
    </section>

    {{-- Latest news --}}
    <section id="news" class="section-anchor border-y border-slate-200/70 bg-white py-14 sm:py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-[.16em] text-[#F06445]">
                        From around the league
                    </p>

                    <h2 class="mt-2 text-2xl font-extrabold tracking-tight sm:text-3xl">
                        Latest News
                    </h2>

                    <p class="mt-2 text-sm text-slate-500">
                        Updates and stories published by the league.
                    </p>
                </div>

                <a href="{{ route('news.index') }}"
                   class="inline-flex items-center gap-2 text-sm font-bold text-[#202766] transition hover:text-[#F06445]">
                    All News &rarr;
                </a>
            </div>

            @if($latestNews->isNotEmpty())
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($latestNews as $article)
                        @php
                            $articleImage = $article->image
                                ?? $article->featured_image
                                ?? null;
                        @endphp

                        <article class="group overflow-hidden rounded-2xl border border-slate-200 bg-white transition hover:-translate-y-1 hover:shadow-lg">

                            @if($articleImage)
                                <img src="{{ asset('storage/' . ltrim($articleImage, '/')) }}"
                                     alt="{{ $article->title }}"
                                     class="news-image"
                                     loading="lazy">
                            @else
                                <div class="news-image flex items-center justify-center bg-[#F6F8FB]">
                                    <div class="text-center">
                                        <div class="text-3xl font-extrabold text-[#202766]">NPL</div>
                                        <p class="mt-1 text-xs font-bold uppercase tracking-[.18em] text-[#F06445]">
                                            Season Hub
                                        </p>
                                    </div>
                                </div>
                            @endif

                            <div class="p-5">
                                <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500">
                                    @if($article->published_at)
                                        <time datetime="{{ \Illuminate\Support\Carbon::parse($article->published_at)->toIso8601String() }}">
                                            {{ \Illuminate\Support\Carbon::parse($article->published_at)->format('d M Y') }}
                                        </time>
                                    @elseif($article->created_at)
                                        <time datetime="{{ \Illuminate\Support\Carbon::parse($article->created_at)->toIso8601String() }}">
                                            {{ \Illuminate\Support\Carbon::parse($article->created_at)->format('d M Y') }}
                                        </time>
                                    @endif

                                    <span aria-hidden="true">&bull;</span>
                                    <span>League News</span>
                                </div>

                                <h3 class="mt-3 text-lg font-extrabold leading-snug text-[#202766] transition group-hover:text-[#F06445]">
                                    {{ $article->title }}
                                </h3>

                                @if(!empty($article->excerpt))
                                    <p class="line-clamp-3 mt-3 text-sm leading-6 text-slate-500">
                                        {{ $article->excerpt }}
                                    </p>
                                @elseif(!empty($article->summary))
                                    <p class="line-clamp-3 mt-3 text-sm leading-6 text-slate-500">
                                        {{ $article->summary }}
                                    </p>
                                @elseif(!empty($article->content))
                                    <p class="line-clamp-3 mt-3 text-sm leading-6 text-slate-500">
                                        {{ \Illuminate\Support\Str::limit(strip_tags($article->content), 150) }}
                                    </p>
                                @endif

                                <a href="{{ route('news.show', $article->slug) }}"
                                   class="mt-5 inline-flex items-center gap-2 text-sm font-extrabold text-[#F06445] transition hover:text-[#202766]">
                                    Read Article &rarr;
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="rounded-2xl border border-slate-200 bg-[#F6F8FB] px-6 py-10 text-center">
                    <h3 class="font-extrabold">No Published News Yet</h3>
                    <p class="mx-auto mt-2 max-w-lg text-sm leading-6 text-slate-500">
                        Published league news will appear here once articles are available.
                    </p>
                </div>
            @endif
        </div>
    </section>

    {{-- Closing CTA --}}
    <section class="bg-[#202766]">
        <div class="mx-auto flex max-w-7xl flex-col items-start justify-between gap-6 px-4 py-12 sm:px-6 md:flex-row md:items-center lg:px-8">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-[.16em] text-[#F06445]">
                    Stay in the game
                </p>

                <h2 class="mt-2 text-2xl font-extrabold text-white sm:text-3xl">
                    Follow every fixture.
                </h2>

                <p class="mt-3 max-w-xl text-sm leading-6 text-slate-300">
                    Keep up with schedules, team information and verified match results in one place.
                </p>
            </div>

            <a href="{{ route('fixtures.index') }}"
               class="inline-flex shrink-0 items-center gap-2 rounded-xl bg-[#F06445] px-6 py-3.5 text-sm font-extrabold text-white transition hover:bg-orange-600">
                Explore All Fixtures &rarr;
            </a>
        </div>
    </section>

</main>

@endsection