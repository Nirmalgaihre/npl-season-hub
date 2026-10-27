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
        return $getValue($team, 'short_name', $getValue($team, 'name', 'Team'));
    };

    $teamLogo = function ($team) use ($getValue) {
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

    $battingCards = [
        ['Runs', $totalRuns, 'Total runs scored'],
        ['Highest Score', $highestScore, 'Best individual score'],
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
        padding: 28px 0 50px;
        color: #222;
        background: #fff;
    }

    .player-page,
    .player-page * {
        box-sizing: border-box;
    }

    .player-page .player-container {
        width: min(1120px, calc(100% - 32px));
        margin: 0 auto;
    }

    .player-page a {
        color: inherit;
        text-decoration: none;
    }

    .player-back {
        display: inline-block;
        margin-bottom: 22px;
        color: #555 !important;
        font-size: 14px;
    }

    .player-back:hover {
        color: #dc2626 !important;
    }

    .player-profile {
        display: flex;
        align-items: center;
        gap: 20px;
        padding: 24px 0;
        margin-bottom: 32px;
        border-bottom: 1px solid #e5e7eb;
    }

    .player-photo,
    .player-initial {
        width: 96px;
        height: 96px;
        flex: 0 0 96px;
        border-radius: 50%;
        object-fit: cover;
        background: #f3f4f6;
    }

    .player-initial {
        display: flex;
        align-items: center;
        justify-content: center;
        color: #dc2626;
        font-size: 32px;
        font-weight: 700;
    }

    .player-label {
        margin-bottom: 7px;
        color: #dc2626;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 1px;
        text-transform: uppercase;
    }

    .player-profile h1 {
        margin: 0 0 8px;
        color: #171717;
        font-size: clamp(26px, 4vw, 36px);
        font-weight: 750;
        line-height: 1.2;
        overflow-wrap: anywhere;
    }

    .player-description {
        margin: 0;
        color: #737373;
        font-size: 14px;
        line-height: 1.6;
    }

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
        color: #171717;
        font-size: 21px;
        font-weight: 750;
    }

    .player-section-heading p {
        margin: 5px 0 0;
        color: #737373;
        font-size: 13px;
    }

    .player-count {
        padding: 6px 10px;
        border: 1px solid #e5e7eb;
        border-radius: 5px;
        color: #555;
        font-size: 12px;
        white-space: nowrap;
    }

    .player-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
    }

    .player-stat {
        min-width: 0;
        padding: 18px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        background: #fff;
    }

    .player-stat-label {
        display: block;
        margin-bottom: 12px;
        color: #737373;
        font-size: 13px;
    }

    .player-stat-value {
        display: block;
        color: #171717;
        font-size: 27px;
        font-weight: 750;
        line-height: 1.2;
        overflow-wrap: anywhere;
        font-variant-numeric: tabular-nums;
    }

    .player-stat-note {
        display: block;
        margin-top: 7px;
        color: #999;
        font-size: 12px;
    }

    .player-table-panel {
        overflow: hidden;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
    }

    .player-table-heading {
        padding: 16px 18px;
        border-bottom: 1px solid #e5e7eb;
        background: #fafafa;
    }

    .player-table-heading h3 {
        margin: 0;
        color: #222;
        font-size: 15px;
        font-weight: 700;
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
        border-bottom: 1px solid #e5e7eb;
        background: #fafafa;
        color: #666;
        font-size: 11px;
        font-weight: 700;
        text-align: left;
        text-transform: uppercase;
    }

    .player-table td {
        padding: 15px;
        border-bottom: 1px solid #f0f0f0;
        color: #404040;
        font-size: 13px;
        vertical-align: middle;
    }

    .player-table tbody tr:last-child td {
        border-bottom: none;
    }

    .player-table tbody tr:hover {
        background: #fafafa;
    }

    .player-match-link {
        color: #dc2626 !important;
        font-weight: 700;
    }

    .player-team {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .player-team-logo {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        object-fit: contain;
        background: #f5f5f5;
    }

    .player-highlight {
        color: #dc2626;
        font-weight: 750;
    }

    .player-secondary {
        margin-top: 6px;
        color: #888;
        font-size: 11px;
        line-height: 1.7;
    }

    .player-empty {
        padding: 38px 18px;
        text-align: center;
    }

    .player-empty h3 {
        margin: 0 0 8px;
        color: #222;
        font-size: 16px;
    }

    .player-empty p {
        margin: 0;
        color: #888;
        font-size: 13px;
        line-height: 1.7;
    }

    .player-note {
        margin-top: 15px;
        color: #888;
        font-size: 12px;
        line-height: 1.7;
    }

    @media (max-width: 800px) {
        .player-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 480px) {
        .player-page {
            padding-top: 18px;
        }

        .player-page .player-container {
            width: calc(100% - 24px);
        }

        .player-profile {
            align-items: flex-start;
            gap: 14px;
            padding: 16px 0 22px;
        }

        .player-photo,
        .player-initial {
            width: 68px;
            height: 68px;
            flex-basis: 68px;
        }

        .player-initial {
            font-size: 25px;
        }

        .player-profile h1 {
            font-size: 25px;
        }

        .player-description {
            font-size: 12px;
        }

        .player-stats {
            gap: 10px;
        }

        .player-stat {
            padding: 14px;
        }

        .player-stat-value {
            font-size: 23px;
        }

        .player-section-heading h2 {
            font-size: 18px;
        }
    }
</style>

<div class="player-page">
    <div class="player-container">

        <a href="{{ url()->previous() }}" class="player-back">
            &larr; Back
        </a>

        {{-- Player profile --}}
        <section class="player-profile">
            @if ($playerPhotoUrl)
                <img
                    src="{{ $playerPhotoUrl }}"
                    alt="{{ $playerName }}"
                    class="player-photo"
                    onerror="this.onerror=null;this.replaceWith(Object.assign(document.createElement('div'),{className:'player-initial',textContent:@js(strtoupper(mb_substr($playerName,0,1)))}))"
                >
            @else
                <div class="player-initial">
                    {{ strtoupper(mb_substr($playerName, 0, 1)) }}
                </div>
            @endif

            <div>
                <div class="player-label">NPL Season 3</div>
                <h1>{{ $playerName }}</h1>
                <p class="player-description">
                    Player profile, season statistics and match performances.
                </p>
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
                    <p>Individual match-by-match performance</p>
                </div>

                <span class="player-count">
                    {{ $performancesCollection->count() }} records
                </span>
            </div>

            <div class="player-table-panel">
                <div class="player-table-heading">
                    <h3>NPL Season 3 Matches</h3>
                </div>

                @if ($performancesCollection->isNotEmpty())
                    <div class="player-table-wrap">
                        <table class="player-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Match</th>
                                    <th>Team</th>
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

                                        $battingTeam = $getValue($performance, 'batting_team', null);
                                        $battingTeamName = is_string($battingTeam)
                                            ? $battingTeam
                                            : $teamName($battingTeam);

                                        $battingTeamLogo = is_string($battingTeam)
                                            ? null
                                            : $teamLogo($battingTeam);

                                        $batter = $getValue($performance, 'batter', null);
                                        $bowler = $getValue($performance, 'bowler', null);

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
                                            <span class="player-highlight">{{ $batRuns }}</span>
                                            <span> off {{ $batBalls }} balls</span>

                                            <div class="player-secondary">
                                                SR {{ $formatNumber($batSR) }}
                                                · 4s {{ $batFours }}
                                                · 6s {{ $batSixes }}
                                            </div>

                                            @if ($dismissal !== '—')
                                                <div class="player-secondary">
                                                    {{ $dismissal }}
                                                </div>
                                            @endif
                                        </td>

                                        <td>
                                            <span class="player-highlight">
                                                {{ $bowlWickets }} W
                                            </span>
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
                        <h3>No match records available</h3>
                        <p>
                            Match performances will appear here when records
                            are available for this player.
                        </p>
                    </div>
                @endif
            </div>

            <p class="player-note">
                Statistics are based on the data provided by the application.
            </p>
        </section>

    </div>
</div>
@endsection