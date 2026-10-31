
@extends('layouts.app')

@section('title', 'Fixtures & Results | NPL Season Hub')

@section('meta_description', 'Follow Nepal Premier League fixtures, live scores, match results, innings summaries, schedules and cricket scorecards.')

@section('content')

@php
    use App\Models\ManualScorecard;
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;
    use Illuminate\Support\Carbon;

    $timezone = config('app.timezone', 'Asia/Kathmandu');
    $fixtures = $fixtures ?? collect();

    $fixtureCollection = method_exists($fixtures, 'getCollection')
        ? $fixtures->getCollection()
        : collect($fixtures);

    $fixtureIds = $fixtureCollection->pluck('id')->filter()->unique()->values();

    $manualScorecards = ManualScorecard::query()
        ->whereIn('fixture_id', $fixtureIds)
        ->orderBy('fixture_id')
        ->orderBy('innings_number')
        ->get()
        ->groupBy('fixture_id');

    $imageUrl = function ($path) {
        if (blank($path)) {
            return null;
        }

        $path = trim((string) $path);

        if (Str::startsWith($path, ['https://', 'http://', 'data:image/'])) {
            return $path;
        }

        return Storage::url(ltrim($path, '/'));
    };

    $teamName = fn ($team, $fallback = 'Team') =>
        $team?->name ?: $team?->short_name ?: $fallback;

    $teamShortName = fn ($team, $fallback = 'TEAM') =>
        $team?->short_name ?: $team?->name ?: $fallback;

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

        $legalBalls = $inning->legal_balls ?? null;

        if ($legalBalls !== null && is_numeric($legalBalls)) {
            return intdiv((int) $legalBalls, 6) . '.' . ((int) $legalBalls % 6);
        }

        $overs = $inning->overs ?? null;

        return $overs !== null && $overs !== '' ? (string) $overs : null;
    };

    $formatDate = function ($date) use ($timezone) {
        if (!$date) {
            return null;
        }

        try {
            return Carbon::parse($date, $timezone)
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
            return Carbon::parse($date, $timezone)
                ->timezone($timezone)
                ->format('h:i A');
        } catch (\Throwable $e) {
            return null;
        }
    };

    $statusInfo = function ($fixture) {
        $raw = strtolower(trim((string) ($fixture->status ?? 'scheduled')));
        $raw = str_replace(['-', ' '], '_', $raw);

        if (
            str_contains($raw, 'live')
            || str_contains($raw, 'progress')
            || str_contains($raw, 'innings_break')
            || $raw === 'in_play'
        ) {
            return [
                'label' => 'LIVE',
                'class' => 'border-red-200 bg-red-50 text-red-700',
                'dot' => 'bg-red-600',
                'priority' => 0,
            ];
        }

        if (
            str_contains($raw, 'abandon')
            || str_contains($raw, 'cancel')
            || str_contains($raw, 'no_result')
            || str_contains($raw, 'noresult')
            || str_contains($raw, 'washout')
        ) {
            return [
                'label' => 'NO RESULT',
                'class' => 'border-slate-200 bg-slate-100 text-slate-600',
                'dot' => 'bg-slate-400',
                'priority' => 2,
            ];
        }

        if (str_contains($raw, 'postpon')) {
            return [
                'label' => 'POSTPONED',
                'class' => 'border-amber-200 bg-amber-50 text-amber-800',
                'dot' => 'bg-amber-500',
                'priority' => 1,
            ];
        }

        if (
            str_contains($raw, 'complete')
            || str_contains($raw, 'finished')
            || str_contains($raw, 'result')
        ) {
            return [
                'label' => 'COMPLETED',
                'class' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                'dot' => 'bg-emerald-600',
                'priority' => 2,
            ];
        }

        return [
            'label' => 'UPCOMING',
            'class' => 'border-blue-200 bg-blue-50 text-blue-700',
            'dot' => 'bg-blue-600',
            'priority' => 1,
        ];
    };

    $totalMatches = method_exists($fixtures, 'total')
        ? $fixtures->total()
        : $fixtureCollection->count();

    $hasPagination = method_exists($fixtures, 'links')
        && method_exists($fixtures, 'currentPage');

    $firstItem = method_exists($fixtures, 'firstItem')
        ? $fixtures->firstItem()
        : ($fixtureCollection->isNotEmpty() ? 1 : 0);

    $lastItem = method_exists($fixtures, 'lastItem')
        ? $fixtures->lastItem()
        : $fixtureCollection->count();
