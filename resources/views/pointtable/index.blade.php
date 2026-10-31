
{{-- resources/views/pointtable/index.blade.php --}}

@extends('layouts.app')

@section('title', 'Points Table | NPL Season Hub')

@section('meta_description', 'View Nepal Premier League standings, team rankings, match records, points and net run rate.')

@section('content')
@php
    use Illuminate\Support\Str;

    /*
    |--------------------------------------------------------------------------
    | Team logo helper
    |--------------------------------------------------------------------------
    | Supports:
    | team.png
    | teams/team.png
    | public/teams/team.png
    | storage/teams/team.png
    | External image URLs
    */

    $logoUrl = function ($team) {
        $path = $team?->logo;

        if (!is_string($path) || trim($path) === '') {
            return null;
        }

        $path = trim($path);

        if (Str::startsWith($path, ['https://', 'http://', 'data:image/'])) {
            return $path;
        }

        $path = ltrim($path, '/');
        $path = preg_replace('#^(public/|storage/)#', '', $path);

        if (!Str::startsWith($path, 'teams/')) {
            $path = 'teams/' . $path;
        }

        return asset('storage/' . $path);
    };

    $formatNrr = function ($nrr) {
        if ($nrr === null || !is_numeric($nrr)) {
            return '—';
        }

        $nrr = (float) $nrr;

        return ($nrr > 0 ? '+' : '') . number_format($nrr, 3);
    };

    $teamInitials = function ($team) {
        $name = $team?->short_name ?: $team?->name ?: 'NPL';

        return Str::upper(Str::substr(trim($name), 0, 3));
    };

    $teamCount = (int) ($totalTeams ?? 0);
    $completedCount = (int) ($completedMatches ?? 0);
    $upcomingCount = (int) ($upcomingMatches ?? 0);
    $standingsCount = isset($standings) ? $standings->count() : 0;
@endphp

