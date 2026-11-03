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
    /* ─────────────────────────────────────────────
       NPL Player Performance – refined design system
       ───────────────────────────────────────────── */
    .player-page {
        --npl-navy: #0b1424;
        --npl-navy-mid: #152238;
        --npl-red: #e11d2e;
        --npl-red-soft: #ff4d5e;
        --npl-muted: #6b7a90;
        --npl-border: #e2e8f0;
        --npl-surface: #ffffff;
        --npl-bg: #f4f6fa;
        --radius-lg: 18px;
        --radius-md: 12px;
        --radius-sm: 8px;
        --shadow-sm: 0 1px 3px rgba(11, 20, 36, 0.04);
        --shadow-md: 0 8px 24px rgba(11, 20, 36, 0.07);
        --shadow-lg: 0 20px 48px rgba(11, 20, 36, 0.1);

        padding: 32px 0 72px;
        color: #1a2332;
        background: var(--npl-bg);
        font-feature-settings: "tnum" 1;
    }

    .player-page,
    .player-page * {
        box-sizing: border-box;
    }

    .player-page .player-container {
        width: min(1160px, calc(100% - 40px));
        margin: 0 auto;
    }

    .player-page a {
        color: inherit;
        text-decoration: none;
    }

    /* ── Back link ── */
    .player-back {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 24px;
        color: var(--npl-muted) !important;
        font-size: 13px;
        font-weight: 600;
        letter-spacing: 0.01em;
        transition: color 0.15s ease;
    }

    .player-back:hover {
        color: var(--npl-red) !important;
    }

    /* ── Hero player card ── */
    .npl-player-card {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 290px;
        min-height: 380px;
        overflow: hidden;
        margin-bottom: 28px;
        border-radius: var(--radius-lg);
        background: var(--npl-surface);
        box-shadow: var(--shadow-lg);
        border: 1px solid rgba(11, 20, 36, 0.06);
    }

    .npl-card-main {
        position: relative;
        z-index: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-width: 0;
        padding: 32px 36px 26px;
        overflow: hidden;
        color: #fff;
        background:
            radial-gradient(ellipse 80% 60% at 92% 8%, rgba(225, 29, 46, 0.28), transparent 50%),
            linear-gradient(145deg, #0b1424 0%, #121f35 55%, #1a2d4a 100%);
    }

    .npl-card-main::before {
        content: "";
        position: absolute;
        inset: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.025'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        opacity: 0.7;
        pointer-events: none;
    }

    .npl-card-main::after {
        position: absolute;
        right: -160px;
        bottom: -200px;
        width: 440px;
        height: 440px;
        border: 48px solid rgba(255, 255, 255, 0.04);
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
        gap: 16px;
    }

    .npl-brand {
        display: flex;
        align-items: center;
        gap: 14px;
        min-width: 0;
    }

    .npl-brand-logo {
        width: 50px;
        height: 50px;
        flex: 0 0 50px;
        padding: 5px;
        border-radius: 12px;
        background: #fff;
        object-fit: contain;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .npl-brand-title {
        margin: 0;
        color: #fff;
        font-size: 14px;
        font-weight: 800;
        letter-spacing: 1.4px;
        line-height: 1.2;
    }

    .npl-brand-subtitle {
        margin-top: 4px;
        color: rgba(185, 198, 218, 0.9);
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 1.8px;
        text-transform: uppercase;
    }

    .npl-season-tag {
        padding: 7px 12px;
        border: 1px solid rgba(255, 255, 255, 0.22);
        border-radius: 6px;
        background: rgba(255, 255, 255, 0.06);
        color: #fff;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 1.2px;
        white-space: nowrap;
        backdrop-filter: blur(4px);
    }

    .npl-card-player {
        padding: 40px 0 32px;
    }

    .npl-card-eyebrow {
        margin-bottom: 12px;
        color: var(--npl-red-soft);
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 2.2px;
        text-transform: uppercase;
    }

    .npl-card-name {
        max-width: 640px;
        margin: 0;
        color: #fff;
        font-size: clamp(28px, 4vw, 48px);
        font-weight: 900;
        letter-spacing: -1.2px;
        line-height: 1.08;
        overflow-wrap: anywhere;
        text-transform: uppercase;
    }

    .npl-card-role {
        display: inline-flex;
        align-items: center;
        margin-top: 18px;
        padding: 8px 14px;
        border-radius: 6px;
        background: var(--npl-red);
        color: #fff;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 1px;
        text-transform: uppercase;
        box-shadow: 0 4px 14px rgba(225, 29, 46, 0.35);
    }

    .npl-card-footer {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 10px 24px;
        padding-top: 18px;
        border-top: 1px solid rgba(255, 255, 255, 0.12);
        color: rgba(199, 209, 224, 0.95);
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.6px;
        text-transform: uppercase;
    }

    .npl-card-footer span {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .npl-card-footer span::before {
        width: 5px;
        height: 5px;
        border-radius: 50%;
        background: var(--npl-red-soft);
        content: "";
        flex-shrink: 0;
    }

    /* Portrait side */
    .npl-card-portrait {
        position: relative;
        display: flex;
        align-items: stretch;
        justify-content: center;
        min-width: 0;
        overflow: hidden;
        background:
            radial-gradient(ellipse at 50% 20%, #ffffff 0%, #eef2f7 55%, #d8e0eb 100%);
    }

    .npl-card-portrait::before {
        position: absolute;
        top: 28px;
        left: 50%;
        width: 220px;
        height: 220px;
        border: 1px solid rgba(11, 20, 36, 0.08);
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
        min-height: 380px;
        max-height: 440px;
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
        width: 150px;
        height: 150px;
        border: 6px solid rgba(255, 255, 255, 0.85);
        border-radius: 50%;
        background: var(--npl-navy);
        color: #fff;
        font-size: 64px;
        font-weight: 900;
        box-shadow: 0 12px 32px rgba(11, 20, 36, 0.2);
    }

    .npl-card-number {
        position: absolute;
        right: 14px;
        bottom: 14px;
        z-index: 3;
        padding: 7px 11px;
        border-radius: 6px;
        background: var(--npl-red);
        color: #fff;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 1px;
        box-shadow: 0 4px 12px rgba(225, 29, 46, 0.4);
    }

    /* ── Quick highlight stats ── */
    .npl-card-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 40px;
    }

    .npl-mini-stat {
        min-width: 0;
        padding: 20px 18px;
        border: 1px solid var(--npl-border);
        border-radius: var(--radius-md);
        background: var(--npl-surface);
        box-shadow: var(--shadow-sm);
        transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
    }

    .npl-mini-stat:hover {
        transform: translateY(-3px);
        border-color: #f5b0b6;
        box-shadow: var(--shadow-md);
    }

    .npl-mini-stat-label {
        display: block;
        margin-bottom: 10px;
        color: var(--npl-muted);
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.8px;
        text-transform: uppercase;
    }

    .npl-mini-stat-value {
        display: block;
        color: var(--npl-navy);
        font-size: 28px;
        font-weight: 800;
        line-height: 1.15;
        overflow-wrap: anywhere;
        letter-spacing: -0.5px;
    }

    .npl-mini-stat-note {
        display: block;
        margin-top: 6px;
        color: #8a94a6;
        font-size: 12px;
        line-height: 1.4;
    }

    /* ── Section blocks ── */
    .player-section {
        margin-top: 44px;
    }

    .player-section-heading {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 18px;
    }

    .player-section-heading h2 {
        margin: 0;
        color: #0f1a2c;
        font-size: 22px;
        font-weight: 800;
        letter-spacing: -0.4px;
        line-height: 1.25;
    }

    .player-section-heading p {
        margin: 6px 0 0;
        color: var(--npl-muted);
        font-size: 14px;
        line-height: 1.5;
    }

    .player-count {
        padding: 7px 12px;
        border: 1px solid var(--npl-border);
        border-radius: 8px;
        background: var(--npl-surface);
        color: #4a5568;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
        box-shadow: var(--shadow-sm);
    }

    /* Stats grid */
    .player-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
    }

    .player-stat {
        min-width: 0;
        padding: 20px 18px;
        border: 1px solid var(--npl-border);
        border-radius: var(--radius-md);
        background: var(--npl-surface);
        transition: border-color 0.18s ease, transform 0.18s ease, box-shadow 0.18s ease;
    }

    .player-stat:hover {
        transform: translateY(-2px);
        border-color: #cdd5e0;
        box-shadow: var(--shadow-sm);
    }

    .player-stat-label {
        display: block;
        margin-bottom: 10px;
        color: var(--npl-muted);
        font-size: 12px;
        font-weight: 600;
    }

    .player-stat-value {
        display: block;
        color: #0f1a2c;
        font-size: 26px;
        font-weight: 800;
        line-height: 1.2;
        overflow-wrap: anywhere;
        letter-spacing: -0.4px;
    }

    .player-stat-note {
        display: block;
        margin-top: 6px;
        color: #8a94a6;
        font-size: 12px;
        line-height: 1.45;
    }

    /* ── Match history table ── */
    .player-table-panel {
        overflow: hidden;
        border: 1px solid var(--npl-border);
        border-radius: var(--radius-md);
        background: var(--npl-surface);
        box-shadow: var(--shadow-sm);
    }

    .player-table-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 20px 22px;
        border-bottom: 1px solid #edf1f6;
        background: #fafbfc;
    }

    .player-table-heading h3 {
        margin: 0;
        color: #0f1a2c;
        font-size: 15px;
        font-weight: 750;
    }

    .player-table-heading p {
        margin: 5px 0 0;
        color: var(--npl-muted);
        font-size: 13px;
    }

    .player-table-wrap {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .player-table {
        width: 100%;
        border-collapse: collapse;
        white-space: nowrap;
    }

    .player-table th {
        padding: 14px 16px;
        border-bottom: 1px solid #e8ecf2;
        background: #f7f9fc;
        color: #5a6578;
        font-size: 11px;
        font-weight: 700;
        text-align: left;
        text-transform: uppercase;
        letter-spacing: 0.6px;
    }

    .player-table td {
        padding: 16px;
        border-bottom: 1px solid #f0f3f7;
        color: #2d3748;
        font-size: 13px;
        vertical-align: middle;
    }

    .player-table tbody tr:last-child td {
        border-bottom: none;
    }

    .player-table tbody tr:hover {
        background: #f8fafc;
    }

    .player-match-link {
        color: var(--npl-red) !important;
        font-weight: 700;
        transition: opacity 0.15s ease;
    }

    .player-match-link:hover {
        opacity: 0.8;
        text-decoration: underline;
    }

    .player-team {
        display: inline-flex;
        align-items: center;
        gap: 9px;
    }

    .player-team-logo {
        width: 28px;
        height: 28px;
        border: 1px solid #e8ebf1;
        border-radius: 50%;
        object-fit: contain;
        background: #fff;
        flex-shrink: 0;
    }

    .player-highlight {
        color: var(--npl-red);
        font-weight: 800;
    }

    .player-secondary {
        margin-top: 4px;
        color: #8a94a6;
        font-size: 11px;
        line-height: 1.55;
    }

    .player-opponent {
        display: flex;
        align-items: center;
        gap: 9px;
    }

    .player-opponent-name {
        color: #1a2332;
        font-weight: 650;
    }

    .player-score {
        display: inline-block;
        padding: 5px 9px;
        border-radius: 6px;
        background: #f0f3f8;
        color: #0f1a2c;
        font-size: 12px;
        font-weight: 750;
    }

    .player-score-unavailable {
        color: #8a94a6;
        font-size: 12px;
    }

    /* Empty state */
    .player-empty {
        padding: 56px 24px;
        text-align: center;
    }

    .player-empty-mark {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 52px;
        height: 52px;
        margin: 0 auto 16px;
        border-radius: 50%;
        background: #f0f3f8;
        color: #6b7a90;
        font-size: 22px;
        font-weight: 800;
    }

    .player-empty h3 {
        margin: 0 0 8px;
        color: #1a2332;
        font-size: 17px;
        font-weight: 750;
    }

    .player-empty p {
        max-width: 400px;
        margin: 0 auto;
        color: var(--npl-muted);
        font-size: 14px;
        line-height: 1.65;
    }

    .player-note {
        margin-top: 16px;
        color: #8a94a6;
        font-size: 12px;
        line-height: 1.6;
    }

    /* ── Responsive ── */
    @media (max-width: 960px) {
        .npl-player-card {
            grid-template-columns: minmax(0, 1fr) 250px;
        }

        .npl-card-main {
            padding: 28px 26px;
        }

        .npl-card-stats,
        .player-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 640px) {
        .player-page {
            padding-top: 20px;
            padding-bottom: 56px;
        }

        .player-page .player-container {
            width: calc(100% - 28px);
        }

        .npl-player-card {
            grid-template-columns: 1fr;
        }

        .npl-card-main {
            min-height: 300px;
            padding: 24px 20px;
        }

        .npl-card-portrait {
            min-height: 280px;
            max-height: 340px;
        }

        .npl-player-photo {
            min-height: 280px;
            max-height: 340px;
            object-position: center 22%;
        }

        .npl-card-portrait::before {
            top: 22px;
            width: 180px;
            height: 180px;
        }

        .npl-player-fallback {
            width: 120px;
            height: 120px;
            font-size: 50px;
        }

        .npl-card-player {
            padding: 28px 0 24px;
        }

        .npl-card-name {
            font-size: clamp(26px, 7.5vw, 38px);
        }

        .npl-card-stats {
            gap: 12px;
            margin-bottom: 32px;
        }

        .npl-mini-stat,
        .player-stat {
            padding: 16px 14px;
        }

        .npl-mini-stat-value,
        .player-stat-value {
            font-size: 24px;
        }

        .player-section {
            margin-top: 36px;
        }

        .player-section-heading h2 {
            font-size: 19px;
        }

        .player-section-heading p {
            font-size: 13px;
        }

        .player-table-heading {
            padding: 16px 18px;
        }

        .player-table th,
        .player-table td {
            padding: 12px 14px;
        }
    }

    @media (max-width: 400px) {
        .npl-card-top {
            align-items: flex-start;
            flex-wrap: wrap;
        }

        .npl-brand {
            gap: 10px;
        }

        .npl-brand-logo {
            width: 42px;
            height: 42px;
            flex-basis: 42px;
        }

        .npl-brand-title {
            font-size: 12px;
            letter-spacing: 0.8px;
        }

        .npl-brand-subtitle {
            font-size: 9px;
            letter-spacing: 1.2px;
        }

        .npl-season-tag {
            padding: 6px 9px;
            font-size: 9px;
        }

        .npl-card-main {
            padding: 20px 16px;
        }

        .npl-card-stats,
        .player-stats {
            gap: 10px;
        }

        .npl-mini-stat,
        .player-stat {
            padding: 14px 12px;
        }

        .npl-mini-stat-value,
        .player-stat-value {
            font-size: 22px;
        }

        .player-section-heading {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
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