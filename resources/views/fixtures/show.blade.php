@extends('layouts.app')

@section('title', 'Match Centre | NPL Season Hub')

@section('content')
@php
    $statusStyles = [
        'live' => 'bg-red-50 text-red-700',
        'scheduled' => 'bg-blue-50 text-blue-700',
        'completed' => 'bg-emerald-50 text-emerald-700',
        'postponed' => 'bg-orange-50 text-orange-700',
        'abandoned' => 'bg-slate-100 text-slate-600',
    ];

    $statusStyle = $statusStyles[$fixture->status] ?? 'bg-slate-100 text-slate-700';

    $homeName = $fixture->homeTeam->short_name ?: $fixture->homeTeam->name;
    $awayName = $fixture->awayTeam->short_name ?: $fixture->awayTeam->name;
@endphp

<div class="min-h-screen bg-[#F6F8FB] text-[#202766]">

    {{-- Breadcrumb and page heading --}}
    <section class="border-b border-slate-200 bg-white">
        <div class="mx-auto max-w-7xl px-5 py-7 sm:px-8 lg:px-10">

            <nav class="mb-7 flex flex-wrap items-center gap-2 text-sm text-slate-500">
                <a href="{{ route('home') }}" class="hover:text-[#202766]">
                    Home
                </a>
                <span>/</span>
                <a href="{{ route('fixtures.index') }}" class="hover:text-[#202766]">
                    Fixtures
                </a>
                <span>/</span>
                <span class="font-semibold text-[#202766]">
                    Match {{ $fixture->match_number }}
                </span>
            </nav>

            <div class="flex flex-col justify-between gap-5 md:flex-row md:items-end">
                <div>
                    <p class="mb-3 text-xs font-bold uppercase tracking-[0.2em] text-[#F06445]">
                        {{ $fixture->season->name }}
                    </p>

                    <h1 class="text-3xl font-extrabold tracking-tight text-[#202766] sm:text-4xl">
                        Match Centre
                    </h1>

                    <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-500 sm:text-base">
                        Match details, official scorecard and result information in one place.
                    </p>
                </div>

                <span class="inline-flex w-fit items-center gap-2 rounded-sm px-4 py-2 text-xs font-bold uppercase tracking-wider {{ $statusStyle }}">
                    @if ($fixture->status === 'live')
                        <span class="h-2 w-2 animate-pulse rounded-full bg-red-600"></span>
                    @endif
                    {{ $fixture->status === 'scheduled' ? 'Not Started Yet' : ucfirst($fixture->status) }}
                </span>
            </div>
        </div>
    </section>

    <main class="mx-auto max-w-7xl px-5 py-9 sm:px-8 lg:px-10">

        {{-- Match information heading --}}
        <div class="mb-5 flex flex-col justify-between gap-2 sm:flex-row sm:items-end">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#F06445]">
                    Match information
                </p>
                <h2 class="mt-2 text-2xl font-extrabold text-[#202766]">
                    {{ $fixture->homeTeam->name }}
                    <span class="font-medium text-slate-400">vs</span>
                    {{ $fixture->awayTeam->name }}
                </h2>
            </div>

            <p class="text-sm text-slate-500">
                Match {{ $fixture->match_number }}
                <span class="mx-1">·</span>
                {{ ucfirst($fixture->match_type) }}
            </p>
        </div>

        {{-- Main scorecard --}}
        <section class="overflow-hidden border border-slate-200 bg-white">

            {{-- Navy competition header --}}
            <div class="bg-[#202766] px-5 py-5 text-white sm:px-8">
                <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-orange-200">
                            Nepal Premier League
                        </p>
                        <h3 class="mt-1 text-lg font-bold">
                            {{ $fixture->season->name }}
                        </h3>
                    </div>

                    <div class="text-sm leading-6 text-slate-200 sm:text-right">
                        <p>
                            {{ $fixture->scheduled_at?->format('D, d M Y') ?? 'Date to be announced' }}
                        </p>
                        <p>
                            {{ $fixture->scheduled_at?->format('h:i A') ?? 'Time to be announced' }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Team score area --}}
            <div class="px-5 py-9 sm:px-10 sm:py-12">
                <div class="grid grid-cols-[1fr_auto_1fr] items-center gap-3 sm:gap-8">

                    {{-- Home team --}}
                    <div class="min-w-0 text-center">
                        @if ($fixture->homeTeam->logo)
                            <img
                                src="{{ asset('storage/' . $fixture->homeTeam->logo) }}"
                                alt="{{ $fixture->homeTeam->name }} logo"
                                class="mx-auto h-16 w-16 object-contain sm:h-24 sm:w-24"
                            >
                        @else
                            <div class="mx-auto flex h-16 w-16 items-center justify-center border border-slate-200 bg-[#F6F8FB] text-lg font-extrabold text-[#202766] sm:h-24 sm:w-24 sm:text-2xl">
                                {{ strtoupper(substr($homeName, 0, 3)) }}
                            </div>
                        @endif

                        <h3 class="mx-auto mt-4 max-w-52 break-words text-base font-extrabold leading-snug text-[#202766] sm:text-xl">
                            {{ $fixture->homeTeam->name }}
                        </h3>

                        @if ($fixture->is_result_verified)
                            <p class="mt-5 text-3xl font-extrabold tracking-tight text-[#202766] sm:text-5xl">
                                {{ $fixture->home_runs ?? 0 }}
                                <span class="text-slate-400">/</span>
                                {{ $fixture->home_wickets ?? 0 }}
                            </p>
                            <p class="mt-2 text-sm text-slate-500">
                                {{ $fixture->home_overs ?? '—' }} overs
                            </p>
                        @else
                            <p class="mt-5 text-sm font-medium text-slate-400">
                                Score not available
                            </p>
                        @endif
                    </div>

                    {{-- Versus --}}
                    <div class="flex flex-col items-center gap-3">
                        <span class="flex h-11 w-11 items-center justify-center bg-[#F06445] text-xs font-extrabold text-white sm:h-14 sm:w-14 sm:text-sm">
                            VS
                        </span>

                        <span class="text-center text-[10px] font-bold uppercase tracking-wider text-slate-400 sm:text-xs">
                            {{ $fixture->status === 'live'
                                ? 'Live match'
                                : ($fixture->status === 'completed' ? 'Full time' : 'Upcoming') }}
                        </span>
                    </div>

                    {{-- Away team --}}
                    <div class="min-w-0 text-center">
                        @if ($fixture->awayTeam->logo)
                            <img
                                src="{{ asset('storage/' . $fixture->awayTeam->logo) }}"
                                alt="{{ $fixture->awayTeam->name }} logo"
                                class="mx-auto h-16 w-16 object-contain sm:h-24 sm:w-24"
                            >
                        @else
                            <div class="mx-auto flex h-16 w-16 items-center justify-center border border-slate-200 bg-[#F6F8FB] text-lg font-extrabold text-[#202766] sm:h-24 sm:w-24 sm:text-2xl">
                                {{ strtoupper(substr($awayName, 0, 3)) }}
                            </div>
                        @endif

                        <h3 class="mx-auto mt-4 max-w-52 break-words text-base font-extrabold leading-snug text-[#202766] sm:text-xl">
                            {{ $fixture->awayTeam->name }}
                        </h3>

                        @if ($fixture->is_result_verified)
                            <p class="mt-5 text-3xl font-extrabold tracking-tight text-[#202766] sm:text-5xl">
                                {{ $fixture->away_runs ?? 0 }}
                                <span class="text-slate-400">/</span>
                                {{ $fixture->away_wickets ?? 0 }}
                            </p>
                            <p class="mt-2 text-sm text-slate-500">
                                {{ $fixture->away_overs ?? '—' }} overs
                            </p>
                        @else
                            <p class="mt-5 text-sm font-medium text-slate-400">
                                Score not available
                            </p>
                        @endif
                    </div>

                </div>

                {{-- Result or status message --}}
                @if ($fixture->is_result_verified)
                    <div class="mx-auto mt-10 max-w-3xl border-l-4 border-[#F06445] bg-[#F6F8FB] px-5 py-5 sm:px-7">
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#F06445]">
                            Official match result
                        </p>

                        @if ($fixture->winner)
                            <h3 class="mt-2 text-xl font-extrabold text-[#202766]">
                                {{ $fixture->winner->name }} won
                            </h3>
                        @endif

                        @if ($fixture->result_summary)
                            <p class="mt-2 text-sm leading-7 text-slate-600">
                                {{ $fixture->result_summary }}
                            </p>
                        @endif

                        @if ($fixture->player_of_match)
                            <div class="mt-4 border-t border-slate-200 pt-4">
                                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                                    Player of the Match
                                </p>
                                <p class="mt-1 font-bold text-[#202766]">
                                    {{ $fixture->player_of_match }}
                                </p>
                            </div>
                        @endif

                        <p class="mt-4 text-xs font-semibold text-emerald-700">
                            ✓ Official result verified
                        </p>
                    </div>
                @else
                    <div class="mx-auto mt-10 max-w-3xl border border-slate-200 bg-[#F6F8FB] px-5 py-7 text-center sm:px-8">
                        @if ($fixture->status === 'scheduled')
                            <div class="mx-auto flex h-11 w-11 items-center justify-center bg-white text-[#202766]">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <circle cx="12" cy="12" r="9"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 2"/>
                                </svg>
                            </div>

                            <h3 class="mt-3 text-lg font-extrabold text-[#202766]">
                                Match not started yet
                            </h3>

                            <p class="mx-auto mt-2 max-w-xl text-sm leading-6 text-slate-500">
                                @if ($fixture->scheduled_at)
                                    The match is scheduled for
                                    {{ $fixture->scheduled_at->format('d M Y, h:i A') }}.
                                @else
                                    The official match date and time have not been announced yet.
                                @endif
                                Score details will appear here when an official result is verified.
                            </p>
                        @elseif ($fixture->status === 'live')
                            <span class="mx-auto block h-3 w-3 animate-pulse rounded-full bg-red-500"></span>
                            <h3 class="mt-3 text-lg font-extrabold text-[#202766]">
                                Match in progress
                            </h3>
                            <p class="mt-2 text-sm leading-6 text-slate-500">
                                An official score has not been published yet.
                            </p>
                        @elseif ($fixture->status === 'completed')
                            <h3 class="text-lg font-extrabold text-[#202766]">
                                Result awaiting verification
                            </h3>
                            <p class="mt-2 text-sm leading-6 text-slate-500">
                                The match is marked as completed. The official result will appear after verification.
                            </p>
                        @elseif ($fixture->status === 'postponed')
                            <h3 class="text-lg font-extrabold text-[#202766]">
                                Match postponed
                            </h3>
                            <p class="mt-2 text-sm text-slate-500">
                                Please check back for updated scheduling information.
                            </p>
                        @elseif ($fixture->status === 'abandoned')
                            <h3 class="text-lg font-extrabold text-[#202766]">
                                Match abandoned
                            </h3>
                            <p class="mt-2 text-sm text-slate-500">
                                No verified result is available for this match.
                            </p>
                        @else
                            <h3 class="text-lg font-extrabold text-[#202766]">
                                Score not available
                            </h3>
                            <p class="mt-2 text-sm text-slate-500">
                                Official match information will appear here when available.
                            </p>
                        @endif
                    </div>
                @endif
            </div>
        </section>

        {{-- Match details --}}
        <section class="mt-9">
            <div class="mb-5">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#F06445]">
                    Match details
                </p>
                <h2 class="mt-2 text-2xl font-extrabold text-[#202766]">
                    Match information
                </h2>
            </div>

            <div class="grid gap-5 md:grid-cols-3">

                <article class="border border-slate-200 bg-white p-6 transition hover:border-[#202766]/30">
                    <div class="flex h-10 w-10 items-center justify-center bg-[#202766] text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3M4 11h16M5 5h14a1 1 0 0 1 1 1v13H4V6a1 1 0 0 1 1-1Z"/>
                        </svg>
                    </div>
                    <p class="mt-5 text-xs font-bold uppercase tracking-wider text-slate-500">
                        Match date
                    </p>
                    <p class="mt-2 font-bold text-[#202766]">
                        {{ $fixture->scheduled_at?->format('d M Y') ?? 'To be announced' }}
                    </p>
                    <p class="mt-1 text-sm text-slate-500">
                        {{ $fixture->scheduled_at?->format('h:i A') ?? 'Time not announced' }}
                    </p>
                </article>

                <article class="border border-slate-200 bg-white p-6 transition hover:border-[#202766]/30">
                    <div class="flex h-10 w-10 items-center justify-center bg-[#F06445] text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21s7-5.2 7-12a7 7 0 1 0-14 0c0 6.8 7 12 7 12Z"/>
                            <circle cx="12" cy="9" r="2.3"/>
                        </svg>
                    </div>
                    <p class="mt-5 text-xs font-bold uppercase tracking-wider text-slate-500">
                        Venue
                    </p>
                    <p class="mt-2 font-bold text-[#202766]">
                        {{ $fixture->venue ?: 'To be announced' }}
                    </p>
                    <p class="mt-1 text-sm text-slate-500">
                        Match location
                    </p>
                </article>

                <article class="border border-slate-200 bg-white p-6 transition hover:border-[#202766]/30">
                    <div class="flex h-10 w-10 items-center justify-center bg-[#202766] text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <circle cx="12" cy="12" r="9"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h8M12 8v8"/>
                        </svg>
                    </div>
                    <p class="mt-5 text-xs font-bold uppercase tracking-wider text-slate-500">
                        Match format
                    </p>
                    <p class="mt-2 font-bold text-[#202766]">
                        {{ ucfirst($fixture->match_type) }}
                    </p>
                    <p class="mt-1 text-sm text-slate-500">
                        Match {{ $fixture->match_number }}
                    </p>
                </article>

            </div>
        </section>