@endphp

<div class="bg-[#f4f6f9] text-slate-900">

    {{-- COMPACT PAGE INTRO --}}
    <section class="border-b border-slate-200 bg-white">
        <div class="mx-auto max-w-7xl px-4 py-7 sm:px-6 sm:py-9 lg:px-8">
            <div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
                <div>
                    <div class="mb-3 flex items-center gap-2">
                        <span class="h-5 w-1 rounded-full bg-red-600"></span>
                        <span class="text-[11px] font-extrabold uppercase tracking-[0.16em] text-red-700">
                            Nepal Premier League
                        </span>
                    </div>

                    <h1 class="text-3xl font-black tracking-tight text-[#111827] sm:text-4xl">
                        Fixtures <span class="text-red-600">&amp; Results</span>
                    </h1>

                    <p class="mt-2 max-w-xl text-sm leading-6 text-slate-500">
                        Match schedules, scorecards and results from the Nepal Premier League.
                    </p>
                </div>

                <div class="flex items-center gap-3 border-t border-slate-100 pt-4 sm:border-l sm:border-t-0 sm:pl-6 sm:pt-0">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-slate-100 text-slate-700">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="1.7">
                            <rect x="3" y="5" width="18" height="16" rx="2"/>
                            <path d="M16 3v4M8 3v4M3 10h18"/>
                        </svg>
                    </div>

                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500">
                            Total fixtures
                        </p>
                        <p class="mt-0.5 text-2xl font-black tabular-nums text-slate-950">
                            {{ number_format($totalMatches) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- MATCH LIST --}}
    <section class="mx-auto max-w-7xl px-4 py-7 sm:px-6 sm:py-9 lg:px-8">

        <div class="mb-5 flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 class="text-xl font-extrabold tracking-tight text-slate-950">
                    Match Centre
                </h2>
                <p class="mt-1 text-sm text-slate-500">
                    Browse fixtures and match results.
                </p>
            </div>

            @if ($fixtureCollection->isNotEmpty())
                <p class="text-xs font-semibold text-slate-500">
                    Showing
                    <span class="text-slate-900">{{ $firstItem }}–{{ $lastItem }}</span>
                    of
                    <span class="text-slate-900">{{ number_format($totalMatches) }}</span>
                </p>
            @endif
        </div>

        @if ($fixtureCollection->isEmpty())

            <div class="rounded-2xl border border-slate-200 bg-white px-6 py-16 text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="1.6">
                        <rect x="3" y="5" width="18" height="16" rx="2"/>
                        <path d="M16 3v4M8 3v4M3 10h18"/>
                    </svg>
                </div>

                <h3 class="mt-4 text-lg font-extrabold text-slate-900">
                    No matches found
                </h3>

                <p class="mt-2 text-sm text-slate-500">
                    Fixtures will appear here when they become available.
                </p>
            </div>

        @else

            <div class="grid grid-cols-1 items-stretch gap-4 xl:grid-cols-2">

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

                        $inningsForFixture = $manualScorecards
                            ->get($fixture->id, collect())
                            ->sortBy('innings_number')
                            ->values();

                        $homeInnings = $inningsForFixture
                            ->filter(fn ($inning) =>
                                (int) $inning->batting_team_id === (int) $fixture->home_team_id
                            )
                            ->values();

                        $awayInnings = $inningsForFixture
                            ->filter(fn ($inning) =>
                                (int) $inning->batting_team_id === (int) $fixture->away_team_id
                            )
                            ->values();

                        $homeInning = $homeInnings->last();
                        $awayInning = $awayInnings->last();

                        $homeRuns = $homeInning?->total_runs ?? $homeInning?->runs;
                        $awayRuns = $awayInning?->total_runs ?? $awayInning?->runs;

                        $homeScore = $homeInning && $homeRuns !== null
                            ? $homeRuns . '/' . ($homeInning->wickets ?? 0)
                            : null;

                        $awayScore = $awayInning && $awayRuns !== null
                            ? $awayRuns . '/' . ($awayInning->wickets ?? 0)
                            : null;

                        $homeOvers = $formatOvers($homeInning);
                        $awayOvers = $formatOvers($awayInning);

                        $status = $statusInfo($fixture);

                        $matchNumber = $fixture->match_number
                            ?? $fixture->match_no
                            ?? null;

                        $matchDate = $fixture->scheduled_at
                            ?? $fixture->match_date
                            ?? $fixture->date
                            ?? null;

                        $venue = $fixture->venue ?? $fixture->ground ?? null;

                        if (is_object($venue)) {
                            $venue = $venue->name ?? null;
                        }

                        $result = $fixture->result_summary
                            ?? $fixture->result
                            ?? null;

                        $resultText = is_string($result) || is_numeric($result)
                            ? trim((string) $result)
                            : '';

                        $fixtureUrl = route('fixtures.show', [
                            'fixture' => $fixture->id,
                        ]);

                        $seasonName = $fixture->season?->name ?? null;
                    @endphp

                    <article class="group flex min-w-0 flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition duration-200 hover:border-slate-300 hover:shadow-md">

                        {{-- MATCH HEADER --}}
                        <div class="flex min-h-[52px] items-center justify-between gap-3 border-b border-slate-100 px-4 py-3 sm:px-5">
                            <div class="flex min-w-0 flex-wrap items-center gap-2">
                                <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-500">
                                    {{ $matchNumber ? 'Match ' . $matchNumber : 'Match #' . $fixture->id }}
                                </span>

                                @if ($seasonName)
                                    <span class="text-slate-300">/</span>
                                    <span class="max-w-32 truncate text-[11px] font-medium text-slate-500">
                                        {{ $seasonName }}
                                    </span>
                                @endif
                            </div>

                            <span class="inline-flex shrink-0 items-center gap-1.5 rounded-md border px-2 py-1 text-[9px] font-extrabold tracking-wider {{ $status['class'] }}">
                                <span class="h-1.5 w-1.5 rounded-full {{ $status['dot'] }} {{ $status['label'] === 'LIVE' ? 'animate-pulse' : '' }}"></span>
                                {{ $status['label'] }}
                            </span>
                        </div>

                        {{-- SCOREBOARD --}}
                        <div class="px-4 py-5 sm:px-5 sm:py-6">
                            <div class="grid grid-cols-[minmax(0,1fr)_44px_minmax(0,1fr)] items-center gap-2 sm:grid-cols-[minmax(0,1fr)_52px_minmax(0,1fr)]">

                                {{-- HOME --}}
                                <div class="min-w-0">
                                    <div class="flex min-w-0 flex-col items-center text-center">
                                        <div class="flex h-[58px] w-[58px] items-center justify-center rounded-xl bg-slate-50 p-2 sm:h-[66px] sm:w-[66px]">
                                            @if ($homeLogo)
                                                <img
                                                    src="{{ $homeLogo }}"
                                                    alt="{{ $homeName }} logo"
                                                    class="h-full w-full object-contain"
                                                    loading="lazy"
                                                    onerror="this.style.display='none';this.nextElementSibling.classList.remove('hidden');"
                                                >
                                                <span class="hidden text-xs font-black text-slate-500">
                                                    {{ strtoupper(substr($homeShortName, 0, 3)) }}
                                                </span>
                                            @else
                                                <span class="text-xs font-black tracking-wide text-slate-500">
                                                    {{ strtoupper(substr($homeShortName, 0, 3)) }}
                                                </span>
                                            @endif
                                        </div>

                                        <h3 class="mt-3 min-h-10 w-full break-words text-[12px] font-bold leading-5 text-slate-800 sm:text-sm">
                                            {{ $homeName }}
                                        </h3>

                                        @if ($homeScore !== null)
                                            <p class="mt-1 text-[27px] font-black leading-none tracking-tight text-slate-950 sm:text-3xl">
                                                {{ $homeScore }}
                                            </p>
                                            <p class="mt-2 text-[10px] font-medium text-slate-500">
                                                {{ $homeOvers !== null ? $homeOvers . ' overs' : 'Overs N/A' }}
                                            </p>
                                        @else
                                            <p class="mt-2 text-xs font-semibold text-slate-400">
                                                —
                                            </p>
                                        @endif
                                    </div>
                                </div>

                                {{-- VERSUS --}}
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <span class="text-[10px] font-black tracking-widest text-slate-300">
                                        VS
                                    </span>
                                    <span class="h-8 w-px bg-slate-200"></span>
                                </div>

                                {{-- AWAY --}}
                                <div class="min-w-0">
                                    <div class="flex min-w-0 flex-col items-center text-center">
                                        <div class="flex h-[58px] w-[58px] items-center justify-center rounded-xl bg-slate-50 p-2 sm:h-[66px] sm:w-[66px]">
                                            @if ($awayLogo)
                                                <img
                                                    src="{{ $awayLogo }}"
                                                    alt="{{ $awayName }} logo"
                                                    class="h-full w-full object-contain"
                                                    loading="lazy"
                                                    onerror="this.style.display='none';this.nextElementSibling.classList.remove('hidden');"
                                                >
                                                <span class="hidden text-xs font-black text-slate-500">
                                                    {{ strtoupper(substr($awayShortName, 0, 3)) }}
                                                </span>
                                            @else
                                                <span class="text-xs font-black tracking-wide text-slate-500">
                                                    {{ strtoupper(substr($awayShortName, 0, 3)) }}
                                                </span>
                                            @endif
                                        </div>

                                        <h3 class="mt-3 min-h-10 w-full break-words text-[12px] font-bold leading-5 text-slate-800 sm:text-sm">
                                            {{ $awayName }}
                                        </h3>

                                        @if ($awayScore !== null)
                                            <p class="mt-1 text-[27px] font-black leading-none tracking-tight text-slate-950 sm:text-3xl">
                                                {{ $awayScore }}
                                            </p>
                                            <p class="mt-2 text-[10px] font-medium text-slate-500">
                                                {{ $awayOvers !== null ? $awayOvers . ' overs' : 'Overs N/A' }}
                                            </p>
                                        @else
                                            <p class="mt-2 text-xs font-semibold text-slate-400">
                                                —
                                            </p>
                                        @endif
                                    </div>
                                </div>

                            </div>

                            {{-- RESULT --}}
                            @if (filled($resultText))
                                <div class="mt-5 flex items-start gap-2 border-l-[3px] border-emerald-500 bg-emerald-50/70 px-3 py-3">
                                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-emerald-700" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="m5 12 4 4L19 6"/>
                                    </svg>
                                    <p class="text-xs font-semibold leading-5 text-emerald-900">
                                        {{ $resultText }}
                                    </p>
                                </div>
                            @endif

                            {{-- INNINGS --}}
                            @if ($inningsForFixture->isNotEmpty())
                                <div class="mt-5 border-t border-slate-100 pt-4">
                                    <p class="mb-2.5 text-[9px] font-extrabold uppercase tracking-[0.15em] text-slate-400">
                                        Innings breakdown
                                    </p>

                                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                                        @foreach ($inningsForFixture as $inning)
                                            @php
                                                $inningTeam = null;

                                                if ((int) $inning->batting_team_id === (int) $fixture->home_team_id) {
                                                    $inningTeam = $homeShortName;
                                                } elseif ((int) $inning->batting_team_id === (int) $fixture->away_team_id) {
                                                    $inningTeam = $awayShortName;
                                                }

                                                $inningRuns = $inning->total_runs ?? $inning->runs;
                                                $inningWickets = $inning->wickets ?? 0;
                                                $inningOvers = $formatOvers($inning);
                                            @endphp

                                            <div class="flex min-w-0 items-center justify-between gap-2 rounded-lg border border-slate-200 px-3 py-2">
                                                <div class="min-w-0">
                                                    <p class="truncate text-[10px] font-bold text-slate-600">
                                                        {{ $inningTeam ?: 'Innings ' . ($inning->innings_number ?? '') }}
                                                    </p>
                                                    @if ($inningOvers !== null)
                                                        <p class="mt-0.5 text-[10px] text-slate-400">
                                                            {{ $inningOvers }} overs
                                                        </p>
                                                    @endif
                                                </div>

                                                <p class="shrink-0 text-sm font-black tabular-nums text-slate-900">
                                                    {{ $inningRuns !== null ? $inningRuns . '/' . $inningWickets : '—' }}
                                                </p>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- MATCH DETAILS AND ACTION --}}
                        <div class="mt-auto border-t border-slate-100 bg-slate-50/70 px-4 py-4 sm:px-5">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div class="min-w-0 space-y-2">
                                    @if ($matchDate)
                                        <div class="flex items-center gap-2 text-[11px] font-semibold text-slate-600">
                                            <svg class="h-4 w-4 shrink-0 text-slate-400" viewBox="0 0 24 24"
                                                fill="none" stroke="currentColor" stroke-width="1.7">
                                                <rect x="3" y="5" width="18" height="16" rx="2"/>
                                                <path d="M16 3v4M8 3v4M3 10h18"/>
                                            </svg>
                                            <span>
                                                {{ $formatDate($matchDate) }}
                                                <span class="px-1 text-slate-300">·</span>
                                                {{ $formatTime($matchDate) ?: 'Time TBA' }} NPT
                                            </span>
                                        </div>
                                    @endif

                                    @if (filled($venue))
                                        <div class="flex items-center gap-2 text-[11px] text-slate-500">
                                            <svg class="h-4 w-4 shrink-0 text-slate-400" viewBox="0 0 24 24"
                                                fill="none" stroke="currentColor" stroke-width="1.7">
                                                <path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"/>
                                                <circle cx="12" cy="10" r="2.5"/>
                                            </svg>
                                            <span class="truncate">{{ $venue }}</span>
                                        </div>
                                    @endif

                                    @if (!$matchDate && !filled($venue))
                                        <p class="text-[11px] text-slate-400">
                                            Match details will be updated soon.
                                        </p>
                                    @endif
                                </div>

                                <a
                                    href="{{ $fixtureUrl }}"
                                    aria-label="View {{ $homeName }} versus {{ $awayName }} match details"
                                    class="inline-flex min-h-10 shrink-0 items-center justify-center gap-2 rounded-lg bg-[#172033] px-4 py-2.5 text-xs font-bold text-white transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                                >
                                    Match Centre
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="1.8">
                                        <path d="M5 12h14M13 6l6 6-6 6"/>
                                    </svg>
                                </a>
                            </div>
                        </div>

                    </article>
                @endforeach

            </div>

            {{-- PAGINATION --}}
            @if ($hasPagination && $fixtures->hasPages())
                <nav aria-label="Fixture pagination" class="mt-8 rounded-xl border border-slate-200 bg-white px-4 py-4 sm:px-5">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-xs leading-5 text-slate-500">
                            Showing
                            <span class="font-bold text-slate-900">{{ $firstItem }}</span>
                            to
                            <span class="font-bold text-slate-900">{{ $lastItem }}</span>
                            of
                            <span class="font-bold text-slate-900">{{ number_format($totalMatches) }}</span>
                            matches
                        </p>

                        <div class="fixtures-pagination">
                            {{ $fixtures->withQueryString()->onEachSide(1)->links() }}
                        </div>
                    </div>
                </nav>
            @endif

        @endif

        <p class="mt-6 text-center text-[10px] text-slate-400">
            Match schedules and scores are displayed using the available match data.
            Times are shown in Nepal Standard Time (NPT).
        </p>

    </section>
