

@extends('layouts.app')

@section('title', 'Fixtures & Results | NPL Season Hub')

@section('meta_description', 'Explore Nepal Premier League fixtures, match-wise scorecards, match results, dates, venues and team scores.')

@section('content')

@php
    use App\Models\ManualScorecard;
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

    $timezone = config('app.timezone', 'Asia/Kathmandu');
    $today = now($timezone)->startOfDay();

    $fixtures = $fixtures ?? collect();
    $fixtureCollection = method_exists($fixtures, 'getCollection')
        ? $fixtures->getCollection()
        : collect($fixtures);

    /*
    |--------------------------------------------------------------------------
    | Match-wise manual scorecards
    |--------------------------------------------------------------------------
    | Every innings is grouped by its own fixture_id. This prevents scores
    | from one match appearing on another match's card.
    */

    $fixtureIds = $fixtureCollection
        ->pluck('id')
        ->filter()
        ->unique()
        ->values();

    $manualScorecards = ManualScorecard::query()
        ->whereIn('fixture_id', $fixtureIds)
        ->orderBy('fixture_id')
        ->orderBy('innings_number')
        ->get()
        ->groupBy('fixture_id');

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    $imageUrl = function ($path) {
        if (blank($path)) {
            return null;
        }

        if (Str::startsWith($path, ['https://', 'http://', 'data:image/'])) {
            return $path;
        }

        return Storage::url(ltrim($path, '/'));
    };

    $teamName = function ($team, $fallback = 'Team') {
        return $team?->name
            ?: $team?->short_name
            ?: $fallback;
    };

    $teamShortName = function ($team, $fallback = 'TEAM') {
        return $team?->short_name
            ?: $team?->name
            ?: $fallback;
    };

    $teamLogo = function ($team) use ($imageUrl) {
        return $imageUrl(
            $team?->logo
            ?? $team?->logo_path
            ?? $team?->image
            ?? $team?->image_path
            ?? null
        );
    };

    $formatOvers = function ($inning) {
        if (!$inning) {
            return null;
        }

        $overs = $inning->overs ?? null;
        $legalBalls = $inning->legal_balls ?? null;

        if ($legalBalls !== null && is_numeric($legalBalls)) {
            return intdiv((int) $legalBalls, 6)
                . '.'
                . ((int) $legalBalls % 6);
        }

        if ($overs === null || $overs === '') {
            return null;
        }

        return (string) $overs;
    };

    $formatDate = function ($date) use ($timezone) {
        if (!$date) {
            return null;
        }

        try {
            return \Illuminate\Support\Carbon::parse($date, $timezone)
                ->timezone($timezone)
                ->format('D, d M Y');
        } catch (\Throwable $e) {
            return (string) $date;
        }
    };

    $formatTime = function ($date) use ($timezone) {
        if (!$date) {
            return null;
        }

        try {
            return \Illuminate\Support\Carbon::parse($date, $timezone)
                ->timezone($timezone)
                ->format('h:i A');
        } catch (\Throwable $e) {
            return null;
        }
    };

    $statusInfo = function ($fixture) {
        $raw = strtolower(trim((string) ($fixture->status ?? 'scheduled')));

        if (
            str_contains($raw, 'live')
            || str_contains($raw, 'progress')
            || str_contains($raw, 'innings')
        ) {
            return [
                'label' => 'LIVE',
                'class' => 'bg-red-50 text-red-700 ring-red-200',
                'dot' => 'bg-red-500 animate-pulse',
            ];
        }

        if (
            str_contains($raw, 'complete')
            || str_contains($raw, 'finished')
            || str_contains($raw, 'result')
        ) {
            return [
                'label' => 'COMPLETED',
                'class' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                'dot' => 'bg-emerald-500',
            ];
        }

        if (
            str_contains($raw, 'abandon')
            || str_contains($raw, 'cancel')
            || str_contains($raw, 'no_result')
        ) {
            return [
                'label' => 'NO RESULT',
                'class' => 'bg-gray-100 text-gray-600 ring-gray-200',
                'dot' => 'bg-gray-400',
            ];
        }

        if (str_contains($raw, 'postpon')) {
            return [
                'label' => 'POSTPONED',
                'class' => 'bg-amber-50 text-amber-700 ring-amber-200',
                'dot' => 'bg-amber-500',
            ];
        }

        return [
            'label' => 'UPCOMING',
            'class' => 'bg-blue-50 text-blue-700 ring-blue-200',
            'dot' => 'bg-blue-500',
        ];
    };
@endphp

