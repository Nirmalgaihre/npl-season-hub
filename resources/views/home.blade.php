@extends('layouts.app')

@php
    /*
    |--------------------------------------------------------------------------
    | Data normalisation
    |--------------------------------------------------------------------------
    */
    $teams              = collect($teams ?? []);
    $recentResults      = collect($recentResults ?? []);
    $latestNews         = collect($latestNews ?? []);
    $liveScoreByFixture = collect($liveScoreByFixture ?? []);

    /*
     * A fixture only counts as live if it is not already finished.
     * This makes the hero switch back to "Upcoming" automatically
     * the moment a live match is marked completed.
     */
    $isFinished = function ($match) {
        $status = strtolower((string) data_get($match, 'status'));

        return in_array($status, ['completed', 'complete', 'finished', 'result', 'abandoned', 'cancelled', 'canceled'], true)
            || filled(data_get($match, 'winner_id'))
            || filled(data_get($match, 'winner_name'))
            || filled(data_get($match, 'winner.name'));
    };

    $liveMatches = collect($liveMatches ?? [])
        ->reject(fn ($m) => $isFinished($m))
        ->values();

    $liveMatch   = $liveMatches->first();
    $hasLive     = $liveMatch !== null;
    $otherLive   = $liveMatches->slice(1)->values();
    $nextMatch   = $nextMatch ?? null;

    /*
    |--------------------------------------------------------------------------
    | Shared view helpers
    |--------------------------------------------------------------------------
    */
    $imageUrl = function ($path) {
        if (blank($path)) {
            return null;
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        return asset('storage/' . ltrim($path, '/'));
    };

    $initial = fn ($name) => strtoupper(mb_substr(trim((string) $name) ?: 'N', 0, 1));

    /* Cricket overs: 10.3 means 10 overs and 3 balls, never 10.5 decimal. */
    $scoreOvers = function ($innings) {
        if (!$innings) {
            return null;
        }

        $legalBalls = data_get($innings, 'legal_balls');

        if ($legalBalls !== null && is_numeric($legalBalls)) {
            $legalBalls = max(0, (int) $legalBalls);

            return intdiv($legalBalls, 6) . '.' . ($legalBalls % 6);
        }

        $overs = data_get($innings, 'overs');

        return $overs !== null && $overs !== '' ? $overs : null;
    };

    $inningsRuns = function ($innings) {
        if (!$innings) {
            return null;
        }

        foreach (['total_runs', 'runs', 'total_score'] as $key) {
            $value = data_get($innings, $key);

            if ($value !== null && $value !== '') {
                return (int) $value;
            }
        }

        return null;
    };

    $formatScore = function ($innings) use ($inningsRuns) {
        $runs = $inningsRuns($innings);

        if ($runs === null) {
            return null;
        }

        $wickets = data_get($innings, 'wickets');

        if ($wickets === null) {
            $wickets = data_get($innings, 'total_wickets', 0);
        }

        return number_format($runs) . '/' . (int) ($wickets ?? 0);
    };

    $matchDate = function ($match) {
        $date = data_get($match, 'scheduled_at') ?? data_get($match, 'match_date');

        if (!$date) {
            return 'Date to be announced';
        }

        try {
            return \Illuminate\Support\Carbon::parse($date)->format('d M Y');
        } catch (\Throwable $e) {
            return 'Date to be announced';
        }
    };

    /*
     * One place that turns a fixture + its innings into everything a
     * scoreboard needs. Used by the live panel and the recent results.
     */
    $scoreFor = function ($fixture) use ($liveScoreByFixture, $formatScore, $scoreOvers, $inningsRuns) {
        $innings = collect($liveScoreByFixture->get(data_get($fixture, 'id'), collect()));

        if ($innings->isEmpty()) {
            $innings = collect(data_get($fixture, 'innings') ?? data_get($fixture, 'scorecards') ?? []);
        }

        $innings = $innings->sortBy('innings_number')->values();

        $homeId = data_get($fixture, 'home_team_id');
        $awayId = data_get($fixture, 'away_team_id');

        $latestFor = function ($teamId) use ($innings) {
            if ($teamId === null) {
                return null;
            }

            return $innings
                ->filter(fn ($i) => (string) data_get($i, 'batting_team_id') === (string) $teamId)
                ->sortBy('innings_number')
                ->last();
        };

        $home = $latestFor($homeId);
        $away = $latestFor($awayId);

        $homeName = data_get($fixture, 'homeTeam.name', 'Home Team');
        $awayName = data_get($fixture, 'awayTeam.name', 'Away Team');

        $current   = $innings->last();
        $battingId = $current ? (string) data_get($current, 'batting_team_id') : null;

        /* Chase summary, only when two innings exist. */
        $chase = null;

        if ($innings->count() >= 2) {
            $first  = $innings->first();
            $second = $innings->last();
            $target = ($inningsRuns($first) ?? 0) + 1;
            $need   = $target - ($inningsRuns($second) ?? 0);

            $chasingName = (string) data_get($second, 'batting_team_id') === (string) $homeId
                ? $homeName
                : $awayName;

            $chase = $need > 0
                ? "{$chasingName} need {$need} " . ($need === 1 ? 'run' : 'runs') . " to win"
                : "{$chasingName} have reached the target";
        } elseif ($innings->count() === 1 && $current) {
            $chase = ((string) $battingId === (string) $homeId ? $homeName : $awayName) . ' batting first';
        }

        return [
            'homeName'   => $homeName,
            'awayName'   => $awayName,
            'homeScore'  => $formatScore($home),
            'awayScore'  => $formatScore($away),
            'homeOvers'  => $scoreOvers($home),
            'awayOvers'  => $scoreOvers($away),
            'homeBats'   => $battingId !== null && $battingId === (string) $homeId,
            'awayBats'   => $battingId !== null && $battingId === (string) $awayId,
            'chase'      => $chase,
            'winnerName' => data_get($fixture, 'winner.name') ?? data_get($fixture, 'winner_name'),
        ];
    };
@endphp

@section('title', $hasLive ? 'Live Now' : 'Home')

@section('meta_description', 'Follow Nepal Premier League live scores, fixtures, manually updated cricket scoreboards, teams, match results and the latest NPL news.')

@section('content')

<style>
    /* =========================================================
       Tokens
       ========================================================= */
    .home-page {
        --navy: #202766;
        --navy-dark: #171D50;
        --navy-deep: #0E1238;
        --orange: #F06445;
        --live: #E5304A;
        --win: #16845B;
        --bg: #F6F8FB;
        --border: #E5E9F2;
        --muted: #64748B;
        --ease: cubic-bezier(.2, .7, .2, 1);
        color: #1E293B;
        background: var(--bg);
    }

    .home-page * { box-sizing: border-box; }

    .home-page a:focus-visible,
    .home-page button:focus-visible {
        outline: 3px solid var(--orange);
        outline-offset: 3px;
        border-radius: 8px;
    }

    .home-container {
        width: 100%;
        max-width: 1280px;
        margin-inline: auto;
        padding-inline: 20px;
    }

    /* =========================================================
       Type + section scaffolding
       ========================================================= */
    .home-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        color: var(--orange);
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 1.6px;
        line-height: 1.6;
        text-transform: uppercase;
    }

    .home-section { padding-block: 64px; }

    .home-section-white {
        border-block: 1px solid #E9EDF4;
        background: #fff;
    }

    .home-section-title {
        color: var(--navy);
        font-size: clamp(25px, 3vw, 34px);
        font-weight: 900;
        letter-spacing: -1px;
        line-height: 1.2;
    }

    .home-section-description {
        margin-top: 10px;
        max-width: 56ch;
        color: var(--muted);
        font-size: 14px;
        line-height: 1.8;
    }

    .home-section-head {
        display: flex;
        align-items: end;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 27px;
    }

    .home-text-link {
        display: inline-flex;
        flex-shrink: 0;
        align-items: center;
        gap: 8px;
        color: var(--navy);
        font-size: 12px;
        font-weight: 800;
        transition: color .2s ease;
    }

    .home-text-link:hover { color: var(--orange); }

    .home-dot {
        width: 7px;
        height: 7px;
        flex-shrink: 0;
        border-radius: 50%;
        background: var(--orange);
    }

    /* =========================================================
       Buttons
       ========================================================= */
    .home-button {
        display: inline-flex;
        min-height: 45px;
        align-items: center;
        justify-content: center;
        gap: 9px;
        border: 1px solid transparent;
        border-radius: 10px;
        padding: 11px 17px;
        font-size: 12px;
        font-weight: 800;
        text-align: center;
        transition: background .2s ease, color .2s ease, border-color .2s ease,
            transform .2s var(--ease), box-shadow .2s ease;
    }

    .home-button:hover { transform: translateY(-2px); }

    .home-button-primary {
        background: var(--orange);
        color: #fff;
        box-shadow: 0 6px 0 #B94328, 0 14px 24px rgb(240 100 69 / 28%);
    }
    .home-button-primary:hover { background: #D94D31; color: #fff; }
    .home-button-primary:active { transform: translateY(3px); box-shadow: 0 2px 0 #B94328; }

    .home-button-live {
        background: var(--live);
        color: #fff;
        box-shadow: 0 6px 0 #AD1F36, 0 14px 24px rgb(229 48 74 / 28%);
    }
    .home-button-live:hover { background: #CC2640; color: #fff; }
    .home-button-live:active { transform: translateY(3px); box-shadow: 0 2px 0 #AD1F36; }

    .home-button-dark { background: var(--navy); color: #fff; }
    .home-button-dark:hover { background: var(--navy-dark); color: #fff; }

    .home-button-outline { border-color: var(--border); background: #fff; color: var(--navy); }
    .home-button-outline:hover { border-color: var(--orange); color: var(--orange); }

    /* =========================================================
       Cards
       ========================================================= */
    .home-card {
        border: 1px solid var(--border);
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 3px 12px rgb(15 23 42 / 3%);
        transition: border-color .2s ease, box-shadow .25s ease;
    }

    .home-card:hover {
        border-color: #D8DDF0;
        box-shadow: 0 24px 45px rgb(32 39 102 / 13%);
    }

    /* =========================================================
       3D engine (driven by the script at the bottom)
       ========================================================= */
    .scene3d { perspective: 1200px; }

    [data-tilt] {
        --rx: 0deg;
        --ry: 0deg;
        --gx: 50%;
        --gy: 50%;
        position: relative;
        transform: rotateX(var(--rx)) rotateY(var(--ry));
        transform-style: preserve-3d;
        transition: transform .5s var(--ease);
        will-change: transform;
    }

    [data-tilt].is-tilting { transition: transform .08s linear; }

    /* Soft light that follows the pointer */
    [data-tilt]::after {
        position: absolute;
        z-index: 5;
        inset: 0;
        border-radius: inherit;
        background: radial-gradient(circle at var(--gx) var(--gy), rgb(255 255 255 / 26%), transparent 48%);
        content: "";
        opacity: 0;
        pointer-events: none;
        transition: opacity .3s ease;
    }

    [data-tilt].is-tilting::after { opacity: 1; }

    .z-1 { transform: translateZ(18px); }
    .z-2 { transform: translateZ(34px); }
    .z-3 { transform: translateZ(54px); }

    /* =========================================================
       Hero
       ========================================================= */
    .home-hero {
        position: relative;
        overflow: hidden;
        isolation: isolate;
        background:
            radial-gradient(ellipse at 80% 5%, rgb(240 100 69 / 17%), transparent 32%),
            radial-gradient(ellipse at 5% 100%, rgb(100 120 220 / 13%), transparent 38%),
            linear-gradient(125deg, #171D50 0%, #202766 55%, #29347B 100%);
        color: #fff;
    }

    .home-hero.is-live {
        background:
            radial-gradient(ellipse at 82% 8%, rgb(229 48 74 / 24%), transparent 34%),
            radial-gradient(ellipse at 5% 100%, rgb(100 120 220 / 13%), transparent 38%),
            linear-gradient(125deg, #131744 0%, #1C2260 55%, #29347B 100%);
    }

    /* Perspective floor grid, a cricket-pitch horizon */
    .home-hero::before {
        position: absolute;
        z-index: -1;
        inset: auto -20% -2px;
        height: 46%;
        background-image:
            linear-gradient(rgb(255 255 255 / 8%) 1px, transparent 1px),
            linear-gradient(90deg, rgb(255 255 255 / 8%) 1px, transparent 1px);
        background-size: 54px 54px;
        content: "";
        mask-image: linear-gradient(to top, #000 10%, transparent 90%);
        -webkit-mask-image: linear-gradient(to top, #000 10%, transparent 90%);
        transform: perspective(520px) rotateX(62deg);
        transform-origin: bottom;
        pointer-events: none;
    }

    .home-hero-rings {
        position: absolute;
        z-index: -1;
        top: 10%;
        right: -130px;
        width: 410px;
        height: 410px;
        border: 1px solid rgb(255 255 255 / 8%);
        border-radius: 50%;
        box-shadow: 0 0 0 45px rgb(255 255 255 / 2%), 0 0 0 90px rgb(255 255 255 / 2%);
        pointer-events: none;
        transform: translate3d(calc(var(--mx, 0) * -18px), calc(var(--my, 0) * -18px), 0);
        transition: transform .4s var(--ease);
    }

    .home-hero-layout {
        display: grid;
        align-items: center;
        gap: 38px;
        padding-block: 56px 72px;
    }

    .home-hero-title {
        max-width: 700px;
        color: #fff;
        font-size: clamp(38px, 5.3vw, 66px);
        font-weight: 900;
        letter-spacing: -2.5px;
        line-height: 1.08;
    }

    .home-hero-title .accent { color: var(--orange); }
    .home-hero.is-live .home-hero-title .accent { color: #FF6F83; }

    .home-hero-description {
        max-width: 530px;
        margin-top: 22px;
        color: rgb(255 255 255 / 74%);
        font-size: 15px;
        line-height: 1.95;
    }

    .home-season-pill {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        border: 1px solid rgb(255 255 255 / 16%);
        border-radius: 100px;
        background: rgb(255 255 255 / 7%);
        padding: 9px 13px;
        color: rgb(255 255 255 / 92%);
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .8px;
        text-transform: uppercase;
        backdrop-filter: blur(6px);
    }

    .home-hero-checks {
        display: flex;
        flex-wrap: wrap;
        gap: 12px 22px;
        margin-top: 27px;
    }

    .home-hero-check {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        color: rgb(255 255 255 / 75%);
        font-size: 12px;
        font-weight: 600;
    }

    .home-check-icon {
        display: inline-flex;
        width: 24px;
        height: 24px;
        flex-shrink: 0;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background: rgb(255 255 255 / 10%);
        color: #FF947C;
    }

    /* The memorable object: a 3D cricket ball floating beside the panel */
    .hero-stage { position: relative; }

    .cricket-ball {
        position: absolute;
        z-index: 6;
        top: -34px;
        left: -38px;
        width: 92px;
        height: 92px;
        border-radius: 50%;
        background:
            radial-gradient(circle at 32% 26%, #FFB09E 0, #E2452B 38%, #8E1A0A 78%, #5A0E05 100%);
        box-shadow:
            inset -10px -12px 22px rgb(0 0 0 / 38%),
            0 30px 38px rgb(0 0 0 / 35%);
        transform: translateZ(90px) translate3d(calc(var(--mx, 0) * 14px), calc(var(--my, 0) * 14px), 0);
        animation: ball-float 6s ease-in-out infinite;
        pointer-events: none;
    }

    .cricket-ball::before,
    .cricket-ball::after {
        position: absolute;
        top: 6%;
        bottom: 6%;
        width: 22%;
        border: 0 dashed rgb(255 255 255 / 78%);
        content: "";
    }

    .cricket-ball::before { left: 33%; border-left-width: 2px; border-radius: 50%; }
    .cricket-ball::after  { right: 33%; border-right-width: 2px; border-radius: 50%; }

    .cricket-ball .ball-spin {
        position: absolute;
        inset: 0;
        border-radius: 50%;
        animation: ball-spin 9s linear infinite;
    }

    @keyframes ball-float {
        0%, 100% { margin-top: 0; }
        50%      { margin-top: -12px; }
    }

    @keyframes ball-spin {
        to { transform: rotate(360deg); }
    }

    /* =========================================================
       Hero panel (shared by live + upcoming)
       ========================================================= */
    .home-panel {
        overflow: hidden;
        border: 1px solid rgb(255 255 255 / 20%);
        border-radius: 22px;
        background: #fff;
        color: #1E293B;
        box-shadow:
            0 2px 0 rgb(255 255 255 / 60%) inset,
            0 40px 80px rgb(0 0 0 / 30%),
            0 12px 0 -4px rgb(8 12 45 / 45%);
    }

    .home-panel-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        border-bottom: 1px solid #EDF0F6;
        padding: 20px 22px;
    }

    .home-panel.is-live .home-panel-top {
        background: linear-gradient(90deg, #FFF3F5, #fff 70%);
    }

    .home-panel-label {
        color: var(--muted);
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 1.5px;
        text-transform: uppercase;
    }

    .home-panel-heading {
        margin-top: 5px;
        color: var(--navy);
        font-size: 20px;
        font-weight: 900;
    }

    .home-status {
        display: inline-flex;
        flex-shrink: 0;
        align-items: center;
        gap: 7px;
        border-radius: 100px;
        padding: 7px 10px;
        font-size: 9px;
        font-weight: 800;
        letter-spacing: .5px;
        text-transform: uppercase;
    }

    .home-status-upcoming { background: #FFF0EB; color: #D94D31; }

    .home-status-live {
        background: var(--live);
        color: #fff;
        box-shadow: 0 0 0 0 rgb(229 48 74 / 50%);
        animation: live-ring 1.8s ease-out infinite;
    }

    .home-status-live .home-dot { background: #fff; animation: live-blink 1.1s ease-in-out infinite; }

    @keyframes live-ring {
        0%   { box-shadow: 0 0 0 0 rgb(229 48 74 / 50%); }
        80%, 100% { box-shadow: 0 0 0 12px rgb(229 48 74 / 0%); }
    }

    @keyframes live-blink {
        50% { opacity: .25; }
    }

    .home-match-feature {
        margin: 18px;
        border: 1px solid #E8ECF4;
        border-radius: 16px;
        background: #F8FAFD;
        padding: 21px 17px;
    }

    .home-match-feature.is-live {
        border-color: #F8D2D8;
        background: linear-gradient(180deg, #FFF7F8, #F8FAFD);
    }

    .home-team-logo {
        width: 54px;
        height: 54px;
        flex-shrink: 0;
        object-fit: contain;
        filter: drop-shadow(0 8px 8px rgb(15 23 42 / 18%));
    }

    .home-team-fallback {
        display: flex;
        width: 54px;
        height: 54px;
        flex-shrink: 0;
        align-items: center;
        justify-content: center;
        border: 1px solid #E8ECF4;
        border-radius: 15px;
        background: #fff;
        color: var(--navy);
        font-size: 21px;
        font-weight: 900;
        box-shadow: 0 6px 0 #E8ECF4;
    }

    .home-feature-team { min-width: 0; text-align: center; }

    .home-feature-team-name {
        margin-top: 11px;
        overflow-wrap: anywhere;
        color: var(--navy);
        font-size: 12px;
        font-weight: 900;
        line-height: 1.5;
    }

    .home-feature-team-caption {
        margin-top: 3px;
        color: #94A3B8;
        font-size: 9px;
        font-weight: 700;
        letter-spacing: .7px;
        text-transform: uppercase;
    }

    .home-match-date {
        border-top: 1px solid #E5E9F2;
        margin-top: 20px;
        padding-top: 16px;
        text-align: center;
    }

    /* Live scoreboard inside the hero */
    .live-row {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        align-items: center;
        gap: 12px;
        border-radius: 12px;
        padding: 12px 10px;
    }

    .live-row + .live-row { margin-top: 6px; }

    .live-row.is-batting {
        background: #fff;
        box-shadow: 0 1px 0 #F1D5DA, 0 10px 22px rgb(229 48 74 / 10%);
    }

    .live-team {
        display: flex;
        min-width: 0;
        align-items: center;
        gap: 12px;
    }

    .live-team-name {
        overflow-wrap: anywhere;
        color: var(--navy);
        font-size: 13px;
        font-weight: 900;
        line-height: 1.4;
    }

    .live-bat-tag {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        margin-top: 3px;
        color: var(--live);
        font-size: 9px;
        font-weight: 800;
        letter-spacing: .8px;
        text-transform: uppercase;
    }

    .live-score {
        color: var(--navy);
        font-size: clamp(22px, 3vw, 30px);
        font-weight: 900;
        font-variant-numeric: tabular-nums;
        letter-spacing: -1px;
        line-height: 1.1;
        text-align: right;
        white-space: nowrap;
    }

    .live-overs {
        margin-top: 3px;
        color: #94A3B8;
        font-size: 10px;
        font-weight: 700;
        text-align: right;
    }

    .live-chase {
        margin-top: 14px;
        border-top: 1px dashed #F0C9D0;
        padding-top: 14px;
        color: #B01E36;
        font-size: 12px;
        font-weight: 800;
        line-height: 1.6;
        text-align: center;
    }

    .live-more {
        display: grid;
        gap: 8px;
        border-top: 1px solid #EDF0F6;
        padding: 14px 18px 4px;
    }

    .live-more-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        border: 1px solid #EDF0F6;
        border-radius: 10px;
        padding: 9px 12px;
        color: var(--navy);
        font-size: 11px;
        font-weight: 800;
        transition: border-color .2s ease, color .2s ease;
    }

    .live-more-item:hover { border-color: var(--live); color: var(--live); }

    /* =========================================================
       Results scoreboard
       ========================================================= */
    .scoreboard-card { position: relative; overflow: hidden; }

    .scoreboard-card::before {
        position: absolute;
        z-index: 1;
        inset: 0 0 auto;
        height: 3px;
        background: linear-gradient(90deg, var(--navy), var(--orange));
        content: "";
    }

    .scoreboard-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 20px 20px 15px;
    }

    .scoreboard-status {
        display: inline-flex;
        flex-shrink: 0;
        align-items: center;
        gap: 6px;
        border-radius: 100px;
        background: #EAF8F1;
        padding: 6px 9px;
        color: var(--win);
        font-size: 9px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .scoreboard-teams {
        margin-inline: 18px;
        border: 1px solid #E9EDF4;
        border-radius: 14px;
        background: #F8FAFD;
        padding: 16px;
    }

    .scoreboard-team {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        align-items: center;
        gap: 10px;
    }

    .scoreboard-team + .scoreboard-team {
        border-top: 1px dashed #DDE3EE;
        margin-top: 14px;
        padding-top: 14px;
    }

    .scoreboard-team-info { display: flex; min-width: 0; align-items: center; gap: 10px; }

    .scoreboard-logo {
        width: 39px;
        height: 39px;
        flex-shrink: 0;
        object-fit: contain;
        filter: drop-shadow(0 5px 5px rgb(15 23 42 / 16%));
    }

    .scoreboard-fallback {
        width: 39px;
        height: 39px;
        border-radius: 12px;
        font-size: 14px;
    }

    .scoreboard-team-name {
        overflow-wrap: anywhere;
        color: var(--navy);
        font-size: 12px;
        font-weight: 800;
        line-height: 1.5;
    }

    .scoreboard-number {
        color: var(--navy);
        font-size: clamp(19px, 2.5vw, 25px);
        font-weight: 900;
        font-variant-numeric: tabular-nums;
        letter-spacing: -.8px;
        line-height: 1.15;
        text-align: right;
        white-space: nowrap;
    }

    .scoreboard-overs {
        margin-top: 4px;
        color: #94A3B8;
        font-size: 10px;
        font-weight: 700;
        text-align: right;
    }

    .scoreboard-result {
        margin: 15px 18px 0;
        border: 1px solid #D8EFE4;
        border-radius: 12px;
        background: #F0FAF5;
        padding: 12px 14px;
    }

    .scoreboard-result-label {
        color: var(--win);
        font-size: 9px;
        font-weight: 900;
        letter-spacing: 1px;
        text-transform: uppercase;
    }

    .scoreboard-result-text {
        margin-top: 5px;
        color: #155D43;
        font-size: 12px;
        font-weight: 800;
        line-height: 1.7;
    }

    .scoreboard-footer { padding: 17px 18px 19px; }

    .scoreboard-note {
        margin: 15px 18px 0;
        border: 1px dashed #D9DFEB;
        border-radius: 12px;
        background: #FAFBFE;
        padding: 15px;
        color: var(--muted);
        font-size: 12px;
        line-height: 1.8;
    }

    /* =========================================================
       Teams
       ========================================================= */
    .home-team-card { padding: 24px 14px 20px; text-align: center; }

    .home-team-card .home-team-logo,
    .home-team-card .home-team-fallback {
        width: 62px;
        height: 62px;
        margin-inline: auto;
    }

    .home-team-card-name {
        margin-top: 17px;
        overflow-wrap: anywhere;
        color: var(--navy);
        font-size: 13px;
        font-weight: 900;
        line-height: 1.55;
    }

    .home-team-card-short { margin-top: 5px; color: #94A3B8; font-size: 11px; font-weight: 700; }

    /* =========================================================
       Empty states
       ========================================================= */
    .home-empty {
        border: 1px dashed #D9DFEB;
        border-radius: 18px;
        background: #fff;
        padding: 42px 22px;
        text-align: center;
    }

    .home-empty-icon {
        display: flex;
        width: 54px;
        height: 54px;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        border: 1px solid #F9D8CE;
        border-radius: 17px;
        background: #FFF0EB;
        color: var(--orange);
        font-size: 22px;
    }

    .home-empty-title { margin-top: 17px; color: var(--navy); font-size: 17px; font-weight: 900; }

    .home-empty-text {
        max-width: 440px;
        margin: 9px auto 0;
        color: var(--muted);
        font-size: 13px;
        line-height: 1.8;
    }

    /* =========================================================
       News
       ========================================================= */
    .home-news-card { overflow: hidden; }

    .home-news-image,
    .home-news-placeholder {
        display: block;
        width: 100%;
        height: 200px;
        object-fit: cover;
        background: #EDF1F7;
    }

    .home-news-placeholder {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background:
            radial-gradient(circle at 80% 10%, rgb(240 100 69 / 20%), transparent 35%),
            linear-gradient(135deg, #171D50, #303C83);
        color: #fff;
    }

    .home-news-title {
        display: -webkit-box;
        overflow: hidden;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
        color: var(--navy);
        font-size: 16px;
        font-weight: 900;
        line-height: 1.55;
    }

    .home-news-excerpt {
        display: -webkit-box;
        overflow: hidden;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 3;
        color: var(--muted);
        font-size: 12px;
        line-height: 1.9;
    }

    /* =========================================================
       CTA
       ========================================================= */
    .home-cta {
        position: relative;
        overflow: hidden;
        background:
            radial-gradient(ellipse at 90% 15%, rgb(240 100 69 / 18%), transparent 30%),
            linear-gradient(120deg, #171D50, #202766);
        color: #fff;
    }

    .home-cta-inner {
        position: relative;
        z-index: 1;
        display: flex;
        flex-direction: column;
        align-items: start;
        justify-content: space-between;
        gap: 25px;
        padding-block: 42px;
    }

    /* =========================================================
       Responsive
       ========================================================= */
    @media (min-width: 640px) {
        .home-container { padding-inline: 28px; }
        .home-hero-layout { padding-block: 75px 88px; }
    }

    @media (min-width: 768px) {
        .home-hero-layout {
            grid-template-columns: minmax(0, 1.05fr) minmax(350px, .85fr);
            gap: 32px;
        }

        .home-cta-inner { flex-direction: row; align-items: center; }
    }

    @media (min-width: 1024px) {
        .home-container { padding-inline: 32px; }
        .home-hero-layout { gap: 55px; padding-block: 88px 104px; }
        .home-section { padding-block: 76px; }
    }

    @media (max-width: 639px) {
        .home-hero-layout { gap: 32px; padding-block: 43px 56px; }
        .home-hero-title { letter-spacing: -1.6px; }
        .home-hero-description { font-size: 14px; }
        .home-section { padding-block: 45px; }
        .home-section-head { align-items: start; }
        .home-match-feature { margin: 12px; padding: 19px 12px; }
        .home-panel-top { padding: 17px; }
        .cricket-ball { top: -26px; left: -14px; width: 64px; height: 64px; }
    }

    @media (max-width: 400px) {
        .home-container { padding-inline: 15px; }
        .home-section-head { flex-direction: column; align-items: start; }
        .home-hero-title { font-size: 37px; }
        .home-hero-checks { flex-direction: column; }
    }

    /* Touch devices and reduced-motion users get a flat, calm UI */
    @media (hover: none), (prefers-reduced-motion: reduce) {
        [data-tilt] { transform: none !important; }
        [data-tilt]::after { display: none; }
    }

    @media (prefers-reduced-motion: reduce) {
        .home-page *,
        .home-page *::before,
        .home-page *::after {
            animation-duration: .01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: .01ms !important;
        }
    }
</style>

<div class="home-page">

    {{-- =====================================================
         HERO
         Live match  -> Live panel
         No live     -> Upcoming panel
         Live ended  -> Upcoming panel (the live list is filtered)
         ===================================================== --}}
    <section class="home-hero {{ $hasLive ? 'is-live' : '' }}" id="hero">
        <div class="home-hero-rings" aria-hidden="true"></div>

        <div class="home-container">
            <div class="home-hero-layout">

                {{-- Copy --}}
                <div>
                    <div class="home-season-pill">
                        <span class="home-dot" @if($hasLive) style="background:var(--live)" @endif></span>
                        Nepal Premier League

                        @if(isset($season) && $season)
                            <span class="text-white/40">/</span>
                            <span>{{ $season->name }}</span>
                        @endif
                    </div>

                    @if($hasLive)
                        <h1 class="home-hero-title mt-7">
                            It&rsquo;s matchday.
                            <span class="accent block">Watch it unfold live.</span>
                        </h1>

                        <p class="home-hero-description">
                            {{ data_get($liveMatch, 'homeTeam.name', 'Home') }} are playing
                            {{ data_get($liveMatch, 'awayTeam.name', 'Away') }} right now.
                            The scoreboard updates as the scorer records each ball.
                        </p>

                        <div class="mt-7 flex flex-wrap gap-3">
                            <a href="{{ route('fixtures.show', $liveMatch) }}"
                               class="home-button home-button-live">
                                <span class="home-dot" style="background:#fff"></span>
                                Open live scorecard
                            </a>

                            <a href="{{ route('fixtures.index') }}"
                               class="home-button border border-white/25 bg-white/5 text-white hover:bg-white/10">
                                All fixtures
                            </a>
                        </div>
                    @else
                        <h1 class="home-hero-title mt-7">
                            Every match.
                            <span class="accent block">Every moment.</span>
                            All in one place.
                        </h1>

                        <p class="home-hero-description">
                            Your home for Nepal cricket. Explore fixtures, check
                            manually updated scoreboards, discover teams and
                            catch up on the latest NPL stories.
                        </p>

                        <div class="mt-7 flex flex-wrap gap-3">
                            <a href="{{ route('fixtures.index') }}"
                               class="home-button home-button-primary">
                                Explore fixtures <span aria-hidden="true">&rarr;</span>
                            </a>

                            <a href="#recent-matches"
                               class="home-button border border-white/25 bg-white/5 text-white hover:bg-white/10">
                                Recent scoreboards <span aria-hidden="true">&darr;</span>
                            </a>
                        </div>
                    @endif

                    <div class="home-hero-checks">
                        <div class="home-hero-check">
                            <span class="home-check-icon" aria-hidden="true">&#10003;</span>
                            Match information
                        </div>
                        <div class="home-hero-check">
                            <span class="home-check-icon" aria-hidden="true">&#10003;</span>
                            Match results
                        </div>
                        <div class="home-hero-check">
                            <span class="home-check-icon" aria-hidden="true">&#10003;</span>
                            League updates
                        </div>
                    </div>
                </div>

                {{-- 3D panel --}}
                <div class="hero-stage scene3d">
                    <div class="cricket-ball" aria-hidden="true"><span class="ball-spin"></span></div>

                    @if($hasLive)
                        {{-- ================= LIVE PANEL ================= --}}
                        @php
                            $ls       = $scoreFor($liveMatch);
                            $homeLogo = $imageUrl(data_get($liveMatch, 'homeTeam.logo'));
                            $awayLogo = $imageUrl(data_get($liveMatch, 'awayTeam.logo'));
                        @endphp

                        <div class="home-panel is-live" data-tilt data-tilt-max="9" aria-live="polite">
                            <div class="home-panel-top">
                                <div class="z-1">
                                    <p class="home-panel-label">Happening now</p>
                                    <h2 class="home-panel-heading">Live Match Centre</h2>
                                </div>

                                <span class="home-status home-status-live z-2">
                                    <span class="home-dot"></span>
                                    Live
                                </span>
                            </div>

                            <div class="home-match-feature is-live">
                                <div class="text-center z-1">
                                    <p class="text-[10px] font-extrabold uppercase tracking-[1.5px] text-slate-500">
                                        {{ data_get($liveMatch, 'season.name', 'Nepal Premier League') }}
                                    </p>

                                    @if(data_get($liveMatch, 'match_number'))
                                        <p class="mt-2 text-xs font-bold text-slate-500">
                                            Match {{ data_get($liveMatch, 'match_number') }}
                                        </p>
                                    @endif
                                </div>

                                <div class="mt-4 z-2">
                                    {{-- Home --}}
                                    <div class="live-row {{ $ls['homeBats'] ? 'is-batting' : '' }}">
                                        <div class="live-team">
                                            @if($homeLogo)
                                                <img src="{{ $homeLogo }}" alt="{{ $ls['homeName'] }} logo"
                                                     class="home-team-logo !h-[44px] !w-[44px]" loading="lazy">
                                            @else
                                                <div class="home-team-fallback !h-[44px] !w-[44px] !text-base">{{ $initial($ls['homeName']) }}</div>
                                            @endif

                                            <div class="min-w-0">
                                                <p class="live-team-name">{{ $ls['homeName'] }}</p>
                                                @if($ls['homeBats'])
                                                    <span class="live-bat-tag"><span class="home-dot" style="background:var(--live)"></span>Batting</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div>
                                            @if($ls['homeScore'] !== null)
                                                <p class="live-score">{{ $ls['homeScore'] }}</p>
                                                <p class="live-overs">{{ $ls['homeOvers'] !== null ? $ls['homeOvers'] . ' overs' : 'Score recorded' }}</p>
                                            @else
                                                <p class="live-score text-slate-300">&mdash;</p>
                                                <p class="live-overs">Yet to bat</p>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Away --}}
                                    <div class="live-row {{ $ls['awayBats'] ? 'is-batting' : '' }}">
                                        <div class="live-team">
                                            @if($awayLogo)
                                                <img src="{{ $awayLogo }}" alt="{{ $ls['awayName'] }} logo"
                                                     class="home-team-logo !h-[44px] !w-[44px]" loading="lazy">
                                            @else
                                                <div class="home-team-fallback !h-[44px] !w-[44px] !text-base">{{ $initial($ls['awayName']) }}</div>
                                            @endif

                                            <div class="min-w-0">
                                                <p class="live-team-name">{{ $ls['awayName'] }}</p>
                                                @if($ls['awayBats'])
                                                    <span class="live-bat-tag"><span class="home-dot" style="background:var(--live)"></span>Batting</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div>
                                            @if($ls['awayScore'] !== null)
                                                <p class="live-score">{{ $ls['awayScore'] }}</p>
                                                <p class="live-overs">{{ $ls['awayOvers'] !== null ? $ls['awayOvers'] . ' overs' : 'Score recorded' }}</p>
                                            @else
                                                <p class="live-score text-slate-300">&mdash;</p>
                                                <p class="live-overs">Yet to bat</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <p class="live-chase z-1">
                                    {{ $ls['chase'] ?? 'The scorer has not recorded a ball yet.' }}
                                </p>
                            </div>

                            <div class="px-[18px] pb-5 z-2">
                                <a href="{{ route('fixtures.show', $liveMatch) }}"
                                   class="home-button home-button-live w-full">
                                    Open live scorecard <span aria-hidden="true">&rarr;</span>
                                </a>
                            </div>

                            @if($otherLive->isNotEmpty())
                                <div class="live-more pb-5">
                                    <p class="home-panel-label">Also live</p>

                                    @foreach($otherLive as $other)
                                        <a href="{{ route('fixtures.show', $other) }}" class="live-more-item">
                                            <span>
                                                {{ data_get($other, 'homeTeam.name', 'Home') }}
                                                vs
                                                {{ data_get($other, 'awayTeam.name', 'Away') }}
                                            </span>
                                            <span aria-hidden="true">&rarr;</span>
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                    @else
                        {{-- ================= UPCOMING PANEL ================= --}}
                        <div class="home-panel" data-tilt data-tilt-max="9">
                            <div class="home-panel-top">
                                <div class="z-1">
                                    <p class="home-panel-label">Coming up next</p>
                                    <h2 class="home-panel-heading">Match Centre</h2>
                                </div>

                                <span class="home-status home-status-upcoming z-2">
                                    <span class="home-dot"></span>
                                    Upcoming
                                </span>
                            </div>

                            @if($nextMatch)
                                <div class="home-match-feature">
                                    <div class="text-center z-1">
                                        <p class="text-[10px] font-extrabold uppercase tracking-[1.5px] text-slate-500">
                                            {{ $nextMatch->season->name ?? 'Nepal Premier League' }}
                                        </p>

                                        @if($nextMatch->match_number)
                                            <p class="mt-2 text-xs font-bold text-slate-500">
                                                Match {{ $nextMatch->match_number }}
                                            </p>
                                        @endif
                                    </div>

                                    <div class="mt-6 grid grid-cols-[1fr_auto_1fr] items-center gap-2 sm:gap-4 z-3">
                                        <div class="home-feature-team">
                                            @if($imageUrl($nextMatch->homeTeam?->logo))
                                                <img src="{{ $imageUrl($nextMatch->homeTeam->logo) }}"
                                                     alt="{{ $nextMatch->homeTeam->name }} logo"
                                                     class="home-team-logo mx-auto" loading="lazy">
                                            @else
                                                <div class="home-team-fallback mx-auto">
                                                    {{ $initial($nextMatch->homeTeam->name ?? 'H') }}
                                                </div>
                                            @endif

                                            <p class="home-feature-team-name">{{ $nextMatch->homeTeam->name ?? 'Home Team' }}</p>
                                            <p class="home-feature-team-caption">Home</p>
                                        </div>

                                        <div class="text-center">
                                            <span class="text-base font-black text-[#F06445]">VS</span>
                                            <div class="mt-1 text-[9px] font-extrabold uppercase tracking-widest text-slate-400">NPL</div>
                                        </div>

                                        <div class="home-feature-team">
                                            @if($imageUrl($nextMatch->awayTeam?->logo))
                                                <img src="{{ $imageUrl($nextMatch->awayTeam->logo) }}"
                                                     alt="{{ $nextMatch->awayTeam->name }} logo"
                                                     class="home-team-logo mx-auto" loading="lazy">
                                            @else
                                                <div class="home-team-fallback mx-auto">
                                                    {{ $initial($nextMatch->awayTeam->name ?? 'A') }}
                                                </div>
                                            @endif

                                            <p class="home-feature-team-name">{{ $nextMatch->awayTeam->name ?? 'Away Team' }}</p>
                                            <p class="home-feature-team-caption">Away</p>
                                        </div>
                                    </div>

                                    <div class="home-match-date z-1">
                                        @if($nextMatch->scheduled_at)
                                            <p class="text-sm font-extrabold text-[#202766]">
                                                {{ \Illuminate\Support\Carbon::parse($nextMatch->scheduled_at)->format('D, d M Y') }}
                                            </p>
                                            <p class="mt-1.5 text-xs font-semibold text-slate-500">
                                                {{ \Illuminate\Support\Carbon::parse($nextMatch->scheduled_at)->format('h:i A') }}
                                            </p>
                                            <p class="mt-2 text-[11px] font-extrabold text-[#F06445]"
                                               data-countdown="{{ \Illuminate\Support\Carbon::parse($nextMatch->scheduled_at)->toIso8601String() }}"></p>
                                        @else
                                            <p class="text-sm font-bold text-slate-500">Schedule to be announced</p>
                                        @endif
                                    </div>
                                </div>

                                <div class="px-[18px] pb-5 z-2">
                                    <a href="{{ route('fixtures.show', $nextMatch) }}"
                                       class="home-button home-button-dark w-full">
                                        View match details <span aria-hidden="true">&rarr;</span>
                                    </a>
                                </div>
                            @else
                                <div class="px-5 py-10 text-center sm:px-7">
                                    <div class="home-empty-icon">&#128197;</div>

                                    <h3 class="mt-4 text-base font-extrabold text-[#202766]">
                                        No upcoming match scheduled
                                    </h3>

                                    <p class="mx-auto mt-2 max-w-sm text-xs leading-6 text-slate-500">
                                        No upcoming fixture is available right now.
                                        Open the fixtures page to see the full schedule.
                                    </p>

                                    <a href="{{ route('fixtures.index') }}"
                                       class="home-button home-button-primary mt-5">
                                        Browse fixtures &rarr;
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </section>

    {{-- =====================================================
         UP NEXT STRIP
         Only while a match is live, so the next fixture is
         still visible without fighting the live panel.
         ===================================================== --}}
    @if($hasLive && $nextMatch)
        <section class="border-b border-[#E9EDF4] bg-white">
            <div class="home-container">
                <div class="flex flex-wrap items-center justify-between gap-4 py-5">
                    <div class="flex items-center gap-3">
                        <span class="home-status home-status-upcoming"><span class="home-dot"></span>Up next</span>

                        <p class="text-sm font-extrabold text-[#202766]">
                            {{ $nextMatch->homeTeam->name ?? 'Home Team' }}
                            <span class="px-1 text-[#F06445]">vs</span>
                            {{ $nextMatch->awayTeam->name ?? 'Away Team' }}
                        </p>

                        @if($nextMatch->scheduled_at)
                            <p class="hidden text-xs font-semibold text-slate-500 sm:block">
                                {{ \Illuminate\Support\Carbon::parse($nextMatch->scheduled_at)->format('D, d M &middot; h:i A') }}
                            </p>
                        @endif
                    </div>

                    <a href="{{ route('fixtures.show', $nextMatch) }}" class="home-text-link">
                        Match details <span aria-hidden="true">&rarr;</span>
                    </a>
                </div>
            </div>
        </section>
    @endif

    {{-- =====================================================
         RECENT MATCHES
         ===================================================== --}}
    <section id="recent-matches" class="home-section scroll-mt-24">
        <div class="home-container">

            <div class="home-section-head">
                <div>
                    <div class="home-eyebrow"><span class="home-dot"></span>Match results</div>
                    <h2 class="home-section-title mt-3">Recent Matches</h2>
                    <p class="home-section-description">
                        Final scores and results from the manual scorecard.
                    </p>
                </div>

                <a href="{{ route('fixtures.index') }}" class="home-text-link">
                    All fixtures <span aria-hidden="true">&rarr;</span>
                </a>
            </div>

            @if($recentResults->isNotEmpty())
                <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3 scene3d">

                    @foreach($recentResults as $result)
                        @php
                            $rs        = $scoreFor($result);
                            $hasScore  = $rs['homeScore'] !== null || $rs['awayScore'] !== null;
                            $homeLogo  = $imageUrl(data_get($result, 'homeTeam.logo'));
                            $awayLogo  = $imageUrl(data_get($result, 'awayTeam.logo'));
                        @endphp

                        <article class="home-card scoreboard-card" data-tilt data-tilt-max="5">

                            <div class="scoreboard-header">
                                <div class="min-w-0 z-1">
                                    <p class="text-[10px] font-extrabold uppercase tracking-[1.3px] text-slate-400">
                                        {{ $result->season->name ?? 'Nepal Premier League' }}
                                    </p>

                                    <p class="mt-1.5 text-xs font-extrabold text-[#202766]">
                                        {{ $result->match_number ? 'Match ' . $result->match_number : 'Match scorecard' }}
                                    </p>

                                    <p class="mt-1 text-[10px] font-medium text-slate-400">
                                        {{ $matchDate($result) }}
                                    </p>
                                </div>

                                <span class="scoreboard-status z-2">
                                    <span class="home-dot" style="background:var(--win)"></span>
                                    Completed
                                </span>
                            </div>

                            <div class="scoreboard-teams z-1">
                                {{-- Home --}}
                                <div class="scoreboard-team">
                                    <div class="scoreboard-team-info">
                                        @if($homeLogo)
                                            <img src="{{ $homeLogo }}" alt="{{ $rs['homeName'] }} logo"
                                                 class="scoreboard-logo" loading="lazy">
                                        @else
                                            <div class="home-team-fallback scoreboard-fallback">{{ $initial($rs['homeName']) }}</div>
                                        @endif

                                        <div class="min-w-0">
                                            <p class="scoreboard-team-name">{{ $rs['homeName'] }}</p>

                                            @if($rs['winnerName'] && $rs['winnerName'] === $rs['homeName'])
                                                <span class="mt-1 inline-block text-[9px] font-extrabold uppercase tracking-wider text-emerald-600">Winner</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div>
                                        @if($rs['homeScore'] !== null)
                                            <p class="scoreboard-number">{{ $rs['homeScore'] }}</p>
                                            <p class="scoreboard-overs">{{ $rs['homeOvers'] !== null ? $rs['homeOvers'] . ' overs' : 'Score recorded' }}</p>
                                        @else
                                            <p class="text-xs font-bold text-slate-400">&mdash;</p>
                                            <p class="scoreboard-overs">No score</p>
                                        @endif
                                    </div>
                                </div>

                                {{-- Away --}}
                                <div class="scoreboard-team">
                                    <div class="scoreboard-team-info">
                                        @if($awayLogo)
                                            <img src="{{ $awayLogo }}" alt="{{ $rs['awayName'] }} logo"
                                                 class="scoreboard-logo" loading="lazy">
                                        @else
                                            <div class="home-team-fallback scoreboard-fallback">{{ $initial($rs['awayName']) }}</div>
                                        @endif

                                        <div class="min-w-0">
                                            <p class="scoreboard-team-name">{{ $rs['awayName'] }}</p>

                                            @if($rs['winnerName'] && $rs['winnerName'] === $rs['awayName'])
                                                <span class="mt-1 inline-block text-[9px] font-extrabold uppercase tracking-wider text-emerald-600">Winner</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div>
                                        @if($rs['awayScore'] !== null)
                                            <p class="scoreboard-number">{{ $rs['awayScore'] }}</p>
                                            <p class="scoreboard-overs">{{ $rs['awayOvers'] !== null ? $rs['awayOvers'] . ' overs' : 'Score recorded' }}</p>
                                        @else
                                            <p class="text-xs font-bold text-slate-400">&mdash;</p>
                                            <p class="scoreboard-overs">No score</p>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            @if($rs['winnerName'])
                                <div class="scoreboard-result z-1">
                                    <p class="scoreboard-result-label">Match winner</p>
                                    <p class="scoreboard-result-text">{{ $rs['winnerName'] }}</p>
                                </div>
                            @elseif(!$hasScore)
                                <div class="scoreboard-note z-1">
                                    The result is in, but the detailed scorecard has not been recorded yet.
                                </div>
                            @else
                                <div class="scoreboard-note z-1" style="border-style:solid;border-color:#E9EDF4">
                                    Final score recorded. Open the match page for the full result.
                                </div>
                            @endif

                            <div class="scoreboard-footer z-2">
                                <a href="{{ route('fixtures.show', $result) }}"
                                   class="home-button home-button-outline w-full">
                                    View full scorecard <span aria-hidden="true">&rarr;</span>
                                </a>
                            </div>
                        </article>
                    @endforeach

                </div>
            @else
                <div class="home-empty">
                    <div class="home-empty-icon">&#127951;</div>
                    <h3 class="home-empty-title">No recent matches yet</h3>
                    <p class="home-empty-text">
                        Completed matches appear here after the result and scorecard are entered.
                    </p>
                    <a href="{{ route('fixtures.index') }}" class="home-button home-button-dark mt-5">
                        Browse fixtures <span aria-hidden="true">&rarr;</span>
                    </a>
                </div>
            @endif

        </div>
    </section>

    {{-- =====================================================
         TEAMS
         ===================================================== --}}
    <section id="teams" class="home-section home-section-white scroll-mt-24">
        <div class="home-container">

            <div class="home-section-head">
                <div>
                    <div class="home-eyebrow">The competition</div>
                    <h2 class="home-section-title mt-3">Meet the Teams</h2>
                    <p class="home-section-description">
                        Discover the teams competing in the Nepal Premier League.
                    </p>
                </div>

                <a href="{{ route('teams.index') }}" class="home-text-link">
                    Explore teams <span aria-hidden="true">&rarr;</span>
                </a>
            </div>

            @if($teams->isNotEmpty())
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 scene3d">
                    @foreach($teams as $team)
                        <a href="{{ route('teams.show', $team) }}"
                           class="home-card home-team-card group" data-tilt data-tilt-max="10">

                            <div class="z-3">
                                @if($imageUrl($team->logo))
                                    <img src="{{ $imageUrl($team->logo) }}"
                                         alt="{{ $team->name }} logo"
                                         class="home-team-logo" loading="lazy">
                                @else
                                    <div class="home-team-fallback transition group-hover:border-orange-200 group-hover:bg-orange-50 group-hover:text-[#F06445]">
                                        {{ $initial($team->name) }}
                                    </div>
                                @endif
                            </div>

                            <h3 class="home-team-card-name z-2">{{ $team->name }}</h3>

                            @if(!empty($team->short_name))
                                <p class="home-team-card-short z-1">{{ $team->short_name }}</p>
                            @endif

                            <span class="z-1 mt-4 inline-flex items-center gap-2 text-[10px] font-extrabold uppercase tracking-wider text-[#F06445]">
                                Team profile <span aria-hidden="true">&rarr;</span>
                            </span>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="home-empty">
                    <div class="home-empty-icon">&#127942;</div>
                    <h3 class="home-empty-title">Teams coming soon</h3>
                    <p class="home-empty-text">
                        Teams appear here once they are added to the league.
                    </p>
                </div>
            @endif

        </div>
    </section>

    {{-- =====================================================
         LATEST NEWS
         ===================================================== --}}
    <section id="news" class="home-section scroll-mt-24">
        <div class="home-container">

            <div class="home-section-head">
                <div>
                    <div class="home-eyebrow">Around the league</div>
                    <h2 class="home-section-title mt-3">Latest Cricket News</h2>
                    <p class="home-section-description">
                        Stories, match reports and updates from the NPL.
                    </p>
                </div>

                <a href="{{ route('news.index') }}" class="home-text-link">
                    All news <span aria-hidden="true">&rarr;</span>
                </a>
            </div>

            @if($latestNews->isNotEmpty())
                <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3 scene3d">
                    @foreach($latestNews as $article)
                        @php
                            $articleImage = $article->image ?? $article->featured_image ?? null;
                            $articleDate  = $article->published_at ?? $article->created_at ?? null;
                        @endphp

                        <article class="home-card home-news-card group" data-tilt data-tilt-max="5">
                            <a href="{{ route('news.show', $article->slug) }}"
                               class="block overflow-hidden"
                               aria-label="Read {{ $article->title }}">

                                @if($imageUrl($articleImage))
                                    <img src="{{ $imageUrl($articleImage) }}"
                                         alt="{{ $article->title }}"
                                         class="home-news-image transition duration-500 group-hover:scale-[1.05]"
                                         loading="lazy">
                                @else
                                    <div class="home-news-placeholder">
                                        <span class="text-3xl font-black tracking-tight">NPL</span>
                                        <span class="mt-2 text-[9px] font-extrabold uppercase tracking-[2px] text-white/70">Cricket Hub</span>
                                    </div>
                                @endif
                            </a>

                            <div class="p-5 z-1">
                                <div class="flex flex-wrap items-center gap-2 text-[10px] font-semibold text-slate-400">
                                    @if($articleDate)
                                        <time datetime="{{ \Illuminate\Support\Carbon::parse($articleDate)->toIso8601String() }}">
                                            {{ \Illuminate\Support\Carbon::parse($articleDate)->format('d M Y') }}
                                        </time>
                                        <span aria-hidden="true">/</span>
                                    @endif

                                    <span class="font-extrabold uppercase tracking-wider text-[#F06445]">NPL News</span>
                                </div>

                                <a href="{{ route('news.show', $article->slug) }}" class="mt-3 block">
                                    <h3 class="home-news-title transition group-hover:text-[#F06445]">
                                        {{ $article->title }}
                                    </h3>
                                </a>

                                @if(!empty($article->excerpt))
                                    <p class="home-news-excerpt mt-3">{{ $article->excerpt }}</p>
                                @elseif(!empty($article->summary))
                                    <p class="home-news-excerpt mt-3">{{ $article->summary }}</p>
                                @elseif(!empty($article->content))
                                    <p class="home-news-excerpt mt-3">
                                        {{ \Illuminate\Support\Str::limit(strip_tags($article->content), 150) }}
                                    </p>
                                @endif

                                <a href="{{ route('news.show', $article->slug) }}"
                                   class="mt-5 inline-flex items-center gap-2 text-xs font-extrabold text-[#F06445] transition hover:text-[#202766]">
                                    Read full story <span aria-hidden="true">&rarr;</span>
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="home-empty">
                    <div class="home-empty-icon">&#128240;</div>
                    <h3 class="home-empty-title">No published news yet</h3>
                    <p class="home-empty-text">
                        Published articles appear here. Check back for league coverage.
                    </p>
                </div>
            @endif

        </div>
    </section>

    {{-- =====================================================
         FINAL CTA
         ===================================================== --}}
    <section class="home-cta">
        <div class="home-container">
            <div class="home-cta-inner">
                <div class="max-w-2xl">
                    <div class="home-eyebrow">Your NPL matchday starts here</div>

                    <h2 class="mt-3 text-2xl font-black tracking-tight text-white sm:text-3xl">
                        Never miss a moment.
                    </h2>

                    <p class="mt-3 max-w-xl text-sm leading-7 text-white/70">
                        Check the match schedule, follow your favourite teams
                        and open recent scoreboards in one place.
                    </p>
                </div>

                <a href="{{ route('fixtures.index') }}" class="home-button home-button-primary shrink-0">
                    Explore all fixtures <span aria-hidden="true">&rarr;</span>
                </a>
            </div>
        </div>
    </section>

</div>

<script>
(function () {
    'use strict';

    var root = document.querySelector('.home-page');
    if (!root) return;

    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var canHover     = window.matchMedia('(hover: hover) and (pointer: fine)').matches;

    /* ---------- 3D tilt ---------- */
    if (canHover && !reduceMotion) {
        root.querySelectorAll('[data-tilt]').forEach(function (el) {
            var max = parseFloat(el.getAttribute('data-tilt-max')) || 6;
            var frame = null;

            el.addEventListener('pointermove', function (e) {
                if (frame) cancelAnimationFrame(frame);

                frame = requestAnimationFrame(function () {
                    var r  = el.getBoundingClientRect();
                    var px = (e.clientX - r.left) / r.width;
                    var py = (e.clientY - r.top)  / r.height;

                    el.classList.add('is-tilting');
                    el.style.setProperty('--ry', ((px - .5) *  max * 2).toFixed(2) + 'deg');
                    el.style.setProperty('--rx', ((py - .5) * -max * 2).toFixed(2) + 'deg');
                    el.style.setProperty('--gx', (px * 100).toFixed(1) + '%');
                    el.style.setProperty('--gy', (py * 100).toFixed(1) + '%');
                });
            });

            el.addEventListener('pointerleave', function () {
                if (frame) cancelAnimationFrame(frame);
                el.classList.remove('is-tilting');
                el.style.setProperty('--rx', '0deg');
                el.style.setProperty('--ry', '0deg');
            });
        });

        /* Hero parallax: the ball and the rings drift with the pointer */
        var hero = document.getElementById('hero');

        if (hero) {
            hero.addEventListener('pointermove', function (e) {
                var r = hero.getBoundingClientRect();
                hero.style.setProperty('--mx', (((e.clientX - r.left) / r.width)  - .5).toFixed(3));
                hero.style.setProperty('--my', (((e.clientY - r.top)  / r.height) - .5).toFixed(3));
            });
        }
    }

    /* ---------- Countdown for the upcoming match ---------- */
    var countdown = root.querySelector('[data-countdown]');

    if (countdown) {
        var target = new Date(countdown.getAttribute('data-countdown')).getTime();

        var tick = function () {
            var diff = target - Date.now();

            if (isNaN(target) || diff <= 0) {
                countdown.textContent = '';
                return;
            }

            var d = Math.floor(diff / 86400000);
            var h = Math.floor(diff % 86400000 / 3600000);
            var m = Math.floor(diff % 3600000 / 60000);

            countdown.textContent = 'Starts in ' +
                (d ? d + 'd ' : '') + h + 'h ' + m + 'm';
        };

        tick();
        setInterval(tick, 30000);
    }

    /* ---------- Live refresh ----------
       While a match is live, reload every 30s so the scoreboard stays
       current. Skips hidden tabs. When the match is marked completed,
       the next load swaps the hero back to the upcoming match. */
    @if($hasLive)
    setInterval(function () {
        if (!document.hidden) {
            window.location.reload();
        }
    }, 30000);
    @endif
})();
</script>

@endsection