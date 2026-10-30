
@extends('layouts.app')

@section('title', $team->name . ' | NPL Season Hub')

@section('meta_description', 'View the ' . $team->name . ' squad, live innings scores, fixtures and results in the Nepal Premier League.')

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

    // Load actual players registered to this team.
    $players = DB::table('players')
        ->where('team_id', $team->id)
        ->orderBy('name')
        ->get();

    // Load scorecards for the fixtures already available on this page.
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

    $teamLogo = $team->logo
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

                <div class="flex h-28 w-28 shrink-0 items-center justify-center border border-slate-200 bg-slate-50 p-3 sm:h-32 sm:w-32">
                    @if ($teamLogo)
                        <img
                            src="{{ $teamLogo }}"
                            alt="{{ $team->name }} logo"
                            class="h-full w-full object-contain"
                        >
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
                        <a
                            href="{{ $team->website_url }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="mt-3 inline-flex items-center gap-1 text-sm font-bold text-[#202766] hover:text-[#F06445]"
                        >
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

        {{-- Cricket trading-style player cards --}}
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

                        <article class="group relative isolate overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-xl">

                            {{-- Card portrait and artwork --}}
                            <div class="relative h-52 overflow-hidden bg-[#102052] sm:h-60">

                                {{-- Navy gradient --}}
                                <div class="absolute inset-0 bg-gradient-to-br from-[#203D88] via-[#14285F] to-[#091538]"></div>

                                {{-- Decorative diagonal shapes --}}
                                <div class="absolute -bottom-12 -left-10 h-36 w-36 rotate-[28deg] bg-[#F06445]"></div>

                                <div class="absolute -bottom-16 -right-12 h-44 w-44 -rotate-[25deg] bg-[#FF815D]"></div>

                                {{-- Subtle pattern --}}
                                <div
                                    class="absolute inset-0 opacity-[0.07]"
                                    style="background-image: repeating-linear-gradient(130deg, transparent 0, transparent 24px, #ffffff 25px, transparent 39px);"
                                ></div>

                                {{-- Player photo --}}
                                @if ($playerPhoto)
                                    <img
                                        src="{{ $playerPhoto }}"
                                        alt="{{ $playerName }}"
                                        loading="lazy"
                                        class="absolute inset-x-0 bottom-0 z-[1] mx-auto h-[96%] w-full object-contain object-bottom transition duration-500 group-hover:scale-105"
                                    >
                                @else
                                    <div class="absolute inset-0 z-[1] flex flex-col items-center justify-center px-3 text-center">
                                        <div class="flex h-20 w-20 items-center justify-center rounded-full border-2 border-white/40 bg-white/10 text-2xl font-black text-white shadow-lg">
                                            {{ Str::upper(Str::substr($playerName, 0, 2)) }}
                                        </div>

                                        <p class="mt-3 text-[10px] font-semibold uppercase tracking-wider text-white/80">
                                            Photo unavailable
                                        </p>
                                    </div>
                                @endif

                                {{-- Team abbreviation --}}
                                <div class="absolute left-3 top-3 z-[2] rounded-md border border-white/20 bg-black/20 px-2 py-1 text-[9px] font-extrabold uppercase tracking-wider text-white backdrop-blur-sm">
                                    {{ $team->short_name ?: Str::upper(Str::substr($team->name, 0, 3)) }}
                                </div>

                                {{-- Role badge --}}
                                <div class="absolute inset-x-0 bottom-0 z-[3] flex translate-y-1/2 justify-center">
                                    <span class="{{ $roleBadge }} max-w-[92%] truncate rounded-full border-2 border-white px-3 py-1.5 text-[9px] font-extrabold uppercase tracking-wider text-white shadow-lg sm:px-4 sm:text-[10px]">
                                        {{ $playerRole !== '' ? $playerRole : 'Player' }}
                                    </span>
                                </div>
                            </div>

                            {{-- Player details --}}
                            <div class="relative px-2 pb-4 pt-7 text-center sm:px-3">

                                <h3 class="break-words text-sm font-black leading-5 text-[#172554] sm:text-base">
                                    {{ $playerName }}
                                </h3>

                                <div class="mt-2 flex items-center justify-center gap-2 text-xs font-medium text-slate-500">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        class="h-4 w-4 shrink-0 text-[#F06445]"
                                        aria-hidden="true"
                                    >
                                        <circle cx="12" cy="12" r="9"></circle>
                                        <path d="M3 12h18M12 3a15 15 0 0 1 0 18M12 3a15 15 0 0 0 0 18"></path>
                                    </svg>

                                    <span class="truncate">
                                        {{ $player->nationality ?: 'Nationality not listed' }}
                                    </span>
                                </div>

                                <div class="mx-auto mt-4 h-1 w-10 rounded-full bg-[#F06445] transition-all duration-300 group-hover:w-16"></div>

                                <p class="mt-3 break-words text-[9px] font-bold uppercase tracking-[0.13em] text-slate-400">
                                    {{ $team->short_name ?: $team->name }}
                                </p>
                            </div>

                        </article>
                    @endforeach

                </div>

            @else
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.7"
                            class="h-7 w-7"
                            aria-hidden="true"
                        >
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

        {{-- Live matches --}}
        <section class="mt-12">

            <div class="mb-5 flex items-center gap-3">
                <span class="h-2.5 w-2.5 rounded-full bg-red-600"></span>
                <div>
                    <h2 class="text-2xl font-extrabold">Live matches</h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Current match status and recorded innings scores.
                    </p>
                </div>
            </div>

            @if ($liveFixtures->isNotEmpty())
                <div class="space-y-4">
                    @foreach ($liveFixtures as $match)
                        @php
                            $innings = $scorecardsByFixture->get($match->id, collect());
                            $latestInningsNumber = $innings->max('innings_number');
                        @endphp

                        <article class="overflow-hidden rounded-xl border border-slate-200 bg-white">

                            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4">
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-wide text-[#202766]">
                                        {{ $match->season?->name ?? 'NPL Season' }}
                                    </p>

                                    <p class="mt-1 text-xs text-slate-500">
                                        Match {{ $match->match_number ?? '—' }}
                                        @if ($match->venue)
                                            · {{ $match->venue }}
                                        @endif
                                    </p>
                                </div>

                                <span class="inline-flex items-center gap-2 rounded-full bg-red-50 px-3 py-1.5 text-xs font-extrabold uppercase text-red-700">
                                    <span class="h-1.5 w-1.5 rounded-full bg-red-600"></span>
                                    Live
                                </span>
                            </div>

                            <div class="grid gap-5 p-5 md:grid-cols-2">

                                <div class="grid grid-cols-[1fr_auto_1fr] items-center gap-3">
                                    <div class="min-w-0 text-center">
                                        @if ($match->homeTeam?->logo)
                                            <img
                                                src="{{ asset('storage/' . $match->homeTeam->logo) }}"
                                                alt="{{ $match->homeTeam->name }}"
                                                class="mx-auto h-12 w-12 object-contain"
                                            >
                                        @endif

                                        <p class="mt-2 break-words text-sm font-bold text-slate-900">
                                            {{ $match->homeTeam?->short_name ?: $match->homeTeam?->name ?: 'Home team' }}
                                        </p>
                                    </div>

                                    <span class="text-xs font-extrabold tracking-widest text-slate-300">VS</span>

                                    <div class="min-w-0 text-center">
                                        @if ($match->awayTeam?->logo)
                                            <img
                                                src="{{ asset('storage/' . $match->awayTeam->logo) }}"
                                                alt="{{ $match->awayTeam->name }}"
                                                class="mx-auto h-12 w-12 object-contain"
                                            >
                                        @endif

                                        <p class="mt-2 break-words text-sm font-bold text-slate-900">
                                            {{ $match->awayTeam?->short_name ?: $match->awayTeam?->name ?: 'Away team' }}
                                        </p>
                                    </div>
                                </div>

                                <div class="border-t border-slate-100 pt-4 md:border-l md:border-t-0 md:pl-5 md:pt-0">
                                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">
                                        Innings score
                                    </h3>

                                    @if ($innings->isNotEmpty())
                                        <div class="mt-3 space-y-3">
                                            @foreach ($innings as $scorecard)
                                                @php
                                                    $isCurrentInnings = (int) $scorecard->innings_number === (int) $latestInningsNumber;

                                                    $oversValue = $scorecard->overs ?? '0';
                                                    $oversLabel = is_numeric($oversValue)
                                                        ? number_format((float) $oversValue, 1, '.', '')
                                                        : $oversValue;
                                                @endphp

                                                <div class="flex items-center justify-between gap-3 {{ $isCurrentInnings ? 'border-l-2 border-red-500 bg-red-50/60 py-2 pl-3 pr-2' : 'py-1' }}">
                                                    <div class="min-w-0">
                                                        <p class="text-sm font-bold text-slate-800">
                                                            {{ $teamNames[$scorecard->batting_team_id] ?? 'Batting team' }}
                                                        </p>

                                                        <p class="mt-1 text-xs text-slate-500">
                                                            Innings {{ $scorecard->innings_number }}

                                                            @if ($isCurrentInnings)
                                                                <span class="ml-1 font-bold text-red-600">· Current</span>
                                                            @endif
                                                        </p>
                                                    </div>

                                                    <div class="shrink-0 text-right">
                                                        <p class="text-xl font-extrabold tabular-nums text-[#202766]">
                                                            {{ $scorecard->total_runs ?? 0 }}/{{ $scorecard->wickets ?? 0 }}
                                                        </p>

                                                        <p class="mt-1 text-xs text-slate-500">
                                                            {{ $oversLabel }} overs
                                                        </p>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="mt-3 text-sm text-slate-500">
                                            Innings score has not been recorded yet.
                                        </p>
                                    @endif
                                </div>

                            </div>

                            <div class="border-t border-slate-100 bg-slate-50 px-5 py-3">
                                <a
                                    href="{{ route('fixtures.show', $match) }}"
                                    class="inline-flex items-center gap-2 text-sm font-bold text-[#202766] hover:text-[#F06445]"
                                >
                                    Open match centre <span aria-hidden="true">→</span>
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="rounded-xl border border-slate-200 bg-white px-6 py-8">
                    <p class="text-sm font-semibold text-slate-700">No live matches right now.</p>
                    <p class="mt-1 text-sm text-slate-500">
                        Upcoming fixtures and previous results are listed below.
                    </p>
                </div>
            @endif

        </section>

        {{-- Upcoming fixtures --}}
        <section class="mt-12">

            <div class="mb-5 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#F06445]">Coming up</p>
                    <h2 class="mt-2 text-2xl font-extrabold">Upcoming matches</h2>
                </div>

                <a
                    href="{{ route('fixtures.index') }}"
                    class="text-sm font-bold text-[#202766] hover:text-[#F06445]"
                >
                    All fixtures →
                </a>
            </div>

            @if ($upcomingFixtures->isNotEmpty())
                <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
                    <div class="divide-y divide-slate-100">
                        @foreach ($upcomingFixtures as $match)
                            <a
                                href="{{ route('fixtures.show', $match) }}"
                                class="grid gap-3 px-5 py-5 transition hover:bg-slate-50 sm:grid-cols-[1fr_1.5fr_1fr] sm:items-center"
                            >
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-wide text-[#202766]">
                                        Match {{ $match->match_number ?? '—' }}
                                    </p>

                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ $match->season?->name ?? 'NPL Season' }}
                                    </p>
                                </div>

                                <div class="grid grid-cols-[1fr_auto_1fr] items-center gap-3">
                                    <p class="break-words text-sm font-bold text-slate-900">
                                        {{ $match->homeTeam?->short_name ?: $match->homeTeam?->name ?: 'Home team' }}
                                    </p>

                                    <span class="text-xs font-bold text-slate-300">VS</span>

                                    <p class="break-words text-right text-sm font-bold text-slate-900">
                                        {{ $match->awayTeam?->short_name ?: $match->awayTeam?->name ?: 'Away team' }}
                                    </p>
                                </div>

                                <div class="sm:text-right">
                                    <p class="text-sm font-semibold text-slate-800">
                                        {{ $match->scheduled_at?->timezone(config('app.timezone'))->format('D, d M Y') ?? 'Date TBA' }}
                                    </p>

                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ $match->scheduled_at?->timezone(config('app.timezone'))->format('h:i A') ?? 'Time TBA' }}

                                        @if ($match->venue)
                                            · {{ $match->venue }}
                                        @endif
                                    </p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="rounded-xl border border-slate-200 bg-white px-5 py-8">
                    <p class="text-sm font-semibold text-slate-700">No upcoming matches.</p>
                    <p class="mt-1 text-sm text-slate-500">
                        New scheduled fixtures will appear here.
                    </p>
                </div>
            @endif

        </section>

        {{-- Recent results --}}
        <section class="mt-12">

            <div class="mb-5">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#F06445]">Match history</p>
                <h2 class="mt-2 text-2xl font-extrabold">Recent results</h2>
            </div>

            @if ($completedFixtures->isNotEmpty())
                <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
                    <div class="divide-y divide-slate-100">
                        @foreach ($completedFixtures as $match)
                            @php
                                $resultIsVerified = (bool) ($match->is_result_verified ?? false);
                            @endphp

                            <a
                                href="{{ route('fixtures.show', $match) }}"
                                class="grid gap-3 px-5 py-5 transition hover:bg-slate-50 sm:grid-cols-[1fr_1.5fr_1fr] sm:items-center"
                            >
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-wide text-[#202766]">
                                        Match {{ $match->match_number ?? '—' }}
                                    </p>

                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ $match->scheduled_at?->timezone(config('app.timezone'))->format('d M Y') ?? 'Date not available' }}
                                    </p>
                                </div>

                                <div class="grid grid-cols-[1fr_auto_1fr] items-center gap-3">
                                    <div>
                                        <p class="break-words text-sm font-bold text-slate-900">
                                            {{ $match->homeTeam?->short_name ?: $match->homeTeam?->name ?: 'Home team' }}
                                        </p>

                                        @if ($resultIsVerified)
                                            <p class="mt-1 text-sm font-extrabold text-[#202766]">
                                                {{ $match->home_runs ?? 0 }}/{{ $match->home_wickets ?? 0 }}
                                            </p>
                                        @endif
                                    </div>

                                    <span class="text-xs font-bold text-slate-300">VS</span>

                                    <div class="text-right">
                                        <p class="break-words text-sm font-bold text-slate-900">
                                            {{ $match->awayTeam?->short_name ?: $match->awayTeam?->name ?: 'Away team' }}
                                        </p>

                                        @if ($resultIsVerified)
                                            <p class="mt-1 text-sm font-extrabold text-[#202766]">
                                                {{ $match->away_runs ?? 0 }}/{{ $match->away_wickets ?? 0 }}
                                            </p>
                                        @endif
                                    </div>
                                </div>

                                <div class="sm:text-right">
                                    @if ($resultIsVerified && $match->result_summary)
                                        <p class="text-sm font-semibold leading-5 text-emerald-700">
                                            {{ $match->result_summary }}
                                        </p>
                                    @elseif ($resultIsVerified)
                                        <p class="text-sm font-semibold text-emerald-700">
                                            Result confirmed
                                        </p>
                                    @else
                                        <p class="text-sm font-semibold text-amber-700">
                                            Result pending verification
                                        </p>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="rounded-xl border border-slate-200 bg-white px-5 py-8">
                    <p class="text-sm font-semibold text-slate-700">No completed matches yet.</p>
                    <p class="mt-1 text-sm text-slate-500">
                        Results will appear here when matches are completed.
                    </p>
                </div>
            @endif

        </section>

        {{-- About the team --}}
        @if ($team->description)
            <section class="mt-12 rounded-xl border border-slate-200 bg-white p-6 sm:p-8">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#F06445]">
                    Team profile
                </p>

                <h2 class="mt-2 text-2xl font-extrabold">
                    About {{ $team->name }}
                </h2>

                <p class="mt-4 whitespace-pre-line break-words text-sm leading-7 text-slate-600">{{ $team->description }}</p>
            </section>
        @endif

        {{-- Participating seasons --}}
        <section class="mt-12">
            <div class="mb-5">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#F06445]">
                    Competition history
                </p>

                <h2 class="mt-2 text-2xl font-extrabold">
                    Participating seasons
                </h2>
            </div>

            @if ($team->seasons && $team->seasons->isNotEmpty())
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($team->seasons as $season)
                        <a
                            href="{{ route('seasons.show', $season) }}"
                            class="rounded-xl border border-slate-200 bg-white p-5 transition hover:border-[#202766] hover:shadow-sm"
                        >
                            <p class="text-xs font-bold uppercase tracking-wide text-[#F06445]">
                                Season {{ $season->season_number ?? '' }}
                            </p>

                            <h3 class="mt-2 font-extrabold">{{ $season->name }}</h3>

                            <span class="mt-4 inline-block text-sm font-bold text-[#202766]">
                                View season →
                            </span>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="rounded-xl border border-slate-200 bg-white p-5 text-sm text-slate-500">
                    No seasons have been assigned to this team yet.
                </div>
            @endif
        </section>

        {{-- Back to teams --}}
        <div class="mt-9 border-t border-slate-200 pt-6">
            <a
                href="{{ route('teams.index') }}"
                class="inline-flex items-center gap-2 rounded-lg bg-[#202766] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#171D50]"
            >
                ← All teams
            </a>
        </div>

    </div>
</div>

@endsection
