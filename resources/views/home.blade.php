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

    /* A finished fixture is never "live". When the scorer completes the
       match, the hero falls back to the upcoming match automatically. */
    $isFinished = function ($match) {
        $status = strtolower((string) data_get($match, 'status'));

        return in_array($status, ['completed', 'complete', 'finished', 'result', 'abandoned', 'cancelled', 'canceled'], true)
            || filled(data_get($match, 'winner_id'))
            || filled(data_get($match, 'winner_name'))
            || filled(data_get($match, 'winner.name'));
    };

    $liveMatches = collect($liveMatches ?? [])->reject(fn ($m) => $isFinished($m))->values();
    $liveMatch   = $liveMatches->first();
    $hasLive     = $liveMatch !== null;
    $otherLive   = $liveMatches->slice(1)->values();
    $nextMatch   = $nextMatch ?? null;

    /*
    |--------------------------------------------------------------------------
    | View helpers
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

    /* Team logo, or a lettered badge when there is no logo. */
    $logo = function ($team, $px = 40) use ($imageUrl, $initial) {
        $url  = $imageUrl(data_get($team, 'logo'));
        $name = (string) data_get($team, 'name', 'Team');

        if ($url) {
            return new \Illuminate\Support\HtmlString(
                '<img class="np-logo" src="' . e($url) . '" alt="' . e($name) . ' logo" width="' . $px . '" height="' . $px . '" loading="lazy" style="width:' . $px . 'px;height:' . $px . 'px">'
            );
        }

        return new \Illuminate\Support\HtmlString(
            '<span class="np-logo-fb" style="width:' . $px . 'px;height:' . $px . 'px;font-size:' . round($px * .42) . 'px">' . e($initial($name)) . '</span>'
        );
    };

    $shortName = fn ($team, $fallback) => data_get($team, 'short_name')
        ?: \Illuminate\Support\Str::limit((string) data_get($team, 'name', $fallback), 14, '');

    /* Cricket overs: 10.3 = 10 overs 3 balls. */
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

    $fmtDate = function ($date, $format = 'd M Y') {
        if (!$date) {
            return null;
        }

        try {
            return \Illuminate\Support\Carbon::parse($date)->format($format);
        } catch (\Throwable $e) {
            return null;
        }
    };

    $matchDate = fn ($m) => $fmtDate(data_get($m, 'scheduled_at') ?? data_get($m, 'match_date')) ?? 'Date to be announced';

    /* Everything a scoreboard needs for one fixture. */
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

        $chase = null;

        if ($innings->count() >= 2) {
            $target = ($inningsRuns($innings->first()) ?? 0) + 1;
            $need   = $target - ($inningsRuns($innings->last()) ?? 0);

            $chasing = (string) data_get($innings->last(), 'batting_team_id') === (string) $homeId ? $homeName : $awayName;

            $chase = $need > 0
                ? "{$chasing} need {$need} " . ($need === 1 ? 'run' : 'runs') . ' to win'
                : "{$chasing} have reached the target";
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

    /* Match ticker: live first, then next, then latest results. */
    $ticker = collect();

    foreach ($liveMatches as $m) {
        $ticker->push(['type' => 'live', 'f' => $m]);
    }

    if ($nextMatch && !$liveMatches->contains(fn ($m) => data_get($m, 'id') === data_get($nextMatch, 'id'))) {
        $ticker->push(['type' => 'up', 'f' => $nextMatch]);
    }

    foreach ($recentResults->take(8) as $m) {
        $ticker->push(['type' => 'done', 'f' => $m]);
    }
@endphp

@section('title', $hasLive ? 'Live Now' : 'Home')

@section('meta_description', 'Follow Nepal Premier League live scores, fixtures, manually updated cricket scoreboards, teams, match results and the latest NPL news.')

@section('content')

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:ital,wght@1,700;1,800;1,900&family=Barlow:wght@500;600;700&display=swap" rel="stylesheet">

<style>
    /* =========================================================
       Tokens
       ========================================================= */
    .np {
        --ink: #070A24;
        --ink-2: #0B1034;
        --navy: #131A52;
        --royal: #202766;
        --blue: #3445C8;
        --orange: #F06445;
        --live: #FF3B57;
        --gold: #FFC53D;
        --win: #2BD9A0;
        --line: rgb(255 255 255 / 11%);
        --soft: rgb(255 255 255 / 62%);
        --glass: rgb(255 255 255 / 6%);
        --cut: polygon(0 0, calc(100% - 22px) 0, 100% 22px, 100% 100%, 22px 100%, 0 calc(100% - 22px));
        --cut-sm: polygon(0 0, calc(100% - 12px) 0, 100% 12px, 100% 100%, 12px 100%, 0 calc(100% - 12px));
        --display: 'Barlow Condensed', 'Arial Narrow', Impact, sans-serif;
        --text: 'Barlow', system-ui, -apple-system, 'Segoe UI', sans-serif;
        --ease: cubic-bezier(.2, .7, .2, 1);
        background: var(--ink);
        color: #fff;
        font-family: var(--text);
        overflow-x: clip;
    }

    .np *, .np *::before, .np *::after { box-sizing: border-box; }
    .np a { color: inherit; text-decoration: none; }
    .np a:focus-visible, .np button:focus-visible { outline: 3px solid var(--gold); outline-offset: 3px; }

    .np-wrap { width: 100%; max-width: 1320px; margin-inline: auto; padding-inline: 20px; }

    .np-display {
        font-family: var(--display);
        font-style: italic;
        font-weight: 800;
        letter-spacing: -.01em;
        line-height: .94;
        text-transform: uppercase;
    }

    .np-num { font-variant-numeric: tabular-nums; }

    /* =========================================================
       3D engine
       ========================================================= */
    .scene { perspective: 1300px; }

    [data-tilt] {
        --rx: 0deg; --ry: 0deg; --gx: 50%; --gy: 50%;
        position: relative;
        transform: rotateX(var(--rx)) rotateY(var(--ry));
        transform-style: preserve-3d;
        transition: transform .55s var(--ease);
        will-change: transform;
    }

    [data-tilt].is-tilting { transition: transform .08s linear; }

    [data-tilt]::after {
        position: absolute;
        z-index: 6;
        inset: 0;
        background: radial-gradient(circle at var(--gx) var(--gy), rgb(255 255 255 / 20%), transparent 46%);
        clip-path: var(--cut);
        content: "";
        opacity: 0;
        pointer-events: none;
        transition: opacity .3s ease;
    }

    [data-tilt].is-tilting::after { opacity: 1; }

    /* A chamfered card made of separate layers so depth survives:
       shadow (back) -> shape -> content (front). */
    .np-tcard { position: relative; transform-style: preserve-3d; }

    .np-tcard::before {
        position: absolute;
        inset: 10% 5% -5%;
        background: rgb(0 0 0 / 55%);
        content: "";
        filter: blur(26px);
        transform: translateZ(-50px);
    }

    .np-shape {
        position: absolute;
        inset: 0;
        background: linear-gradient(160deg, #1A2266 0%, #101650 55%, #0B1034 100%);
        box-shadow: inset 0 0 0 1px var(--line);
        clip-path: var(--cut);
    }

    .np-shape::before {
        position: absolute;
        inset: 0 0 auto;
        height: 4px;
        background: linear-gradient(90deg, var(--orange), var(--gold));
        content: "";
    }

    .np-body { position: relative; transform: translateZ(26px); transform-style: preserve-3d; }

    .d1 { transform: translateZ(14px); }
    .d2 { transform: translateZ(30px); }
    .d3 { transform: translateZ(52px); }

    /* =========================================================
       Shared pieces
       ========================================================= */
    .np-logo { flex-shrink: 0; object-fit: contain; filter: drop-shadow(0 8px 10px rgb(0 0 0 / 45%)); }

    .np-logo-fb {
        display: inline-flex;
        flex-shrink: 0;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        background: linear-gradient(160deg, #2C3790, #171F63);
        box-shadow: inset 0 0 0 1px var(--line), 0 6px 0 rgb(0 0 0 / 25%);
        font-family: var(--display);
        font-style: italic;
        font-weight: 800;
    }

    .np-tag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 9px;
        clip-path: var(--cut-sm);
        font-family: var(--display);
        font-size: 13px;
        font-style: italic;
        font-weight: 800;
        letter-spacing: .08em;
        line-height: 1.2;
        text-transform: uppercase;
    }

    .np-tag-live { background: var(--live); color: #fff; }
    .np-tag-up   { background: var(--gold); color: #1A1500; }
    .np-tag-done { background: rgb(255 255 255 / 14%); color: #fff; }

    .np-pulse {
        width: 8px; height: 8px; border-radius: 50%; background: #fff;
        animation: np-blink 1.1s ease-in-out infinite;
    }

    @keyframes np-blink { 50% { opacity: .2; } }

    .np-btn {
        display: inline-flex;
        min-height: 50px;
        align-items: center;
        justify-content: center;
        gap: 10px;
        border: 0;
        padding: 0 26px;
        clip-path: var(--cut-sm);
        font-family: var(--display);
        font-size: 18px;
        font-style: italic;
        font-weight: 800;
        letter-spacing: .06em;
        text-transform: uppercase;
        transition: transform .2s var(--ease), filter .2s ease, background .2s ease;
    }

    .np-btn:hover { filter: brightness(1.1); transform: translateY(-2px); }

    .np-btn-orange { background: var(--orange); color: #fff; }
    .np-btn-live   { background: var(--live); color: #fff; }
    .np-btn-ghost  { background: rgb(255 255 255 / 10%); color: #fff; box-shadow: inset 0 0 0 1px rgb(255 255 255 / 25%); }
    .np-btn-white  { background: #fff; color: var(--royal); }
    .np-btn-block  { width: 100%; }

    /* Section heading: angled orange bar + condensed title */
    .np-head {
        display: flex;
        align-items: end;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 30px;
    }

    .np-title {
        position: relative;
        padding-left: 22px;
        font-size: clamp(38px, 5vw, 62px);
    }

    .np-title::before {
        position: absolute;
        top: 4px; bottom: 4px; left: 0;
        width: 9px;
        background: linear-gradient(var(--orange), var(--gold));
        clip-path: polygon(0 0, 100% 12%, 100% 100%, 0 88%);
        content: "";
    }

    .np-sub { margin: 10px 0 0 22px; max-width: 54ch; color: var(--soft); font-size: 15px; line-height: 1.7; }

    .np-more {
        display: inline-flex;
        flex-shrink: 0;
        align-items: center;
        gap: 8px;
        font-family: var(--display);
        font-size: 18px;
        font-style: italic;
        font-weight: 800;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: var(--gold);
        transition: gap .2s var(--ease), color .2s ease;
    }

    .np-more:hover { gap: 14px; color: #fff; }

    .np-section { position: relative; padding-block: 78px; }
    .np-section-alt { background: linear-gradient(180deg, var(--ink-2), var(--ink)); }

    /* =========================================================
       Match ticker
       ========================================================= */
    .np-ticker { position: relative; z-index: 5; border-bottom: 1px solid var(--line); background: #05071A; }

    .np-ticker-in { display: flex; align-items: stretch; gap: 10px; padding: 12px 0; }

    .np-ticker-label {
        display: none;
        flex-shrink: 0;
        align-items: center;
        padding: 0 16px;
        background: var(--orange);
        clip-path: polygon(0 0, 100% 0, calc(100% - 14px) 100%, 0 100%);
        font-family: var(--display);
        font-size: 17px;
        font-style: italic;
        font-weight: 800;
        letter-spacing: .08em;
        line-height: 1.1;
        text-transform: uppercase;
    }

    .np-ticker-track {
        display: flex;
        flex: 1;
        gap: 10px;
        min-width: 0;
        overflow-x: auto;
        padding: 2px;
        scroll-snap-type: x proximity;
        scrollbar-width: none;
    }

    .np-ticker-track::-webkit-scrollbar { display: none; }

    .np-ticker-btn {
        display: none;
        width: 38px;
        flex-shrink: 0;
        align-items: center;
        justify-content: center;
        border: 0;
        background: var(--glass);
        color: #fff;
        font-size: 18px;
        cursor: pointer;
        clip-path: var(--cut-sm);
        transition: background .2s ease;
    }

    .np-ticker-btn:hover { background: rgb(255 255 255 / 16%); }

    .np-tk {
        display: block;
        flex: 0 0 272px;
        scroll-snap-align: start;
        padding: 10px 14px 11px;
        background: linear-gradient(160deg, #161E5E, #0E1443);
        box-shadow: inset 0 0 0 1px var(--line);
        clip-path: var(--cut-sm);
        transition: transform .25s var(--ease), background .25s ease;
    }

    .np-tk:hover { transform: translateY(-3px); background: linear-gradient(160deg, #1D2776, #121A55); }
    .np-tk.is-live { box-shadow: inset 0 0 0 1px rgb(255 59 87 / 60%); }

    .np-tk-meta { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 8px; color: var(--soft); font-size: 11px; font-weight: 700; }

    .np-tk-row { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
    .np-tk-row + .np-tk-row { margin-top: 6px; }
    .np-tk-team { display: flex; min-width: 0; align-items: center; gap: 9px; font-size: 14px; font-weight: 700; white-space: nowrap; }
    .np-tk-score { font-family: var(--display); font-size: 22px; font-style: italic; font-weight: 800; line-height: 1; white-space: nowrap; }
    .np-tk-score small { margin-left: 4px; color: var(--soft); font-family: var(--text); font-size: 10px; font-style: normal; font-weight: 600; }
    .np-tk-foot { margin-top: 9px; border-top: 1px dashed var(--line); padding-top: 8px; color: var(--gold); font-size: 11px; font-weight: 700; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .np-tk.is-live .np-tk-foot { color: #FF8CA0; }
    .np-tk.is-done .np-tk-foot { color: var(--win); }

    /* =========================================================
       Hero
       ========================================================= */
    .np-hero {
        position: relative;
        isolation: isolate;
        overflow: hidden;
        background:
            radial-gradient(ellipse at 75% 0%, rgb(52 69 200 / 55%), transparent 55%),
            radial-gradient(ellipse at 0% 100%, rgb(240 100 69 / 22%), transparent 45%),
            linear-gradient(180deg, #0D1243 0%, #070A24 100%);
        clip-path: polygon(0 0, 100% 0, 100% calc(100% - 56px), 0 100%);
        padding-bottom: 56px;
    }

    .np-hero.is-live {
        background:
            radial-gradient(ellipse at 75% 0%, rgb(255 59 87 / 38%), transparent 52%),
            radial-gradient(ellipse at 0% 100%, rgb(52 69 200 / 35%), transparent 45%),
            linear-gradient(180deg, #120F3E 0%, #070A24 100%);
    }

    /* Floodlight beams */
    .np-beam {
        position: absolute;
        z-index: -1;
        top: -30%;
        width: 34%;
        height: 140%;
        background: linear-gradient(180deg, rgb(255 255 255 / 20%), transparent 70%);
        filter: blur(30px);
        opacity: .55;
        pointer-events: none;
        transform-origin: top center;
    }

    .np-beam-a { left: 8%;  transform: rotate(18deg); }
    .np-beam-b { right: 6%; transform: rotate(-16deg); opacity: .4; }

    /* Pitch floor in perspective */
    .np-floor {
        position: absolute;
        z-index: -1;
        inset: auto -25% 0;
        height: 48%;
        background-image:
            linear-gradient(rgb(255 255 255 / 10%) 1px, transparent 1px),
            linear-gradient(90deg, rgb(255 255 255 / 10%) 1px, transparent 1px);
        background-size: 60px 60px;
        mask-image: linear-gradient(to top, #000 5%, transparent 85%);
        -webkit-mask-image: linear-gradient(to top, #000 5%, transparent 85%);
        transform: perspective(540px) rotateX(64deg);
        transform-origin: bottom;
        pointer-events: none;
    }

    /* Giant background word that drifts with the pointer */
    .np-ghost {
        position: absolute;
        z-index: -1;
        right: -2%;
        bottom: 8%;
        font-size: clamp(160px, 28vw, 420px);
        color: transparent;
        -webkit-text-stroke: 2px rgb(255 255 255 / 7%);
        pointer-events: none;
        transform: translate3d(calc(var(--mx, 0) * -30px), calc(var(--my, 0) * -20px), 0);
        transition: transform .5s var(--ease);
        user-select: none;
    }

    .np-hero-grid { display: grid; align-items: center; gap: 40px; padding-block: 52px 36px; }

    .np-season { display: inline-flex; align-items: center; gap: 10px; color: var(--gold); font-family: var(--display); font-size: 18px; font-style: italic; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; }
    .np-season::before { width: 38px; height: 3px; background: var(--gold); content: ""; }

    .np-hero-title { margin-top: 16px; font-size: clamp(62px, 11vw, 148px); }
    .np-hero-title .line { display: block; animation: np-in .8s var(--ease) both; }
    .np-hero-title .line:nth-child(2) { animation-delay: .08s; }
    .np-hero-title .line:nth-child(3) { animation-delay: .16s; }

    .np-outline { color: transparent; -webkit-text-stroke: 2px #fff; }
    .np-hot { color: var(--orange); }
    .np-hero.is-live .np-hot { color: var(--live); }

    @keyframes np-in {
        from { opacity: 0; transform: translateX(-46px) skewX(-8deg); }
        to   { opacity: 1; transform: none; }
    }

    .np-hero-text { max-width: 500px; margin-top: 22px; color: var(--soft); font-size: 17px; font-weight: 500; line-height: 1.75; }
    .np-hero-actions { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 30px; }

    /* Floating cricket ball */
    .np-stage { position: relative; }

    .np-ball {
        position: absolute;
        z-index: 8;
        top: -46px;
        left: -44px;
        width: 108px;
        height: 108px;
        border-radius: 50%;
        background: radial-gradient(circle at 32% 26%, #FFB09E 0, #E2452B 38%, #8E1A0A 78%, #4F0B04 100%);
        box-shadow: inset -12px -14px 26px rgb(0 0 0 / 40%), 0 34px 40px rgb(0 0 0 / 45%);
        transform: translateZ(110px) translate3d(calc(var(--mx, 0) * 22px), calc(var(--my, 0) * 22px), 0);
        animation: np-float 6s ease-in-out infinite;
        pointer-events: none;
    }

    .np-ball::before, .np-ball::after {
        position: absolute; top: 6%; bottom: 6%; width: 22%;
        border: 0 dashed rgb(255 255 255 / 80%);
        content: "";
    }

    .np-ball::before { left: 33%;  border-left-width: 2px;  border-radius: 50%; }
    .np-ball::after  { right: 33%; border-right-width: 2px; border-radius: 50%; }

    @keyframes np-float { 0%, 100% { margin-top: 0; } 50% { margin-top: -14px; } }

    /* Hero panel */
    .np-panel-top { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 22px 24px 8px; }
    .np-panel-kicker { color: var(--soft); font-size: 12px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; }
    .np-panel-title { margin-top: 4px; font-size: 30px; }

    .np-live-rows { margin: 8px 16px 0; }

    .np-live-row {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        align-items: center;
        gap: 14px;
        padding: 14px 14px;
        background: rgb(255 255 255 / 4%);
        clip-path: var(--cut-sm);
    }

    .np-live-row + .np-live-row { margin-top: 8px; }
    .np-live-row.is-bat { background: linear-gradient(90deg, rgb(255 59 87 / 22%), rgb(255 255 255 / 5%)); box-shadow: inset 4px 0 0 var(--live); }
    .np-live-team { display: flex; min-width: 0; align-items: center; gap: 14px; }
    .np-live-name { overflow-wrap: anywhere; font-size: 17px; font-weight: 700; line-height: 1.25; }
    .np-bat-tag { display: inline-flex; align-items: center; gap: 6px; margin-top: 4px; color: #FF8CA0; font-size: 11px; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; }
    .np-big-score { font-size: clamp(38px, 5vw, 56px); text-align: right; white-space: nowrap; }
    .np-overs { margin-top: 2px; color: var(--soft); font-size: 12px; font-weight: 600; text-align: right; }

    .np-chase {
        margin: 14px 16px 0;
        padding: 12px 14px;
        background: rgb(255 197 61 / 12%);
        color: var(--gold);
        font-size: 14px;
        font-weight: 700;
        text-align: center;
        clip-path: var(--cut-sm);
    }

    .np-panel-foot { padding: 16px 16px 22px; }

    .np-also { display: grid; gap: 6px; margin: 0 16px 18px; }
    .np-also a { display: flex; justify-content: space-between; gap: 10px; padding: 10px 12px; background: var(--glass); font-size: 13px; font-weight: 700; clip-path: var(--cut-sm); transition: background .2s ease; }
    .np-also a:hover { background: rgb(255 255 255 / 14%); }

    /* Upcoming layout */
    .np-vs-row { display: grid; grid-template-columns: 1fr auto 1fr; align-items: center; gap: 10px; margin: 18px 18px 0; }
    .np-vs-team { min-width: 0; text-align: center; }
    .np-vs-team .np-logo, .np-vs-team .np-logo-fb { margin-inline: auto; }
    .np-vs-name { margin-top: 12px; overflow-wrap: anywhere; font-size: 15px; font-weight: 700; line-height: 1.3; }
    .np-vs-cap { margin-top: 3px; color: var(--soft); font-size: 11px; font-weight: 600; letter-spacing: .1em; text-transform: uppercase; }
    .np-vs { font-size: 44px; color: var(--orange); }
    .np-when { margin: 20px 18px 0; padding: 14px; background: var(--glass); text-align: center; clip-path: var(--cut-sm); }
    .np-when strong { display: block; font-size: 17px; font-weight: 700; }
    .np-when span { display: block; margin-top: 4px; color: var(--soft); font-size: 13px; font-weight: 600; }
    .np-count { margin-top: 8px; color: var(--gold); font-family: var(--display); font-size: 22px; font-style: italic; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; }

    /* =========================================================
       Moving band
       ========================================================= */
    .np-band {
        position: relative;
        z-index: 3;
        overflow: hidden;
        margin-block: -22px 6px;
        padding: 12px 0;
        background: var(--orange);
        transform: rotate(-1.6deg);
        box-shadow: 0 14px 30px rgb(0 0 0 / 35%);
    }

    .np-band-track { display: flex; width: max-content; animation: np-marquee 28s linear infinite; }

    .np-band-track span {
        flex-shrink: 0;
        padding-inline: 22px;
        font-family: var(--display);
        font-size: 26px;
        font-style: italic;
        font-weight: 800;
        letter-spacing: .06em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .np-band-track span:nth-child(even) { color: #1A0F0B; }

    @keyframes np-marquee { to { transform: translateX(-50%); } }

    /* =========================================================
       Result cards
       ========================================================= */
    .np-res-grid { display: grid; gap: 22px; }

    .np-res-head { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 22px 22px 12px; }
    .np-res-meta strong { display: block; font-size: 15px; font-weight: 700; }
    .np-res-meta span { display: block; margin-top: 2px; color: var(--soft); font-size: 12px; font-weight: 600; }

    .np-res-rows { margin: 0 14px; }

    .np-res-row { display: grid; grid-template-columns: minmax(0, 1fr) auto; align-items: center; gap: 12px; padding: 13px 12px; clip-path: var(--cut-sm); background: rgb(255 255 255 / 4%); }
    .np-res-row + .np-res-row { margin-top: 7px; }
    .np-res-row.is-win { background: linear-gradient(90deg, rgb(43 217 160 / 20%), rgb(255 255 255 / 4%)); box-shadow: inset 4px 0 0 var(--win); }
    .np-res-team { display: flex; min-width: 0; align-items: center; gap: 12px; font-size: 15px; font-weight: 700; line-height: 1.25; overflow-wrap: anywhere; }
    .np-win-tag { display: block; margin-top: 3px; color: var(--win); font-size: 10px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; }
    .np-res-score { font-size: 32px; text-align: right; white-space: nowrap; }
    .np-res-score.is-empty { color: rgb(255 255 255 / 30%); font-size: 24px; }

    .np-res-result { margin: 14px 14px 0; padding: 11px 14px; background: var(--win); color: #04281C; font-family: var(--display); font-size: 20px; font-style: italic; font-weight: 800; letter-spacing: .03em; text-transform: uppercase; clip-path: var(--cut-sm); }
    .np-res-note { margin: 14px 14px 0; padding: 11px 14px; background: var(--glass); color: var(--soft); font-size: 13px; line-height: 1.6; clip-path: var(--cut-sm); }
    .np-res-foot { padding: 16px 14px 20px; }

    /* =========================================================
       Teams
       ========================================================= */
    .np-team-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; }

    .np-team { display: block; min-height: 270px; }

    .np-team .np-shape { background: linear-gradient(170deg, #20297A 0%, #121A57 60%, #0B1034 100%); }

    .np-team-letter {
        position: absolute;
        right: -6px;
        bottom: -22px;
        font-size: 170px;
        color: transparent;
        -webkit-text-stroke: 2px rgb(255 255 255 / 10%);
        line-height: 1;
        pointer-events: none;
    }

    .np-team-body { display: flex; min-height: 270px; flex-direction: column; justify-content: space-between; padding: 22px 20px 20px; }
    .np-team-name { margin-top: 18px; font-size: 28px; line-height: 1; }
    .np-team-short { margin-top: 6px; color: var(--gold); font-size: 13px; font-weight: 700; letter-spacing: .14em; text-transform: uppercase; }
    .np-team-link { display: inline-flex; align-items: center; gap: 8px; color: var(--soft); font-family: var(--display); font-size: 17px; font-style: italic; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; transition: color .2s ease, gap .2s var(--ease); }
    .np-team:hover .np-team-link { gap: 14px; color: #fff; }

    /* =========================================================
       News
       ========================================================= */
    .np-news-grid { display: grid; gap: 18px; }

    .np-feature { position: relative; display: block; min-height: 420px; overflow: hidden; clip-path: var(--cut); background: var(--navy); }

    .np-feature img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; transition: transform .8s var(--ease); }
    .np-feature:hover img { transform: scale(1.06); }

    .np-feature-ph { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; background: radial-gradient(circle at 80% 10%, rgb(240 100 69 / 35%), transparent 40%), linear-gradient(135deg, #131A52, #303C9A); font-size: 150px; color: rgb(255 255 255 / 12%); }

    .np-feature-shade { position: absolute; inset: 0; background: linear-gradient(180deg, transparent 25%, rgb(5 7 26 / 92%) 100%); }
    .np-feature-copy { position: absolute; inset: auto 0 0; padding: 26px 26px 28px; }
    .np-feature-title { margin-top: 12px; font-size: clamp(32px, 4vw, 50px); }
    .np-feature-text { display: -webkit-box; max-width: 60ch; margin-top: 12px; overflow: hidden; color: rgb(255 255 255 / 75%); font-size: 15px; line-height: 1.7; -webkit-box-orient: vertical; -webkit-line-clamp: 2; }

    .np-news-list { display: grid; gap: 12px; align-content: start; }

    .np-news-item { display: grid; grid-template-columns: 112px minmax(0, 1fr); gap: 14px; padding: 12px; background: linear-gradient(160deg, #161E5E, #0E1443); box-shadow: inset 0 0 0 1px var(--line); clip-path: var(--cut-sm); transition: background .25s ease, transform .25s var(--ease); }
    .np-news-item:hover { background: linear-gradient(160deg, #1D2776, #121A55); transform: translateX(4px); }
    .np-news-thumb { position: relative; height: 96px; overflow: hidden; background: linear-gradient(135deg, #131A52, #303C9A); clip-path: var(--cut-sm); }
    .np-news-thumb img { width: 100%; height: 100%; object-fit: cover; }
    .np-news-thumb span { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; font-size: 34px; color: rgb(255 255 255 / 25%); }
    .np-news-item h3 { display: -webkit-box; overflow: hidden; font-size: 24px; line-height: 1.02; -webkit-box-orient: vertical; -webkit-line-clamp: 3; }
    .np-date { color: var(--gold); font-size: 12px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }

    /* =========================================================
       Empty states
       ========================================================= */
    .np-empty { padding: 56px 24px; background: var(--glass); box-shadow: inset 0 0 0 1px var(--line); clip-path: var(--cut); text-align: center; }
    .np-empty h3 { font-size: 36px; }
    .np-empty p { max-width: 440px; margin: 12px auto 0; color: var(--soft); font-size: 15px; line-height: 1.7; }
    .np-empty .np-btn { margin-top: 22px; }

    /* =========================================================
       CTA
       ========================================================= */
    .np-cta {
        position: relative;
        overflow: hidden;
        margin-top: 30px;
        padding-block: 96px 70px;
        background:
            radial-gradient(ellipse at 90% 0%, rgb(240 100 69 / 35%), transparent 45%),
            linear-gradient(120deg, #1A2266, #0D1243);
        clip-path: polygon(0 56px, 100% 0, 100% 100%, 0 100%);
    }

    .np-cta-in { display: flex; flex-direction: column; align-items: start; justify-content: space-between; gap: 26px; }
    .np-cta h2 { font-size: clamp(52px, 8vw, 104px); }

    /* =========================================================
       Responsive
       ========================================================= */
    @media (min-width: 640px) {
        .np-wrap { padding-inline: 28px; }
        .np-team-grid { grid-template-columns: repeat(3, 1fr); }
        .np-ticker-btn { display: flex; }
    }

    @media (min-width: 768px) {
        .np-ticker-label { display: flex; }
        .np-res-grid { grid-template-columns: repeat(2, 1fr); }
        .np-hero-grid { grid-template-columns: minmax(0, 1.1fr) minmax(380px, .9fr); gap: 36px; }
        .np-cta-in { flex-direction: row; align-items: center; }
    }

    @media (min-width: 1024px) {
        .np-wrap { padding-inline: 36px; }
        .np-hero-grid { padding-block: 84px 56px; gap: 64px; }
        .np-team-grid { grid-template-columns: repeat(4, 1fr); }
        .np-news-grid { grid-template-columns: 1.55fr 1fr; }
    }

    @media (min-width: 1200px) {
        .np-res-grid { grid-template-columns: repeat(3, 1fr); }
    }

    @media (max-width: 639px) {
        .np-section { padding-block: 54px; }
        .np-head { align-items: start; flex-direction: column; gap: 12px; }
        .np-ball { top: -34px; left: -10px; width: 72px; height: 72px; }
        .np-feature { min-height: 340px; }
        .np-news-item { grid-template-columns: 92px minmax(0, 1fr); }
        .np-band-track span { font-size: 21px; }
    }

    @media (hover: none), (prefers-reduced-motion: reduce) {
        [data-tilt] { transform: none !important; }
        [data-tilt]::after { display: none; }
    }

    @media (prefers-reduced-motion: reduce) {
        .np *, .np *::before, .np *::after {
            animation-duration: .01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: .01ms !important;
        }
    }
</style>

<div class="np">

    {{-- =====================================================
         MATCH TICKER (live -> next -> recent results)
         ===================================================== --}}
    @if($ticker->isNotEmpty())
        <div class="np-ticker" aria-label="Match ticker">
            <div class="np-wrap">
                <div class="np-ticker-in">
                    <div class="np-ticker-label">Match<br>Centre</div>

                    <button type="button" class="np-ticker-btn" data-ticker="-1" aria-label="Scroll matches left">&#8249;</button>

                    <div class="np-ticker-track" id="np-ticker-track">
                        @foreach($ticker as $item)
                            @php
                                $f  = $item['f'];
                                $t  = $item['type'];
                                $ts = $t === 'up' ? null : $scoreFor($f);
                                $hn = $shortName(data_get($f, 'homeTeam'), 'Home');
                                $an = $shortName(data_get($f, 'awayTeam'), 'Away');
                                $when = $fmtDate(data_get($f, 'scheduled_at'), 'D, d M · h:i A');
                            @endphp

                            <a href="{{ route('fixtures.show', $f) }}" class="np-tk is-{{ $t }}">
                                <div class="np-tk-meta">
                                    @if($t === 'live')
                                        <span class="np-tag np-tag-live"><span class="np-pulse"></span>Live</span>
                                    @elseif($t === 'up')
                                        <span class="np-tag np-tag-up">Up next</span>
                                    @else
                                        <span class="np-tag np-tag-done">Result</span>
                                    @endif

                                    <span>{{ data_get($f, 'match_number') ? 'Match ' . data_get($f, 'match_number') : $matchDate($f) }}</span>
                                </div>

                                <div class="np-tk-row">
                                    <span class="np-tk-team">{{ $logo(data_get($f, 'homeTeam'), 26) }}{{ $hn }}</span>
                                    @if($ts && $ts['homeScore'] !== null)
                                        <span class="np-tk-score np-num">{{ $ts['homeScore'] }}@if($ts['homeOvers'] !== null)<small>({{ $ts['homeOvers'] }})</small>@endif</span>
                                    @endif
                                </div>

                                <div class="np-tk-row">
                                    <span class="np-tk-team">{{ $logo(data_get($f, 'awayTeam'), 26) }}{{ $an }}</span>
                                    @if($ts && $ts['awayScore'] !== null)
                                        <span class="np-tk-score np-num">{{ $ts['awayScore'] }}@if($ts['awayOvers'] !== null)<small>({{ $ts['awayOvers'] }})</small>@endif</span>
                                    @endif
                                </div>

                                <div class="np-tk-foot">
                                    @if($t === 'live')
                                        {{ $ts['chase'] ?? 'Match in progress' }}
                                    @elseif($t === 'up')
                                        {{ $when ?? 'Schedule to be announced' }}
                                    @else
                                        {{ $ts['winnerName'] ? $ts['winnerName'] . ' won' : 'Final score recorded' }}
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>

                    <button type="button" class="np-ticker-btn" data-ticker="1" aria-label="Scroll matches right">&#8250;</button>
                </div>
            </div>
        </div>
    @endif

    {{-- =====================================================
         HERO
         Live -> live panel. Not live / finished -> upcoming.
         ===================================================== --}}
    <section class="np-hero {{ $hasLive ? 'is-live' : '' }}" id="hero">
        <span class="np-beam np-beam-a" aria-hidden="true"></span>
        <span class="np-beam np-beam-b" aria-hidden="true"></span>
        <span class="np-floor" aria-hidden="true"></span>
        <span class="np-ghost np-display" aria-hidden="true">{{ $hasLive ? 'Live' : 'NPL' }}</span>

        <div class="np-wrap">
            <div class="np-hero-grid">

                {{-- Copy --}}
                <div>
                    <p class="np-season">
                        Nepal Premier League
                        @if(isset($season) && $season) &nbsp;&bull;&nbsp; {{ $season->name }} @endif
                    </p>

                    @if($hasLive)
                        <h1 class="np-display np-hero-title">
                            <span class="line">Match</span>
                            <span class="line np-hot">Live</span>
                            <span class="line np-outline">Now</span>
                        </h1>

                        <p class="np-hero-text">
                            {{ data_get($liveMatch, 'homeTeam.name', 'Home') }} are playing
                            {{ data_get($liveMatch, 'awayTeam.name', 'Away') }} right now.
                            The score updates as the scorer records each ball.
                        </p>

                        <div class="np-hero-actions">
                            <a href="{{ route('fixtures.show', $liveMatch) }}" class="np-btn np-btn-live">
                                <span class="np-pulse"></span> Open live scorecard
                            </a>
                            <a href="{{ route('fixtures.index') }}" class="np-btn np-btn-ghost">All fixtures</a>
                        </div>
                    @else
                        <h1 class="np-display np-hero-title">
                            <span class="line">Nepal</span>
                            <span class="line np-hot">Premier</span>
                            <span class="line np-outline">League</span>
                        </h1>

                        <p class="np-hero-text">
                            Every fixture, every scoreboard and every team in one place.
                            Check what is next, then catch up on the results.
                        </p>

                        <div class="np-hero-actions">
                            <a href="{{ route('fixtures.index') }}" class="np-btn np-btn-orange">Explore fixtures &rarr;</a>
                            <a href="#recent-matches" class="np-btn np-btn-ghost">Recent scoreboards</a>
                        </div>
                    @endif
                </div>

                {{-- 3D panel --}}
                <div class="np-stage scene">
                    <div class="np-ball" aria-hidden="true"></div>

                    @if($hasLive)
                        @php $ls = $scoreFor($liveMatch); @endphp

                        <div class="np-tcard" data-tilt data-tilt-max="8" aria-live="polite">
                            <div class="np-shape"></div>

                            <div class="np-body">
                                <div class="np-panel-top">
                                    <div class="d1">
                                        <p class="np-panel-kicker">
                                            {{ data_get($liveMatch, 'season.name', 'Nepal Premier League') }}
                                            @if(data_get($liveMatch, 'match_number')) &bull; Match {{ data_get($liveMatch, 'match_number') }} @endif
                                        </p>
                                        <h2 class="np-display np-panel-title">Live scorecard</h2>
                                    </div>

                                    <span class="np-tag np-tag-live d3"><span class="np-pulse"></span>Live</span>
                                </div>

                                <div class="np-live-rows d2">
                                    <div class="np-live-row {{ $ls['homeBats'] ? 'is-bat' : '' }}">
                                        <div class="np-live-team">
                                            {{ $logo(data_get($liveMatch, 'homeTeam'), 52) }}
                                            <div class="min-w-0">
                                                <p class="np-live-name">{{ $ls['homeName'] }}</p>
                                                @if($ls['homeBats'])<span class="np-bat-tag"><span class="np-pulse"></span>Batting</span>@endif
                                            </div>
                                        </div>

                                        <div>
                                            @if($ls['homeScore'] !== null)
                                                <p class="np-display np-big-score np-num">{{ $ls['homeScore'] }}</p>
                                                <p class="np-overs">{{ $ls['homeOvers'] !== null ? $ls['homeOvers'] . ' overs' : 'Score recorded' }}</p>
                                            @else
                                                <p class="np-display np-big-score" style="color:rgb(255 255 255 / 25%)">&mdash;</p>
                                                <p class="np-overs">Yet to bat</p>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="np-live-row {{ $ls['awayBats'] ? 'is-bat' : '' }}">
                                        <div class="np-live-team">
                                            {{ $logo(data_get($liveMatch, 'awayTeam'), 52) }}
                                            <div class="min-w-0">
                                                <p class="np-live-name">{{ $ls['awayName'] }}</p>
                                                @if($ls['awayBats'])<span class="np-bat-tag"><span class="np-pulse"></span>Batting</span>@endif
                                            </div>
                                        </div>

                                        <div>
                                            @if($ls['awayScore'] !== null)
                                                <p class="np-display np-big-score np-num">{{ $ls['awayScore'] }}</p>
                                                <p class="np-overs">{{ $ls['awayOvers'] !== null ? $ls['awayOvers'] . ' overs' : 'Score recorded' }}</p>
                                            @else
                                                <p class="np-display np-big-score" style="color:rgb(255 255 255 / 25%)">&mdash;</p>
                                                <p class="np-overs">Yet to bat</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <p class="np-chase d1">{{ $ls['chase'] ?? 'The scorer has not recorded a ball yet.' }}</p>

                                <div class="np-panel-foot d2">
                                    <a href="{{ route('fixtures.show', $liveMatch) }}" class="np-btn np-btn-live np-btn-block">Open live scorecard &rarr;</a>
                                </div>

                                @if($otherLive->isNotEmpty())
                                    <div class="np-also d1">
                                        <p class="np-panel-kicker">Also live</p>
                                        @foreach($otherLive as $other)
                                            <a href="{{ route('fixtures.show', $other) }}">
                                                <span>{{ data_get($other, 'homeTeam.name', 'Home') }} vs {{ data_get($other, 'awayTeam.name', 'Away') }}</span>
                                                <span aria-hidden="true">&rarr;</span>
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>

                    @else
                        <div class="np-tcard" data-tilt data-tilt-max="8">
                            <div class="np-shape"></div>

                            <div class="np-body">
                                <div class="np-panel-top">
                                    <div class="d1">
                                        <p class="np-panel-kicker">Coming up next</p>
                                        <h2 class="np-display np-panel-title">Match centre</h2>
                                    </div>

                                    <span class="np-tag np-tag-up d3">Upcoming</span>
                                </div>

                                @if($nextMatch)
                                    <p class="np-panel-kicker d1" style="padding:6px 24px 0">
                                        {{ $nextMatch->season->name ?? 'Nepal Premier League' }}
                                        @if($nextMatch->match_number) &bull; Match {{ $nextMatch->match_number }} @endif
                                    </p>

                                    <div class="np-vs-row d3">
                                        <div class="np-vs-team">
                                            {{ $logo($nextMatch->homeTeam, 84) }}
                                            <p class="np-vs-name">{{ $nextMatch->homeTeam->name ?? 'Home Team' }}</p>
                                            <p class="np-vs-cap">Home</p>
                                        </div>

                                        <span class="np-display np-vs">VS</span>

                                        <div class="np-vs-team">
                                            {{ $logo($nextMatch->awayTeam, 84) }}
                                            <p class="np-vs-name">{{ $nextMatch->awayTeam->name ?? 'Away Team' }}</p>
                                            <p class="np-vs-cap">Away</p>
                                        </div>
                                    </div>

                                    <div class="np-when d2">
                                        @if($nextMatch->scheduled_at)
                                            <strong>{{ $fmtDate($nextMatch->scheduled_at, 'D, d M Y') }}</strong>
                                            <span>{{ $fmtDate($nextMatch->scheduled_at, 'h:i A') }}</span>
                                            <p class="np-count" data-countdown="{{ \Illuminate\Support\Carbon::parse($nextMatch->scheduled_at)->toIso8601String() }}"></p>
                                        @else
                                            <strong>Schedule to be announced</strong>
                                        @endif
                                    </div>

                                    <div class="np-panel-foot d2">
                                        <a href="{{ route('fixtures.show', $nextMatch) }}" class="np-btn np-btn-orange np-btn-block">View match details &rarr;</a>
                                    </div>
                                @else
                                    <div style="padding:34px 24px 30px;text-align:center">
                                        <h3 class="np-display" style="font-size:34px">No upcoming match</h3>
                                        <p style="margin:10px auto 0;max-width:320px;color:var(--soft);font-size:14px;line-height:1.7">
                                            No fixture is scheduled right now. Open the fixtures page to see the full schedule.
                                        </p>
                                        <a href="{{ route('fixtures.index') }}" class="np-btn np-btn-orange" style="margin-top:20px">Browse fixtures &rarr;</a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </section>

    {{-- MOVING BAND --}}
    <div class="np-band" aria-hidden="true">
        <div class="np-band-track">
            @for($i = 0; $i < 2; $i++)
                @foreach(['Every match', '&#9670;', 'Every moment', '&#9670;', 'Nepal Premier League', '&#9670;', 'Live scores', '&#9670;', 'Fixtures', '&#9670;', 'Results', '&#9670;'] as $word)
                    <span>{!! $word !!}</span>
                @endforeach
            @endfor
        </div>
    </div>

    {{-- =====================================================
         RECENT MATCHES
         ===================================================== --}}
    <section id="recent-matches" class="np-section" style="scroll-margin-top:90px">
        <div class="np-wrap">
            <div class="np-head">
                <div>
                    <h2 class="np-display np-title">Recent results</h2>
                    <p class="np-sub">Final scores from the manual scorecard.</p>
                </div>

                <a href="{{ route('fixtures.index') }}" class="np-more">All fixtures <span aria-hidden="true">&rarr;</span></a>
            </div>

            @if($recentResults->isNotEmpty())
                <div class="np-res-grid scene">
                    @foreach($recentResults as $result)
                        @php
                            $rs       = $scoreFor($result);
                            $hasScore = $rs['homeScore'] !== null || $rs['awayScore'] !== null;
                            $homeWon  = $rs['winnerName'] && $rs['winnerName'] === $rs['homeName'];
                            $awayWon  = $rs['winnerName'] && $rs['winnerName'] === $rs['awayName'];
                        @endphp

                        <article class="np-tcard" data-tilt data-tilt-max="6">
                            <div class="np-shape"></div>

                            <div class="np-body">
                                <div class="np-res-head">
                                    <div class="np-res-meta d1">
                                        <strong>{{ $result->match_number ? 'Match ' . $result->match_number : 'Match scorecard' }}</strong>
                                        <span>{{ $result->season->name ?? 'Nepal Premier League' }} &bull; {{ $matchDate($result) }}</span>
                                    </div>

                                    <span class="np-tag np-tag-done d2">Final</span>
                                </div>

                                <div class="np-res-rows d2">
                                    <div class="np-res-row {{ $homeWon ? 'is-win' : '' }}">
                                        <div class="np-res-team">
                                            {{ $logo(data_get($result, 'homeTeam'), 40) }}
                                            <div class="min-w-0">
                                                {{ $rs['homeName'] }}
                                                @if($homeWon)<span class="np-win-tag">Winner</span>@endif
                                            </div>
                                        </div>

                                        @if($rs['homeScore'] !== null)
                                            <div>
                                                <p class="np-display np-res-score np-num">{{ $rs['homeScore'] }}</p>
                                                <p class="np-overs">{{ $rs['homeOvers'] !== null ? $rs['homeOvers'] . ' ov' : 'Recorded' }}</p>
                                            </div>
                                        @else
                                            <p class="np-display np-res-score is-empty">&mdash;</p>
                                        @endif
                                    </div>

                                    <div class="np-res-row {{ $awayWon ? 'is-win' : '' }}">
                                        <div class="np-res-team">
                                            {{ $logo(data_get($result, 'awayTeam'), 40) }}
                                            <div class="min-w-0">
                                                {{ $rs['awayName'] }}
                                                @if($awayWon)<span class="np-win-tag">Winner</span>@endif
                                            </div>
                                        </div>

                                        @if($rs['awayScore'] !== null)
                                            <div>
                                                <p class="np-display np-res-score np-num">{{ $rs['awayScore'] }}</p>
                                                <p class="np-overs">{{ $rs['awayOvers'] !== null ? $rs['awayOvers'] . ' ov' : 'Recorded' }}</p>
                                            </div>
                                        @else
                                            <p class="np-display np-res-score is-empty">&mdash;</p>
                                        @endif
                                    </div>
                                </div>

                                @if($rs['winnerName'])
                                    <p class="np-res-result d1">{{ $rs['winnerName'] }} won</p>
                                @elseif(!$hasScore)
                                    <p class="np-res-note d1">The result is in, but the detailed scorecard has not been recorded yet.</p>
                                @else
                                    <p class="np-res-note d1">Final score recorded. Open the match page for the full result.</p>
                                @endif

                                <div class="np-res-foot d2">
                                    <a href="{{ route('fixtures.show', $result) }}" class="np-btn np-btn-ghost np-btn-block">Full scorecard &rarr;</a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="np-empty">
                    <h3 class="np-display">No results yet</h3>
                    <p>Completed matches appear here after the result and scorecard are entered.</p>
                    <a href="{{ route('fixtures.index') }}" class="np-btn np-btn-orange">Browse fixtures &rarr;</a>
                </div>
            @endif
        </div>
    </section>

    {{-- =====================================================
         TEAMS
         ===================================================== --}}
    <section id="teams" class="np-section np-section-alt" style="scroll-margin-top:90px">
        <div class="np-wrap">
            <div class="np-head">
                <div>
                    <h2 class="np-display np-title">The teams</h2>
                    <p class="np-sub">Every side competing in the Nepal Premier League.</p>
                </div>

                <a href="{{ route('teams.index') }}" class="np-more">All teams <span aria-hidden="true">&rarr;</span></a>
            </div>

            @if($teams->isNotEmpty())
                <div class="np-team-grid scene">
                    @foreach($teams as $team)
                        <a href="{{ route('teams.show', $team) }}" class="np-tcard np-team" data-tilt data-tilt-max="10">
                            <div class="np-shape"></div>
                            <span class="np-team-letter np-display" aria-hidden="true">{{ $initial($team->name) }}</span>

                            <div class="np-body np-team-body">
                                <div class="d3">{{ $logo($team, 76) }}</div>

                                <div class="d2">
                                    <h3 class="np-display np-team-name">{{ $team->name }}</h3>
                                    @if(!empty($team->short_name))
                                        <p class="np-team-short">{{ $team->short_name }}</p>
                                    @endif
                                </div>

                                <span class="np-team-link d1">Team profile <span aria-hidden="true">&rarr;</span></span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="np-empty">
                    <h3 class="np-display">Teams coming soon</h3>
                    <p>Teams appear here once they are added to the league.</p>
                </div>
            @endif
        </div>
    </section>

    {{-- =====================================================
         NEWS
         ===================================================== --}}
    <section id="news" class="np-section" style="scroll-margin-top:90px">
        <div class="np-wrap">
            <div class="np-head">
                <div>
                    <h2 class="np-display np-title">Latest news</h2>
                    <p class="np-sub">Stories, match reports and updates from the NPL.</p>
                </div>

                <a href="{{ route('news.index') }}" class="np-more">All news <span aria-hidden="true">&rarr;</span></a>
            </div>

            @if($latestNews->isNotEmpty())
                @php
                    $featured = $latestNews->first();
                    $rest     = $latestNews->slice(1, 4)->values();
                    $fImage   = $imageUrl($featured->image ?? $featured->featured_image ?? null);
                    $fDate    = $fmtDate($featured->published_at ?? $featured->created_at ?? null);
                    $fText    = $featured->excerpt ?? $featured->summary
                                ?? \Illuminate\Support\Str::limit(strip_tags((string) ($featured->content ?? '')), 160);
                @endphp

                <div class="np-news-grid" @if($rest->isEmpty()) style="grid-template-columns:1fr" @endif>
                    <a href="{{ route('news.show', $featured->slug) }}" class="np-feature">
                        @if($fImage)
                            <img src="{{ $fImage }}" alt="{{ $featured->title }}" loading="lazy">
                        @else
                            <span class="np-feature-ph np-display" aria-hidden="true">NPL</span>
                        @endif

                        <span class="np-feature-shade"></span>

                        <span class="np-feature-copy">
                            <span class="np-tag np-tag-up">Top story</span>
                            <h3 class="np-display np-feature-title">{{ $featured->title }}</h3>
                            @if($fText)<span class="np-feature-text">{{ $fText }}</span>@endif
                            @if($fDate)<span class="np-date" style="display:block;margin-top:12px">{{ $fDate }}</span>@endif
                        </span>
                    </a>

                    @if($rest->isNotEmpty())
                        <div class="np-news-list">
                            @foreach($rest as $article)
                                @php
                                    $aImage = $imageUrl($article->image ?? $article->featured_image ?? null);
                                    $aDate  = $fmtDate($article->published_at ?? $article->created_at ?? null);
                                @endphp

                                <a href="{{ route('news.show', $article->slug) }}" class="np-news-item">
                                    <span class="np-news-thumb">
                                        @if($aImage)
                                            <img src="{{ $aImage }}" alt="" loading="lazy">
                                        @else
                                            <span class="np-display" aria-hidden="true">NPL</span>
                                        @endif
                                    </span>

                                    <span>
                                        @if($aDate)<span class="np-date">{{ $aDate }}</span>@endif
                                        <h3 class="np-display" style="margin-top:6px">{{ $article->title }}</h3>
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            @else
                <div class="np-empty">
                    <h3 class="np-display">No news yet</h3>
                    <p>Published articles appear here. Check back for league coverage.</p>
                </div>
            @endif
        </div>
    </section>

    {{-- FINAL CTA --}}
    <section class="np-cta">
        <div class="np-wrap">
            <div class="np-cta-in">
                <h2 class="np-display">Never<br><span class="np-outline">miss</span> a ball</h2>

                <div>
                    <p style="max-width:380px;margin-bottom:22px;color:var(--soft);font-size:16px;line-height:1.7">
                        Check the schedule, follow your team and open every scoreboard in one place.
                    </p>
                    <a href="{{ route('fixtures.index') }}" class="np-btn np-btn-orange">Explore all fixtures &rarr;</a>
                </div>
            </div>
        </div>
    </section>

</div>

<script>
(function () {
    'use strict';

    var root = document.querySelector('.np');
    if (!root) return;

    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var canHover     = window.matchMedia('(hover: hover) and (pointer: fine)').matches;

    /* ---------- 3D tilt + hero parallax ---------- */
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

        var hero = document.getElementById('hero');

        if (hero) {
            hero.addEventListener('pointermove', function (e) {
                var r = hero.getBoundingClientRect();
                hero.style.setProperty('--mx', (((e.clientX - r.left) / r.width)  - .5).toFixed(3));
                hero.style.setProperty('--my', (((e.clientY - r.top)  / r.height) - .5).toFixed(3));
            });
        }
    }

    /* ---------- Ticker arrows ---------- */
    var track = document.getElementById('np-ticker-track');

    root.querySelectorAll('[data-ticker]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (!track) return;
            track.scrollBy({
                left: parseInt(btn.getAttribute('data-ticker'), 10) * 290,
                behavior: reduceMotion ? 'auto' : 'smooth'
            });
        });
    });

    /* ---------- Countdown to the next match ---------- */
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

            countdown.textContent = 'Starts in ' + (d ? d + 'd ' : '') + h + 'h ' + m + 'm';
        };

        tick();
        setInterval(tick, 30000);
    }

    @if($hasLive)
    /* ---------- Live refresh ----------
       Reload every 30s while a match is live. When it is marked
       completed, the next load shows the upcoming match again. */
    setInterval(function () {
        if (!document.hidden) {
            window.location.reload();
        }
    }, 30000);
    @endif
})();
</script>

@endsection