<div class="min-h-screen bg-[#F5F7FB]">
    {{-- PAGE HEADER --}}
    <section class="border-b border-gray-200 bg-white">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <div class="mb-3 inline-flex items-center gap-2 rounded-full bg-orange-50 px-3 py-1.5 text-xs font-bold uppercase tracking-wider text-orange-700">
                        <span class="h-2 w-2 rounded-full bg-orange-500"></span>
                        Nepal Premier League
                    </div>

                    <h1 class="text-3xl font-black tracking-tight text-gray-950 sm:text-4xl">
                        Fixtures <span class="text-orange-600">&</span> Results
                    </h1>

                    <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-500 sm:text-base">
                        Follow every match, check team scores and explore match-wise scorecards.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <div class="rounded-xl border border-gray-200 bg-white px-4 py-3">
                        <p class="text-xs font-medium text-gray-500">Total matches</p>
                        <p class="mt-1 text-2xl font-black text-gray-900">
                            {{ method_exists($fixtures, 'total') ? $fixtures->total() : $fixtureCollection->count() }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- FIXTURE LIST --}}
    <section class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @if ($fixtureCollection->isEmpty())
            <div class="rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-16 text-center">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-gray-100 text-gray-400">
                    <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                        <circle cx="12" cy="12" r="9"></circle>
                        <path d="M12 7v5l3 2"></path>
                    </svg>
                </div>

                <h2 class="mt-5 text-lg font-bold text-gray-900">
                    No fixtures found
                </h2>

                <p class="mt-2 text-sm text-gray-500">
                    Matches will appear here when fixtures are available.
                </p>
            </div>
        @else
            <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">All Matches</h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Each match displays scores from its own saved innings.
                    </p>
                </div>

                <div class="flex items-center gap-2 text-xs font-medium text-gray-500">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    Saved scorecards appear automatically
                </div>
            </div>

            <div class="space-y-4">
                @foreach ($fixtureCollection as $fixture)
                    @php
                        $home = $fixture->homeTeam ?? null;
                        $away = $fixture->awayTeam ?? null;

                        $homeName = $teamName($home, 'Home Team');
                        $awayName = $teamName($away, 'Away Team');

                        $homeShortName = $teamShortName($home, 'HOME');
                        $awayShortName = $teamShortName($away, 'AWAY');

                        $homeLogo = $teamLogo($home);
                        $awayLogo = $teamLogo($away);

                        /*
                         * IMPORTANT:
                         * Only retrieve scorecards whose fixture_id matches
                         * the current fixture.
                         */
                        $inningsForFixture = $manualScorecards
                            ->get($fixture->id, collect())
                            ->sortBy('innings_number')
                            ->values();

                        $homeInnings = $inningsForFixture
                            ->filter(function ($inning) use ($fixture) {
                                return (int) $inning->batting_team_id
                                    === (int) $fixture->home_team_id;
                            })
                            ->values();

                        $awayInnings = $inningsForFixture
                            ->filter(function ($inning) use ($fixture) {
                                return (int) $inning->batting_team_id
                                    === (int) $fixture->away_team_id;
                            })
                            ->values();

                        $homeInning = $homeInnings->last();
                        $awayInning = $awayInnings->last();

                        $homeScoreAvailable = $homeInning !== null;
                        $awayScoreAvailable = $awayInning !== null;

                        $homeRuns = $homeInning?->total_runs
                            ?? $homeInning?->runs;

                        $homeWickets = $homeInning?->wickets;

                        $awayRuns = $awayInning?->total_runs
                            ?? $awayInning?->runs;

                        $awayWickets = $awayInning?->wickets;

                        $homeOvers = $formatOvers($homeInning);
                        $awayOvers = $formatOvers($awayInning);

                        $homeScoreLabel = $homeScoreAvailable && $homeRuns !== null
                            ? $homeRuns . '/' . ($homeWickets ?? 0)
                            : null;

                        $awayScoreLabel = $awayScoreAvailable && $awayRuns !== null
                            ? $awayRuns . '/' . ($awayWickets ?? 0)
                            : null;

                        $status = $statusInfo($fixture);

                        $matchNumber = $fixture->match_number
                            ?? $fixture->match_no
                            ?? null;

                        $matchDate = $fixture->scheduled_at
                            ?? $fixture->match_date
                            ?? $fixture->date
                            ?? null;

                        $venue = $fixture->venue
                            ?? $fixture->ground
                            ?? null;

                        if (is_object($venue)) {
                            $venue = $venue->name ?? null;
                        }

                        $result = $fixture->result_summary
                            ?? $fixture->result
                            ?? null;

                        $hasAnyScore = $homeScoreAvailable || $awayScoreAvailable;

                        $fixtureUrl = route('fixtures.show', ['fixture' => $fixture->id]);
                    @endphp

                    <article class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition hover:border-gray-300 hover:shadow-md">
                        {{-- MATCH TOP BAR --}}
                        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-4 py-3 sm:px-6">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-lg bg-gray-100 px-2.5 py-1.5 text-xs font-bold text-gray-700">
                                    @if ($matchNumber)
                                        Match {{ $matchNumber }}
                                    @else
                                        Match #{{ $fixture->id }}
                                    @endif
                                </span>

                                @if (!empty($fixture->season?->name))
                                    <span class="text-xs font-medium text-gray-500">
                                        {{ $fixture->season->name }}
                                    </span>
                                @endif
                            </div>

                            <span class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-[10px] font-extrabold tracking-wider ring-1 {{ $status['class'] }}">
                                <span class="h-1.5 w-1.5 rounded-full {{ $status['dot'] }}"></span>
                                {{ $status['label'] }}
                            </span>
                        </div>

                        {{-- TEAMS AND MATCH SCORES --}}
                        <div class="grid grid-cols-1 items-stretch md:grid-cols-[1fr_auto_1fr]">
                            {{-- HOME TEAM --}}
                            <div class="flex min-w-0 items-center gap-3 px-4 py-5 sm:gap-4 sm:px-6 md:py-7">
                                <div class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-gray-100 bg-gray-50 sm:h-16 sm:w-16">
                                    @if ($homeLogo)
                                        <img
                                            src="{{ $homeLogo }}"
                                            alt="{{ $homeName }} logo"
                                            class="h-full w-full object-contain p-2"
                                            loading="lazy"
                                            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                        >
                                        <span class="hidden h-full w-full items-center justify-center text-sm font-black text-gray-500">
                                            {{ strtoupper(substr($homeShortName, 0, 3)) }}
                                        </span>
                                    @else
                                        <span class="text-sm font-black text-gray-500">
                                            {{ strtoupper(substr($homeShortName, 0, 3)) }}
                                        </span>
                                    @endif
                                </div>

                                <div class="min-w-0 flex-1">
                                    <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">
                                        Home
                                    </p>

                                    <h3 class="mt-1 break-words text-base font-extrabold leading-tight text-gray-900 sm:text-lg">
                                        {{ $homeName }}
                                    </h3>

                                    @if ($homeScoreLabel !== null)
                                        <div class="mt-2 flex flex-wrap items-baseline gap-2">
                                            <span class="text-2xl font-black tracking-tight text-gray-950 sm:text-3xl">
                                                {{ $homeScoreLabel }}
                                            </span>

                                            @if ($homeOvers)
                                                <span class="text-xs font-semibold text-gray-500">
                                                    ({{ $homeOvers }} ov)
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <p class="mt-2 text-xs font-medium text-gray-400">
                                            Score not available
                                        </p>
                                    @endif
                                </div>
                            </div>

                            {{-- VS / MATCH STATUS --}}
                            <div class="flex items-center justify-center px-4 pb-4 md:px-5 md:pb-0">
                                <div class="flex flex-row items-center gap-3 md:flex-col md:gap-2">
                                    <span class="text-xs font-black uppercase tracking-widest text-gray-300">
                                        VS
                                    </span>

                                    @if ($hasAnyScore)
                                        <span class="rounded-md bg-orange-50 px-2 py-1 text-[9px] font-extrabold uppercase tracking-wider text-orange-700">
                                            Scorecard
                                        </span>
                                    @else
                                        <span class="text-[10px] font-semibold text-gray-400">
                                            Match Centre
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- AWAY TEAM --}}
                            <div class="flex min-w-0 flex-row-reverse items-center gap-3 border-t border-gray-100 px-4 py-5 text-right sm:gap-4 sm:px-6 md:flex-row md:border-l md:border-t-0 md:py-7 md:text-left">
                                <div class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-gray-100 bg-gray-50 sm:h-16 sm:w-16">
                                    @if ($awayLogo)
                                        <img
                                            src="{{ $awayLogo }}"
                                            alt="{{ $awayName }} logo"
                                            class="h-full w-full object-contain p-2"
                                            loading="lazy"
                                            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                        >
                                        <span class="hidden h-full w-full items-center justify-center text-sm font-black text-gray-500">
                                            {{ strtoupper(substr($awayShortName, 0, 3)) }}
                                        </span>
                                    @else
                                        <span class="text-sm font-black text-gray-500">
                                            {{ strtoupper(substr($awayShortName, 0, 3)) }}
                                        </span>
                                    @endif
                                </div>

                                <div class="min-w-0 flex-1">
                                    <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">
                                        Away
                                    </p>

                                    <h3 class="mt-1 break-words text-base font-extrabold leading-tight text-gray-900 sm:text-lg">
                                        {{ $awayName }}
                                    </h3>

                                    @if ($awayScoreLabel !== null)
                                        <div class="mt-2 flex flex-wrap items-baseline justify-end gap-2 md:justify-start">
                                            <span class="text-2xl font-black tracking-tight text-gray-950 sm:text-3xl">
                                                {{ $awayScoreLabel }}
                                            </span>

                                            @if ($awayOvers)
                                                <span class="text-xs font-semibold text-gray-500">
                                                    ({{ $awayOvers }} ov)
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <p class="mt-2 text-xs font-medium text-gray-400">
                                            Score not available
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- RESULT --}}
                        @if (filled($result))
                            <div class="border-t border-gray-100 bg-emerald-50/60 px-4 py-3 sm:px-6">
                                <div class="flex items-start gap-2">
                                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="m5 12 4 4L19 6"></path>
                                    </svg>

                                    <p class="text-sm font-semibold leading-6 text-emerald-800">
                                        {{ is_string($result) ? $result : '' }}
                                    </p>
                                </div>
                            </div>
                        @endif

                        {{-- ALL INNINGS FOR THIS MATCH --}}
                        @if ($inningsForFixture->isNotEmpty())
                            <div class="border-t border-gray-100 bg-gray-50/70 px-4 py-3 sm:px-6">
                                <p class="mb-2 text-[10px] font-extrabold uppercase tracking-widest text-gray-400">
                                    This match · Innings summary
                                </p>

                                <div class="flex flex-wrap gap-2">
                                    @foreach ($inningsForFixture as $inning)
                                        @php
                                            $inningTeam = null;

                                            if ((int) $inning->batting_team_id === (int) $fixture->home_team_id) {
                                                $inningTeam = $homeName;
                                            } elseif ((int) $inning->batting_team_id === (int) $fixture->away_team_id) {
                                                $inningTeam = $awayName;
                                            }

                                            $inningRuns = $inning->total_runs ?? $inning->runs;
                                            $inningWickets = $inning->wickets ?? 0;
                                            $inningOvers = $formatOvers($inning);
                                        @endphp

                                        <div class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2">
                                            <span class="text-xs font-semibold text-gray-500">
                                                {{ $inningTeam ?: 'Innings ' . ($inning->innings_number ?? '') }}
                                            </span>

                                            <span class="text-sm font-extrabold text-gray-900">
                                                @if ($inningRuns !== null)
                                                    {{ $inningRuns }}/{{ $inningWickets }}
                                                @else
                                                    Score pending
                                                @endif
                                            </span>

                                            @if ($inningOvers)
                                                <span class="text-[11px] font-medium text-gray-400">
                                                    {{ $inningOvers }} ov
                                                </span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- MATCH DETAILS AND ACTION --}}
                        <div class="flex flex-col gap-4 border-t border-gray-100 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                            <div class="flex flex-wrap gap-x-5 gap-y-3">
                                @if ($matchDate)
                                    <div class="flex items-start gap-2.5">
                                        <svg class="mt-0.5 h-4 w-4 shrink-0 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                            <rect x="3" y="5" width="18" height="16" rx="2"></rect>
                                            <path d="M16 3v4M8 3v4M3 10h18"></path>
                                        </svg>

                                        <div>
                                            <p class="text-xs font-bold text-gray-800">
                                                {{ $formatDate($matchDate) }}
                                            </p>

                                            <p class="mt-0.5 text-[11px] text-gray-500">
                                                {{ $formatTime($matchDate) ?: 'Time not announced' }}
                                                <span class="text-gray-300">·</span>
                                                Nepal time
                                            </p>
                                        </div>
                                    </div>
                                @endif

                                @if (filled($venue))
                                    <div class="flex items-start gap-2.5">
                                        <svg class="mt-0.5 h-4 w-4 shrink-0 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                            <path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"></path>
                                            <circle cx="12" cy="10" r="2.5"></circle>
                                        </svg>

                                        <div>
                                            <p class="text-xs font-bold text-gray-800">
                                                {{ $venue }}
                                            </p>
                                            <p class="mt-0.5 text-[11px] text-gray-500">
                                                Match venue
                                            </p>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <a
                                href="{{ $fixtureUrl }}"
                                class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-gray-950 px-4 py-3 text-xs font-bold text-white transition hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2"
                            >
                                View Match
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M5 12h14M13 6l6 6-6 6"></path>
                                </svg>
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>

            {{-- PAGINATION --}}
            @if (method_exists($fixtures, 'links'))
                <div class="mt-8">
                    {{ $fixtures->withQueryString()->links() }}
                </div>
            @endif
        @endif
    </section>
</div>

@endsection
