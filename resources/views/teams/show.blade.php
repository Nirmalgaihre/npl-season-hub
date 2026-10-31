@extends('layouts.app')

@section('title', $team->name . ' | NPL Season Hub')

@section('meta_description', 'View the ' . $team->name . ' squad, live innings scores, fixtures and results in the Nepal
Premier League.')

@section('content')

@php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

$teamFixtures = collect($fixtures ?? []);

$liveFixtures = $teamFixtures->filter(
fn ($match) => strtolower($match->status ?? '') === 'live'
);

$upcomingFixtures = $teamFixtures
->filter(fn ($match) => strtolower($match->status ?? '') === 'scheduled')
->sortBy(fn ($match) => $match->scheduled_at?->timestamp ?? PHP_INT_MAX);

$completedFixtures = $teamFixtures
->filter(fn ($match) => strtolower($match->status ?? '') === 'completed')
->sortByDesc(fn ($match) => $match->scheduled_at?->timestamp ?? 0);

// Actual players registered to this team.
$players = DB::table('players')
->where('team_id', $team->id)
->orderBy('name')
->get();

// Scorecards belonging to fixtures available on this page.
$fixtureIds = $teamFixtures->pluck('id')->filter()->values();

$scorecardsByFixture = $fixtureIds->isNotEmpty()
? DB::table('manual_scorecards')
->whereIn('fixture_id', $fixtureIds)
->orderBy('innings_number')
->get()
->groupBy('fixture_id')
: collect();

$battingTeamIds = $scorecardsByFixture
->flatten(1)
->pluck('batting_team_id')
->filter()
->unique()
->values();

$teamNames = $battingTeamIds->isNotEmpty()
? DB::table('teams')
->whereIn('id', $battingTeamIds)
->pluck('name', 'id')
: collect();

// Existing team logo field.
$teamLogo = !empty($team->logo)
? asset('storage/' . $team->logo)
: null;
@endphp

<div class="min-h-screen bg-[#F6F8FB] text-[#202766]">

    {{-- Team heading --}}
    <section class="border-b border-slate-200 bg-white">
        <div class="mx-auto max-w-7xl px-5 py-7 sm:px-8 lg:px-10">

            <nav class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
                <a href="{{ route('home') }}" class="transition hover:text-[#F06445]">Home</a>
                <span>/</span>
                <a href="{{ route('teams.index') }}" class="transition hover:text-[#F06445]">Teams</a>
                <span>/</span>
                <span class="font-semibold text-[#202766]">{{ $team->name }}</span>
            </nav>

            <div class="mt-7 flex flex-col gap-6 sm:flex-row sm:items-center">

                <div
                    class="flex h-28 w-28 shrink-0 items-center justify-center border border-slate-200 bg-slate-50 p-3 sm:h-32 sm:w-32">
                    @if ($teamLogo)
                    <img src="{{ $teamLogo }}" alt="{{ $team->name }} logo" class="h-full w-full object-contain">
                    @else
                    <span class="text-2xl font-extrabold">
                        {{ Str::upper(Str::substr($team->short_name ?: $team->name, 0, 3)) }}
                    </span>
                    @endif
                </div>

                <div class="min-w-0 flex-1">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#F06445]">
                        Nepal Premier League
                    </p>

                    <h1 class="mt-2 text-3xl font-extrabold tracking-tight sm:text-4xl">
                        {{ $team->name }}
                    </h1>

                    @if ($team->short_name)
                    <p class="mt-2 text-sm font-semibold uppercase tracking-wider text-slate-500">
                        {{ $team->short_name }}
                    </p>
                    @endif

                    @if ($team->home_venue)
                    <p class="mt-3 text-sm text-slate-600">
                        <span class="font-bold text-slate-800">Home ground:</span>
                        {{ $team->home_venue }}
                    </p>
                    @endif

                    @if ($team->website_url)
                    <a href="{{ $team->website_url }}" target="_blank" rel="noopener noreferrer"
                        class="mt-3 inline-flex items-center gap-1 text-sm font-bold text-[#202766] hover:text-[#F06445]">
                        Official website <span aria-hidden="true">↗</span>
                    </a>
                    @endif
                </div>

            </div>
        </div>
    </section>

    <div class="mx-auto max-w-7xl px-5 py-8 sm:px-8 lg:px-10">

        {{-- Team overview --}}
        <section class="grid grid-cols-2 gap-px overflow-hidden border border-slate-200 bg-slate-200 sm:grid-cols-4">

            <div class="bg-white p-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Squad players</p>
                <p class="mt-2 text-3xl font-extrabold">{{ $players->count() }}</p>
            </div>

            <div class="bg-white p-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Live matches</p>
                <p class="mt-2 text-3xl font-extrabold text-red-600">{{ $liveFixtures->count() }}</p>
            </div>

            <div class="bg-white p-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Upcoming</p>
                <p class="mt-2 text-3xl font-extrabold">{{ $upcomingFixtures->count() }}</p>
            </div>

            <div class="bg-white p-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Completed</p>
                <p class="mt-2 text-3xl font-extrabold">{{ $completedFixtures->count() }}</p>
            </div>

        </section>

        {{-- Player squad --}}
        <section id="squad" class="mt-12">

            <div class="mb-7 flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-[#F06445]">
                        The squad
                    </p>

                    <h2 class="mt-2 text-3xl font-black tracking-tight text-[#172554] sm:text-4xl">
                        Players
                    </h2>

                    <p class="mt-2 text-sm text-slate-500">
                        Players registered to {{ $team->name }}.
                    </p>
                </div>

                <span class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-[#202766]">
                    {{ $players->count() }} {{ Str::plural('Player', $players->count()) }}
                </span>
            </div>

            @if ($players->isNotEmpty())

            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 sm:gap-5 lg:grid-cols-4 xl:grid-cols-5">

                @foreach ($players as $player)
                @php
                $playerName = $player->name ?? 'Player';
                $playerRole = trim($player->role ?? '');
                $roleLower = Str::lower($playerRole);

                if (
                str_contains($roleLower, 'all-round')
                || str_contains($roleLower, 'allround')
                || str_contains($roleLower, 'all round')
                ) {
                $roleBadge = 'bg-violet-600';
                } elseif (str_contains($roleLower, 'wicket')) {
                $roleBadge = 'bg-emerald-600';
                } elseif (str_contains($roleLower, 'bowl')) {
                $roleBadge = 'bg-sky-600';
                } else {
                $roleBadge = 'bg-[#F06445]';
                }

                $playerPhoto = !empty($player->photo_path)
                ? asset('storage/' . $player->photo_path)
                : null;
                @endphp

                <article
                    class="group relative isolate overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-xl">

                    {{-- Player portrait --}}
                    <div class="relative h-52 overflow-hidden bg-[#102052] sm:h-60">

                        {{-- Navy gradient --}}
                        <div class="absolute inset-0 bg-gradient-to-br from-[#203D88] via-[#14285F] to-[#091538]"></div>

                        {{-- Decorative shapes --}}
                        <div class="absolute -bottom-12 -left-10 h-36 w-36 rotate-[28deg] bg-[#F06445]"></div>

                        <div class="absolute -bottom-16 -right-12 h-44 w-44 -rotate-[25deg] bg-[#FF815D]"></div>

                        {{-- Background pattern --}}
                        <div class="absolute inset-0 opacity-[0.07]"
                            style="background-image: repeating-linear-gradient(130deg, transparent 0, transparent 24px, #ffffff 25px, transparent 39px);">
                        </div>

                        {{-- Player photo or team-logo fallback --}}
                        @if ($playerPhoto)
                        <img src="{{ $playerPhoto }}" alt="{{ $playerName }}" loading="lazy"
                            class="absolute inset-0 z-[1] h-full w-full object-contain object-bottom transition duration-500 group-hover:scale-105">
                        @else
                        <div
                            class="absolute inset-0 z-[1] flex flex-col items-center justify-center px-3 pb-10 text-center">

                            @if ($teamLogo)
                            <img src="{{ $teamLogo }}" alt="{{ $team->name }} logo" loading="lazy"
                                class="mb-2 h-16 w-16 object-contain drop-shadow-lg">
                            @else
                            <div
                                class="flex h-20 w-20 items-center justify-center rounded-full border-2 border-white/40 bg-white/10 text-2xl font-black text-white shadow-lg">
                                {{ Str::upper(Str::substr($playerName, 0, 2)) }}
                            </div>
                            @endif

                            <p class="mt-2 max-w-full text-[10px] font-bold uppercase tracking-wider text-white/90">
                                {{ $team->name }}
                            </p>

                            <p class="mt-1 text-[9px] font-semibold uppercase tracking-wider text-white/75">
                                Photo unavailable
                            </p>
                        </div>
                        @endif

                        {{-- Team abbreviation --}}
                        <div
                            class="absolute left-3 top-3 z-[4] rounded-md border border-white/25 bg-black/25 px-2 py-1 text-[9px] font-extrabold uppercase tracking-wider text-white backdrop-blur-sm">
                            {{ $team->short_name ?: Str::upper(Str::substr($team->name, 0, 3)) }}
                        </div>

                        {{-- Full role badge: stays inside portrait --}}
                        <div class="absolute inset-x-2 bottom-2 z-[5] flex justify-center">
                            <span
                                class="{{ $roleBadge }} max-w-full rounded-full border border-white/80 px-2.5 py-1.5 text-center text-[8px] font-extrabold uppercase leading-tight tracking-wide text-white shadow-lg sm:px-3 sm:text-[9px]">
                                {{ $playerRole !== '' ? $playerRole : 'Player' }}
                            </span>
                        </div>

                    </div>

                    {{-- Player details --}}
                    <div class="relative px-2 pb-4 pt-4 text-center sm:px-3">

                        <h3 class="break-words text-sm font-black leading-5 text-[#172554] sm:text-base">
                            {{ $playerName }}
                        </h3>

                        <div class="mt-2 flex items-center justify-center gap-2 text-xs font-medium text-slate-500">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="1.8" class="h-4 w-4 shrink-0 text-[#F06445]"
                                aria-hidden="true">
                                <circle cx="12" cy="12" r="9"></circle>
                                <path d="M3 12h18M12 3a15 15 0 0 1 0 18M12 3a15 15 0 0 0 0 18"></path>
                            </svg>

                            <span class="truncate">
                                {{ $player->nationality ?: 'Nationality not listed' }}
                            </span>
                        </div>

                        <div
                            class="mx-auto mt-4 h-1 w-10 rounded-full bg-[#F06445] transition-all duration-300 group-hover:w-16">
                        </div>

                        <p class="mt-3 break-words text-[9px] font-bold uppercase tracking-[0.13em] text-slate-400">
                            {{ $team->short_name ?: $team->name }}
                        </p>
                    </div>

                </article>
                @endforeach

            </div>

            @else
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center">
                <div
                    class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="1.7" class="h-7 w-7" aria-hidden="true">
                        <circle cx="12" cy="8" r="4"></circle>
                        <path d="M4 21a8 8 0 0 1 16 0"></path>
                    </svg>
                </div>

                <h3 class="mt-4 font-bold text-slate-900">No players listed yet</h3>

                <p class="mt-2 text-sm text-slate-500">
                    Players registered for {{ $team->name }} will appear here.
                </p>
            </div>
            @endif

        </section>

        {{-- Match details and team performance --}}
        <section id="team-match-details" class="mt-12">

            {{-- Section heading --}}
            <div class="mb-7 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-[#F06445]">
                        Match centre
                    </p>

                    <h2 class="mt-2 text-3xl font-black tracking-tight text-[#172554] sm:text-4xl">
                        Match Details
                    </h2>

                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
                        Upcoming fixtures, recent results and the latest recorded performance of
                        {{ $team->name }} in the Nepal Premier League.
                    </p>
                </div>

                <a href="{{ route('fixtures.index') }}"
                    class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-[#202766] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#171D50]">
                    All fixtures
                    <span aria-hidden="true">→</span>
                </a>
            </div>

            {{-- Performance summary --}}
            @php
            $verifiedResults = $completedFixtures->filter(
            fn ($match) => (bool) ($match->is_result_verified ?? false)
            );

            $totalVerified = $verifiedResults->count();

            $teamWins = $verifiedResults->filter(function ($match) use ($team) {
            $isHome = (int) ($match->home_team_id ?? 0) === (int) $team->id;
            $isAway = (int) ($match->away_team_id ?? 0) === (int) $team->id;

            if (!$isHome && !$isAway) {
            return false;
            }

            $teamRuns = $isHome
            ? (int) ($match->home_runs ?? 0)
            : (int) ($match->away_runs ?? 0);

            $opponentRuns = $isHome
            ? (int) ($match->away_runs ?? 0)
            : (int) ($match->home_runs ?? 0);

            return $teamRuns > $opponentRuns;
            })->count();

            $teamLosses = $verifiedResults->filter(function ($match) use ($team) {
            $isHome = (int) ($match->home_team_id ?? 0) === (int) $team->id;
            $isAway = (int) ($match->away_team_id ?? 0) === (int) $team->id;

            if (!$isHome && !$isAway) {
            return false;
            }

            $teamRuns = $isHome
            ? (int) ($match->home_runs ?? 0)
            : (int) ($match->away_runs ?? 0);

            $opponentRuns = $isHome
            ? (int) ($match->away_runs ?? 0)
            : (int) ($match->home_runs ?? 0);

            return $teamRuns < $opponentRuns; })->count();

                $teamTies = $verifiedResults->filter(function ($match) use ($team) {
                $isHome = (int) ($match->home_team_id ?? 0) === (int) $team->id;
                $isAway = (int) ($match->away_team_id ?? 0) === (int) $team->id;

                if (!$isHome && !$isAway) {
                return false;
                }

                $teamRuns = $isHome
                ? (int) ($match->home_runs ?? 0)
                : (int) ($match->away_runs ?? 0);

                $opponentRuns = $isHome
                ? (int) ($match->away_runs ?? 0)
                : (int) ($match->home_runs ?? 0);

                return $teamRuns === $opponentRuns;
                })->count();

                $teamResultCount = $teamWins + $teamLosses + $teamTies;

                $winRate = $teamResultCount > 0
                ? round(($teamWins / $teamResultCount) * 100)
                : 0;
                @endphp

                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">

                    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-xs font-bold uppercase tracking-wide text-slate-500">
                                Upcoming
                            </span>
                            <span
                                class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-50 text-lg text-blue-700">
                                ↗
                            </span>
                        </div>
                        <p class="mt-4 text-3xl font-black text-[#202766]">
                            {{ $upcomingFixtures->count() }}
                        </p>
                        <p class="mt-1 text-xs text-slate-500">Scheduled matches</p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-xs font-bold uppercase tracking-wide text-slate-500">
                                Wins
                            </span>
                            <span
                                class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50 text-lg text-emerald-700">
                                ✓
                            </span>
                        </div>
                        <p class="mt-4 text-3xl font-black text-emerald-700">
                            {{ $teamWins }}
                        </p>
                        <p class="mt-1 text-xs text-slate-500">Based on recorded scores</p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-xs font-bold uppercase tracking-wide text-slate-500">
                                Losses
                            </span>
                            <span
                                class="flex h-9 w-9 items-center justify-center rounded-xl bg-rose-50 text-lg text-rose-700">
                                ↘
                            </span>
                        </div>
                        <p class="mt-4 text-3xl font-black text-rose-700">
                            {{ $teamLosses }}
                        </p>
                        <p class="mt-1 text-xs text-slate-500">Based on recorded scores</p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-xs font-bold uppercase tracking-wide text-slate-500">
                                Win rate
                            </span>
                            <span
                                class="flex h-9 w-9 items-center justify-center rounded-xl bg-violet-50 text-lg text-violet-700">
                                %
                            </span>
                        </div>
                        <p class="mt-4 text-3xl font-black text-[#202766]">
                            {{ $teamResultCount > 0 ? $winRate . '%' : '—' }}
                        </p>
                        <p class="mt-1 text-xs text-slate-500">
                            {{ $teamResultCount }} verified result{{ $teamResultCount === 1 ? '' : 's' }}
                        </p>
                    </div>

                </div>

                {{-- Upcoming fixtures --}}
                <div class="mt-10">

                    <div class="mb-5 flex items-center justify-between gap-3">
                        <div>
                            <h3 class="text-xl font-extrabold text-[#172554]">
                                Upcoming fixtures
                            </h3>
                            <p class="mt-1 text-sm text-slate-500">
                                What is coming up next?
                            </p>
                        </div>

                        <span class="rounded-full bg-blue-50 px-3 py-1.5 text-xs font-extrabold text-blue-700">
                            {{ $upcomingFixtures->count() }} scheduled
                        </span>
                    </div>

                    @if ($upcomingFixtures->isNotEmpty())

                    <div class="grid gap-4 lg:grid-cols-2">

                        @foreach ($upcomingFixtures as $match)
                        @php
                        $homeTeam = $match->homeTeam;
                        $awayTeam = $match->awayTeam;
                        $isHomeTeam = (int) ($match->home_team_id ?? 0) === (int) $team->id;
                        $isAwayTeam = (int) ($match->away_team_id ?? 0) === (int) $team->id;
                        @endphp

                        <article
                            class="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:border-[#202766]/30 hover:shadow-lg">

                            <div
                                class="flex items-center justify-between gap-3 border-b border-slate-100 bg-slate-50/80 px-4 py-3 sm:px-5">
                                <div>
                                    <p class="text-xs font-extrabold uppercase tracking-wide text-[#202766]">
                                        {{ $match->season?->name ?? 'Nepal Premier League' }}
                                    </p>
                                    <p class="mt-1 text-xs text-slate-500">
                                        Match {{ $match->match_number ?? '—' }}
                                    </p>
                                </div>

                                <span
                                    class="rounded-full border border-blue-100 bg-blue-50 px-3 py-1.5 text-[10px] font-extrabold uppercase tracking-wide text-blue-700">
                                    Upcoming
                                </span>
                            </div>

                            <div class="p-5">

                                <div class="grid grid-cols-[1fr_auto_1fr] items-center gap-3">

                                    {{-- Home team --}}
                                    <div class="min-w-0 text-center">
                                        <div
                                            class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-50 p-2 sm:h-20 sm:w-20">
                                            @if ($homeTeam?->logo)
                                            <img src="{{ asset('storage/' . $homeTeam->logo) }}"
                                                alt="{{ $homeTeam->name }}" loading="lazy"
                                                class="h-full w-full object-contain">
                                            @else
                                            <span class="text-lg font-black text-[#202766]">
                                                {{ Str::upper(Str::substr($homeTeam?->short_name ?: $homeTeam?->name ?: 'HT', 0, 3)) }}
                                            </span>
                                            @endif
                                        </div>

                                        <p class="mt-3 break-words text-sm font-extrabold text-slate-900">
                                            {{ $homeTeam?->name ?? 'Home team' }}
                                        </p>

                                        @if ($isHomeTeam)
                                        <span
                                            class="mt-2 inline-block rounded-full bg-[#202766] px-2.5 py-1 text-[9px] font-extrabold uppercase tracking-wide text-white">
                                            {{ $team->short_name ?: 'Your team' }}
                                        </span>
                                        @endif
                                    </div>

                                    {{-- VS --}}
                                    <div class="text-center">
                                        <span
                                            class="flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-[10px] font-black tracking-wider text-slate-400">
                                            VS
                                        </span>
                                    </div>

                                    {{-- Away team --}}
                                    <div class="min-w-0 text-center">
                                        <div
                                            class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-50 p-2 sm:h-20 sm:w-20">
                                            @if ($awayTeam?->logo)
                                            <img src="{{ asset('storage/' . $awayTeam->logo) }}"
                                                alt="{{ $awayTeam->name }}" loading="lazy"
                                                class="h-full w-full object-contain">
                                            @else
                                            <span class="text-lg font-black text-[#202766]">
                                                {{ Str::upper(Str::substr($awayTeam?->short_name ?: $awayTeam?->name ?: 'AT', 0, 3)) }}
                                            </span>
                                            @endif
                                        </div>

                                        <p class="mt-3 break-words text-sm font-extrabold text-slate-900">
                                            {{ $awayTeam?->name ?? 'Away team' }}
                                        </p>

                                        @if ($isAwayTeam)
                                        <span
                                            class="mt-2 inline-block rounded-full bg-[#202766] px-2.5 py-1 text-[9px] font-extrabold uppercase tracking-wide text-white">
                                            {{ $team->short_name ?: 'Your team' }}
                                        </span>
                                        @endif
                                    </div>

                                </div>

                                <div class="mt-5 grid gap-3 rounded-xl bg-slate-50 p-4 sm:grid-cols-2">

                                    <div>
                                        <p class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">
                                            Date & time
                                        </p>
                                        <p class="mt-1.5 text-sm font-bold text-slate-800">
                                            {{ $match->scheduled_at?->timezone(config('app.timezone'))->format('D, d M Y') ?? 'Date TBA' }}
                                        </p>
                                        <p class="mt-1 text-xs text-slate-500">
                                            {{ $match->scheduled_at?->timezone(config('app.timezone'))->format('h:i A') ?? 'Time TBA' }}
                                        </p>
                                    </div>

                                    <div>
                                        <p class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">
                                            Venue
                                        </p>
                                        <p class="mt-1.5 text-sm font-bold text-slate-800">
                                            {{ $match->venue ?: 'Venue to be announced' }}
                                        </p>
                                    </div>

                                </div>

                                <a href="{{ route('fixtures.show', $match) }}"
                                    class="mt-4 flex items-center justify-center gap-2 rounded-xl border border-slate-200 px-4 py-3 text-sm font-bold text-[#202766] transition hover:border-[#202766] hover:bg-[#202766] hover:text-white">
                                    View match details
                                    <span aria-hidden="true">→</span>
                                </a>

                            </div>
                        </article>
                        @endforeach

                    </div>

                    @else
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-5 py-10 text-center">
                        <div
                            class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-xl text-slate-500">
                            ◷
                        </div>
                        <h4 class="mt-4 font-extrabold text-slate-800">
                            No upcoming fixtures
                        </h4>
                        <p class="mt-2 text-sm text-slate-500">
                            No scheduled matches are currently available for this team.
                        </p>
                        <a href="{{ route('fixtures.index') }}"
                            class="mt-5 inline-flex items-center gap-2 text-sm font-bold text-[#202766] hover:text-[#F06445]">
                            Browse all fixtures →
                        </a>
                    </div>
                    @endif

                </div>

                {{-- Recent results --}}
                <div class="mt-12">

                    <div class="mb-5 flex flex-wrap items-end justify-between gap-3">
                        <div>
                            <h3 class="text-xl font-extrabold text-[#172554]">
                                Recent results
                            </h3>
                            <p class="mt-1 text-sm text-slate-500">
                                Match outcomes and verification status.
                            </p>
                        </div>

                        <span
                            class="rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-600">
                            {{ $completedFixtures->count() }} completed
                        </span>
                    </div>

                    @if ($completedFixtures->isNotEmpty())

                    <div class="space-y-4">

                        @foreach ($completedFixtures as $match)
                        @php
                        $resultIsVerified = (bool) ($match->is_result_verified ?? false);

                        $isHomeTeam = (int) ($match->home_team_id ?? 0) === (int) $team->id;
                        $isAwayTeam = (int) ($match->away_team_id ?? 0) === (int) $team->id;

                        $teamRuns = $isHomeTeam
                        ? $match->home_runs
                        : ($isAwayTeam ? $match->away_runs : null);

                        $opponentRuns = $isHomeTeam
                        ? $match->away_runs
                        : ($isAwayTeam ? $match->home_runs : null);

                        $resultLabel = 'Result pending verification';
                        $resultClass = 'border-amber-200 bg-amber-50 text-amber-800';

                        if ($resultIsVerified) {
                        if ($teamRuns === null || $opponentRuns === null) {
                        $resultLabel = 'Result confirmed';
                        $resultClass = 'border-emerald-200 bg-emerald-50 text-emerald-800';
                        } elseif ((int) $teamRuns > (int) $opponentRuns) {
                        $resultLabel = 'Victory';
                        $resultClass = 'border-emerald-200 bg-emerald-50 text-emerald-800';
                        } elseif ((int) $teamRuns < (int) $opponentRuns) { $resultLabel='Defeat' ;
                            $resultClass='border-rose-200 bg-rose-50 text-rose-800' ; } else {
                            $resultLabel='Scores level' ; $resultClass='border-slate-200 bg-slate-100 text-slate-700' ;
                            } } @endphp <article
                            class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:shadow-md">

                            <div
                                class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-4 py-4 sm:px-5">
                                <div>
                                    <p class="text-xs font-extrabold uppercase tracking-wide text-[#202766]">
                                        {{ $match->season?->name ?? 'Nepal Premier League' }}
                                    </p>
                                    <p class="mt-1 text-xs text-slate-500">
                                        Match {{ $match->match_number ?? '—' }}
                                        ·
                                        {{ $match->scheduled_at?->timezone(config('app.timezone'))->format('d M Y') ?? 'Date not available' }}
                                    </p>
                                </div>

                                <span
                                    class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-[10px] font-extrabold uppercase tracking-wide {{ $resultClass }}">
                                    <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                    {{ $resultLabel }}
                                </span>
                            </div>

                            <div class="grid gap-5 p-5 sm:grid-cols-[1fr_auto_1fr] sm:items-center">

                                {{-- Home score --}}
                                <div
                                    class="flex items-center gap-3 {{ $isHomeTeam ? 'rounded-xl bg-blue-50/70 p-3' : '' }}">
                                    <div
                                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-slate-50 p-1.5">
                                        @if ($match->homeTeam?->logo)
                                        <img src="{{ asset('storage/' . $match->homeTeam->logo) }}"
                                            alt="{{ $match->homeTeam->name }}" loading="lazy"
                                            class="h-full w-full object-contain">
                                        @else
                                        <span class="text-xs font-black text-[#202766]">
                                            {{ Str::upper(Str::substr($match->homeTeam?->short_name ?: $match->homeTeam?->name ?: 'HT', 0, 3)) }}
                                        </span>
                                        @endif
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <p class="break-words text-sm font-extrabold text-slate-800">
                                            {{ $match->homeTeam?->name ?? 'Home team' }}
                                        </p>

                                        @if ($resultIsVerified)
                                        <p class="mt-1 text-xl font-black tabular-nums text-[#202766]">
                                            {{ $match->home_runs ?? 0 }}/{{ $match->home_wickets ?? 0 }}
                                        </p>
                                        @else
                                        <p class="mt-1 text-xs text-slate-400">
                                            Score not verified
                                        </p>
                                        @endif
                                    </div>
                                </div>

                                <div class="text-center">
                                    <span class="text-xs font-black tracking-widest text-slate-300">
                                        VS
                                    </span>
                                </div>

                                {{-- Away score --}}
                                <div
                                    class="flex items-center gap-3 {{ $isAwayTeam ? 'rounded-xl bg-blue-50/70 p-3' : '' }}">
                                    <div
                                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-slate-50 p-1.5">
                                        @if ($match->awayTeam?->logo)
                                        <img src="{{ asset('storage/' . $match->awayTeam->logo) }}"
                                            alt="{{ $match->awayTeam->name }}" loading="lazy"
                                            class="h-full w-full object-contain">
                                        @else
                                        <span class="text-xs font-black text-[#202766]">
                                            {{ Str::upper(Str::substr($match->awayTeam?->short_name ?: $match->awayTeam?->name ?: 'AT', 0, 3)) }}
                                        </span>
                                        @endif
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <p class="break-words text-sm font-extrabold text-slate-800">
                                            {{ $match->awayTeam?->name ?? 'Away team' }}
                                        </p>

                                        @if ($resultIsVerified)
                                        <p class="mt-1 text-xl font-black tabular-nums text-[#202766]">
                                            {{ $match->away_runs ?? 0 }}/{{ $match->away_wickets ?? 0 }}
                                        </p>
                                        @else
                                        <p class="mt-1 text-xs text-slate-400">
                                            Score not verified
                                        </p>
                                        @endif
                                    </div>
                                </div>

                            </div>

                            {{-- Match outcome summary --}}
                            <div class="border-t border-slate-100 bg-slate-50/70 px-5 py-4">

                                @if ($resultIsVerified && $match->result_summary)
                                <p class="text-sm font-bold leading-6 text-emerald-800">
                                    {{ $match->result_summary }}
                                </p>
                                @elseif ($resultIsVerified)
                                <p class="text-sm font-semibold text-slate-700">
                                    Official result has been confirmed.
                                </p>
                                @else
                                <p class="text-sm leading-6 text-amber-800">
                                    The recorded result is awaiting verification. The scores above are not presented as
                                    an official result.
                                </p>
                                @endif

                                <a href="{{ route('fixtures.show', $match) }}"
                                    class="mt-3 inline-flex items-center gap-2 text-sm font-extrabold text-[#202766] hover:text-[#F06445]">
                                    View full match details
                                    <span aria-hidden="true">→</span>
                                </a>

                            </div>

                            </article>
                            @endforeach

                    </div>

                    @else
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-5 py-10 text-center">
                        <div
                            class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-xl text-slate-500">
                            ◷
                        </div>
                        <h4 class="mt-4 font-extrabold text-slate-800">
                            No match results yet
                        </h4>
                        <p class="mt-2 text-sm text-slate-500">
                            Completed match results will appear here when available.
                        </p>
                    </div>
                    @endif

                </div>

                {{-- Performance information --}}
                <div class="mt-8 rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
                    <div class="flex items-start gap-3">
                        <span
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#202766]/5 text-lg text-[#202766]">
                            i
                        </span>

                        <div>
                            <h3 class="font-extrabold text-[#172554]">
                                About these statistics
                            </h3>

                            <p class="mt-2 text-sm leading-6 text-slate-500">
                                Win and loss figures are calculated from verified completed fixtures
                                involving {{ $team->name }}. Unverified results are excluded.
                                A win rate is shown only when at least one verified result with
                                a recorded score is available.
                            </p>
                        </div>
                    </div>
                </div>

        </section>


        {{-- Back to teams --}}
        <div class="mt-9 border-t border-slate-200 pt-6">
            <a href="{{ route('teams.index') }}"
                class="inline-flex items-center gap-2 rounded-lg bg-[#202766] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#171D50]">
                ← All teams
            </a>
        </div>

    </div>
</div>

@endsection