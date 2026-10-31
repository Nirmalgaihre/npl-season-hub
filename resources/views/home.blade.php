@extends('layouts.app')

@section('title', 'NPL Season Hub | Nepal Premier League')

@section('content')
@php
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Facades\Route;

    $teams = collect($teams ?? []);
    $liveMatches = collect($liveMatches ?? []);
    $recentResults = collect($recentResults ?? []);
    $latestNews = collect($latestNews ?? []);
    $liveScoreByFixture = $liveScoreByFixture ?? collect();

    /*
    |--------------------------------------------------------------------------
    | Image paths
    |--------------------------------------------------------------------------
    | Supports full URLs, storage paths, and filenames.
    */

    $imageUrl = function ($path, $folder = null) {
        if (blank($path)) {
            return null;
        }

        $path = trim((string) $path);

        if (
            filter_var($path, FILTER_VALIDATE_URL) ||
            str_starts_with($path, 'data:image/')
        ) {
            return $path;
        }

        $path = ltrim($path, '/');

        if (str_starts_with($path, 'public/')) {
            $path = substr($path, 7);
        }

        if (str_starts_with($path, 'storage/')) {
            return asset($path);
        }

        if ($folder && !str_starts_with($path, $folder . '/')) {
            $path = $folder . '/' . $path;
        }

        return asset('storage/' . $path);
    };

    $teamLogo = function ($team) use ($imageUrl) {
        return $imageUrl(data_get($team, 'logo'), 'teams');
    };

    $teamName = function ($team, $fallback = 'Team') {
        return data_get($team, 'name') ?: $fallback;
    };

    $teamShortName = function ($team) use ($teamName) {
        return data_get($team, 'short_name')
            ?: data_get($team, 'short_code')
            ?: strtoupper(substr($teamName($team), 0, 3));
    };

    /*
    |--------------------------------------------------------------------------
    | Match helpers
    |--------------------------------------------------------------------------
    */

    $matchDate = function ($match) {
        $date = data_get($match, 'scheduled_at')
            ?? data_get($match, 'match_date')
            ?? data_get($match, 'start_time')
            ?? data_get($match, 'date');

        if (!$date) {
            return null;
        }

        try {
            return Carbon::parse($date);
        } catch (\Throwable $e) {
            return null;
        }
    };

    $formatDate = function ($match) use ($matchDate) {
        $date = $matchDate($match);

        return $date ? $date->format('d M Y') : 'Date to be announced';
    };

    $formatTime = function ($match) use ($matchDate) {
        $date = $matchDate($match);

        return $date ? $date->format('h:i A') : 'Time to be announced';
    };

    $isFinished = function ($match) {
        $status = strtolower((string) (
            data_get($match, 'status')
            ?? data_get($match, 'match_status')
            ?? ''
        ));

        return in_array($status, [
            'completed',
            'complete',
            'finished',
            'result',
            'abandoned',
            'no_result',
            'no result',
        ], true);
    };

    $isLive = function ($match) use ($isFinished) {
        if ($isFinished($match)) {
            return false;
        }

        $status = strtolower((string) (
            data_get($match, 'status')
            ?? data_get($match, 'match_status')
            ?? ''
        ));

        return in_array($status, [
            'live',
            'in_progress',
            'in progress',
            'innings_break',
            'innings break',
            'break',
        ], true);
    };

    $isUpcoming = function ($match) use ($isFinished, $isLive) {
        if ($isFinished($match) || $isLive($match)) {
            return false;
        }

        $status = strtolower((string) (
            data_get($match, 'status')
            ?? data_get($match, 'match_status')
            ?? ''
        ));

        return in_array($status, [
            'scheduled',
            'upcoming',
            'pending',
            'fixture',
            'not_started',
            'not started',
        ], true);
    };

    /*
    |--------------------------------------------------------------------------
    | Cricket score helpers
    |--------------------------------------------------------------------------
    */

    $inningsFor = function ($match) use ($liveScoreByFixture) {
        $fixtureId = data_get($match, 'id');

        $innings = $liveScoreByFixture instanceof \Illuminate\Support\Collection
            ? $liveScoreByFixture->get($fixtureId, collect())
            : ($liveScoreByFixture[$fixtureId] ?? collect());

        return collect($innings ?? [])
            ->sortBy('innings_number')
            ->values();
    };

    $scoreFor = function ($match, $team = null) use ($inningsFor) {
        $innings = $inningsFor($match);

        if ($team) {
            $teamId = data_get($team, 'id');

            $innings = $innings->filter(function ($inning) use ($teamId) {
                $battingTeamId = data_get($inning, 'batting_team_id')
                    ?? data_get($inning, 'team_id')
                    ?? data_get($inning, 'battingTeam.id');

                return (string) $battingTeamId === (string) $teamId;
            });
        }

        $inning = $innings->sortByDesc(function ($item) {
            return (int) (
                data_get($item, 'is_current', false) ? 100000 : 0
            ) + (int) data_get($item, 'innings_number', 0);
        })->first();

        if (!$inning) {
            return null;
        }

        $runs = data_get($inning, 'total_runs', 0);
        $wickets = data_get($inning, 'wickets', 0);
        $legalBalls = (int) data_get($inning, 'legal_balls', 0);

        $overs = intdiv($legalBalls, 6)
            . '.'
            . ($legalBalls % 6);

        return [
            'runs' => $runs,
            'wickets' => $wickets,
            'overs' => $overs,
            'score' => $runs . '/' . $wickets,
            'inning' => $inning,
        ];
    };

    $matchStatusLabel = function ($match) use ($isLive, $isFinished, $isUpcoming) {
        if ($isLive($match)) {
            return 'LIVE';
        }

        if ($isFinished($match)) {
            return 'RESULT';
        }

        if ($isUpcoming($match)) {
            return 'UPCOMING';
        }

        return strtoupper(str_replace('_', ' ', (string) (
            data_get($match, 'status') ?? 'MATCH'
        )));
    };

    /*
    |--------------------------------------------------------------------------
    | Homepage data
    |--------------------------------------------------------------------------
    */

    $liveMatches = $liveMatches
        ->filter(fn ($match) => $isLive($match))
        ->values();

    $liveMatch = $liveMatches->first();

    $upcomingMatches = collect();

    if (isset($nextMatch) && $nextMatch) {
        $upcomingMatches->push($nextMatch);
    }

    $upcomingMatches = $upcomingMatches
        ->merge(
            $liveMatches->isNotEmpty()
                ? collect()
                : collect()
        )
        ->filter(fn ($match) => $isUpcoming($match))
        ->unique('id')
        ->values();

    $featuredMatch = $liveMatch ?: $upcomingMatches->first();

    if (!$featuredMatch) {
        $featuredMatch = $recentResults->first();
    }

    $homeTeam = $featuredMatch
        ? (data_get($featuredMatch, 'homeTeam')
            ?? data_get($featuredMatch, 'home_team'))
        : null;

    $awayTeam = $featuredMatch
        ? (data_get($featuredMatch, 'awayTeam')
            ?? data_get($featuredMatch, 'away_team'))
        : null;

    $homeScore = $featuredMatch
        ? $scoreFor($featuredMatch, $homeTeam)
        : null;

    $awayScore = $featuredMatch
        ? $scoreFor($featuredMatch, $awayTeam)
        : null;

    $featuredIsLive = $featuredMatch && $isLive($featuredMatch);
    $featuredIsFinished = $featuredMatch && $isFinished($featuredMatch);

    $featuredResult = $featuredMatch
        ? (
            data_get($featuredMatch, 'result')
            ?? data_get($featuredMatch, 'result_summary')
            ?? data_get($featuredMatch, 'status_text')
        )
        : null;

    $fixtureCount = $liveMatches->count()
        + $recentResults->count()
        + $upcomingMatches->count();

    $fixtureUrl = function ($match) {
        if (!$match) {
            return '#';
        }

        return Route::has('fixtures.show')
            ? route('fixtures.show', $match)
            : '#';
    };

    $teamUrl = function ($team) {
        if (!$team) {
            return '#';
        }

        return Route::has('teams.show')
            ? route('teams.show', $team)
            : '#';
    };

    $newsUrl = function ($article) {
        if (!$article) {
            return '#';
        }

        return Route::has('news.show')
            ? route('news.show', $article)
            : '#';
    };
