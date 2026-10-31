
@extends('layouts.app')

@php
    $liveMatches = collect($liveMatches ?? []);
    $teams = collect($teams ?? []);
    $recentResults = collect($recentResults ?? []);
    $latestNews = collect($latestNews ?? []);
    $liveScoreByFixture = collect($liveScoreByFixture ?? []);

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

    $initial = function ($name) {
        return strtoupper(mb_substr(trim((string) $name) ?: 'N', 0, 1));
    };

    $scoreOvers = function ($innings) {
        if (!$innings) {
            return null;
        }

        $legalBalls = data_get($innings, 'legal_balls');

        if ($legalBalls !== null) {
            $legalBalls = max(0, (int) $legalBalls);

            return intdiv($legalBalls, 6) . '.' . ($legalBalls % 6);
        }

        return data_get($innings, 'overs');
    };

    $formatScore = function ($innings) {
        if (!$innings) {
            return null;
        }

        $runs = data_get($innings, 'total_runs');
        $wickets = data_get($innings, 'wickets');

        if ($runs === null) {
            $runs = data_get($innings, 'runs');
        }

        if ($runs === null) {
            return null;
        }

        return number_format((int) $runs) . '/' . (int) ($wickets ?? 0);
    };

    $matchDate = function ($match) {
        $date = data_get($match, 'scheduled_at')
            ?? data_get($match, 'match_date');

        if (!$date) {
            return 'Date to be announced';
        }

        try {
            return \Illuminate\Support\Carbon::parse($date)->format('d M Y');
        } catch (\Throwable $e) {
            return 'Date to be announced';
        }
    };
@endphp

@section('title', 'Home')

@section('meta_description', 'Follow Nepal Premier League fixtures, manually updated cricket scoreboards, teams, match results and the latest NPL news.')

@section('content')

