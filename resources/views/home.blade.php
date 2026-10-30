
{{-- resources/views/home.blade.php --}}

@extends('layouts.app')

@php
    $liveMatches = $liveMatches ?? collect();
    $teams = $teams ?? collect();
    $recentResults = $recentResults ?? collect();
    $latestNews = $latestNews ?? collect();
    $liveScoreByFixture = $liveScoreByFixture ?? collect();
@endphp

@section('title', 'Home')
@section('meta_description', 'Follow Nepal Premier League fixtures, live cricket scores, teams, match results and the latest NPL news on NPL Cricket Hub.')

@section('content')

<style>
    .home-page {
        --home-navy: #202766;
        --home-dark: #171D50;
        --home-orange: #F06445;
        --home-bg: #F6F8FB;
        --home-border: #E5E9F2;
        --home-muted: #64748B;
        color: #1E293B;
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
        color: var(--home-orange);
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 1.8px;
        line-height: 1.5;
        text-transform: uppercase;
    }

    .home-section-title {
        color: var(--home-navy);
        font-size: clamp(25px, 3vw, 34px);
        font-weight: 900;
        letter-spacing: -1.15px;
        line-height: 1.2;
    }

    .home-section-description {
        margin-top: 10px;
        color: var(--home-muted);
        font-size: 14px;
        line-height: 1.8;
    }

    .home-card {
        border: 1px solid var(--home-border);
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
        min-height: 46px;
        align-items: center;
        justify-content: center;
        gap: 10px;
        border-radius: 10px;
        padding: 12px 19px;
        font-size: 13px;
        font-weight: 800;
        transition: background-color .18s ease, transform .18s ease,
            border-color .18s ease;
    }

    .home-button:hover {
        transform: translateY(-1px);
    }

    .home-button-primary {
        background: var(--home-orange);
        color: #fff;
    }

    .home-button-primary:hover {
        background: #D94D31;
        color: #fff;
    }

    .home-button-dark {
        background: var(--home-navy);
        color: #fff;
    }

    .home-button-dark:hover {
        background: var(--home-dark);
        color: #fff;
    }

    .home-button-outline {
        border: 1px solid var(--home-border);
        background: #fff;
        color: var(--home-navy);
    }

    .home-button-outline:hover {
        border-color: var(--home-orange);
        color: var(--home-orange);
    }

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
        box-shadow:
            0 0 0 45px rgb(255 255 255 / 2%),
            0 0 0 90px rgb(255 255 255 / 2%);
        pointer-events: none;
    }

    .home-hero-layout {
        display: grid;
        align-items: center;
        gap: 42px;
        padding-block: 56px;
    }

    .home-hero-title {
        max-width: 700px;
        color: #fff;
        font-size: clamp(38px, 5.3vw, 66px);
        font-weight: 900;
        letter-spacing: -2.7px;
        line-height: 1.08;
    }

    .home-hero-title span {
        color: var(--home-orange);
    }

    .home-hero-description {
        max-width: 530px;
        margin-top: 23px;
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
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .8px;
        text-transform: uppercase;
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
        font-size: 12px;
    }

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
        color: #64748B;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 1.6px;
        text-transform: uppercase;
    }

    .home-panel-heading {
        margin-top: 5px;
        color: var(--home-navy);
        font-size: 20px;
        font-weight: 900;
        letter-spacing: -.6px;
    }

    .home-status {
        display: inline-flex;
        flex-shrink: 0;
        align-items: center;
        gap: 7px;
        border-radius: 100px;
        padding: 7px 10px;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .5px;
    }

    .home-status-upcoming {
        background: #FFF0EB;
        color: #D94D31;
    }

    .home-status-live {
        background: #FFF0EB;
        color: #D94D31;
    }

    .home-status-completed {
        background: #EAF8F1;
        color: #16845B;
    }

    .home-status-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: currentColor;
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
        color: var(--home-navy);
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
        color: var(--home-navy);
        font-size: 13px;
        font-weight: 900;
        line-height: 1.5;
    }

    .home-feature-team-caption {
        margin-top: 3px;
        color: #94A3B8;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .7px;
    }

    .home-vs {
        color: var(--home-orange);
        font-size: 16px;
        font-weight: 900;
    }

    .home-match-date {
        border-top: 1px solid #E5E9F2;
        margin-top: 20px;
        padding-top: 16px;
        text-align: center;
    }

    .home-section {
        padding-block: 64px;
    }

    .home-section-white {
        border-block: 1px solid #E9EDF4;
        background: #fff;
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
        color: var(--home-navy);
        font-size: 12px;
        font-weight: 800;
    }

    .home-text-link:hover {
        color: var(--home-orange);
    }

    .home-live-dot {
        display: inline-block;
        width: 8px;
        height: 8px;
        flex-shrink: 0;
        border-radius: 50%;
        background: var(--home-orange);
        animation: homePulse 1.7s infinite;
    }

    @keyframes homePulse {
        0%, 100% {
            box-shadow: 0 0 0 0 rgb(240 100 69 / 35%);
        }
        50% {
            box-shadow: 0 0 0 6px rgb(240 100 69 / 0%);
        }
    }

    .home-live-card {
        overflow: hidden;
    }

    .home-live-card-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        border-bottom: 1px solid #EDF0F6;
        padding: 15px 18px;
    }

    .home-live-team-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .home-live-team-info {
        display: flex;
        min-width: 0;
        align-items: center;
        gap: 11px;
    }

    .home-live-team-name {
        overflow-wrap: anywhere;
        color: var(--home-navy);
        font-size: 12px;
        font-weight: 800;
        line-height: 1.5;
    }

    .home-live-score {
        flex-shrink: 0;
        color: var(--home-navy);
        font-size: 21px;
        font-weight: 900;
        letter-spacing: -.8px;
        text-align: right;
    }

    .home-live-overs {
        margin-top: 3px;
        color: #94A3B8;
        font-size: 10px;
        font-weight: 700;
        text-align: right;
    }

    .home-empty {
        border: 1px dashed #D9DFEB;
        border-radius: 18px;
        background: #fff;
        padding: 45px 22px;
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
        color: var(--home-orange);
        font-size: 22px;
    }

    .home-empty-title {
        margin-top: 17px;
        color: var(--home-navy);
        font-size: 17px;
        font-weight: 900;
    }

    .home-empty-text {
        max-width: 440px;
        margin: 9px auto 0;
        color: var(--home-muted);
        font-size: 13px;
        line-height: 1.8;
    }

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

    .home-team-card .home-team-fallback {
        font-size: 23px;
    }

    .home-team-card-name {
        margin-top: 17px;
        overflow-wrap: anywhere;
        color: var(--home-navy);
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

    .home-result-card {
        padding: 21px;
    }

    .home-result-team {
        min-width: 0;
        text-align: center;
    }

    .home-result-team .home-team-logo,
    .home-result-team .home-team-fallback {
        width: 44px;
        height: 44px;
        margin-inline: auto;
        border-radius: 12px;
    }

    .home-result-team .home-team-fallback {
        font-size: 17px;
    }

    .home-result-team-name {
        margin-top: 10px;
        overflow-wrap: anywhere;
        color: var(--home-navy);
        font-size: 12px;
        font-weight: 800;
        line-height: 1.5;
    }

    .home-news-image {
        display: block;
        width: 100%;
        height: 200px;
        object-fit: cover;
        background: #EDF1F7;
        transition: transform .35s ease;
    }

    .home-news-placeholder {
        display: flex;
        height: 200px;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background:
            radial-gradient(circle at 80% 10%, rgb(240 100 69 / 20%), transparent 35%),
            linear-gradient(135deg, #171D50, #303C83);
        color: #fff;
    }

    .home-news-card:hover .home-news-image {
        transform: scale(1.035);
    }

    .home-news-card {
        overflow: hidden;
    }

    .home-news-title {
        display: -webkit-box;
        overflow: hidden;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
        color: var(--home-navy);
        font-size: 16px;
        font-weight: 900;
        line-height: 1.55;
    }

    .home-news-excerpt {
        display: -webkit-box;
        overflow: hidden;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 3;
        color: var(--home-muted);
        font-size: 12px;
        line-height: 1.9;
    }

    .home-cta {
        position: relative;
        overflow: hidden;
        background:
            radial-gradient(ellipse at 90% 15%, rgb(240 100 69 / 18%), transparent 30%),
            linear-gradient(120deg, #171D50, #202766);
        color: #fff;
    }

    .home-cta::after {
        position: absolute;
        top: -85px;
        right: 5%;
        width: 220px;
        height: 220px;
        border: 1px solid rgb(255 255 255 / 10%);
        border-radius: 50%;
        box-shadow: 0 0 0 35px rgb(255 255 255 / 3%);
        content: "";
        pointer-events: none;
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
            grid-template-columns: minmax(0, 1.05fr) minmax(370px, .85fr);
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

        .home-section-head .home-text-link {
            margin-top: 4px;
        }

        .home-panel-top {
            padding: 17px;
        }

        .home-match-feature {
            margin: 12px;
            padding: 19px 12px;
        }

        .home-section-title {
            letter-spacing: -.8px;
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

        .home-live-score {
            font-size: 18px;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .home-live-dot {
            animation: none;
        }

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
                        <span class="home-live-dot"></span>
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
                        Your home for Nepal cricket. Follow NPL fixtures,
                        live match scores, team updates, verified results
                        and the latest stories from the league.
                    </p>

                    <div class="mt-7 flex flex-wrap gap-3">
                        <a href="{{ route('fixtures.index') }}"
                           class="home-button home-button-primary">
                            Explore Fixtures
                            <span aria-hidden="true">&rarr;</span>
                        </a>

                        <a href="#live-scores"
                           class="home-button border border-white/25 bg-white/5 text-white hover:bg-white/10">
                            <span class="home-live-dot"></span>
                            Live Match Centre
                        </a>
                    </div>

                    <div class="home-hero-checks">
                        <div class="home-hero-check">
                            <span class="home-check-icon" aria-hidden="true">&#10003;</span>
                            Match information
                        </div>

                        <div class="home-hero-check">
                            <span class="home-check-icon" aria-hidden="true">&#10003;</span>
                            Verified results
                        </div>

                        <div class="home-hero-check">
                            <span class="home-check-icon" aria-hidden="true">&#10003;</span>
                            League updates
                        </div>
                    </div>
                </div>

                {{-- FEATURED MATCH --}}
                <div class="home-panel">
                    <div class="home-panel-top">
                        <div>
                            <p class="home-panel-label">The next big game</p>
                            <h2 class="home-panel-heading">Match Centre</h2>
                        </div>

                        <span class="home-status home-status-upcoming">
                            <span class="home-status-dot"></span>
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
                                    @if($nextMatch->homeTeam?->logo)
                                        <img src="{{ asset('storage/' . ltrim($nextMatch->homeTeam->logo, '/')) }}"
                                             alt="{{ $nextMatch->homeTeam->name }} logo"
                                             class="home-team-logo mx-auto"
                                             loading="lazy">
                                    @else
                                        <div class="home-team-fallback mx-auto">
                                            {{ strtoupper(substr($nextMatch->homeTeam->name ?? 'H', 0, 1)) }}
                                        </div>
                                    @endif

                                    <p class="home-feature-team-name">
                                        {{ $nextMatch->homeTeam->name ?? 'Home Team' }}
                                    </p>
                                    <p class="home-feature-team-caption">Home</p>
                                </div>

                                <div class="text-center">
                                    <span class="home-vs">VS</span>
                                    <div class="mt-1 text-[9px] font-extrabold uppercase tracking-widest text-slate-400">
                                        NPL
                                    </div>
                                </div>

                                <div class="home-feature-team">
                                    @if($nextMatch->awayTeam?->logo)
                                        <img src="{{ asset('storage/' . ltrim($nextMatch->awayTeam->logo, '/')) }}"
                                             alt="{{ $nextMatch->awayTeam->name }} logo"
                                             class="home-team-logo mx-auto"
                                             loading="lazy">
                                    @else
                                        <div class="home-team-fallback mx-auto">
                                            {{ strtoupper(substr($nextMatch->awayTeam->name ?? 'A', 0, 1)) }}
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
                                View Match Details
                                <span aria-hidden="true">&rarr;</span>
                            </a>
                        </div>
                    @else
                        <div class="px-5 py-10 text-center sm:px-7">
                            <div class="home-empty-icon">&#128197;</div>

                            <h3 class="mt-4 text-base font-extrabold text-[#202766]">
                                No Upcoming Match Scheduled
                            </h3>

                            <p class="mx-auto mt-2 max-w-sm text-xs leading-6 text-slate-500">
                                There are no upcoming fixtures available right now.
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

    {{-- LIVE SCORES --}}
    <section id="live-scores" class="home-section scroll-mt-24">
        <div class="home-container">

            <div class="home-section-head">
                <div>
                    <div class="home-eyebrow">
                        <span class="home-live-dot"></span>
                        Live Match Centre
                    </div>

                    <h2 class="home-section-title mt-3">Live Scores</h2>

                    <p class="home-section-description">
                        Follow the scores of matches currently marked as live.
                    </p>
                </div>

                <a href="{{ route('fixtures.index') }}" class="home-text-link">
                    All Fixtures <span aria-hidden="true">&rarr;</span>
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

                        <article class="home-card home-live-card">
                            <div class="home-live-card-top">
                                <div class="flex items-center gap-2">
                                    <span class="home-live-dot"></span>
                                    <span class="text-[10px] font-extrabold uppercase tracking-[1.4px] text-[#D94D31]">
                                        Live Now
                                    </span>
                                </div>

                                <span class="text-[10px] font-bold text-slate-400">
                                    {{ $match->match_number ? 'Match ' . $match->match_number : 'NPL Match' }}
                                </span>
                            </div>

                            <div class="p-5">
                                @if($match->season)
                                    <p class="mb-5 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                        {{ $match->season->name }}
                                    </p>
                                @endif

                                <div class="space-y-5">
                                    {{-- Home team --}}
                                    <div class="home-live-team-row">
                                        <div class="home-live-team-info">
                                            @if($match->homeTeam?->logo)
                                                <img src="{{ asset('storage/' . ltrim($match->homeTeam->logo, '/')) }}"
                                                     alt="{{ $match->homeTeam->name }} logo"
                                                     class="h-10 w-10 shrink-0 object-contain"
                                                     loading="lazy">
                                            @else
                                                <div class="home-team-fallback !h-10 !w-10 !rounded-xl !text-base">
                                                    {{ strtoupper(substr($match->homeTeam->name ?? 'H', 0, 1)) }}
                                                </div>
                                            @endif

                                            <div class="min-w-0">
                                                <p class="home-live-team-name">
                                                    {{ $match->homeTeam->name ?? 'Home Team' }}
                                                </p>

                                                @if($currentInnings && (string) $currentInnings->batting_team_id === (string) $homeTeamId)
                                                    <p class="mt-1 text-[10px] font-extrabold text-[#F06445]">BATTING</p>
                                                @endif
                                            </div>
                                        </div>

                                        <div>
                                            @if($homeRuns !== null)
                                                <p class="home-live-score">{{ $homeRuns }}/{{ $homeWickets }}</p>
                                                <p class="home-live-overs">{{ $homeOvers }} overs</p>
                                            @else
                                                <p class="text-xs font-bold text-slate-400">Yet to bat</p>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="border-t border-dashed border-slate-200"></div>

                                    {{-- Away team --}}
                                    <div class="home-live-team-row">
                                        <div class="home-live-team-info">
                                            @if($match->awayTeam?->logo)
                                                <img src="{{ asset('storage/' . ltrim($match->awayTeam->logo, '/')) }}"
                                                     alt="{{ $match->awayTeam->name }} logo"
                                                     class="h-10 w-10 shrink-0 object-contain"
                                                     loading="lazy">
                                            @else
                                                <div class="home-team-fallback !h-10 !w-10 !rounded-xl !text-base">
                                                    {{ strtoupper(substr($match->awayTeam->name ?? 'A', 0, 1)) }}
                                                </div>
                                            @endif

                                            <div class="min-w-0">
                                                <p class="home-live-team-name">
                                                    {{ $match->awayTeam->name ?? 'Away Team' }}
                                                </p>

                                                @if($currentInnings && (string) $currentInnings->batting_team_id === (string) $awayTeamId)
                                                    <p class="mt-1 text-[10px] font-extrabold text-[#F06445]">BATTING</p>
                                                @endif
                                            </div>
                                        </div>

                                        <div>
                                            @if($awayRuns !== null)
                                                <p class="home-live-score">{{ $awayRuns }}/{{ $awayWickets }}</p>
                                                <p class="home-live-overs">{{ $awayOvers }} overs</p>
                                            @else
                                                <p class="text-xs font-bold text-slate-400">Yet to bat</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                @if($currentInnings)
                                    <div class="mt-5 rounded-xl border border-orange-100 bg-orange-50/80 p-4">
                                        <div class="flex items-center justify-between gap-3">
                                            <div class="min-w-0">
                                                <p class="text-[9px] font-extrabold uppercase tracking-[1.3px] text-slate-500">
                                                    Current Innings
                                                </p>
                                                <p class="mt-1 truncate text-xs font-extrabold text-[#202766]">
                                                    {{ $currentInnings->battingTeam->name ?? 'Batting team' }}
                                                </p>
                                            </div>

                                            <div class="shrink-0 text-right">
                                                <p class="text-xl font-black tracking-tight text-[#D94D31]">
                                                    {{ $currentRuns }}/{{ $currentWickets }}
                                                </p>
                                                <p class="text-[10px] font-semibold text-slate-500">
                                                    {{ $currentOvers }} overs
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="mt-5 rounded-xl bg-slate-50 p-4">
                                        <p class="text-xs font-semibold leading-6 text-slate-500">
                                            The match is marked live. Score details have not been recorded yet.
                                        </p>
                                    </div>
                                @endif

                                <a href="{{ route('fixtures.show', $match) }}"
                                   class="home-button home-button-outline mt-5 w-full">
                                    View Scorecard
                                    <span aria-hidden="true">&rarr;</span>
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="home-empty">
                    <div class="home-empty-icon">&#127951;</div>

                    <h3 class="home-empty-title">No Live Matches Right Now</h3>

                    <p class="home-empty-text">
                        There are currently no fixtures marked as live.
                        Check the schedule for upcoming games and completed results.
                    </p>

                    <a href="{{ route('fixtures.index') }}" class="home-button home-button-dark mt-5">
                        View Fixtures <span aria-hidden="true">&rarr;</span>
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

                            @if($team->logo)
                                <img src="{{ asset('storage/' . ltrim($team->logo, '/')) }}"
                                     alt="{{ $team->name }} logo"
                                     class="home-team-logo transition duration-200 group-hover:scale-105"
                                     loading="lazy">
                            @else
                                <div class="home-team-fallback transition group-hover:border-orange-200 group-hover:bg-orange-50 group-hover:text-[#F06445]">
                                    {{ strtoupper(substr($team->name, 0, 1)) }}
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

    {{-- RECENT RESULTS --}}
    <section id="results" class="home-section scroll-mt-24">
        <div class="home-container">

            <div class="home-section-head">
                <div>
                    <div class="home-eyebrow">Full Time</div>
                    <h2 class="home-section-title mt-3">Recent Results</h2>
                    <p class="home-section-description">
                        Catch up on the latest completed fixtures and match outcomes.
                    </p>
                </div>

                <a href="{{ route('fixtures.index') }}" class="home-text-link">
                    All Results <span aria-hidden="true">&rarr;</span>
                </a>
            </div>

            @if($recentResults->isNotEmpty())
                <div class="grid gap-5 md:grid-cols-2">
                    @foreach($recentResults as $result)
                        <article class="home-card home-result-card">

                            <div class="flex items-center justify-between gap-3">
                                <span class="home-status home-status-completed">
                                    <span class="home-status-dot"></span>
                                    Completed
                                </span>

                                <span class="text-[10px] font-bold text-slate-400">
                                    {{ $result->match_number ? 'MATCH ' . $result->match_number : 'FINAL RESULT' }}
                                </span>
                            </div>

                            @if($result->season)
                                <p class="mt-4 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                    {{ $result->season->name }}
                                </p>
                            @endif

                            <div class="mt-6 grid grid-cols-[1fr_auto_1fr] items-center gap-3">

                                <div class="home-result-team">
                                    @if($result->homeTeam?->logo)
                                        <img src="{{ asset('storage/' . ltrim($result->homeTeam->logo, '/')) }}"
                                             alt="{{ $result->homeTeam->name }} logo"
                                             class="home-team-logo"
                                             loading="lazy">
                                    @else
                                        <div class="home-team-fallback">
                                            {{ strtoupper(substr($result->homeTeam->name ?? 'H', 0, 1)) }}
                                        </div>
                                    @endif

                                    <p class="home-result-team-name">
                                        {{ $result->homeTeam->name ?? 'Home Team' }}
                                    </p>
                                </div>

                                <span class="text-xs font-black text-slate-300">VS</span>

                                <div class="home-result-team">
                                    @if($result->awayTeam?->logo)
                                        <img src="{{ asset('storage/' . ltrim($result->awayTeam->logo, '/')) }}"
                                             alt="{{ $result->awayTeam->name }} logo"
                                             class="home-team-logo"
                                             loading="lazy">
                                    @else
                                        <div class="home-team-fallback">
                                            {{ strtoupper(substr($result->awayTeam->name ?? 'A', 0, 1)) }}
                                        </div>
                                    @endif

                                    <p class="home-result-team-name">
                                        {{ $result->awayTeam->name ?? 'Away Team' }}
                                    </p>
                                </div>
                            </div>

                            <div class="mt-6 rounded-xl border border-slate-100 bg-slate-50 px-4 py-4 text-center">
                                @if($result->winner)
                                    <p class="text-[9px] font-extrabold uppercase tracking-[1.4px] text-slate-400">
                                        Match Winner
                                    </p>
                                    <p class="mt-1.5 text-sm font-extrabold text-[#202766]">
                                        {{ $result->winner->name }}
                                    </p>
                                @else
                                    <p class="text-xs font-bold text-slate-500">
                                        Match completed
                                    </p>
                                @endif
                            </div>

                            @if($result->scheduled_at)
                                <p class="mt-4 text-center text-[10px] font-semibold text-slate-400">
                                    {{ \Illuminate\Support\Carbon::parse($result->scheduled_at)->format('d M Y') }}
                                </p>
                            @endif

                            <a href="{{ route('fixtures.show', $result) }}"
                               class="home-button home-button-outline mt-4 w-full">
                                View Match Details <span aria-hidden="true">&rarr;</span>
                            </a>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="home-empty">
                    <div class="home-empty-icon">&#127942;</div>
                    <h3 class="home-empty-title">No Results Available Yet</h3>
                    <p class="home-empty-text">
                        Completed fixtures will appear here when results are available.
                    </p>
                    <a href="{{ route('fixtures.index') }}" class="home-button home-button-dark mt-5">
                        Browse Fixtures &rarr;
                    </a>
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

                                @if($articleImage)
                                    <img src="{{ asset('storage/' . ltrim($articleImage, '/')) }}"
                                         alt="{{ $article->title }}"
                                         class="home-news-image"
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
                        check the results and stay connected with Nepal cricket.
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