<div class="npl-standings-page">

    {{-- =========================================================
         HERO
    ========================================================== --}}
    <section class="npl-standings-hero">
        <div class="npl-standings-hero-pattern"></div>

        <div class="npl-standings-container npl-standings-hero-inner">

            <div class="npl-standings-hero-copy">
                <div class="npl-standings-eyebrow">
                    <span class="npl-standings-live-dot"></span>
                    NEPAL PREMIER LEAGUE
                </div>

                <h1>
                    The race<br>
                    <span>for the top.</span>
                </h1>

                <p>
                    Every point matters. Follow the standings, track each
                    team's progress and see who's climbing the NPL table.
                </p>

                <div class="npl-standings-hero-meta">
                    <span>
                        <i class="fa-solid fa-trophy"></i>
                        League standings
                    </span>

                    <span class="npl-standings-meta-divider"></span>

                    <span>
                        <i class="fa-solid fa-chart-line"></i>
                        Team performance
                    </span>
                </div>
            </div>

            <div class="npl-standings-hero-art" aria-hidden="true">
                <div class="npl-standings-orbit npl-standings-orbit-one"></div>
                <div class="npl-standings-orbit npl-standings-orbit-two"></div>

                <div class="npl-standings-trophy">
                    <i class="fa-solid fa-trophy"></i>
                </div>

                <span class="npl-standings-art-title">THE TABLE</span>
                <span class="npl-standings-art-subtitle">
                    ONE LEAGUE. ONE CHAMPION.
                </span>
            </div>
        </div>

        <div class="npl-standings-hero-line"></div>
    </section>

    {{-- =========================================================
         MAIN CONTENT
    ========================================================== --}}
    <main class="npl-standings-container npl-standings-main">

        {{-- SEASON SELECTOR --}}
        <section class="npl-season-toolbar">
            <div class="npl-season-heading">
                <span class="npl-standings-section-kicker">THE COMPETITION</span>

                <h2>
                    Points <span>Table</span>
                </h2>

                <p>
                    Current rankings and the numbers behind the race.
                </p>
            </div>

            @if ($seasons->isNotEmpty())
                <form
                    action="{{ route('pointtable.index') }}"
                    method="GET"
                    class="npl-season-form"
                >
                    <label for="season" class="npl-season-label">
                        <i class="fa-regular fa-calendar"></i>
                        Select season
                    </label>

                    <div class="npl-season-controls">
                        <select
                            id="season"
                            name="season"
                            class="npl-season-select"
                        >
                            @foreach ($seasons as $season)
                                <option
                                    value="{{ $season->id }}"
                                    @selected(
                                        $selectedSeason &&
                                        (string) $selectedSeason->id === (string) $season->id
                                    )
                                >
                                    {{ $season->name }}
                                </option>
                            @endforeach
                        </select>

                        <button type="submit" class="npl-season-button">
                            View table
                            <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </div>
                </form>
            @endif
        </section>

        @if (!$selectedSeason)

            {{-- NO SEASON --}}
            <section class="npl-standings-empty">
                <div class="npl-standings-empty-icon">
                    <i class="fa-solid fa-calendar-xmark"></i>
                </div>

                <h2>No season available</h2>

                <p>
                    Add a season and assign participating teams and fixtures
                    to display the league standings here.
                </p>
            </section>

        @else

            {{-- =================================================
                 SEASON STRIP
            ================================================== --}}
            <section class="npl-season-strip">
                <div class="npl-season-strip-left">
                    <div class="npl-season-strip-icon">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>

                    <div>
                        <span class="npl-season-strip-label">CURRENT VIEW</span>
                        <h2>{{ $selectedSeason->name }}</h2>
                    </div>
                </div>

                <div class="npl-season-strip-right">
                    <span class="npl-season-status-dot"></span>
                    Season standings
                </div>
            </section>

            {{-- =================================================
                 SUMMARY CARDS
            ================================================== --}}
            <section class="npl-standings-stats">

                <article class="npl-stat-card">
                    <div class="npl-stat-top">
                        <span class="npl-stat-label">Participating teams</span>

                        <span class="npl-stat-icon npl-stat-icon-red">
                            <i class="fa-solid fa-people-group"></i>
                        </span>
                    </div>

                    <div class="npl-stat-value">{{ $teamCount }}</div>

                    <div class="npl-stat-foot">
                        <span class="npl-stat-foot-dot npl-dot-red"></span>
                        Teams in this season
                    </div>
                </article>

                <article class="npl-stat-card">
                    <div class="npl-stat-top">
                        <span class="npl-stat-label">Completed matches</span>

                        <span class="npl-stat-icon npl-stat-icon-green">
                            <i class="fa-solid fa-circle-check"></i>
                        </span>
                    </div>

                    <div class="npl-stat-value">{{ $completedCount }}</div>

                    <div class="npl-stat-foot">
                        <span class="npl-stat-foot-dot npl-dot-green"></span>
                        Recorded as completed
                    </div>
                </article>

                <article class="npl-stat-card">
                    <div class="npl-stat-top">
                        <span class="npl-stat-label">Remaining matches</span>

                        <span class="npl-stat-icon npl-stat-icon-blue">
                            <i class="fa-regular fa-clock"></i>
                        </span>
                    </div>

                    <div class="npl-stat-value">{{ $upcomingCount }}</div>

                    <div class="npl-stat-foot">
                        <span class="npl-stat-foot-dot npl-dot-blue"></span>
                        Upcoming or in progress
                    </div>
                </article>

                <article class="npl-stat-card">
                    <div class="npl-stat-top">
                        <span class="npl-stat-label">Teams ranked</span>

                        <span class="npl-stat-icon npl-stat-icon-purple">
                            <i class="fa-solid fa-ranking-star"></i>
                        </span>
                    </div>

                    <div class="npl-stat-value">{{ $standingsCount }}</div>

                    <div class="npl-stat-foot">
                        <span class="npl-stat-foot-dot npl-dot-purple"></span>
                        Listed in the standings
                    </div>
                </article>

            </section>

            {{-- =================================================
                 TABLE HEADING
            ================================================== --}}
            <section class="npl-table-heading">
                <div>
                    <span class="npl-standings-section-kicker">
                        LEAGUE RANKINGS
                    </span>

                    <h2>{{ $selectedSeason->name }} Standings</h2>

                    <p>
                        Teams are ranked by points, with net run rate used
                        as a tiebreaker where applicable.
                    </p>
                </div>

                <div class="npl-table-heading-badge">
                    <i class="fa-solid fa-arrows-up-down"></i>
                    <span>Live standings view</span>
                </div>
            </section>

            {{-- =================================================
                 STANDINGS TABLE
            ================================================== --}}
            <section class="npl-standings-card">

                <div class="npl-standings-card-top">
                    <div class="npl-table-title">
                        <span class="npl-table-title-mark"></span>

                        <div>
                            <h3>Team standings</h3>
                            <p>Season performance at a glance</p>
                        </div>
                    </div>

                    <div class="npl-table-team-count">
                        {{ $standingsCount }}
                        {{ $standingsCount === 1 ? 'TEAM' : 'TEAMS' }}
                    </div>
                </div>

                @if ($standings->isEmpty())

                    <div class="npl-standings-empty npl-standings-empty-inside">
                        <div class="npl-standings-empty-icon">
                            <i class="fa-solid fa-chart-column"></i>
                        </div>

                        <h2>No standings available yet</h2>

                        <p>
                            Assign teams to this season or add fixtures
                            containing the participating teams.
                        </p>
                    </div>

                @else

                    <div class="npl-standings-table-scroll">
                        <table class="npl-standings-table">
                            <thead>
                                <tr>
                                    <th class="npl-col-position">POS</th>
                                    <th class="npl-col-team">TEAM</th>
                                    <th class="npl-col-number">P</th>
                                    <th class="npl-col-number">W</th>
                                    <th class="npl-col-number">L</th>
                                    <th class="npl-col-number">T</th>
                                    <th class="npl-col-number">NR</th>
                                    <th class="npl-col-points">PTS</th>
                                    <th class="npl-col-nrr">NRR</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($standings as $index => $row)
                                    @php
                                        $team = $row['team'] ?? null;
                                        $logo = $logoUrl($team);
                                        $position = $index + 1;

                                        $played = (int) ($row['played'] ?? 0);
                                        $won = (int) ($row['won'] ?? 0);
                                        $lost = (int) ($row['lost'] ?? 0);
                                        $tied = (int) ($row['tied'] ?? 0);
                                        $noResult = (int) ($row['no_result'] ?? 0);
                                        $points = (int) ($row['points'] ?? 0);

                                        $nrr = $row['nrr'] ?? null;

                                        $penaltyPoints = (int) ($row['penalty_points'] ?? 0);

                                        $positionClass = match ($position) {
                                            1 => 'npl-position-first',
                                            2 => 'npl-position-second',
                                            3 => 'npl-position-third',
                                            default => 'npl-position-normal',
                                        };
                                    @endphp

                                    <tr @class([
                                        'npl-standings-row',
                                        'npl-standings-row-first' => $position === 1,
                                    ])>

                                        {{-- POSITION --}}
                                        <td class="npl-col-position">
                                            <div class="npl-position-cell">
                                                @if ($position === 1)
                                                    <span class="npl-position-crown">
                                                        <i class="fa-solid fa-crown"></i>
                                                    </span>
                                                @endif

                                                <span class="npl-position-number {{ $positionClass }}">
                                                    {{ str_pad($position, 2, '0', STR_PAD_LEFT) }}
                                                </span>
                                            </div>
                                        </td>

                                        {{-- TEAM --}}
                                        <td class="npl-col-team">
                                            <div class="npl-team-cell">

                                                <div class="npl-team-logo-wrap">
                                                    @if ($logo)
                                                        <img
                                                            src="{{ $logo }}"
                                                            alt="{{ $team?->name ?? 'Team' }} logo"
                                                            class="npl-table-team-logo"
                                                            loading="lazy"
                                                            decoding="async"
                                                            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                                        >

                                                        <span class="npl-table-team-fallback" style="display:none">
                                                            {{ $teamInitials($team) }}
                                                        </span>
                                                    @else
                                                        <span class="npl-table-team-fallback">
                                                            {{ $teamInitials($team) }}
                                                        </span>
                                                    @endif
                                                </div>

                                                <div class="npl-team-details">
                                                    <span class="npl-team-name">
                                                        {{ $team?->name ?? 'Unknown team' }}
                                                    </span>

                                                    @if ($team?->short_name)
                                                        <span class="npl-team-short">
                                                            {{ $team->short_name }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>

                                        {{-- PLAYED --}}
                                        <td class="npl-col-number">
                                            <span class="npl-table-number">
                                                {{ $played }}
                                            </span>
                                        </td>

                                        {{-- WON --}}
                                        <td class="npl-col-number">
                                            <span class="npl-table-number npl-number-won">
                                                {{ $won }}
                                            </span>
                                        </td>

                                        {{-- LOST --}}
                                        <td class="npl-col-number">
                                            <span class="npl-table-number">
                                                {{ $lost }}
                                            </span>
                                        </td>

                                        {{-- TIED --}}
                                        <td class="npl-col-number">
                                            <span class="npl-table-number">
                                                {{ $tied }}
                                            </span>
                                        </td>

                                        {{-- NO RESULT --}}
                                        <td class="npl-col-number">
                                            <span class="npl-table-number">
                                                {{ $noResult }}
                                            </span>
                                        </td>

                                        {{-- POINTS --}}
                                        <td class="npl-col-points">
                                            <div class="npl-points-cell">
                                                <span class="npl-points-badge">
                                                    {{ $points }}
                                                </span>

                                                @if ($penaltyPoints > 0)
                                                    <span class="npl-penalty-label">
                                                        {{ $penaltyPoints }} penalty
                                                    </span>
                                                @endif
                                            </div>
                                        </td>

                                        {{-- NET RUN RATE --}}
                                        <td class="npl-col-nrr">
                                            @if ($nrr !== null && is_numeric($nrr))
                                                <span class="npl-nrr-value {{ (float) $nrr > 0 ? 'npl-nrr-positive' : ((float) $nrr < 0 ? 'npl-nrr-negative' : 'npl-nrr-neutral') }}">
                                                    {{ $formatNrr($nrr) }}
                                                </span>
                                            @else
                                                <span class="npl-nrr-unavailable">—</span>
                                            @endif
                                        </td>

                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- TABLE FOOTER --}}
                    <div class="npl-standings-table-footer">
                        <div class="npl-table-footer-info">
                            <i class="fa-solid fa-circle-info"></i>

                            <p>
                                P: Played · W: Won · L: Lost · T: Tied ·
                                NR: No Result · PTS: Points · NRR: Net Run Rate
                            </p>
                        </div>

                        <span class="npl-table-footer-season">
                            {{ $selectedSeason->name }}
                        </span>
                    </div>

                @endif
            </section>

            {{-- =================================================
                 POINTS SYSTEM
            ================================================== --}}
            <section class="npl-points-info">
                <div class="npl-points-info-heading">
                    <div class="npl-points-info-icon">
                        <i class="fa-solid fa-book-open"></i>
                    </div>

                    <div>
                        <h2>Understanding the points system</h2>
                        <p>How match results contribute to the standings.</p>
                    </div>
                </div>

                <div class="npl-points-rules">
                    <article class="npl-points-rule">
                        <span class="npl-rule-symbol npl-rule-win">
                            <i class="fa-solid fa-check"></i>
                        </span>

                        <div>
                            <h3>Win</h3>
                            <p>2 points</p>
                        </div>
                    </article>

                    <article class="npl-points-rule">
                        <span class="npl-rule-symbol npl-rule-draw">
                            <i class="fa-solid fa-equals"></i>
                        </span>

                        <div>
                            <h3>Tie / No result</h3>
                            <p>1 point</p>
                        </div>
                    </article>

                    <article class="npl-points-rule">
                        <span class="npl-rule-symbol npl-rule-loss">
                            <i class="fa-solid fa-xmark"></i>
                        </span>

                        <div>
                            <h3>Loss</h3>
                            <p>0 points</p>
                        </div>
                    </article>
                </div>

                <div class="npl-nrr-note">
                    <i class="fa-solid fa-chart-line"></i>

                    <p>
                        <strong>Net Run Rate (NRR)</strong> helps separate
                        teams level on points. Competition-specific scoring
                        and tiebreaking rules should follow the official
                        tournament regulations.
                    </p>
                </div>
            </section>

        @endif

    </main>