<style>
    .home-page {
        --navy: #202766;
        --navy-dark: #171D50;
        --orange: #F06445;
        --bg: #F6F8FB;
        --border: #E5E9F2;
        --muted: #64748B;
        color: #1E293B;
        background: var(--bg);
    }

    .home-page * {
        box-sizing: border-box;
    }

    .home-container {
        width: 100%;
        max-width: 1280px;
        margin-inline: auto;
        padding-inline: 20px;
    }

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

    .home-section {
        padding-block: 64px;
    }

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

    .home-text-link:hover {
        color: var(--orange);
    }

    .home-card {
        border: 1px solid var(--border);
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 3px 12px rgb(15 23 42 / 3%);
        transition: border-color .2s ease, box-shadow .2s ease,
            transform .2s ease;
    }

    .home-card:hover {
        border-color: #D8DDF0;
        box-shadow: 0 14px 35px rgb(32 39 102 / 8%);
    }

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
        transition: .2s ease;
    }

    .home-button:hover {
        transform: translateY(-1px);
    }

    .home-button-primary {
        background: var(--orange);
        color: #fff;
    }

    .home-button-primary:hover {
        background: #D94D31;
        color: #fff;
    }

    .home-button-dark {
        background: var(--navy);
        color: #fff;
    }

    .home-button-dark:hover {
        background: var(--navy-dark);
        color: #fff;
    }

    .home-button-outline {
        border-color: var(--border);
        background: #fff;
        color: var(--navy);
    }

    .home-button-outline:hover {
        border-color: var(--orange);
        color: var(--orange);
    }

    /* Hero */

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

    .home-hero::before {
        position: absolute;
        z-index: -1;
        inset: 0;
        background-image: radial-gradient(rgb(255 255 255 / 9%) .7px, transparent .7px);
        background-size: 23px 23px;
        content: "";
        opacity: .34;
        pointer-events: none;
    }

    .home-hero-glow {
        position: absolute;
        z-index: -1;
        top: 12%;
        right: -130px;
        width: 410px;
        height: 410px;
        border: 1px solid rgb(255 255 255 / 8%);
        border-radius: 50%;
        box-shadow: 0 0 0 45px rgb(255 255 255 / 2%),
                    0 0 0 90px rgb(255 255 255 / 2%);
        pointer-events: none;
    }

    .home-hero-layout {
        display: grid;
        align-items: center;
        gap: 38px;
        padding-block: 56px;
    }

    .home-hero-title {
        max-width: 700px;
        color: #fff;
        font-size: clamp(38px, 5.3vw, 66px);
        font-weight: 900;
        letter-spacing: -2.5px;
        line-height: 1.08;
    }

    .home-hero-title span {
        color: var(--orange);
    }

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
    }

    .home-dot {
        width: 7px;
        height: 7px;
        flex-shrink: 0;
        border-radius: 50%;
        background: var(--orange);
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

    /* Featured match */

    .home-panel {
        overflow: hidden;
        border: 1px solid rgb(255 255 255 / 20%);
        border-radius: 22px;
        background: #fff;
        color: #1E293B;
        box-shadow: 0 24px 70px rgb(0 0 0 / 18%);
    }

    .home-panel-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        border-bottom: 1px solid #EDF0F6;
        padding: 20px 22px;
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

    .home-status-upcoming {
        background: #FFF0EB;
        color: #D94D31;
    }

    .home-status-completed {
        background: #EAF8F1;
        color: #16845B;
    }

    .home-match-feature {
        margin: 18px;
        border: 1px solid #E8ECF4;
        border-radius: 16px;
        background: #F8FAFD;
        padding: 21px 17px;
    }

    .home-team-logo {
        width: 54px;
        height: 54px;
        flex-shrink: 0;
        object-fit: contain;
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
    }

    .home-feature-team {
        min-width: 0;
        text-align: center;
    }

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

    /* Manual scoreboard */

    .scoreboard-card {
        overflow: hidden;
        position: relative;
    }

    .scoreboard-card::before {
        position: absolute;
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

    .scoreboard-tag {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        border: 1px solid #E5E9F2;
        border-radius: 100px;
        background: #F8FAFD;
        padding: 6px 9px;
        color: #64748B;
        font-size: 9px;
        font-weight: 800;
        letter-spacing: .7px;
        text-transform: uppercase;
    }

    .scoreboard-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 100px;
        background: #EAF8F1;
        padding: 6px 9px;
        color: #16845B;
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

    .scoreboard-team-info {
        display: flex;
        min-width: 0;
        align-items: center;
        gap: 10px;
    }

    .scoreboard-logo {
        width: 39px;
        height: 39px;
        flex-shrink: 0;
        object-fit: contain;
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
        color: #16845B;
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

    .scoreboard-footer {
        padding: 17px 18px 19px;
    }

    .scoreboard-no-score {
        margin: 15px 18px 0;
        border: 1px dashed #D9DFEB;
        border-radius: 12px;
        background: #FAFBFE;
        padding: 15px;
        color: #64748B;
        font-size: 12px;
        line-height: 1.8;
    }

    /* Teams */

    .home-team-card {
        padding: 24px 14px 20px;
        text-align: center;
    }

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

    .home-team-card-short {
        margin-top: 5px;
        color: #94A3B8;
        font-size: 11px;
        font-weight: 700;
    }

    /* Empty states */

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

    .home-empty-title {
        margin-top: 17px;
        color: var(--navy);
        font-size: 17px;
        font-weight: 900;
    }

    .home-empty-text {
        max-width: 440px;
        margin: 9px auto 0;
        color: var(--muted);
        font-size: 13px;
        line-height: 1.8;
    }

    /* News */

    .home-news-card {
        overflow: hidden;
    }

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

    /* Final CTA */

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

    @media (min-width: 640px) {
        .home-container {
            padding-inline: 28px;
        }

        .home-hero-layout {
            padding-block: 75px;
        }
    }

    @media (min-width: 768px) {
        .home-hero-layout {
            grid-template-columns: minmax(0, 1.05fr) minmax(350px, .85fr);
            gap: 32px;
        }

        .home-cta-inner {
            flex-direction: row;
            align-items: center;
        }
    }

    @media (min-width: 1024px) {
        .home-container {
            padding-inline: 32px;
        }

        .home-hero-layout {
            gap: 55px;
            padding-block: 88px;
        }

        .home-section {
            padding-block: 76px;
        }
    }

    @media (max-width: 639px) {
        .home-hero-layout {
            gap: 32px;
            padding-block: 43px;
        }

        .home-hero-title {
            letter-spacing: -1.6px;
        }

        .home-hero-description {
            font-size: 14px;
        }

        .home-section {
            padding-block: 45px;
        }

        .home-section-head {
            align-items: start;
        }

        .home-match-feature {
            margin: 12px;
            padding: 19px 12px;
        }

        .home-panel-top {
            padding: 17px;
        }
    }

    @media (max-width: 400px) {
        .home-container {
            padding-inline: 15px;
        }

        .home-section-head {
            flex-direction: column;
            align-items: start;
        }

        .home-hero-title {
            font-size: 37px;
        }

        .home-hero-checks {
            flex-direction: column;
        }
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

    {{-- HERO --}}
    <section class="home-hero">
        <div class="home-hero-glow" aria-hidden="true"></div>

        <div class="home-container">
            <div class="home-hero-layout">

                <div>
                    <div class="home-season-pill">
                        <span class="home-dot"></span>
                        Nepal Premier League

                        @if(isset($season) && $season)
                            <span class="text-white/40">/</span>
                            <span>{{ $season->name }}</span>
                        @endif
                    </div>

                    <h1 class="home-hero-title mt-7">
                        Every Match.
                        <span class="block">Every Moment.</span>
                        All in One Place.
                    </h1>

                    <p class="home-hero-description">
                        Your home for Nepal cricket. Explore fixtures, check
                        manually updated scoreboards, discover teams and
                        catch up on the latest NPL stories.
                    </p>

                    <div class="mt-7 flex flex-wrap gap-3">
                        <a href="{{ route('fixtures.index') }}"
                           class="home-button home-button-primary">
                            Explore Fixtures <span aria-hidden="true">&rarr;</span>
                        </a>

                        <a href="#recent-matches"
                           class="home-button border border-white/25 bg-white/5 text-white hover:bg-white/10">
                            Recent Scoreboards <span aria-hidden="true">&darr;</span>
                        </a>
                    </div>

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

                {{-- FEATURED UPCOMING MATCH --}}
                <div class="home-panel">
                    <div class="home-panel-top">
                        <div>
                            <p class="home-panel-label">Coming up next</p>
                            <h2 class="home-panel-heading">Match Centre</h2>
                        </div>

                        <span class="home-status home-status-upcoming">
                            <span class="home-dot"></span>
                            Upcoming
                        </span>
                    </div>

                    @if(isset($nextMatch) && $nextMatch)
                        <div class="home-match-feature">
                            <div class="text-center">
                                <p class="text-[10px] font-extrabold uppercase tracking-[1.5px] text-slate-500">
                                    {{ $nextMatch->season->name ?? 'Nepal Premier League' }}
                                </p>

                                @if($nextMatch->match_number)
                                    <p class="mt-2 text-xs font-bold text-slate-500">
                                        Match {{ $nextMatch->match_number }}
                                    </p>
                                @endif
                            </div>

                            <div class="mt-6 grid grid-cols-[1fr_auto_1fr] items-center gap-2 sm:gap-4">
                                <div class="home-feature-team">
                                    @if($imageUrl($nextMatch->homeTeam?->logo))
                                        <img src="{{ $imageUrl($nextMatch->homeTeam->logo) }}"
                                             alt="{{ $nextMatch->homeTeam->name }} logo"
                                             class="home-team-logo mx-auto"
                                             loading="lazy">
                                    @else
                                        <div class="home-team-fallback mx-auto">
                                            {{ $initial($nextMatch->homeTeam->name ?? 'H') }}
                                        </div>
                                    @endif

                                    <p class="home-feature-team-name">
                                        {{ $nextMatch->homeTeam->name ?? 'Home Team' }}
                                    </p>
                                    <p class="home-feature-team-caption">Home</p>
                                </div>

                                <div class="text-center">
                                    <span class="text-base font-black text-[#F06445]">VS</span>
                                    <div class="mt-1 text-[9px] font-extrabold uppercase tracking-widest text-slate-400">
                                        NPL
                                    </div>
                                </div>

                                <div class="home-feature-team">
                                    @if($imageUrl($nextMatch->awayTeam?->logo))
                                        <img src="{{ $imageUrl($nextMatch->awayTeam->logo) }}"
                                             alt="{{ $nextMatch->awayTeam->name }} logo"
                                             class="home-team-logo mx-auto"
                                             loading="lazy">
                                    @else
                                        <div class="home-team-fallback mx-auto">
                                            {{ $initial($nextMatch->awayTeam->name ?? 'A') }}
                                        </div>
                                    @endif

                                    <p class="home-feature-team-name">
                                        {{ $nextMatch->awayTeam->name ?? 'Away Team' }}
                                    </p>
                                    <p class="home-feature-team-caption">Away</p>
                                </div>
                            </div>

                            <div class="home-match-date">
                                @if($nextMatch->scheduled_at)
                                    <p class="text-sm font-extrabold text-[#202766]">
                                        {{ \Illuminate\Support\Carbon::parse($nextMatch->scheduled_at)->format('D, d M Y') }}
                                    </p>
                                    <p class="mt-1.5 text-xs font-semibold text-slate-500">
                                        {{ \Illuminate\Support\Carbon::parse($nextMatch->scheduled_at)->format('h:i A') }}
                                    </p>
                                @else
                                    <p class="text-sm font-bold text-slate-500">
                                        Schedule to be announced
                                    </p>
                                @endif
                            </div>
                        </div>

                        <div class="px-[18px] pb-5">
                            <a href="{{ route('fixtures.show', $nextMatch) }}"
                               class="home-button home-button-dark w-full">
                                View Match Details <span aria-hidden="true">&rarr;</span>
                            </a>
                        </div>
                    @else
                        <div class="px-5 py-10 text-center sm:px-7">
                            <div class="home-empty-icon">&#128197;</div>

                            <h3 class="mt-4 text-base font-extrabold text-[#202766]">
                                No Upcoming Match Scheduled
                            </h3>

                            <p class="mx-auto mt-2 max-w-sm text-xs leading-6 text-slate-500">
                                No upcoming fixture is available right now.
                                Visit the fixtures page to explore the schedule.
                            </p>

                            <a href="{{ route('fixtures.index') }}"
                               class="home-button home-button-primary mt-5">
                                Browse Fixtures &rarr;
                            </a>
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </section>

    {{-- RECENT MATCHES: MANUAL SCOREBOARD --}}
    <section id="recent-matches" class="home-section scroll-mt-24">
        <div class="home-container">

            <div class="home-section-head">
                <div>
                    <div class="home-eyebrow">
                        <span class="home-dot"></span>
                        Match Results
                    </div>

                    <h2 class="home-section-title mt-3">Recent Matches</h2>

                    <p class="home-section-description">
                        Match scores and results recorded in the scorecard.
                    </p>
                </div>

                <a href="{{ route('fixtures.index') }}" class="home-text-link">
                    All Fixtures <span aria-hidden="true">&rarr;</span>
                </a>
            </div>

            @if($recentResults->isNotEmpty())
                <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">

                    @foreach($recentResults as $result)
                        @php
                            /*
                             * Use innings data already supplied to the view,
                             * or data available through the fixture model.
                             *
                             * Expected innings fields:
                             * innings_number, batting_team_id, total_runs,
                             * wickets, legal_balls (or overs).
                             */
                            $innings = collect(
                                $liveScoreByFixture->get($result->id, collect())
                            );

                            if ($innings->isEmpty()) {
                                $innings = collect(
                                    $result->innings
                                    ?? $result->scorecards
                                    ?? []
                                );
                            }

                            $innings = $innings
                                ->sortBy('innings_number')
                                ->values();

                            $homeTeamId = data_get($result, 'home_team_id');
                            $awayTeamId = data_get($result, 'away_team_id');

                            $homeInnings = $innings
                                ->filter(function ($inning) use ($homeTeamId) {
                                    return (string) data_get($inning, 'batting_team_id')
                                        === (string) $homeTeamId;
                                })
                                ->sortBy('innings_number')
                                ->last();

                            $awayInnings = $innings
                                ->filter(function ($inning) use ($awayTeamId) {
                                    return (string) data_get($inning, 'batting_team_id')
                                        === (string) $awayTeamId;
                                })
                                ->sortBy('innings_number')
                                ->last();

                            $homeScore = $formatScore($homeInnings);
                            $awayScore = $formatScore($awayInnings);

                            $homeOvers = $scoreOvers($homeInnings);
                            $awayOvers = $scoreOvers($awayInnings);

                            $winnerName = data_get($result, 'winner.name')
                                ?? data_get($result, 'winner_name');

                            $hasScore = $homeScore !== null || $awayScore !== null;

                            $matchStatus = strtolower(
                                (string) (
                                    data_get($result, 'status')
                                    ?? data_get($result, 'match_status')
                                    ?? ''
                                )
                            );
                        @endphp

                        <article class="home-card scoreboard-card">
                            {{-- Scoreboard heading --}}
                            <div class="scoreboard-header">
                                <div class="min-w-0">
                                    <p class="text-[10px] font-extrabold uppercase tracking-[1.3px] text-slate-400">
                                        {{ $result->season->name ?? 'Nepal Premier League' }}
                                    </p>

                                    <p class="mt-1.5 text-xs font-extrabold text-[#202766]">
                                        {{ $result->match_number
                                            ? 'Match ' . $result->match_number
                                            : 'Match Scorecard' }}
                                    </p>

                                    <p class="mt-1 text-[10px] font-medium text-slate-400">
                                        {{ $matchDate($result) }}
                                    </p>
                                </div>

                                <span class="scoreboard-status">
                                    <span class="home-dot" style="background:#16845B"></span>
                                    Completed
                                </span>
                            </div>

                            {{-- Teams and manual scores --}}
                            <div class="scoreboard-teams">

                                {{-- Home team --}}
                                <div class="scoreboard-team">
                                    <div class="scoreboard-team-info">
                                        @if($imageUrl($result->homeTeam?->logo))
                                            <img src="{{ $imageUrl($result->homeTeam->logo) }}"
                                                 alt="{{ $result->homeTeam->name }} logo"
                                                 class="scoreboard-logo"
                                                 loading="lazy">
                                        @else
                                            <div class="home-team-fallback !h-[39px] !w-[39px] !rounded-xl !text-sm">
                                                {{ $initial($result->homeTeam->name ?? 'H') }}
                                            </div>
                                        @endif

                                        <div class="min-w-0">
                                            <p class="scoreboard-team-name">
                                                {{ $result->homeTeam->name ?? 'Home Team' }}
                                            </p>

                                            @if($winnerName && $winnerName === ($result->homeTeam->name ?? null))
                                                <span class="mt-1 inline-block text-[9px] font-extrabold uppercase tracking-wider text-emerald-600">
                                                    Winner
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <div>
                                        @if($homeScore !== null)
                                            <p class="scoreboard-number">{{ $homeScore }}</p>
                                            <p class="scoreboard-overs">
                                                @if($homeOvers !== null)
                                                    {{ $homeOvers }} overs
                                                @else
                                                    Score recorded
                                                @endif
                                            </p>
                                        @else
                                            <p class="text-xs font-bold text-slate-400">—</p>
                                            <p class="scoreboard-overs">No score</p>
                                        @endif
                                    </div>
                                </div>

                                {{-- Away team --}}
                                <div class="scoreboard-team">
                                    <div class="scoreboard-team-info">
                                        @if($imageUrl($result->awayTeam?->logo))
                                            <img src="{{ $imageUrl($result->awayTeam->logo) }}"
                                                 alt="{{ $result->awayTeam->name }} logo"
                                                 class="scoreboard-logo"
                                                 loading="lazy">
                                        @else
                                            <div class="home-team-fallback !h-[39px] !w-[39px] !rounded-xl !text-sm">
                                                {{ $initial($result->awayTeam->name ?? 'A') }}
                                            </div>
                                        @endif

                                        <div class="min-w-0">
                                            <p class="scoreboard-team-name">
                                                {{ $result->awayTeam->name ?? 'Away Team' }}
                                            </p>

                                            @if($winnerName && $winnerName === ($result->awayTeam->name ?? null))
                                                <span class="mt-1 inline-block text-[9px] font-extrabold uppercase tracking-wider text-emerald-600">
                                                    Winner
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <div>
                                        @if($awayScore !== null)
                                            <p class="scoreboard-number">{{ $awayScore }}</p>
                                            <p class="scoreboard-overs">
                                                @if($awayOvers !== null)
                                                    {{ $awayOvers }} overs
                                                @else
                                                    Score recorded
                                                @endif
                                            </p>
                                        @else
                                            <p class="text-xs font-bold text-slate-400">—</p>
                                            <p class="scoreboard-overs">No score</p>
                                        @endif
                                    </div>
                                </div>

                            </div>

                            {{-- Result or scorecard notice --}}
                            @if($winnerName)
                                <div class="scoreboard-result">
                                    <p class="scoreboard-result-label">Match Winner</p>
                                    <p class="scoreboard-result-text">
                                        {{ $winnerName }}
                                    </p>
                                </div>
                            @elseif(!$hasScore)
                                <div class="scoreboard-no-score">
                                    The match result is available, but a detailed scorecard
                                    has not been recorded yet.
                                </div>
                            @else
                                <div class="mx-[18px] mt-4 rounded-xl border border-slate-100 bg-slate-50 px-4 py-3">
                                    <p class="text-xs font-semibold leading-6 text-slate-600">
                                        Final score recorded. Open the match page for the full result.
                                    </p>
                                </div>
                            @endif

                            <div class="scoreboard-footer">
                                <a href="{{ route('fixtures.show', $result) }}"
                                   class="home-button home-button-outline w-full">
                                    View Full Scorecard
                                    <span aria-hidden="true">&rarr;</span>
                                </a>
                            </div>
                        </article>
                    @endforeach

                </div>
            @else
                <div class="home-empty">
                    <div class="home-empty-icon">&#127951;</div>

                    <h3 class="home-empty-title">No Recent Matches Yet</h3>

                    <p class="home-empty-text">
                        Completed matches will appear here after results
                        and scorecards have been entered.
                    </p>

                    <a href="{{ route('fixtures.index') }}"
                       class="home-button home-button-dark mt-5">
                        Browse Fixtures <span aria-hidden="true">&rarr;</span>
                    </a>
                </div>
            @endif
        </div>
    </section>

    {{-- TEAMS --}}
    <section id="teams" class="home-section home-section-white scroll-mt-24">
        <div class="home-container">

            <div class="home-section-head">
                <div>
                    <div class="home-eyebrow">The Competition</div>
                    <h2 class="home-section-title mt-3">Meet the Teams</h2>
                    <p class="home-section-description">
                        Discover the teams competing in Nepal Premier League.
                    </p>
                </div>

                <a href="{{ route('teams.index') }}" class="home-text-link">
                    Explore Teams <span aria-hidden="true">&rarr;</span>
                </a>
            </div>

            @if($teams->isNotEmpty())
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                    @foreach($teams as $team)
                        <a href="{{ route('teams.show', $team) }}"
                           class="home-card home-team-card group">

                            @if($imageUrl($team->logo))
                                <img src="{{ $imageUrl($team->logo) }}"
                                     alt="{{ $team->name }} logo"
                                     class="home-team-logo transition duration-200 group-hover:scale-105"
                                     loading="lazy">
                            @else
                                <div class="home-team-fallback transition group-hover:border-orange-200 group-hover:bg-orange-50 group-hover:text-[#F06445]">
                                    {{ $initial($team->name) }}
                                </div>
                            @endif

                            <h3 class="home-team-card-name">{{ $team->name }}</h3>

                            @if(!empty($team->short_name))
                                <p class="home-team-card-short">{{ $team->short_name }}</p>
                            @endif

                            <span class="mt-4 inline-flex items-center gap-2 text-[10px] font-extrabold uppercase tracking-wider text-[#F06445]">
                                Team Profile <span aria-hidden="true">&rarr;</span>
                            </span>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="home-empty">
                    <div class="home-empty-icon">&#127942;</div>
                    <h3 class="home-empty-title">Teams Coming Soon</h3>
                    <p class="home-empty-text">
                        Teams will appear here when they are available in the database.
                    </p>
                </div>
            @endif

        </div>
    </section>

    {{-- LATEST NEWS --}}
    <section id="news" class="home-section home-section-white scroll-mt-24">
        <div class="home-container">

            <div class="home-section-head">
                <div>
                    <div class="home-eyebrow">Around the League</div>
                    <h2 class="home-section-title mt-3">Latest Cricket News</h2>
                    <p class="home-section-description">
                        Stories, match reports and updates from the NPL.
                    </p>
                </div>

                <a href="{{ route('news.index') }}" class="home-text-link">
                    All News <span aria-hidden="true">&rarr;</span>
                </a>
            </div>

            @if($latestNews->isNotEmpty())
                <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($latestNews as $article)
                        @php
                            $articleImage = $article->image
                                ?? $article->featured_image
                                ?? null;
                        @endphp

                        <article class="home-card home-news-card group">
                            <a href="{{ route('news.show', $article->slug) }}"
                               class="block overflow-hidden"
                               aria-label="Read {{ $article->title }}">

                                @if($imageUrl($articleImage))
                                    <img src="{{ $imageUrl($articleImage) }}"
                                         alt="{{ $article->title }}"
                                         class="home-news-image transition duration-300 group-hover:scale-[1.03]"
                                         loading="lazy">
                                @else
                                    <div class="home-news-placeholder">
                                        <span class="text-3xl font-black tracking-tight">NPL</span>
                                        <span class="mt-2 text-[9px] font-extrabold uppercase tracking-[2px] text-white/70">
                                            Cricket Hub
                                        </span>
                                    </div>
                                @endif
                            </a>

                            <div class="p-5">
                                <div class="flex flex-wrap items-center gap-2 text-[10px] font-semibold text-slate-400">
                                    @if($article->published_at)
                                        <time datetime="{{ \Illuminate\Support\Carbon::parse($article->published_at)->toIso8601String() }}">
                                            {{ \Illuminate\Support\Carbon::parse($article->published_at)->format('d M Y') }}
                                        </time>
                                    @elseif($article->created_at)
                                        <time datetime="{{ \Illuminate\Support\Carbon::parse($article->created_at)->toIso8601String() }}">
                                            {{ \Illuminate\Support\Carbon::parse($article->created_at)->format('d M Y') }}
                                        </time>
                                    @endif

                                    <span aria-hidden="true">/</span>

                                    <span class="font-extrabold uppercase tracking-wider text-[#F06445]">
                                        NPL News
                                    </span>
                                </div>

                                <a href="{{ route('news.show', $article->slug) }}"
                                   class="mt-3 block">
                                    <h3 class="home-news-title transition group-hover:text-[#F06445]">
                                        {{ $article->title }}
                                    </h3>
                                </a>

                                @if(!empty($article->excerpt))
                                    <p class="home-news-excerpt mt-3">
                                        {{ $article->excerpt }}
                                    </p>
                                @elseif(!empty($article->summary))
                                    <p class="home-news-excerpt mt-3">
                                        {{ $article->summary }}
                                    </p>
                                @elseif(!empty($article->content))
                                    <p class="home-news-excerpt mt-3">
                                        {{ \Illuminate\Support\Str::limit(strip_tags($article->content), 150) }}
                                    </p>
                                @endif

                                <a href="{{ route('news.show', $article->slug) }}"
                                   class="mt-5 inline-flex items-center gap-2 text-xs font-extrabold text-[#F06445] transition hover:text-[#202766]">
                                    Read Full Story <span aria-hidden="true">&rarr;</span>
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="home-empty">
                    <div class="home-empty-icon">&#128240;</div>
                    <h3 class="home-empty-title">No Published News Yet</h3>
                    <p class="home-empty-text">
                        Published articles will appear here when available.
                        Check back for the latest league coverage.
                    </p>
                </div>
            @endif

        </div>
    </section>

    {{-- FINAL CALL TO ACTION --}}
    <section class="home-cta">
        <div class="home-container">
            <div class="home-cta-inner">
                <div class="max-w-2xl">
                    <div class="home-eyebrow">Your NPL Matchday Starts Here</div>

                    <h2 class="mt-3 text-2xl font-black tracking-tight text-white sm:text-3xl">
                        Never Miss a Moment.
                    </h2>

                    <p class="mt-3 max-w-xl text-sm leading-7 text-white/70">
                        Explore match schedules, discover your favourite teams,
                        check recent scoreboards and stay connected with Nepal cricket.
                    </p>
                </div>

                <a href="{{ route('fixtures.index') }}"
                   class="home-button home-button-primary shrink-0">
                    Explore All Fixtures <span aria-hidden="true">&rarr;</span>
                </a>
            </div>
        </div>
    </section>

</div>

@endsection
