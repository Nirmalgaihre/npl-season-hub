
@extends('layouts.app')

@section('title', ($playerName ?? 'Player') . ' - Player Performance | NPL Season Hub')

@section('content')
@php
    $getValue = function ($entry, $key, $default = '—') {
        $value = data_get($entry, $key, $default);

        return $value === null || $value === '' ? $default : $value;
    };

    $formatNumber = function ($value, $decimals = 2) {
        if ($value === null || $value === '' || $value === '—') {
            return '—';
        }

        return is_numeric($value)
            ? number_format((float) $value, $decimals)
            : $value;
    };

    $formatDate = function ($date) {
        if (!$date || $date === '—') {
            return 'Date TBC';
        }

        try {
            return \Illuminate\Support\Carbon::parse($date)->format('d M Y');
        } catch (\Throwable $e) {
            return $date;
        }
    };

    $teamName = function ($team) use ($getValue) {
        if (is_string($team)) {
            return $team;
        }

        return $getValue($team, 'short_name', $getValue($team, 'name', 'Team'));
    };

    $teamLogo = function ($team) use ($getValue) {
        if (is_string($team) || !$team) {
            return null;
        }

        $logo = $getValue(
            $team,
            'logo',
            $getValue($team, 'logo_path', $getValue($team, 'image', null))
        );

        if (!$logo || $logo === '—') {
            return null;
        }

        if (
            str_starts_with($logo, 'https://') ||
            str_starts_with($logo, 'http://') ||
            str_starts_with($logo, 'data:') ||
            str_starts_with($logo, '/')
        ) {
            return $logo;
        }

        return str_starts_with($logo, 'storage/')
            ? asset($logo)
            : \Illuminate\Support\Facades\Storage::disk('public')->url($logo);
    };

    $player = $player ?? null;
    $playerName = $playerName ?? $getValue($player, 'name', 'Player');

    $battingStats = $battingStats ?? [];
    $bowlingStats = $bowlingStats ?? [];
    $performancesCollection = collect($performances ?? []);

    $totalRuns = $getValue($battingStats, 'runs', 0);
    $highestScore = $getValue($battingStats, 'highest_score', 0);
    $battingAverage = $getValue($battingStats, 'average', '—');
    $strikeRate = $getValue($battingStats, 'strike_rate', '—');
    $fours = $getValue($battingStats, 'fours', 0);
    $sixes = $getValue($battingStats, 'sixes', 0);
    $battingInnings = $getValue($battingStats, 'innings', 0);
    $notOuts = $getValue($battingStats, 'not_outs', 0);

    $totalWickets = $getValue($bowlingStats, 'wickets', 0);
    $economy = $getValue($bowlingStats, 'economy', '—');
    $overs = $getValue($bowlingStats, 'overs', 0);
    $runsConceded = $getValue($bowlingStats, 'runs', 0);
    $maidens = $getValue($bowlingStats, 'maidens', 0);
    $bowlingInnings = $getValue($bowlingStats, 'innings', 0);

    $playerPhoto = $getValue(
        $player,
        'photo',
        $getValue($player, 'image', $getValue($player, 'photo_path', null))
    );

    $playerPhotoUrl = null;

    if ($playerPhoto && $playerPhoto !== '—') {
        if (
            str_starts_with($playerPhoto, 'https://') ||
            str_starts_with($playerPhoto, 'http://') ||
            str_starts_with($playerPhoto, '/')
        ) {
            $playerPhotoUrl = $playerPhoto;
        } elseif (str_starts_with($playerPhoto, 'storage/')) {
            $playerPhotoUrl = asset($playerPhoto);
        } else {
            $playerPhotoUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($playerPhoto);
        }
    }

    $playerInitial = strtoupper(mb_substr($playerName, 0, 1));
    $playerRole = $getValue(
        $player,
        'role',
        $getValue($player, 'playing_role', 'CRICKETER')
    );

    $playerTeamName = $getValue(
        $player,
        'team.short_name',
        $getValue($player, 'team.name', 'NPL PLAYER')
    );

    /*
     * Set this path to the actual NPL logo in your public directory.
     * Expected file: public/images/npl-logo.png
     */
    $nplLogo = asset('images/npl-logo.png');

    $battingCards = [
        ['Runs', $totalRuns, 'Total runs scored'],
        ['Highest Score', $highestScore, 'Best individual innings'],
        ['Batting Average', $formatNumber($battingAverage), 'Runs per dismissal'],
        ['Strike Rate', $formatNumber($strikeRate), 'Runs per 100 balls'],
        ['Fours', $fours, 'Boundaries scored'],
        ['Sixes', $sixes, 'Maximums scored'],
        ['Innings', $battingInnings, 'Batting innings'],
        ['Not Outs', $notOuts, 'Innings not dismissed'],
    ];

    $bowlingCards = [
        ['Wickets', $totalWickets, 'Total wickets taken'],
        ['Economy', $formatNumber($economy), 'Runs conceded per over'],
        ['Overs', $overs, 'Overs bowled'],
        ['Runs Conceded', $runsConceded, 'Bowling runs conceded'],
        ['Maidens', $maidens, 'Maiden overs'],
        ['Innings', $bowlingInnings, 'Innings bowled'],
    ];