{{-- Ball-by-ball scorecard --}}
<section class="mt-10">
    <div class="mb-5">
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#F06445]">
            Live commentary
        </p>
        <h2 class="mt-2 text-2xl font-extrabold text-[#202766]">
            Ball-by-ball scorecard
        </h2>
        <p class="mt-2 text-sm text-slate-500">
            प्रत्येक बलको नतिजा, batsman र bowler को विवरण।
        </p>
    </div>

    @forelse ($innings as $inning)
        @php
            $battingTeam = $inning->batting_team_id == $fixture->home_team_id
                ? $fixture->homeTeam
                : $fixture->awayTeam;

            $legalBalls = (int) ($inning->legal_balls ?? 0);
            $overs = intdiv($legalBalls, 6) . '.' . ($legalBalls % 6);
        @endphp

        <article class="mb-7 overflow-hidden border border-slate-200 bg-white">
            <div class="flex flex-col justify-between gap-3 bg-[#202766] px-5 py-5 text-white sm:flex-row sm:items-center sm:px-7">
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest text-orange-200">
                        Innings {{ $inning->innings_number }}
                    </p>
                    <h3 class="mt-1 text-xl font-extrabold">
                        {{ $battingTeam->name ?? 'Batting team' }}
                    </h3>
                </div>

                <div class="sm:text-right">
                    <p class="text-3xl font-extrabold">
                        {{ $inning->total_runs }}/{{ $inning->wickets }}
                    </p>
                    <p class="mt-1 text-sm text-slate-300">
                        {{ $overs }} overs
                        @if ($inning->is_current)
                            <span class="ml-2 inline-flex items-center gap-1 font-bold text-red-300">
                                <span class="h-2 w-2 animate-pulse rounded-full bg-red-400"></span>
                                LIVE
                            </span>
                        @endif
                    </p>
                </div>
            </div>

            <div class="p-4 sm:p-6">
                @if ($inning->deliveries->isEmpty())
                    <div class="border border-dashed border-slate-300 px-4 py-8 text-center">
                        <p class="font-semibold text-[#202766]">
                            Ball-by-ball data उपलब्ध छैन।
                        </p>
                        <p class="mt-2 text-sm text-slate-500">
                            यस innings का delivery records database मा थपिएपछि यहाँ देखिनेछन्।
                        </p>
                    </div>
                @else
                    @php
                        $overGroups = $inning->deliveries->groupBy(function ($delivery) {
                            return $delivery->over_number ?? 'unknown';
                        });
                    @endphp

                    @foreach ($overGroups as $overNumber => $balls)
                        <div class="mb-5 overflow-hidden border border-slate-200 last:mb-0">
                            <div class="flex items-center justify-between bg-slate-50 px-4 py-3">
                                <h4 class="font-extrabold text-[#202766]">
                                    Over {{ is_numeric($overNumber) ? ((int) $overNumber + 1) : $overNumber }}
                                </h4>
                                <span class="text-xs font-semibold text-slate-500">
                                    {{ $balls->count() }} deliveries recorded
                                </span>
                            </div>

                            <div class="divide-y divide-slate-100">
                                @foreach ($balls as $ball)
                                    @php
                                        $runs = (int) ($ball->total_runs ?? $ball->runs ?? 0);
                                        $batterRuns = (int) ($ball->batter_runs ?? $ball->runs_off_bat ?? 0);
                                        $extras = (int) ($ball->extras ?? 0);
                                        $isWicket = (bool) ($ball->is_wicket ?? false);
                                        $extraType = strtolower($ball->extra_type ?? '');
                                        $label = $ball->commentary ?? $ball->description ?? null;

                                        if ($isWicket) {
                                            $resultLabel = 'WICKET';
                                            $badgeClass = 'bg-red-600 text-white';
                                        } elseif (str_contains($extraType, 'wide')) {
                                            $resultLabel = 'WD';
                                            $badgeClass = 'bg-orange-100 text-orange-800';
                                        } elseif (str_contains($extraType, 'bye') || str_contains($extraType, 'leg')) {
                                            $resultLabel = strtoupper($extraType ?: 'EXTRA');
                                            $badgeClass = 'bg-blue-100 text-blue-800';
                                        } elseif ($batterRuns === 6) {
                                            $resultLabel = 'SIX';
                                            $badgeClass = 'bg-purple-100 text-purple-800';
                                        } elseif ($batterRuns === 4) {
                                            $resultLabel = 'FOUR';
                                            $badgeClass = 'bg-emerald-100 text-emerald-800';
                                        } elseif ($runs === 0) {
                                            $resultLabel = 'DOT';
                                            $badgeClass = 'bg-slate-100 text-slate-700';
                                        } else {
                                            $resultLabel = '+' . $runs;
                                            $badgeClass = 'bg-slate-100 text-slate-800';
                                        }
                                    @endphp

                                    <div class="flex items-start gap-3 px-4 py-4 sm:gap-4">
                                        <div class="flex h-10 min-w-10 items-center justify-center bg-[#F6F8FB] px-2 text-xs font-extrabold text-[#202766]">
                                            {{ $ball->ball_number ?? '•' }}
                                        </div>

                                        <div class="min-w-0 flex-1">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="font-bold text-[#202766]">
                                                    {{ $ball->batsman_name ?? $ball->batter_name ?? 'Batter' }}
                                                </span>

                                                <span class="text-slate-400">vs</span>

                                                <span class="text-sm text-slate-600">
                                                    {{ $ball->bowler_name ?? 'Bowler' }}
                                                </span>
                                            </div>

                                            <p class="mt-1 text-sm leading-6 text-slate-600">
                                                @if ($label)
                                                    {{ $label }}
                                                @elseif ($isWicket)
                                                    Wicket! Batter dismissed.
                                                @elseif (str_contains($extraType, 'wide'))
                                                    Wide ball.
                                                @elseif (str_contains($extraType, 'leg'))
                                                    Leg bye.
                                                @elseif (str_contains($extraType, 'bye'))
                                                    Bye.
                                                @elseif ($batterRuns === 4)
                                                    Four runs!
                                                @elseif ($batterRuns === 6)
                                                    Six runs!
                                                @elseif ($runs === 0)
                                                    No run.
                                                @else
                                                    {{ $runs }} run(s).
                                                @endif
                                            </p>
                                        </div>

                                        <span class="inline-flex shrink-0 items-center justify-center px-3 py-2 text-xs font-extrabold {{ $badgeClass }}">
                                            {{ $resultLabel }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </article>
    @empty
        <div class="border border-slate-200 bg-white px-5 py-10 text-center">
            <h3 class="font-bold text-[#202766]">
                Innings data उपलब्ध छैन।
            </h3>
            <p class="mt-2 text-sm text-slate-500">
                यस match को innings records database मा जाँच गर्नुहोस्।
            </p>
        </div>
    @endforelse
</section>


        {{-- Bottom navigation --}}
        <div class="mt-9 flex flex-col gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:items-center sm:justify-between">
            <a href="{{ route('fixtures.index') }}"
               class="inline-flex items-center justify-center gap-2 bg-[#202766] px-6 py-3 text-sm font-bold text-white transition hover:bg-[#171d50]">
                <span aria-hidden="true">←</span>
                Browse all fixtures
            </a>

            <a href="{{ route('home') }}"
               class="inline-flex items-center justify-center border border-slate-300 bg-white px-6 py-3 text-sm font-bold text-[#202766] transition hover:bg-slate-100">
                Back to homepage
            </a>
        </div>

    </main>
</div>
@endsection