</div>

<style>
    .fixtures-pagination nav[role="navigation"] {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: flex-end;
        gap: 4px;
    }

    .fixtures-pagination nav[role="navigation"] > div:first-child {
        display: none;
    }

    .fixtures-pagination nav[role="navigation"] > div:last-child {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 4px;
    }

    .fixtures-pagination nav[role="navigation"] a,
    .fixtures-pagination nav[role="navigation"] span[aria-current="page"] > span,
    .fixtures-pagination nav[role="navigation"] span[aria-disabled="true"] > span {
        display: inline-flex;
        min-width: 36px;
        min-height: 36px;
        align-items: center;
        justify-content: center;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 0 10px;
        font-size: 12px;
        font-weight: 700;
        color: #334155;
        background: #fff;
    }

    .fixtures-pagination nav[role="navigation"] span[aria-current="page"] > span {
        border-color: #b91c1c;
        color: #fff;
        background: #b91c1c;
    }

    .fixtures-pagination nav[role="navigation"] a:hover {
        border-color: #b91c1c;
        color: #b91c1c;
    }

    .fixtures-pagination nav[role="navigation"] span[aria-disabled="true"] > span {
        color: #cbd5e1;
        background: #f8fafc;
    }

    @media (max-width: 639px) {
        .fixtures-pagination nav[role="navigation"] {
            justify-content: flex-start;
        }
    }
</style>

@endsection