@endphp

<style>
    .player-page {
        --npl-navy: #101a31;
        --npl-red: #e52d3a;
        --npl-muted: #778196;
        padding: 28px 0 56px;
        color: #20283a;
        background: #f5f7fb;
    }

    .player-page,
    .player-page * {
        box-sizing: border-box;
    }

    .player-page .player-container {
        width: min(1180px, calc(100% - 36px));
        margin: 0 auto;
    }

    .player-page a {
        color: inherit;
        text-decoration: none;
    }

    .player-back {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 22px;
        color: #586277 !important;
        font-size: 13px;
        font-weight: 700;
    }

    .player-back:hover {
        color: var(--npl-red) !important;
    }

    /* NPL player card */
    .npl-player-card {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 300px;
        min-height: 365px;
        overflow: hidden;
        margin-bottom: 20px;
        border: 1px solid #e1e6ef;
        border-radius: 17px;
        background: #fff;
        box-shadow: 0 12px 34px rgba(16, 26, 49, .08);
    }

    .npl-card-main {
        position: relative;
        z-index: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-width: 0;
        padding: 28px 32px 22px;
        overflow: hidden;
        color: #fff;
        background:
            radial-gradient(ellipse at 90% 10%, rgba(229, 45, 58, .24), transparent 38%),
            linear-gradient(125deg, #101a31, #172746 68%, #263750);
    }

    .npl-card-main::after {
        position: absolute;
        right: -140px;
        bottom: -230px;
        width: 420px;
        height: 420px;
        border: 42px solid rgba(255, 255, 255, .045);
        border-radius: 50%;
        content: "";
        pointer-events: none;
    }

    .npl-card-top,
    .npl-card-player,
    .npl-card-footer {
        position: relative;
        z-index: 2;
    }

    .npl-card-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
    }

    .npl-brand {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }

    .npl-brand-logo {
        width: 52px;
        height: 52px;
        flex: 0 0 52px;
        padding: 4px;
        border-radius: 11px;
        background: #fff;
        object-fit: contain;
    }

    .npl-brand-title {
        margin: 0;
        color: #fff;
        font-size: 15px;
        font-weight: 900;
        letter-spacing: 1.2px;
    }

    .npl-brand-subtitle {
        margin-top: 5px;
        color: #b9c6da;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 2px;
    }

    .npl-season-tag {
        padding: 8px 10px;
        border: 1px solid rgba(255, 255, 255, .24);
        border-radius: 5px;
        color: #fff;
        font-size: 10px;
        font-weight: 900;
        letter-spacing: 1px;
        white-space: nowrap;
    }

    .npl-card-player {
        padding: 36px 0 28px;
    }

    .npl-card-eyebrow {
        margin-bottom: 10px;
        color: #ff727d;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: 2.5px;
        text-transform: uppercase;
    }

    .npl-card-name {
        max-width: 680px;
        margin: 0;
        color: #fff;
        font-size: clamp(30px, 4.2vw, 53px);
        font-weight: 900;
        letter-spacing: -1.5px;
        line-height: 1.06;
        overflow-wrap: anywhere;
        text-transform: uppercase;
    }

    .npl-card-role {
        display: inline-flex;
        align-items: center;
        margin-top: 17px;
        padding: 9px 12px;
        border-radius: 5px;
        background: var(--npl-red);
        color: #fff;
        font-size: 10px;
        font-weight: 900;
        letter-spacing: 1.1px;
        text-transform: uppercase;
    }

    .npl-card-footer {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 9px 20px;
        padding-top: 17px;
        border-top: 1px solid rgba(255, 255, 255, .15);
        color: #c7d1e0;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .8px;
        text-transform: uppercase;
    }

    .npl-card-footer span {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .npl-card-footer span::before {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #ff6973;
        content: "";
    }

    .npl-card-portrait {
        position: relative;
        display: flex;
        align-items: stretch;
        justify-content: center;
        min-width: 0;
        overflow: hidden;
        background:
            radial-gradient(ellipse at 50% 25%, #fff, #eef1f6 62%, #dce2eb);
    }

    .npl-card-portrait::before {
        position: absolute;
        top: 34px;
        left: 50%;
        width: 230px;
        height: 230px;
        border: 1px solid rgba(16, 26, 49, .1);
        border-radius: 50%;
        content: "";
        transform: translateX(-50%);
    }

    .npl-player-photo {
        position: relative;
        z-index: 1;
        display: block;
        width: 100%;
        height: 100%;
        min-height: 365px;
        max-height: 430px;
        object-fit: cover;
        object-position: center top;
    }

    .npl-player-fallback {
        position: relative;
        z-index: 1;
        align-self: center;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 160px;
        height: 160px;
        border: 8px solid rgba(255, 255, 255, .8);
        border-radius: 50%;
        background: var(--npl-navy);
        color: #fff;
        font-size: 68px;
        font-weight: 900;
        box-shadow: 0 15px 35px rgba(16, 26, 49, .18);
    }

    .npl-card-number {
        position: absolute;
        right: 14px;
        bottom: 14px;
        z-index: 3;
        padding: 8px 10px;
        border-radius: 5px;
        background: var(--npl-red);
        color: #fff;
        font-size: 10px;
        font-weight: 900;
        letter-spacing: 1px;
    }

    /* Quick stats */
    .npl-card-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 13px;
        margin-bottom: 34px;
    }

    .npl-mini-stat {
        min-width: 0;
        padding: 18px;
        border: 1px solid #e3e8f0;
        border-radius: 10px;
        background: #fff;
        box-shadow: 0 3px 10px rgba(16, 26, 49, .025);
        transition: transform .2s, box-shadow .2s, border-color .2s;
    }

    .npl-mini-stat:hover {
        transform: translateY(-2px);
        border-color: #f1a2a8;
        box-shadow: 0 8px 20px rgba(16, 26, 49, .06);
    }

    .npl-mini-stat-label {
        display: block;
        margin-bottom: 12px;
        color: var(--npl-muted);
        font-size: 10px;
        font-weight: 900;
        letter-spacing: 1px;
        text-transform: uppercase;
    }

    .npl-mini-stat-value {
        display: block;
        color: var(--npl-navy);
        font-size: 28px;
        font-weight: 900;
        line-height: 1.15;
        overflow-wrap: anywhere;
        font-variant-numeric: tabular-nums;
    }

    .npl-mini-stat-note {
        display: block;
        margin-top: 7px;
        color: #8a93a3;
        font-size: 11px;
    }

    /* Sections */
    .player-section {
        margin-top: 34px;
    }

    .player-section-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 16px;
    }

    .player-section-heading h2 {
        margin: 0;
        color: #172238;
        font-size: 21px;
        font-weight: 850;
        letter-spacing: -.4px;
    }

    .player-section-heading p {
        margin: 5px 0 0;
        color: #7b8495;
        font-size: 13px;
    }

    .player-count {
        padding: 7px 10px;
        border: 1px solid #e0e5ed;
        border-radius: 6px;
        background: #fff;
        color: #586277;
        font-size: 11px;
        font-weight: 800;
        white-space: nowrap;
    }

    .player-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 13px;
    }

    .player-stat {
        min-width: 0;
        padding: 18px;
        border: 1px solid #e2e7ef;
        border-radius: 9px;
        background: #fff;
        transition: border-color .2s, transform .2s;
    }

    .player-stat:hover {
        transform: translateY(-2px);
        border-color: #c9d2e0;
    }

    .player-stat-label {
        display: block;
        margin-bottom: 12px;
        color: #778196;
        font-size: 12px;
        font-weight: 650;
    }

    .player-stat-value {
        display: block;
        color: #172238;
        font-size: 26px;
        font-weight: 850;
        line-height: 1.2;
        overflow-wrap: anywhere;
        font-variant-numeric: tabular-nums;
    }

    .player-stat-note {
        display: block;
        margin-top: 7px;
        color: #929aaa;
        font-size: 11px;
        line-height: 1.5;
    }

    /* Match history */
    .player-table-panel {
        overflow: hidden;
        border: 1px solid #e0e5ed;
        border-radius: 11px;
        background: #fff;
        box-shadow: 0 5px 18px rgba(16, 26, 49, .035);
    }

    .player-table-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 18px 20px;
        border-bottom: 1px solid #e8ecf2;
        background: #fff;
    }

    .player-table-heading h3 {
        margin: 0;
        color: #172238;
        font-size: 15px;
        font-weight: 850;
    }

    .player-table-heading p {
        margin: 5px 0 0;
        color: #7b8495;
        font-size: 12px;
    }

    .player-table-wrap {
        width: 100%;
        overflow-x: auto;
    }

    .player-table {
        width: 100%;
        border-collapse: collapse;
        white-space: nowrap;
    }

    .player-table th {
        padding: 13px 15px;
        border-bottom: 1px solid #e4e8ef;
        background: #f7f9fc;
        color: #687286;
        font-size: 10px;
        font-weight: 900;
        text-align: left;
        text-transform: uppercase;
        letter-spacing: .7px;
    }

    .player-table td {
        padding: 15px;
        border-bottom: 1px solid #edf0f5;
        color: #3e485b;
        font-size: 12px;
        vertical-align: middle;
    }

    .player-table tbody tr:last-child td {
        border-bottom: none;
    }

    .player-table tbody tr:hover {
        background: #fafbfe;
    }

    .player-match-link {
        color: #d92332 !important;
        font-weight: 850;
    }

    .player-match-link:hover {
        text-decoration: underline;
    }

    .player-team {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .player-team-logo {
        width: 29px;
        height: 29px;
        border: 1px solid #e8ebf1;
        border-radius: 50%;
        object-fit: contain;
        background: #fff;
    }

    .player-highlight {
        color: #d92332;
        font-weight: 900;
    }

    .player-secondary {
        margin-top: 5px;
        color: #8a93a3;
        font-size: 10px;
        line-height: 1.7;
    }

    .player-opponent {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .player-opponent-name {
        color: #293449;
        font-weight: 750;
    }

    .player-score {
        display: inline-block;
        padding: 6px 8px;
        border-radius: 5px;
        background: #f0f3f8;
        color: #172238;
        font-size: 12px;
        font-weight: 900;
        font-variant-numeric: tabular-nums;
    }

    .player-score-unavailable {
        color: #929aaa;
        font-size: 11px;
    }

    .player-empty {
        padding: 42px 18px;
        text-align: center;
    }

    .player-empty-mark {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 48px;
        height: 48px;
        margin: 0 auto 13px;
        border-radius: 50%;
        background: #f0f3f8;
        color: #69758a;
        font-size: 22px;
        font-weight: 900;
    }

    .player-empty h3 {
        margin: 0 0 8px;
        color: #202b40;
        font-size: 16px;
        font-weight: 850;
    }

    .player-empty p {
        max-width: 420px;
        margin: 0 auto;
        color: #7b8495;
        font-size: 12px;
        line-height: 1.8;
    }

    .player-note {
        margin-top: 13px;
        color: #8a93a3;
        font-size: 11px;
        line-height: 1.7;
    }

    @media (max-width: 900px) {
        .npl-player-card {
            grid-template-columns: minmax(0, 1fr) 240px;
        }

        .npl-card-main {
            padding: 24px;
        }

        .npl-card-stats,
        .player-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 620px) {
        .player-page {
            padding-top: 18px;
        }

        .player-page .player-container {
            width: calc(100% - 24px);
        }

        .npl-player-card {
            grid-template-columns: minmax(0, 1fr);
        }

        .npl-card-main {
            min-height: 300px;
            padding: 21px 18px;
        }

        .npl-card-portrait {
            min-height: 270px;
            max-height: 340px;
        }

        .npl-player-photo {
            min-height: 270px;
            max-height: 340px;
            object-position: center 25%;
        }

        .npl-card-portrait::before {
            top: 25px;
            width: 180px;
            height: 180px;
        }

        .npl-player-fallback {
            width: 125px;
            height: 125px;
            font-size: 52px;
        }

        .npl-card-player {
            padding: 30px 0;
        }

        .npl-card-name {
            font-size: clamp(28px, 8vw, 42px);
        }

        .npl-card-stats {
            gap: 10px;
        }

        .npl-mini-stat,
        .player-stat {
            padding: 15px;
        }

        .npl-mini-stat-value,
        .player-stat-value {
            font-size: 23px;
        }

        .player-section-heading h2 {
            font-size: 19px;
        }
    }

    @media (max-width: 380px) {
        .npl-card-top {
            align-items: flex-start;
        }

        .npl-brand {
            gap: 8px;
        }

        .npl-brand-logo {
            width: 38px;
            height: 38px;
            flex-basis: 38px;
        }

        .npl-brand-title {
            font-size: 11px;
            letter-spacing: .5px;
        }

        .npl-brand-subtitle {
            font-size: 8px;
            letter-spacing: 1px;
        }

        .npl-season-tag {
            padding: 7px;
            font-size: 8px;
        }

        .npl-card-main {
            padding: 17px 14px;
        }

        .npl-card-stats,
        .player-stats {
            gap: 8px;
        }

        .npl-mini-stat,
        .player-stat {
            padding: 12px;
        }

        .npl-mini-stat-value,
        .player-stat-value {
            font-size: 21px;
        }

        .player-section-heading {
            align-items: flex-start;
        }
    }
</style>

<div class="player-page">
    <div class="player-container">

        <a href="{{ url()->previous() }}" class="player-back">
            <span aria-hidden="true">←</span>
            <span>Back</span>
        </a>

        {{-- NPL collectible-style player card --}}
        <section class="npl-player-card">

            <div class="npl-card-main">
                <div class="npl-card-top">
                    <div class="npl-brand">
                        <img
                            src="{{ $nplLogo }}"
                            alt="Nepal Premier League"
                            class="npl-brand-logo"
                            onerror="this.style.display='none'"
                        >

                        <div>
                            <p class="npl-brand-title">NEPAL PREMIER LEAGUE</p>
                            <div class="npl-brand-subtitle">OFFICIAL PLAYER PROFILE</div>
                        </div>
                    </div>

                    <span class="npl-season-tag">SEASON 03</span>
                </div>

                <div class="npl-card-player">
                    <div class="npl-card-eyebrow">The player card</div>

                    <h1 class="npl-card-name">{{ $playerName }}</h1>

                    <div class="npl-card-role">{{ $playerRole }}</div>
                </div>

                <div class="npl-card-footer">
                    <span>{{ $playerTeamName }}</span>
                    <span>Nepal Premier League</span>
                </div>
            </div>

            <div class="npl-card-portrait">
                @if ($playerPhotoUrl)
                    <img
                        src="{{ $playerPhotoUrl }}"
                        alt="{{ $playerName }}"
                        class="npl-player-photo"
                        onerror="this.onerror=null;this.style.display='none';this.nextElementSibling.style.display='flex';"
                    >
                @endif

                <div
                    class="npl-player-fallback"
                    @if ($playerPhotoUrl) style="display:none" @endif
                >
                    {{ $playerInitial }}
                </div>

                <div class="npl-card-number">NPL · S03</div>
            </div>
        </section>

        {{-- Featured season statistics --}}
        <section class="npl-card-stats" aria-label="Player highlights">
            <div class="npl-mini-stat">
                <span class="npl-mini-stat-label">Total Runs</span>
                <strong class="npl-mini-stat-value">{{ $totalRuns }}</strong>
                <span class="npl-mini-stat-note">Runs scored</span>
            </div>

            <div class="npl-mini-stat">
                <span class="npl-mini-stat-label">Highest Score</span>
                <strong class="npl-mini-stat-value">{{ $highestScore }}</strong>
                <span class="npl-mini-stat-note">Best individual innings</span>
            </div>

            <div class="npl-mini-stat">
                <span class="npl-mini-stat-label">Wickets</span>
                <strong class="npl-mini-stat-value">{{ $totalWickets }}</strong>
                <span class="npl-mini-stat-note">Wickets taken</span>
            </div>

            <div class="npl-mini-stat">
                <span class="npl-mini-stat-label">Strike Rate</span>
                <strong class="npl-mini-stat-value">{{ $formatNumber($strikeRate) }}</strong>
                <span class="npl-mini-stat-note">Runs per 100 balls</span>
            </div>
        </section>

        {{-- Batting statistics --}}
        <section class="player-section">
            <div class="player-section-heading">
                <div>
                    <h2>Batting Statistics</h2>
                    <p>Season batting performance</p>
                </div>
            </div>

            <div class="player-stats">
                @foreach ($battingCards as $stat)
                    <div class="player-stat">
                        <span class="player-stat-label">{{ $stat[0] }}</span>
                        <strong class="player-stat-value">{{ $stat[1] }}</strong>
                        <span class="player-stat-note">{{ $stat[2] }}</span>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Bowling statistics --}}
        <section class="player-section">
            <div class="player-section-heading">
                <div>
                    <h2>Bowling Statistics</h2>
                    <p>Season bowling performance</p>
                </div>
            </div>

            <div class="player-stats">
                @foreach ($bowlingCards as $stat)
                    <div class="player-stat">
                        <span class="player-stat-label">{{ $stat[0] }}</span>
                        <strong class="player-stat-value">{{ $stat[1] }}</strong>
                        <span class="player-stat-note">{{ $stat[2] }}</span>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Match history --}}
        <section class="player-section">
            <div class="player-section-heading">
                <div>
                    <h2>Match History</h2>
                    <p>See the opponent and this player's batting and bowling performance.</p>
                </div>

                <span class="player-count">
                    {{ $performancesCollection->count() }}
                    {{ \Illuminate\Support\Str::plural('record', $performancesCollection->count()) }}
                </span>
            </div>

            <div class="player-table-panel">
                <div class="player-table-heading">
                    <div>
                        <h3>NPL Season 3 Matches</h3>
                        <p>Match-by-match performance and available team totals</p>
                    </div>
                </div>

                @if ($performancesCollection->isNotEmpty())
                    <div class="player-table-wrap">
                        <table class="player-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Match</th>
                                    <th>Player's Team</th>
                                    <th>Opponent</th>
                                    <th>Match Score</th>
                                    <th>Batting</th>
                                    <th>Bowling</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($performancesCollection as $performance)
                                    @php
                                        $fixture = data_get($performance, 'fixture');

                                        $fixtureId = $getValue(
                                            $performance,
                                            'fixture_id',
                                            $getValue($fixture, 'id', null)
                                        );

                                        $matchDate = $getValue(
                                            $fixture,
                                            'scheduled_at',
                                            $getValue(
                                                $fixture,
                                                'match_date',
                                                $getValue($performance, 'match_date', null)
                                            )
                                        );

                                        $battingTeam = data_get($performance, 'batting_team');
                                        $bowlingTeam = data_get($performance, 'bowling_team');

                                        $battingTeamName = $teamName($battingTeam);
                                        $battingTeamLogo = $teamLogo($battingTeam);

                                        $fixtureHome = data_get($fixture, 'homeTeam');
                                        $fixtureAway = data_get($fixture, 'awayTeam');

                                        $battingTeamId = data_get($performance, 'batting_team_id')
                                            ?? data_get($battingTeam, 'id');

                                        if (!$battingTeamId && is_string($battingTeam)) {
                                            $battingTeamId = null;
                                        }

                                        $opponent = $bowlingTeam;

                                        if (!$opponent && $battingTeamId) {
                                            if ((string) data_get($fixture, 'home_team_id') === (string) $battingTeamId) {
                                                $opponent = $fixtureAway;
                                            } elseif ((string) data_get($fixture, 'away_team_id') === (string) $battingTeamId) {
                                                $opponent = $fixtureHome;
                                            }
                                        }

                                        $opponentName = $opponent
                                            ? $teamName($opponent)
                                            : 'Opponent unavailable';

                                        $opponentLogo = $teamLogo($opponent);

                                        $batter = data_get($performance, 'batter');
                                        $bowler = data_get($performance, 'bowler');

                                        $batRuns = $getValue($batter, 'runs', 0);
                                        $batBalls = $getValue($batter, 'balls', 0);
                                        $batSR = $getValue($batter, 'strike_rate', '—');
                                        $batFours = $getValue($batter, 'fours', 0);
                                        $batSixes = $getValue($batter, 'sixes', 0);
                                        $dismissal = $getValue($batter, 'dismissal', '—');

                                        $bowlOvers = $getValue($bowler, 'overs', 0);
                                        $bowlMaidens = $getValue($bowler, 'maidens', 0);
                                        $bowlRuns = $getValue($bowler, 'runs', 0);
                                        $bowlWickets = $getValue($bowler, 'wickets', 0);
                                        $bowlEconomy = $getValue($bowler, 'economy', '—');

                                        /*
                                         * The controller may pass manual_scorecards
                                         * as a relation or loaded attribute. No live
                                         * score data is used here.
                                         */
                                        $manualCards = collect(
                                            data_get($fixture, 'manual_scorecards', [])
                                        );

                                        $homeTeamId = data_get($fixture, 'home_team_id');
                                        $awayTeamId = data_get($fixture, 'away_team_id');

                                        $homeScorecard = $manualCards->first(
                                            fn ($card) => (string) data_get($card, 'batting_team_id') === (string) $homeTeamId
                                        );

                                        $awayScorecard = $manualCards->first(
                                            fn ($card) => (string) data_get($card, 'batting_team_id') === (string) $awayTeamId
                                        );

                                        $homeScore = $homeScorecard
                                            ? data_get($homeScorecard, 'total_runs') . '/' . data_get($homeScorecard, 'wickets', 0)
                                            : null;

                                        $awayScore = $awayScorecard
                                            ? data_get($awayScorecard, 'total_runs') . '/' . data_get($awayScorecard, 'wickets', 0)
                                            : null;

                                        $homeOvers = $homeScorecard
                                            ? data_get($homeScorecard, 'overs')
                                            : null;

                                        $awayOvers = $awayScorecard
                                            ? data_get($awayScorecard, 'overs')
                                            : null;

                                        $scoreSummary = $homeScore && $awayScore
                                            ? $homeScore . ' · ' . $awayScore
                                            : null;
                                    @endphp

                                    <tr>
                                        <td>{{ $formatDate($matchDate) }}</td>

                                        <td>
                                            @if ($fixtureId && \Illuminate\Support\Facades\Route::has('fixtures.show'))
                                                <a
                                                    href="{{ route('fixtures.show', $fixtureId) }}"
                                                    class="player-match-link"
                                                >
                                                    Match #{{ $fixtureId }}
                                                </a>
                                            @else
                                                Match #{{ $fixtureId ?? '—' }}
                                            @endif
                                        </td>

                                        <td>
                                            <div class="player-team">
                                                @if ($battingTeamLogo)
                                                    <img
                                                        src="{{ $battingTeamLogo }}"
                                                        alt="{{ $battingTeamName }}"
                                                        class="player-team-logo"
                                                        loading="lazy"
                                                    >
                                                @endif
                                                <span>{{ $battingTeamName }}</span>
                                            </div>
                                        </td>

                                        <td>
                                            <div class="player-opponent">
                                                @if ($opponentLogo)
                                                    <img
                                                        src="{{ $opponentLogo }}"
                                                        alt="{{ $opponentName }}"
                                                        class="player-team-logo"
                                                        loading="lazy"
                                                    >
                                                @endif
                                                <span class="player-opponent-name">{{ $opponentName }}</span>
                                            </div>
                                        </td>

                                        <td>
                                            @if ($scoreSummary)
                                                <span class="player-score">{{ $scoreSummary }}</span>
                                                <div class="player-secondary">
                                                    {{ $homeOvers !== null ? $homeOvers . ' ov' : '' }}
                                                    {{ $homeOvers !== null && $awayOvers !== null ? ' · ' : '' }}
                                                    {{ $awayOvers !== null ? $awayOvers . ' ov' : '' }}
                                                </div>
                                            @else
                                                <span class="player-score-unavailable">Score unavailable</span>
                                            @endif
                                        </td>

                                        <td>
                                            <span class="player-highlight">{{ $batRuns }}</span>
                                            <span> off {{ $batBalls }} balls</span>

                                            <div class="player-secondary">
                                                SR {{ $formatNumber($batSR) }}
                                                · 4s {{ $batFours }}
                                                · 6s {{ $batSixes }}
                                            </div>

                                            @if ($dismissal !== '—')
                                                <div class="player-secondary">{{ $dismissal }}</div>
                                            @endif
                                        </td>

                                        <td>
                                            <span class="player-highlight">{{ $bowlWickets }} W</span>
                                            <span> · {{ $bowlRuns }} runs</span>

                                            <div class="player-secondary">
                                                {{ $bowlOvers }} overs
                                                · {{ $bowlMaidens }} maidens
                                                · ECO {{ $formatNumber($bowlEconomy) }}
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="player-empty">
                        <div class="player-empty-mark">↗</div>
                        <h3>No match records available</h3>
                        <p>
                            Individual match performances will appear here when
                            records are available for this player.
                        </p>
                    </div>
                @endif
            </div>

            <p class="player-note">
                Player statistics are based on application records. Match totals
                are displayed only when manual scorecard data is available.
            </p>
        </section>

    </div>
</div>
@endsection