</div>

<style>
    .npl-standings-page {
        --pt-red: #e3293b;
        --pt-red-dark: #bd1e30;
        --pt-navy: #101a30;
        --pt-text: #172033;
        --pt-muted: #748095;
        --pt-border: #e5e9f1;
        background: #f5f7fb;
        color: var(--pt-text);
    }

    .npl-standings-page *,
    .npl-standings-page *::before,
    .npl-standings-page *::after {
        box-sizing: border-box;
    }

    .npl-standings-container {
        width: min(1240px, calc(100% - 40px));
        margin-inline: auto;
    }

    /* HERO */

    .npl-standings-hero {
        position: relative;
        isolation: isolate;
        overflow: hidden;
        background: var(--pt-navy);
        color: #fff;
    }

    .npl-standings-hero-pattern {
        position: absolute;
        z-index: -1;
        inset: 0;
        opacity: .2;
        background-image:
            linear-gradient(135deg, transparent 46%, rgba(255,255,255,.07) 46%, rgba(255,255,255,.07) 47%, transparent 47%),
            linear-gradient(45deg, transparent 46%, rgba(255,255,255,.04) 46%, rgba(255,255,255,.04) 47%, transparent 47%);
        background-size: 70px 70px;
        mask-image: linear-gradient(90deg, #000, transparent 100%);
    }

    .npl-standings-hero::before {
        position: absolute;
        z-index: -1;
        top: -250px;
        right: 9%;
        width: 470px;
        height: 470px;
        border-radius: 50%;
        background: rgba(227,41,59,.16);
        content: "";
    }

    .npl-standings-hero-inner {
        display: flex;
        min-height: 330px;
        align-items: center;
        justify-content: space-between;
        gap: 32px;
        padding-block: 52px;
    }

    .npl-standings-hero-copy {
        max-width: 660px;
    }

    .npl-standings-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        color: #ff8792;
        font-size: 10px;
        font-weight: 900;
        letter-spacing: .2em;
    }

    .npl-standings-live-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--pt-red);
        box-shadow: 0 0 0 4px rgba(227,41,59,.17);
    }

    .npl-standings-hero h1 {
        margin: 20px 0 0;
        color: #fff;
        font-size: clamp(39px, 5vw, 60px);
        font-weight: 900;
        line-height: 1.03;
        letter-spacing: -.055em;
    }

    .npl-standings-hero h1 span {
        color: #ff6573;
    }

    .npl-standings-hero-copy > p {
        max-width: 510px;
        margin: 18px 0 0;
        color: #b9c4d6;
        font-size: 14px;
        line-height: 1.9;
    }

    .npl-standings-hero-meta {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
        margin-top: 25px;
        color: #e0e6f0;
        font-size: 11px;
        font-weight: 700;
    }

    .npl-standings-hero-meta span:not(.npl-standings-meta-divider) {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .npl-standings-hero-meta i {
        color: #ff6573;
    }

    .npl-standings-meta-divider {
        width: 1px;
        height: 15px;
        background: rgba(255,255,255,.2);
    }

    .npl-standings-hero-art {
        position: relative;
        display: flex;
        flex: 0 0 260px;
        min-height: 245px;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .npl-standings-orbit {
        position: absolute;
        border: 1px solid rgba(255,255,255,.13);
        border-radius: 50%;
        transform: rotate(-25deg);
    }

    .npl-standings-orbit-one {
        width: 215px;
        height: 215px;
    }

    .npl-standings-orbit-two {
        width: 165px;
        height: 255px;
        border-color: rgba(255,101,115,.3);
    }

    .npl-standings-trophy {
        position: relative;
        z-index: 1;
        display: flex;
        width: 115px;
        height: 115px;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(255,255,255,.23);
        border-radius: 30px;
        background: linear-gradient(145deg, #ed4657, #a91529);
        box-shadow: 0 25px 60px rgba(0,0,0,.27);
        transform: rotate(-7deg);
    }

    .npl-standings-trophy i {
        color: #fff;
        font-size: 48px;
        transform: rotate(7deg);
    }

    .npl-standings-art-title {
        position: relative;
        margin-top: 18px;
        color: #fff;
        font-size: 18px;
        font-weight: 900;
        letter-spacing: .19em;
    }

    .npl-standings-art-subtitle {
        position: relative;
        margin-top: 6px;
        color: #aab6ca;
        font-size: 8px;
        font-weight: 800;
        letter-spacing: .17em;
    }

    .npl-standings-hero-line {
        height: 4px;
        background: linear-gradient(90deg, #a91529, #ff6573 48%, #f6a4ac 72%, transparent);
    }

    /* MAIN */

    .npl-standings-main {
        padding-top: 38px;
        padding-bottom: 65px;
    }

    .npl-season-toolbar {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 24px;
        margin-bottom: 27px;
    }

    .npl-standings-section-kicker {
        color: var(--pt-red);
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .19em;
    }

    .npl-season-heading h2 {
        margin: 8px 0 0;
        color: #111827;
        font-size: clamp(27px, 3vw, 35px);
        font-weight: 900;
        line-height: 1.2;
        letter-spacing: -.045em;
    }

    .npl-season-heading h2 span {
        color: var(--pt-red);
    }

    .npl-season-heading > p {
        margin: 9px 0 0;
        color: var(--pt-muted);
        font-size: 13px;
        line-height: 1.7;
    }

    .npl-season-form {
        min-width: 280px;
    }

    .npl-season-label {
        display: block;
        margin-bottom: 9px;
        color: #657086;
        font-size: 10px;
        font-weight: 900;
        letter-spacing: .1em;
        text-transform: uppercase;
    }

    .npl-season-label i {
        margin-right: 5px;
        color: var(--pt-red);
    }

    .npl-season-controls {
        display: flex;
        gap: 8px;
    }

    .npl-season-select {
        min-width: 0;
        flex: 1;
        height: 44px;
        padding: 0 12px;
        border: 1px solid #dfe4ed;
        border-radius: 9px;
        background: #fff;
        color: #263146;
        font-family: inherit;
        font-size: 12px;
        font-weight: 700;
        outline: none;
    }

    .npl-season-select:focus {
        border-color: var(--pt-red);
        box-shadow: 0 0 0 3px rgba(227,41,59,.09);
    }

    .npl-season-button {
        display: inline-flex;
        height: 44px;
        align-items: center;
        justify-content: center;
        gap: 9px;
        padding: 0 15px;
        border: 0;
        border-radius: 9px;
        background: var(--pt-navy);
        color: #fff;
        font-family: inherit;
        font-size: 11px;
        font-weight: 800;
        cursor: pointer;
        transition: background .2s ease, transform .2s ease;
    }

    .npl-season-button:hover {
        background: var(--pt-red);
        transform: translateY(-1px);
    }

    /* SEASON STRIP */

    .npl-season-strip {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 19px;
        padding: 16px 19px;
        border: 1px solid #e5e9f1;
        border-radius: 13px;
        background: #fff;
    }

    .npl-season-strip-left {
        display: flex;
        align-items: center;
        gap: 13px;
        min-width: 0;
    }

    .npl-season-strip-icon {
        display: flex;
        width: 43px;
        height: 43px;
        flex: 0 0 43px;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        background: #fff0f2;
        color: var(--pt-red);
        font-size: 18px;
    }

    .npl-season-strip-label {
        color: #8992a3;
        font-size: 8px;
        font-weight: 900;
        letter-spacing: .16em;
    }

    .npl-season-strip-left h2 {
        margin: 4px 0 0;
        color: #172033;
        font-size: 17px;
        font-weight: 900;
        line-height: 1.3;
    }

    .npl-season-strip-right {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #667186;
        font-size: 10px;
        font-weight: 800;
        white-space: nowrap;
    }

    .npl-season-status-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #16a36a;
        box-shadow: 0 0 0 3px #e4f7ed;
    }

    /* SUMMARY CARDS */

    .npl-standings-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 15px;
        margin-bottom: 37px;
    }

    .npl-stat-card {
        min-width: 0;
        padding: 19px;
        border: 1px solid #e5e9f1;
        border-radius: 13px;
        background: #fff;
        box-shadow: 0 3px 10px rgba(17,24,39,.018);
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .npl-stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 25px rgba(17,24,39,.055);
    }

    .npl-stat-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }

    .npl-stat-label {
        color: #727d90;
        font-size: 11px;
        font-weight: 700;
        line-height: 1.5;
    }

    .npl-stat-icon {
        display: flex;
        width: 38px;
        height: 38px;
        flex: 0 0 38px;
        align-items: center;
        justify-content: center;
        border-radius: 11px;
        font-size: 15px;
    }

    .npl-stat-icon-red {
        background: #fff0f2;
        color: #df3546;
    }

    .npl-stat-icon-green {
        background: #e7f8ef;
        color: #138658;
    }

    .npl-stat-icon-blue {
        background: #eaf3ff;
        color: #3677d3;
    }

    .npl-stat-icon-purple {
        background: #f0edff;
        color: #7653d6;
    }

    .npl-stat-value {
        margin-top: 15px;
        color: #141d30;
        font-size: 34px;
        font-weight: 900;
        line-height: 1;
        letter-spacing: -.05em;
        font-variant-numeric: tabular-nums;
    }

    .npl-stat-foot {
        display: flex;
        align-items: center;
        gap: 7px;
        margin-top: 12px;
        color: #8992a2;
        font-size: 10px;
        line-height: 1.5;
    }

    .npl-stat-foot-dot {
        width: 6px;
        height: 6px;
        flex: 0 0 6px;
        border-radius: 50%;
    }

    .npl-dot-red { background: #e3293b; }
    .npl-dot-green { background: #16a36a; }
    .npl-dot-blue { background: #4285e8; }
    .npl-dot-purple { background: #8461df; }

    /* TABLE HEADING */

    .npl-table-heading {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 17px;
    }

    .npl-table-heading h2 {
        margin: 8px 0 0;
        color: #151e31;
        font-size: 24px;
        font-weight: 900;
        line-height: 1.25;
        letter-spacing: -.04em;
    }

    .npl-table-heading p {
        margin: 8px 0 0;
        color: #7b8597;
        font-size: 12px;
        line-height: 1.7;
    }

    .npl-table-heading-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 12px;
        border: 1px solid #e5e9f1;
        border-radius: 9px;
        background: #fff;
        color: #6d778b;
        font-size: 10px;
        font-weight: 800;
        white-space: nowrap;
    }

    .npl-table-heading-badge i {
        color: var(--pt-red);
    }

    /* TABLE CARD */

    .npl-standings-card {
        overflow: hidden;
        border: 1px solid #e3e8f0;
        border-radius: 15px;
        background: #fff;
        box-shadow: 0 5px 18px rgba(18,29,50,.025);
    }

    .npl-standings-card-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 19px 21px;
        border-bottom: 1px solid #edf0f5;
    }

    .npl-table-title {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .npl-table-title-mark {
        width: 4px;
        height: 33px;
        flex: 0 0 4px;
        border-radius: 8px;
        background: var(--pt-red);
    }

    .npl-table-title h3 {
        margin: 0;
        color: #172033;
        font-size: 14px;
        font-weight: 900;
    }

    .npl-table-title p {
        margin: 4px 0 0;
        color: #9099a9;
        font-size: 10px;
    }

    .npl-table-team-count {
        padding: 8px 10px;
        border: 1px solid #e7ebf2;
        border-radius: 7px;
        background: #f8f9fc;
        color: #707b8f;
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .1em;
        white-space: nowrap;
    }

    .npl-standings-table-scroll {
        width: 100%;
        overflow-x: auto;
        overscroll-behavior-x: contain;
        scrollbar-width: thin;
        scrollbar-color: #cbd2df #f4f6fa;
    }

    .npl-standings-table {
        width: 100%;
        min-width: 790px;
        border-collapse: separate;
        border-spacing: 0;
        text-align: left;
    }

    .npl-standings-table thead th {
        padding: 15px 12px;
        border-bottom: 1px solid #e8ecf3;
        background: #f7f8fb;
        color: #7e899b;
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .12em;
        white-space: nowrap;
    }

    .npl-standings-table thead th:first-child,
    .npl-standings-table tbody td:first-child {
        padding-left: 20px;
    }

    .npl-standings-table thead th:last-child,
    .npl-standings-table tbody td:last-child {
        padding-right: 20px;
    }

    .npl-standings-table tbody td {
        height: 77px;
        padding: 12px;
        border-bottom: 1px solid #edf0f5;
        background: #fff;
        vertical-align: middle;
        transition: background .16s ease;
    }

    .npl-standings-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .npl-standings-row:hover td {
        background: #fff8f8;
    }

    .npl-standings-row-first td {
        background: #fffdf7;
    }

    .npl-standings-row-first:hover td {
        background: #fff8e8;
    }

    .npl-col-position {
        width: 75px;
        text-align: center;
    }

    .npl-col-team {
        min-width: 240px;
        text-align: left;
    }

    .npl-col-number {
        width: 55px;
        text-align: center;
    }

    .npl-col-points {
        width: 85px;
        text-align: center;
    }

    .npl-col-nrr {
        width: 95px;
        text-align: right;
    }

    .npl-position-cell {
        display: inline-flex;
        min-width: 36px;
        align-items: center;
        justify-content: center;
        gap: 4px;
    }

    .npl-position-crown {
        color: #c58b16;
        font-size: 10px;
    }

    .npl-position-number {
        display: inline-flex;
        min-width: 31px;
        height: 31px;
        align-items: center;
        justify-content: center;
        border-radius: 9px;
        font-size: 11px;
        font-weight: 900;
        font-variant-numeric: tabular-nums;
    }

    .npl-position-first {
        border: 1px solid #f3d58c;
        background: #fff1c9;
        color: #93620a;
    }

    .npl-position-second {
        border: 1px solid #dfe3eb;
        background: #f0f2f6;
        color: #606c7e;
    }

    .npl-position-third {
        border: 1px solid #f0c7a6;
        background: #fff0e3;
        color: #a65a28;
    }

    .npl-position-normal {
        color: #7d8798;
    }

    .npl-team-cell {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }

    .npl-team-logo-wrap {
        display: flex;
        width: 45px;
        height: 45px;
        flex: 0 0 45px;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        border: 1px solid #e9edf4;
        border-radius: 12px;
        background: #f8f9fc;
    }

    .npl-table-team-logo {
        display: block;
        width: 100%;
        height: 100%;
        padding: 5px;
        object-fit: contain;
    }

    .npl-table-team-fallback {
        display: flex;
        width: 100%;
        height: 100%;
        align-items: center;
        justify-content: center;
        background: #fff0f2;
        color: #c72d3e;
        font-size: 10px;
        font-weight: 900;
        letter-spacing: -.02em;
    }

    .npl-team-details {
        display: flex;
        min-width: 0;
        flex-direction: column;
        gap: 5px;
    }

    .npl-team-name {
        overflow-wrap: anywhere;
        color: #1a2336;
        font-size: 12px;
        font-weight: 850;
        line-height: 1.5;
    }

    .npl-team-short {
        color: #929bad;
        font-size: 9px;
        font-weight: 800;
        letter-spacing: .1em;
        text-transform: uppercase;
    }

    .npl-table-number {
        color: #515c70;
        font-size: 12px;
        font-weight: 700;
        font-variant-numeric: tabular-nums;
    }

    .npl-number-won {
        color: #16845b;
        font-weight: 900;
    }

    .npl-points-cell {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 5px;
    }

    .npl-points-badge {
        display: inline-flex;
        min-width: 36px;
        height: 32px;
        align-items: center;
        justify-content: center;
        padding: 0 8px;
        border-radius: 9px;
        background: #172139;
        color: #fff;
        font-size: 13px;
        font-weight: 900;
        font-variant-numeric: tabular-nums;
    }

    .npl-standings-row-first .npl-points-badge {
        background: #b9861d;
    }

    .npl-penalty-label {
        color: #d92f41;
        font-size: 9px;
        font-weight: 800;
        white-space: nowrap;
    }

    .npl-nrr-value {
        font-size: 12px;
        font-weight: 900;
        font-variant-numeric: tabular-nums;
    }

    .npl-nrr-positive {
        color: #148253;
    }

    .npl-nrr-negative {
        color: #d93647;
    }

    .npl-nrr-neutral {
        color: #667085;
    }

    .npl-nrr-unavailable {
        color: #a3aab8;
        font-size: 14px;
    }

    .npl-standings-table-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        padding: 14px 20px;
        border-top: 1px solid #e9edf4;
        background: #f8f9fc;
    }

    .npl-table-footer-info {
        display: flex;
        align-items: flex-start;
        gap: 9px;
        color: #7c8799;
    }

    .npl-table-footer-info i {
        margin-top: 3px;
        color: #9ca5b4;
        font-size: 12px;
    }

    .npl-table-footer-info p {
        margin: 0;
        font-size: 10px;
        line-height: 1.8;
    }

    .npl-table-footer-season {
        color: #7b8597;
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .1em;
        white-space: nowrap;
    }

    /* POINTS EXPLANATION */

    .npl-points-info {
        margin-top: 23px;
        padding: 23px;
        border: 1px solid #e4e9f1;
        border-radius: 14px;
        background: #fff;
    }

    .npl-points-info-heading {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .npl-points-info-icon {
        display: flex;
        width: 42px;
        height: 42px;
        flex: 0 0 42px;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        background: #fff0f2;
        color: var(--pt-red);
        font-size: 17px;
    }

    .npl-points-info-heading h2 {
        margin: 0;
        color: #172033;
        font-size: 15px;
        font-weight: 900;
    }

    .npl-points-info-heading p {
        margin: 5px 0 0;
        color: #8992a3;
        font-size: 11px;
        line-height: 1.6;
    }

    .npl-points-rules {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 13px;
        margin-top: 22px;
    }

    .npl-points-rule {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 15px;
        border: 1px solid #edf0f5;
        border-radius: 11px;
        background: #fafbfc;
    }

    .npl-rule-symbol {
        display: flex;
        width: 35px;
        height: 35px;
        flex: 0 0 35px;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        font-size: 13px;
    }

    .npl-rule-win {
        background: #e5f7ed;
        color: #128453;
    }

    .npl-rule-draw {
        background: #fff2d8;
        color: #a36c0b;
    }

    .npl-rule-loss {
        background: #fce9eb;
        color: #d93647;
    }

    .npl-points-rule h3 {
        margin: 0;
        color: #273247;
        font-size: 11px;
        font-weight: 850;
    }

    .npl-points-rule p {
        margin: 5px 0 0;
        color: #737e91;
        font-size: 11px;
    }

    .npl-nrr-note {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin-top: 18px;
        padding-top: 17px;
        border-top: 1px solid #edf0f5;
        color: #828c9d;
    }

    .npl-nrr-note i {
        margin-top: 3px;
        color: var(--pt-red);
        font-size: 13px;
    }

    .npl-nrr-note p {
        margin: 0;
        font-size: 11px;
        line-height: 1.8;
    }

    .npl-nrr-note strong {
        color: #4b5669;
    }

    /* EMPTY STATES */

    .npl-standings-empty {
        padding: 60px 22px;
        border: 1px dashed #d6deea;
        border-radius: 15px;
        background: #fff;
        text-align: center;
    }

    .npl-standings-empty-inside {
        border: 0;
        border-radius: 0;
        background: #fff;
    }

    .npl-standings-empty-icon {
        display: flex;
        width: 60px;
        height: 60px;
        align-items: center;
        justify-content: center;
        margin: 0 auto 18px;
        border: 1px solid #f2c4ca;
        border-radius: 18px;
        background: #fff0f2;
        color: var(--pt-red);
        font-size: 23px;
    }

    .npl-standings-empty h2 {
        margin: 0;
        color: #172033;
        font-size: 21px;
        font-weight: 900;
        letter-spacing: -.035em;
    }

    .npl-standings-empty p {
        max-width: 470px;
        margin: 11px auto 0;
        color: #7b8597;
        font-size: 12px;
        line-height: 1.9;
    }

    /* RESPONSIVE */

    @media (max-width: 1050px) {
        .npl-standings-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .npl-standings-hero-art {
            flex-basis: 220px;
        }

        .npl-standings-orbit-one {
            width: 185px;
            height: 185px;
        }

        .npl-standings-orbit-two {
            width: 145px;
            height: 220px;
        }

        .npl-standings-trophy {
            width: 100px;
            height: 100px;
        }
    }

    @media (max-width: 700px) {
        .npl-standings-container {
            width: calc(100% - 30px);
        }

        .npl-standings-hero-inner {
            min-height: auto;
            padding-block: 42px;
        }

        .npl-standings-hero-art {
            display: none;
        }

        .npl-standings-hero h1 {
            font-size: clamp(38px, 10vw, 50px);
        }

        .npl-standings-hero-copy > p {
            max-width: 480px;
            font-size: 13px;
        }

        .npl-standings-main {
            padding-top: 29px;
            padding-bottom: 45px;
        }

        .npl-season-toolbar {
            align-items: stretch;
            flex-direction: column;
            gap: 20px;
        }

        .npl-season-form {
            min-width: 0;
            width: 100%;
        }

        .npl-season-strip {
            align-items: flex-start;
            padding: 14px;
        }

        .npl-season-strip-right {
            padding-top: 7px;
            font-size: 9px;
        }

        .npl-standings-stats {
            gap: 11px;
            margin-bottom: 29px;
        }

        .npl-stat-card {
            padding: 14px;
        }

        .npl-stat-label {
            font-size: 10px;
        }

        .npl-stat-icon {
            width: 32px;
            height: 32px;
            flex-basis: 32px;
            font-size: 13px;
        }

        .npl-stat-value {
            margin-top: 13px;
            font-size: 29px;
        }

        .npl-stat-foot {
            align-items: flex-start;
            font-size: 9px;
        }

        .npl-table-heading {
            align-items: flex-start;
            flex-direction: column;
            gap: 12px;
        }

        .npl-table-heading h2 {
            font-size: 22px;
        }

        .npl-standings-card-top {
            padding: 15px;
        }

        .npl-standings-table {
            min-width: 760px;
        }

        .npl-standings-table-footer {
            align-items: flex-start;
            flex-direction: column;
            padding: 13px 15px;
        }

        .npl-points-info {
            padding: 17px;
        }

        .npl-points-rules {
            grid-template-columns: 1fr;
            gap: 9px;
            margin-top: 17px;
        }

        .npl-points-rule {
            padding: 12px;
        }
    }

    @media (max-width: 390px) {
        .npl-standings-stats {
            grid-template-columns: 1fr 1fr;
        }

        .npl-stat-card {
            padding: 12px;
        }

        .npl-stat-top {
            align-items: flex-start;
        }

        .npl-stat-label {
            max-width: 95px;
        }

        .npl-stat-value {
            font-size: 27px;
        }

        .npl-season-strip-right {
            max-width: 95px;
            white-space: normal;
            line-height: 1.5;
        }

        .npl-season-strip-left h2 {
            font-size: 15px;
        }

        .npl-standings-card-top {
            gap: 8px;
        }

        .npl-table-title p {
            max-width: 170px;
            line-height: 1.5;
        }

        .npl-table-team-count {
            padding: 7px;
            font-size: 8px;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .npl-standings-page *,
        .npl-standings-page *::before,
        .npl-standings-page *::after {
            animation-duration: .01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: .01ms !important;
        }
    }
</style>
@endsection