@endphp

<style>
    .npl-home {
        --npl-navy: #101c30;
        --npl-navy-light: #192941;
        --npl-red: #c6283d;
        --npl-red-dark: #a91e31;
        --npl-gold: #e4b85c;
        --npl-text: #172033;
        --npl-muted: #6b7280;
        --npl-border: #e5e7eb;
        color: var(--npl-text);
        background: #fff;
        font-family: inherit;
    }

    .npl-home *,
    .npl-home *::before,
    .npl-home *::after {
        box-sizing: border-box;
    }

    .npl-container {
        width: min(1180px, calc(100% - 36px));
        margin: 0 auto;
    }

    .npl-section {
        padding: 42px 0;
    }

    .npl-section-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 22px;
    }

    .npl-section-heading h2 {
        margin: 0;
        color: var(--npl-navy);
        font-size: clamp(21px, 3vw, 28px);
        font-weight: 800;
        letter-spacing: -.5px;
    }

    .npl-section-heading p {
        margin: 6px 0 0;
        color: var(--npl-muted);
        font-size: 14px;
    }

    .npl-link {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        color: var(--npl-red);
        font-size: 14px;
        font-weight: 700;
        text-decoration: none;
        white-space: nowrap;
    }

    .npl-link:hover {
        color: var(--npl-red-dark);
    }

    .npl-button {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        gap: 9px;
        min-height: 43px;
        padding: 11px 18px;
        border: 1px solid transparent;
        border-radius: 5px;
        background: var(--npl-red);
        color: #fff;
        font-size: 14px;
        font-weight: 700;
        text-decoration: none;
        transition: background .2s ease;
    }

    .npl-button:hover {
        background: var(--npl-red-dark);
        color: #fff;
    }

    .npl-button-outline {
        border-color: rgba(255,255,255,.45);
        background: transparent;
    }

    .npl-button-outline:hover {
        background: rgba(255,255,255,.1);
    }

    /* Main introduction */

    .npl-hero {
        position: relative;
        overflow: hidden;
        background: var(--npl-navy);
        color: #fff;
    }

    .npl-hero::after {
        position: absolute;
        top: -100px;
        right: -90px;
        width: 360px;
        height: 360px;
        border: 1px solid rgba(255,255,255,.08);
        border-radius: 50%;
        content: "";
        pointer-events: none;
    }

    .npl-hero-inner {
        position: relative;
        z-index: 1;
        display: grid;
        grid-template-columns: 1fr 1fr;
        align-items: center;
        gap: 42px;
        padding-top: 45px;
        padding-bottom: 45px;
    }

    .npl-kicker {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        margin-bottom: 15px;
        color: var(--npl-gold);
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 1.7px;
        text-transform: uppercase;
    }

    .npl-kicker::before {
        width: 24px;
        height: 2px;
        background: var(--npl-gold);
        content: "";
    }

    .npl-hero h1 {
        max-width: 560px;
        margin: 0;
        color: #fff;
        font-size: clamp(32px, 5vw, 54px);
        font-weight: 900;
        line-height: 1.08;
        letter-spacing: -1.4px;
    }

    .npl-hero h1 span {
        color: var(--npl-gold);
    }

    .npl-hero-copy {
        max-width: 480px;
        margin: 18px 0 23px;
        color: #d1d9e6;
        font-size: 15px;
        line-height: 1.8;
    }

    .npl-hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .npl-hero-note {
        margin-top: 24px;
        color: #aeb9ca;
        font-size: 12px;
    }

    /* Featured match */

    .npl-match-card {
        overflow: hidden;
        border: 1px solid rgba(255,255,255,.14);
        border-radius: 8px;
        background: #fff;
        color: var(--npl-text);
        box-shadow: 0 15px 35px rgba(0,0,0,.15);
    }

    .npl-match-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 15px 18px;
        border-bottom: 1px solid var(--npl-border);
    }

    .npl-match-top strong {
        color: var(--npl-navy);
        font-size: 12px;
        font-weight: 800;
        letter-spacing: .5px;
        text-transform: uppercase;
    }

    .npl-status {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 6px 9px;
        border-radius: 4px;
        background: #f3f4f6;
        color: #4b5563;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .6px;
    }

    .npl-status-live {
        background: #fff0f0;
        color: #bb1e32;
    }

    .npl-status-live::before {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #c6283d;
        content: "";
    }

    .npl-match-body {
        padding: 24px 18px 19px;
    }

    .npl-match-teams {
        display: grid;
        grid-template-columns: 1fr 40px 1fr;
        align-items: center;
        gap: 8px;
    }

    .npl-match-team {
        min-width: 0;
        text-align: center;
    }

    .npl-team-logo {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 74px;
        height: 74px;
        margin: 0 auto 12px;
        overflow: hidden;
        border: 1px solid var(--npl-border);
        border-radius: 50%;
        background: #f9fafb;
    }

    .npl-team-logo img {
        width: 100%;
        height: 100%;
        padding: 7px;
        object-fit: contain;
    }

    .npl-team-placeholder {
        color: var(--npl-navy);
        font-size: 20px;
        font-weight: 900;
    }

    .npl-match-team h3 {
        margin: 0;
        color: var(--npl-navy);
        font-size: 14px;
        font-weight: 800;
        line-height: 1.4;
        overflow-wrap: anywhere;
    }

    .npl-team-short {
        margin-top: 5px;
        color: var(--npl-muted);
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .7px;
    }

    .npl-team-score {
        margin-top: 9px;
        color: var(--npl-navy);
        font-size: 23px;
        font-weight: 900;
        letter-spacing: -.6px;
    }

    .npl-team-overs {
        margin-top: 2px;
        color: var(--npl-muted);
        font-size: 11px;
    }

    .npl-versus {
        color: #9ca3af;
        font-size: 12px;
        font-weight: 900;
        text-align: center;
    }

    .npl-match-info {
        margin-top: 22px;
        padding-top: 15px;
        border-top: 1px solid var(--npl-border);
        color: var(--npl-muted);
        font-size: 12px;
        line-height: 1.8;
        text-align: center;
    }

    .npl-match-footer {
        padding: 0 18px 18px;
    }

    .npl-match-footer .npl-button {
        width: 100%;
    }

    .npl-empty-match {
        padding: 28px 20px;
        text-align: center;
    }

    .npl-empty-match h3 {
        margin: 0 0 8px;
        color: var(--npl-navy);
        font-size: 19px;
    }

    .npl-empty-match p {
        margin: 0 0 18px;
        color: var(--npl-muted);
        font-size: 14px;
        line-height: 1.7;
    }

    /* Live ticker */

    .npl-ticker {
        border-bottom: 1px solid #e8eaf0;
        background: #f8f9fb;
    }

    .npl-ticker-inner {
        display: flex;
        align-items: center;
        gap: 16px;
        min-height: 48px;
    }

    .npl-ticker-label {
        flex-shrink: 0;
        color: var(--npl-red);
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .8px;
        text-transform: uppercase;
    }

    .npl-ticker-items {
        display: flex;
        flex-wrap: wrap;
        gap: 9px 20px;
        padding: 10px 0;
    }

    .npl-ticker-items a {
        color: #374151;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
    }

    .npl-ticker-items a:hover {
        color: var(--npl-red);
    }

    /* Results */

    .npl-results-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 18px;
    }

    .npl-result-card {
        overflow: hidden;
        border: 1px solid var(--npl-border);
        border-radius: 6px;
        background: #fff;
        transition: border-color .2s ease;
    }

    .npl-result-card:hover {
        border-color: #cbd5e1;
    }

    .npl-result-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 13px 15px;
        border-bottom: 1px solid #eef0f4;
        background: #fafbfc;
        color: var(--npl-muted);
        font-size: 11px;
    }

    .npl-result-header strong {
        color: #4b5563;
        font-weight: 800;
    }

    .npl-result-teams {
        padding: 13px 15px;
    }

    .npl-result-team {
        display: grid;
        grid-template-columns: 35px minmax(0, 1fr) auto;
        align-items: center;
        gap: 10px;
        padding: 8px 0;
    }

    .npl-result-team + .npl-result-team {
        border-top: 1px solid #f1f2f5;
    }

    .npl-result-logo {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        overflow: hidden;
        border: 1px solid var(--npl-border);
        border-radius: 50%;
        background: #fff;
    }

    .npl-result-logo img {
        width: 100%;
        height: 100%;
        padding: 3px;
        object-fit: contain;
    }

    .npl-result-logo span {
        color: var(--npl-navy);
        font-size: 10px;
        font-weight: 900;
    }

    .npl-result-team-name {
        color: #374151;
        font-size: 12px;
        font-weight: 700;
        overflow-wrap: anywhere;
    }

    .npl-result-score {
        color: var(--npl-navy);
        font-size: 13px;
        font-weight: 900;
        white-space: nowrap;
    }

    .npl-result-footer {
        padding: 12px 15px;
        border-top: 1px solid #eef0f4;
        color: var(--npl-muted);
        font-size: 12px;
        line-height: 1.6;
    }

    .npl-result-footer a {
        color: var(--npl-red);
        font-weight: 700;
        text-decoration: none;
    }

    /* Teams */

    .npl-teams-section {
        background: #f7f8fa;
    }

    .npl-teams-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 14px;
    }

    .npl-team-card {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-width: 0;
        min-height: 160px;
        padding: 19px 12px;
        border: 1px solid var(--npl-border);
        border-radius: 6px;
        background: #fff;
        color: var(--npl-text);
        text-align: center;
        text-decoration: none;
        transition: border-color .2s ease, transform .2s ease;
    }

    .npl-team-card:hover {
        transform: translateY(-2px);
        border-color: #cbd5e1;
        color: var(--npl-text);
    }

    .npl-team-card .npl-team-logo {
        width: 66px;
        height: 66px;
        margin-bottom: 12px;
    }

    .npl-team-card h3 {
        margin: 0;
        color: var(--npl-navy);
        font-size: 13px;
        font-weight: 800;
        line-height: 1.45;
        overflow-wrap: anywhere;
    }

    .npl-team-card small {
        margin-top: 5px;
        color: var(--npl-muted);
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .6px;
    }

    /* News */

    .npl-news-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 20px;
    }

    .npl-news-card {
        overflow: hidden;
        border: 1px solid var(--npl-border);
        border-radius: 6px;
        background: #fff;
        transition: border-color .2s ease;
    }

    .npl-news-card:hover {
        border-color: #cbd5e1;
    }

    .npl-news-image {
        display: block;
        aspect-ratio: 16 / 9;
        overflow: hidden;
        background: #eef0f4;
    }

    .npl-news-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform .25s ease;
    }

    .npl-news-card:hover .npl-news-image img {
        transform: scale(1.025);
    }

    .npl-news-placeholder {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
        color: #9ca3af;
        font-size: 13px;
        font-weight: 700;
    }

    .npl-news-content {
        padding: 17px;
    }

    .npl-news-date {
        color: var(--npl-red);
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .3px;
        text-transform: uppercase;
    }

    .npl-news-content h3 {
        margin: 9px 0;
        font-size: 17px;
        font-weight: 800;
        line-height: 1.45;
    }

    .npl-news-content h3 a {
        color: var(--npl-navy);
        text-decoration: none;
    }

    .npl-news-content h3 a:hover {
        color: var(--npl-red);
    }

    .npl-news-content p {
        display: -webkit-box;
        overflow: hidden;
        margin: 0 0 14px;
        color: var(--npl-muted);
        font-size: 13px;
        line-height: 1.7;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 3;
    }

    /* Empty states */

    .npl-empty {
        grid-column: 1 / -1;
        padding: 28px 20px;
        border: 1px dashed #d1d5db;
        border-radius: 6px;
        color: var(--npl-muted);
        font-size: 14px;
        text-align: center;
    }

    /* Responsive */

    @media (max-width: 900px) {
        .npl-hero-inner {
            grid-template-columns: 1fr;
            gap: 30px;
        }

        .npl-hero-copy {
            max-width: 620px;
        }

        .npl-match-card {
            max-width: 580px;
            width: 100%;
        }

        .npl-teams-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .npl-results-grid,
        .npl-news-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 600px) {
        .npl-container {
            width: min(100% - 26px, 1180px);
        }

        .npl-section {
            padding: 30px 0;
        }

        .npl-hero-inner {
            padding-top: 32px;
            padding-bottom: 32px;
        }

        .npl-hero h1 {
            font-size: 36px;
        }

        .npl-hero-copy {
            font-size: 14px;
        }

        .npl-section-heading {
            align-items: flex-start;
        }

        .npl-section-heading h2 {
            font-size: 22px;
        }

        .npl-section-heading p {
            font-size: 12px;
        }

        .npl-link {
            margin-top: 4px;
            font-size: 12px;
        }

        .npl-match-body {
            padding: 20px 12px 15px;
        }

        .npl-team-logo {
            width: 60px;
            height: 60px;
        }

        .npl-match-team h3 {
            font-size: 12px;
        }

        .npl-team-score {
            font-size: 20px;
        }

        .npl-ticker-inner {
            align-items: flex-start;
            gap: 10px;
        }

        .npl-ticker-label {
            padding-top: 13px;
        }

        .npl-ticker-items {
            gap: 8px 14px;
        }

        .npl-results-grid,
        .npl-news-grid {
            grid-template-columns: 1fr;
        }

        .npl-teams-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        .npl-team-card {
            min-height: 145px;
        }

        .npl-news-content h3 {
            font-size: 16px;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .npl-home *,
        .npl-home *::before,
        .npl-home *::after {
            scroll-behavior: auto !important;
            transition: none !important;
        }
    }
</style>

<div class="npl-home">

    {{-- Featured section --}}
    <section class="npl-hero">
        <div class="npl-container npl-hero-inner">

            <div>
                <div class="npl-kicker">Nepal Premier League</div>

                <h1>
                    All the action.<br>
                    <span>One destination.</span>
                </h1>

                <p class="npl-hero-copy">
                    Follow the Nepal Premier League with match scores,
                    fixtures, team information, results and the latest cricket news.
                </p>

                <div class="npl-hero-actions">
                    @if(Route::has('fixtures.index'))
                        <a href="{{ route('fixtures.index') }}" class="npl-button">
                            View Fixtures
                        </a>
                    @endif

                    @if(Route::has('teams.index'))
                        <a href="{{ route('teams.index') }}" class="npl-button npl-button-outline">
                            Explore Teams
                        </a>
                    @endif
                </div>

                <div class="npl-hero-note">
                    Match information and scores are displayed from available site data.
                </div>
            </div>

            <div>
                <div class="npl-match-card">

                    @if($featuredMatch)
                        <div class="npl-match-top">
                            <strong>
                                @if($featuredIsLive)
                                    Live Match Centre
                                @elseif($featuredIsFinished)
                                    Latest Match Result
                                @else
                                    Featured Fixture
                                @endif
                            </strong>

                            <span class="npl-status {{ $featuredIsLive ? 'npl-status-live' : '' }}">
                                {{ $matchStatusLabel($featuredMatch) }}
                            </span>
                        </div>

                        <div class="npl-match-body">
                            <div class="npl-match-teams">

                                <div class="npl-match-team">
                                    <div class="npl-team-logo">
                                        @if($teamLogo($homeTeam))
                                            <img
                                                src="{{ $teamLogo($homeTeam) }}"
                                                alt="{{ $teamName($homeTeam, 'Home Team') }}"
                                                loading="lazy"
                                            >
                                        @else
                                            <span class="npl-team-placeholder">
                                                {{ $teamShortName($homeTeam) }}
                                            </span>
                                        @endif
                                    </div>

                                    <h3>{{ $teamName($homeTeam, 'Home Team') }}</h3>

                                    <div class="npl-team-short">
                                        {{ $teamShortName($homeTeam) }}
                                    </div>

                                    @if($homeScore)
                                        <div class="npl-team-score">
                                            {{ $homeScore['score'] }}
                                        </div>
                                        <div class="npl-team-overs">
                                            Overs {{ $homeScore['overs'] }}
                                        </div>
                                    @endif
                                </div>

                                <div class="npl-versus">VS</div>

                                <div class="npl-match-team">
                                    <div class="npl-team-logo">
                                        @if($teamLogo($awayTeam))
                                            <img
                                                src="{{ $teamLogo($awayTeam) }}"
                                                alt="{{ $teamName($awayTeam, 'Away Team') }}"
                                                loading="lazy"
                                            >
                                        @else
                                            <span class="npl-team-placeholder">
                                                {{ $teamShortName($awayTeam) }}
                                            </span>
                                        @endif
                                    </div>

                                    <h3>{{ $teamName($awayTeam, 'Away Team') }}</h3>

                                    <div class="npl-team-short">
                                        {{ $teamShortName($awayTeam) }}
                                    </div>

                                    @if($awayScore)
                                        <div class="npl-team-score">
                                            {{ $awayScore['score'] }}
                                        </div>
                                        <div class="npl-team-overs">
                                            Overs {{ $awayScore['overs'] }}
                                        </div>
                                    @endif
                                </div>

                            </div>

                            <div class="npl-match-info">
                                <div>
                                    {{ $formatDate($featuredMatch) }}
                                    &nbsp;·&nbsp;
                                    {{ $formatTime($featuredMatch) }}
                                </div>

                                @if($featuredResult)
                                    <div>{{ $featuredResult }}</div>
                                @elseif($featuredIsLive)
                                    <div>Follow the match for the latest available score.</div>
                                @elseif(!$featuredIsFinished)
                                    <div>Match details and updates</div>
                                @endif
                            </div>
                        </div>

                        <div class="npl-match-footer">
                            <a href="{{ $fixtureUrl($featuredMatch) }}" class="npl-button">
                                {{ $featuredIsLive ? 'Open Live Match' : ($featuredIsFinished ? 'View Scorecard' : 'View Match Details') }}
                            </a>
                        </div>
                    @else
                        <div class="npl-empty-match">
                            <h3>No Featured Match</h3>
                            <p>
                                There is no live match or upcoming fixture available to display right now.
                                Check the fixtures page for match information.
                            </p>

                            @if(Route::has('fixtures.index'))
                                <a href="{{ route('fixtures.index') }}" class="npl-button">
                                    View All Fixtures
                                </a>
                            @endif
                        </div>
                    @endif

                </div>
            </div>

        </div>
    </section>

    {{-- Quick match links --}}
    @if($liveMatches->isNotEmpty() || $upcomingMatches->isNotEmpty())
        <section class="npl-ticker">
            <div class="npl-container npl-ticker-inner">
                <div class="npl-ticker-label">
                    {{ $liveMatches->isNotEmpty() ? 'Match Updates' : 'Upcoming' }}
                </div>

                <div class="npl-ticker-items">
                    @foreach($liveMatches->take(3) as $match)
                        @php
                            $tickerHome = data_get($match, 'homeTeam')
                                ?? data_get($match, 'home_team');

                            $tickerAway = data_get($match, 'awayTeam')
                                ?? data_get($match, 'away_team');
                        @endphp

                        <a href="{{ $fixtureUrl($match) }}">
                            <span style="color:#c6283d;font-weight:800;">LIVE</span>
                            &nbsp;{{ $teamShortName($tickerHome) }}
                            vs {{ $teamShortName($tickerAway) }}
                        </a>
                    @endforeach

                    @foreach($upcomingMatches->take(3) as $match)
                        @php
                            $tickerHome = data_get($match, 'homeTeam')
                                ?? data_get($match, 'home_team');

                            $tickerAway = data_get($match, 'awayTeam')
                                ?? data_get($match, 'away_team');
                        @endphp

                        <a href="{{ $fixtureUrl($match) }}">
                            {{ $teamShortName($tickerHome) }}
                            vs {{ $teamShortName($tickerAway) }}
                            · {{ $formatDate($match) }}
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Recent results --}}
    <section class="npl-section">
        <div class="npl-container">

            <div class="npl-section-heading">
                <div>
                    <h2>Recent Results</h2>
                    <p>Scores and outcomes from completed matches.</p>
                </div>

                @if(Route::has('fixtures.index'))
                    <a class="npl-link" href="{{ route('fixtures.index') }}">
                        All Fixtures <span aria-hidden="true">→</span>
                    </a>
                @endif
            </div>

            <div class="npl-results-grid">
                @forelse($recentResults->take(3) as $match)
                    @php
                        $resultHome = data_get($match, 'homeTeam')
                            ?? data_get($match, 'home_team');

                        $resultAway = data_get($match, 'awayTeam')
                            ?? data_get($match, 'away_team');

                        $resultHomeScore = $scoreFor($match, $resultHome);
                        $resultAwayScore = $scoreFor($match, $resultAway);

                        $resultText = data_get($match, 'result')
                            ?? data_get($match, 'result_summary')
                            ?? data_get($match, 'status_text');
                    @endphp

                    <article class="npl-result-card">
                        <div class="npl-result-header">
                            <strong>{{ $formatDate($match) }}</strong>
                            <span>FINAL RESULT</span>
                        </div>

                        <div class="npl-result-teams">
                            <div class="npl-result-team">
                                <div class="npl-result-logo">
                                    @if($teamLogo($resultHome))
                                        <img
                                            src="{{ $teamLogo($resultHome) }}"
                                            alt="{{ $teamName($resultHome) }}"
                                            loading="lazy"
                                        >
                                    @else
                                        <span>{{ $teamShortName($resultHome) }}</span>
                                    @endif
                                </div>

                                <div class="npl-result-team-name">
                                    {{ $teamName($resultHome, 'Home Team') }}
                                </div>

                                <div class="npl-result-score">
                                    {{ $resultHomeScore['score'] ?? '—' }}
                                </div>
                            </div>

                            <div class="npl-result-team">
                                <div class="npl-result-logo">
                                    @if($teamLogo($resultAway))
                                        <img
                                            src="{{ $teamLogo($resultAway) }}"
                                            alt="{{ $teamName($resultAway) }}"
                                            loading="lazy"
                                        >
                                    @else
                                        <span>{{ $teamShortName($resultAway) }}</span>
                                    @endif
                                </div>

                                <div class="npl-result-team-name">
                                    {{ $teamName($resultAway, 'Away Team') }}
                                </div>

                                <div class="npl-result-score">
                                    {{ $resultAwayScore['score'] ?? '—' }}
                                </div>
                            </div>
                        </div>

                        <div class="npl-result-footer">
                            @if($resultText)
                                {{ $resultText }}
                            @else
                                <a href="{{ $fixtureUrl($match) }}">View match scorecard →</a>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="npl-empty">
                        No completed match results are available yet.
                    </div>
                @endforelse
            </div>

        </div>
    </section>

    @include('components.player-statistics')

    {{-- Teams --}}
    <section class="npl-section npl-teams-section">
        <div class="npl-container">

            <div class="npl-section-heading">
                <div>
                    <h2>Teams</h2>
                    <p>Meet the teams competing in the league.</p>
                </div>

                @if(Route::has('teams.index'))
                    <a class="npl-link" href="{{ route('teams.index') }}">
                        All Teams <span aria-hidden="true">→</span>
                    </a>
                @endif
            </div>

            <div class="npl-teams-grid">
                @forelse($teams->take(10) as $team)
                    <a href="{{ $teamUrl($team) }}" class="npl-team-card">
                        <div class="npl-team-logo">
                            @if($teamLogo($team))
                                <img
                                    src="{{ $teamLogo($team) }}"
                                    alt="{{ $teamName($team) }} logo"
                                    loading="lazy"
                                >
                            @else
                                <span class="npl-team-placeholder">
                                    {{ $teamShortName($team) }}
                                </span>
                            @endif
                        </div>

                        <h3>{{ $teamName($team) }}</h3>

                        <small>{{ $teamShortName($team) }}</small>
                    </a>
                @empty
                    <div class="npl-empty">
                        Team information will appear here when available.
                    </div>
                @endforelse
            </div>

        </div>
    </section>

    {{-- Latest news --}}
    <section class="npl-section">
        <div class="npl-container">

            <div class="npl-section-heading">
                <div>
                    <h2>Latest Cricket News</h2>
                    <p>News, match reports and updates from the NPL.</p>
                </div>

                @if(Route::has('news.index'))
                    <a class="npl-link" href="{{ route('news.index') }}">
                        All News <span aria-hidden="true">→</span>
                    </a>
                @endif
            </div>

            <div class="npl-news-grid">
                @forelse($latestNews->take(3) as $article)
                    @php
                        $articleImage = $imageUrl(
                            data_get($article, 'featured_image')
                                ?? data_get($article, 'thumbnail')
                                ?? data_get($article, 'image')
                                ?? data_get($article, 'image_path')
                        );

                        $articleTitle = data_get($article, 'title')
                            ?? 'NPL News';

                        $articleExcerpt = data_get($article, 'excerpt')
                            ?? data_get($article, 'description')
                            ?? data_get($article, 'meta_description');

                        $articleDate = data_get($article, 'published_at')
                            ?? data_get($article, 'created_at');
                    @endphp

                    <article class="npl-news-card">
                        <a href="{{ $newsUrl($article) }}" class="npl-news-image">
                            @if($articleImage)
                                <img
                                    src="{{ $articleImage }}"
                                    alt="{{ $articleTitle }}"
                                    loading="lazy"
                                >
                            @else
                                <div class="npl-news-placeholder">
                                    NPL NEWS
                                </div>
                            @endif
                        </a>

                        <div class="npl-news-content">
                            <div class="npl-news-date">
                                @if($articleDate)
                                    {{ \Illuminate\Support\Carbon::parse($articleDate)->format('d M Y') }}
                                @else
                                    NPL News
                                @endif
                            </div>

                            <h3>
                                <a href="{{ $newsUrl($article) }}">
                                    {{ $articleTitle }}
                                </a>
                            </h3>

                            @if($articleExcerpt)
                                <p>{{ \Illuminate\Support\Str::limit(strip_tags($articleExcerpt), 150) }}</p>
                            @endif

                            <a class="npl-link" href="{{ $newsUrl($article) }}">
                                Read Article <span aria-hidden="true">→</span>
                            </a>
                        </div>
                    </article>
                @empty
                    <div class="npl-empty">
                        No published news articles are available at the moment.
                    </div>
                @endforelse
            </div>

        </div>
    </section>

</div>
@endsection