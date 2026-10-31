
@extends('layouts.app')

@section('title', 'NPL Teams | NPL Season Hub')

@section('content')
@php
    use Illuminate\Support\Str;

    /*
    |--------------------------------------------------------------------------
    | Team logo helper
    |--------------------------------------------------------------------------
    | Supports paths such as:
    | logo.png
    | teams/logo.png
    | public/teams/logo.png
    | storage/teams/logo.png
    | Full external image URLs
    */

    $teamLogoUrl = function ($path) {
        if (! is_string($path) || trim($path) === '') {
            return null;
        }

        $path = trim($path);

        if (Str::startsWith($path, ['https://', 'http://', 'data:image/'])) {
            return $path;
        }

        $path = ltrim($path, '/');
        $path = preg_replace('#^(public/|storage/)#', '', $path);

        if (! Str::startsWith($path, 'teams/')) {
            $path = 'teams/' . $path;
        }

        return asset('storage/' . $path);
    };

    $teamCount = $teams->total();
@endphp

<div class="npl-teams-page">
    {{-- Hero --}}
    <section class="npl-teams-hero">
        <div class="npl-teams-hero-pattern"></div>

        <div class="npl-teams-container npl-teams-hero-inner">
            <div class="npl-teams-hero-copy">
                <div class="npl-teams-eyebrow">
                    <span class="npl-live-dot"></span>
                    NEPAL PREMIER LEAGUE
                </div>

                <h1>
                    The teams.<br>
                    <span>The pride of Nepal.</span>
                </h1>

                <p>
                    Meet the franchises, discover their home grounds,
                    and explore the teams competing in the NPL season.
                </p>

                <div class="npl-teams-hero-meta">
                    <span>
                        <i class="fa-solid fa-shield-halved"></i>
                        Team directory
                    </span>

                    <span class="npl-meta-divider"></span>

                    <span>
                        <i class="fa-solid fa-trophy"></i>
                        Season hub
                    </span>
                </div>
            </div>

            <div class="npl-teams-hero-art" aria-hidden="true">
                <div class="npl-hero-orbit npl-hero-orbit-one"></div>
                <div class="npl-hero-orbit npl-hero-orbit-two"></div>

                <div class="npl-hero-emblem">
                    <i class="fa-solid fa-trophy"></i>
                </div>

                <span class="npl-hero-art-label">NPL</span>
                <span class="npl-hero-art-subtitle">CRICKET • PASSION • PRIDE</span>
            </div>
        </div>

        <div class="npl-hero-bottom-line"></div>
    </section>

    {{-- Team directory --}}
    <section class="npl-teams-directory">
        <div class="npl-teams-container">

            <div class="npl-teams-section-heading">
                <div>
                    <span class="npl-section-kicker">THE FRANCHISES</span>
                    <h2>Meet the teams</h2>
                    <p>Every team has a story. Find yours.</p>
                </div>

                @if ($teamCount > 0)
                    <div class="npl-team-count">
                        <span class="npl-team-count-number">
                            {{ str_pad($teamCount, 2, '0', STR_PAD_LEFT) }}
                        </span>
                        <span class="npl-team-count-label">
                            {{ $teamCount === 1 ? 'TEAM' : 'TEAMS' }}<br>
                            LISTED
                        </span>
                    </div>
                @endif
            </div>

            @if ($teams->isNotEmpty())
                <div class="npl-teams-grid">
                    @foreach ($teams as $team)
                        @php
                            $logo = $teamLogoUrl($team->logo ?? null);
                            $teamName = $team->name ?? 'NPL Team';
                            $shortName = $team->short_name ?: Str::upper(Str::substr($teamName, 0, 3));
                        @endphp

                        <a
                            href="{{ route('teams.show', $team) }}"
                            class="npl-team-card"
                            aria-label="View {{ $teamName }} team profile"
                        >
                            <div class="npl-team-card-top">
                                <span class="npl-team-card-label">NPL FRANCHISE</span>

                                <span class="npl-team-card-arrow">
                                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                </span>
                            </div>

                            <div class="npl-team-logo-stage">
                                <div class="npl-team-logo-glow"></div>

                                @if ($logo)
                                    <img
                                        src="{{ $logo }}"
                                        alt="{{ $teamName }} logo"
                                        loading="lazy"
                                        decoding="async"
                                        class="npl-team-logo"
                                        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                    >
                                @endif

                                <div
                                    class="npl-team-logo-fallback"
                                    @if ($logo) style="display:none" @endif
                                >
                                    {{ Str::upper(Str::substr($shortName, 0, 3)) }}
                                </div>
                            </div>

                            <div class="npl-team-card-content">
                                <div class="npl-team-short-name">
                                    {{ $shortName }}
                                </div>

                                <h3>{{ $teamName }}</h3>

                                <div class="npl-team-venue">
                                    <span class="npl-venue-icon">
                                        <i class="fa-solid fa-location-dot"></i>
                                    </span>

                                    <span>
                                        {{ $team->home_venue ?: 'Home venue to be announced' }}
                                    </span>
                                </div>

                                <div class="npl-team-card-footer">
                                    <span>Explore team profile</span>
                                    <span class="npl-team-footer-arrow">
                                        <i class="fa-solid fa-arrow-right"></i>
                                    </span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>

                @if (method_exists($teams, 'hasPages') && $teams->hasPages())
                    <div class="npl-teams-pagination">
                        {{ $teams->links() }}
                    </div>
                @endif
            @else
                <div class="npl-teams-empty">
                    <div class="npl-empty-icon">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>

                    <h2>Teams are getting ready</h2>

                    <p>
                        Team profiles will appear here once they are published.
                        Check back soon for the latest NPL team information.
                    </p>
                </div>
            @endif
        </div>
    </section>
</div>

<style>
    .npl-teams-page {
        --npl-red: #e3293b;
        --npl-red-dark: #bd1e30;
        --npl-navy: #101a30;
        --npl-muted: #697386;
        background: #f5f7fb;
        color: #111827;
        min-height: 70vh;
    }

    .npl-teams-page *,
    .npl-teams-page *::before,
    .npl-teams-page *::after {
        box-sizing: border-box;
    }

    .npl-teams-container {
        width: min(1240px, calc(100% - 40px));
        margin-inline: auto;
    }

    /* Hero */
    .npl-teams-hero {
        position: relative;
        isolation: isolate;
        overflow: hidden;
        background: var(--npl-navy);
        color: #fff;
    }

    .npl-teams-hero-pattern {
        position: absolute;
        inset: 0;
        z-index: -1;
        opacity: .18;
        background-image:
            linear-gradient(135deg, transparent 45%, rgba(255,255,255,.08) 45%, rgba(255,255,255,.08) 46%, transparent 46%),
            linear-gradient(45deg, transparent 45%, rgba(255,255,255,.05) 45%, rgba(255,255,255,.05) 46%, transparent 46%);
        background-size: 72px 72px;
        mask-image: linear-gradient(to right, black, transparent 90%);
    }

    .npl-teams-hero::before {
        content: "";
        position: absolute;
        z-index: -1;
        width: 440px;
        height: 440px;
        top: -260px;
        right: 12%;
        border-radius: 50%;
        background: rgba(227,41,59,.17);
        filter: blur(3px);
    }

    .npl-teams-hero-inner {
        min-height: 350px;
        padding-block: 62px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 36px;
    }

    .npl-teams-hero-copy {
        position: relative;
        z-index: 1;
        max-width: 700px;
    }

    .npl-teams-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        color: #ff8993;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .2em;
    }

    .npl-live-dot {
        width: 8px;
        height: 8px;
        flex: 0 0 8px;
        border-radius: 50%;
        background: var(--npl-red);
        box-shadow: 0 0 0 4px rgba(227,41,59,.16);
    }

    .npl-teams-hero h1 {
        margin: 22px 0 0;
        color: #fff;
        font-size: clamp(36px, 5.2vw, 62px);
        font-weight: 900;
        line-height: 1.04;
        letter-spacing: -.055em;
    }

    .npl-teams-hero h1 span {
        color: #ff6573;
    }

    .npl-teams-hero-copy > p {
        max-width: 520px;
        margin: 20px 0 0;
        color: #b8c2d4;
        font-size: 15px;
        line-height: 1.9;
    }

    .npl-teams-hero-meta {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 18px;
        margin-top: 26px;
        color: #e0e6f0;
        font-size: 12px;
        font-weight: 700;
    }

    .npl-teams-hero-meta span:not(.npl-meta-divider) {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .npl-teams-hero-meta i {
        color: #ff6573;
    }

    .npl-meta-divider {
        width: 1px;
        height: 16px;
        background: rgba(255,255,255,.2);
    }

    .npl-teams-hero-art {
        position: relative;
        display: flex;
        flex: 0 0 285px;
        min-height: 260px;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .npl-hero-orbit {
        position: absolute;
        border: 1px solid rgba(255,255,255,.13);
        border-radius: 50%;
        transform: rotate(-25deg);
    }

    .npl-hero-orbit-one {
        width: 235px;
        height: 235px;
    }

    .npl-hero-orbit-two {
        width: 185px;
        height: 275px;
        border-color: rgba(255,101,115,.35);
    }

    .npl-hero-emblem {
        position: relative;
        z-index: 1;
        display: flex;
        width: 126px;
        height: 126px;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(255,255,255,.23);
        border-radius: 32px;
        background: linear-gradient(145deg, #e83c4d, #a91529);
        box-shadow: 0 25px 65px rgba(0,0,0,.28), inset 0 1px 0 rgba(255,255,255,.2);
        transform: rotate(-8deg);
    }

    .npl-hero-emblem i {
        color: white;
        font-size: 55px;
        transform: rotate(8deg);
    }

    .npl-hero-art-label {
        position: relative;
        margin-top: 19px;
        color: #fff;
        font-size: 23px;
        font-weight: 900;
        letter-spacing: .24em;
    }

    .npl-hero-art-subtitle {
        position: relative;
        margin-top: 7px;
        color: #aab6ca;
        font-size: 8px;
        font-weight: 800;
        letter-spacing: .2em;
    }

    .npl-hero-bottom-line {
        height: 4px;
        background: linear-gradient(90deg, #a91529, #ff6573 45%, #f6a4ac 70%, transparent);
    }

    /* Directory heading */
    .npl-teams-directory {
        padding: 52px 0 76px;
    }

    .npl-teams-section-heading {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 28px;
    }

    .npl-section-kicker {
        color: var(--npl-red);
        font-size: 10px;
        font-weight: 900;
        letter-spacing: .2em;
    }

    .npl-teams-section-heading h2 {
        margin: 9px 0 0;
        color: #111827;
        font-size: clamp(25px, 3vw, 34px);
        font-weight: 900;
        line-height: 1.2;
        letter-spacing: -.04em;
    }

    .npl-teams-section-heading p {
        margin: 9px 0 0;
        color: var(--npl-muted);
        font-size: 14px;
    }

    .npl-team-count {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 17px;
        border: 1px solid #e4e8f0;
        border-radius: 12px;
        background: #fff;
    }

    .npl-team-count-number {
        color: var(--npl-red);
        font-size: 30px;
        font-weight: 900;
        line-height: 1;
        letter-spacing: -.05em;
    }

    .npl-team-count-label {
        color: #788195;
        font-size: 9px;
        font-weight: 900;
        line-height: 1.6;
        letter-spacing: .12em;
    }

    /* Team cards */
    .npl-teams-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 22px;
    }

    .npl-team-card {
        position: relative;
        display: flex;
        min-width: 0;
        flex-direction: column;
        overflow: hidden;
        border: 1px solid #e5e9f1;
        border-radius: 16px;
        background: #fff;
        color: inherit;
        text-decoration: none;
        box-shadow: 0 3px 12px rgba(17,24,39,.025);
        transition: transform .22s ease, border-color .22s ease, box-shadow .22s ease;
    }

    .npl-team-card::before {
        content: "";
        position: absolute;
        z-index: 1;
        top: 0;
        left: 0;
        width: 100%;
        height: 3px;
        background: linear-gradient(90deg, var(--npl-red), #ff8a94);
        transform: scaleX(0);
        transform-origin: left;
        transition: transform .22s ease;
    }

    .npl-team-card:hover {
        transform: translateY(-5px);
        border-color: #f0bdc3;
        box-shadow: 0 18px 38px rgba(17,24,39,.09);
    }

    .npl-team-card:hover::before {
        transform: scaleX(1);
    }

    .npl-team-card:focus-visible {
        outline: 3px solid rgba(227,41,59,.5);
        outline-offset: 4px;
    }

    .npl-team-card-top {
        position: relative;
        z-index: 2;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 19px 19px 0;
    }

    .npl-team-card-label {
        color: #8b94a5;
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .15em;
    }

    .npl-team-card-arrow {
        display: flex;
        width: 31px;
        height: 31px;
        align-items: center;
        justify-content: center;
        border: 1px solid #edf0f5;
        border-radius: 9px;
        color: #7b8494;
        font-size: 11px;
        transition: background .2s ease, color .2s ease;
    }

    .npl-team-card:hover .npl-team-card-arrow {
        border-color: var(--npl-red);
        background: var(--npl-red);
        color: #fff;
    }

    .npl-team-logo-stage {
        position: relative;
        display: flex;
        min-height: 185px;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        margin: 12px 14px 0;
        border-radius: 12px;
        background:
            radial-gradient(ellipse at center, #fff 0%, #f7f8fb 64%, #f1f3f8 100%);
    }

    .npl-team-logo-stage::after {
        content: "";
        position: absolute;
        right: 12%;
        bottom: 18px;
        left: 12%;
        height: 1px;
        background: linear-gradient(90deg, transparent, #e1e5ed, transparent);
    }

    .npl-team-logo-glow {
        position: absolute;
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: rgba(227,41,59,.045);
        filter: blur(18px);
        transition: transform .25s ease;
    }

    .npl-team-card:hover .npl-team-logo-glow {
        transform: scale(1.5);
    }

    .npl-team-logo {
        position: relative;
        z-index: 1;
        display: block;
        width: 145px;
        height: 145px;
        max-width: 75%;
        object-fit: contain;
        filter: drop-shadow(0 8px 8px rgba(15,23,42,.08));
        transition: transform .25s ease;
    }

    .npl-team-card:hover .npl-team-logo {
        transform: scale(1.06);
    }

    .npl-team-logo-fallback {
        position: relative;
        z-index: 1;
        display: flex;
        width: 94px;
        height: 94px;
        align-items: center;
        justify-content: center;
        border: 1px solid #f4b8bf;
        border-radius: 25px;
        background: linear-gradient(145deg, #fff, #ffe9ec);
        color: var(--npl-red-dark);
        font-size: 25px;
        font-weight: 900;
        letter-spacing: -.04em;
        box-shadow: 0 8px 25px rgba(227,41,59,.08);
    }

    .npl-team-card-content {
        display: flex;
        flex: 1;
        flex-direction: column;
        padding: 20px 20px 0;
    }

    .npl-team-short-name {
        color: var(--npl-red);
        font-size: 10px;
        font-weight: 900;
        letter-spacing: .16em;
    }

    .npl-team-card-content h3 {
        margin: 7px 0 0;
        color: #111827;
        font-size: 20px;
        font-weight: 850;
        line-height: 1.35;
        letter-spacing: -.035em;
        overflow-wrap: anywhere;
        transition: color .2s ease;
    }

    .npl-team-card:hover .npl-team-card-content h3 {
        color: var(--npl-red-dark);
    }

    .npl-team-venue {
        display: flex;
        align-items: flex-start;
        gap: 9px;
        margin-top: 14px;
        color: #737d8f;
        font-size: 12px;
        line-height: 1.6;
    }

    .npl-venue-icon {
        display: inline-flex;
        flex: 0 0 18px;
        justify-content: center;
        margin-top: 2px;
        color: #a2aaba;
    }

    .npl-team-card-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-top: 22px;
        padding: 15px 0 17px;
        border-top: 1px solid #edf0f5;
        color: #4b5565;
        font-size: 12px;
        font-weight: 800;
    }

    .npl-team-footer-arrow {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        flex: 0 0 28px;
        border-radius: 50%;
        background: #fcebed;
        color: var(--npl-red);
        transition: transform .2s ease, background .2s ease, color .2s ease;
    }

    .npl-team-card:hover .npl-team-footer-arrow {
        transform: translateX(3px);
        background: var(--npl-red);
        color: #fff;
    }

    /* Empty state */
    .npl-teams-empty {
        padding: 68px 20px;
        border: 1px dashed #d7deea;
        border-radius: 16px;
        background: rgba(255,255,255,.8);
        text-align: center;
    }

    .npl-empty-icon {
        display: flex;
        width: 65px;
        height: 65px;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        border: 1px solid #f3c4c9;
        border-radius: 20px;
        background: #fff0f2;
        color: var(--npl-red);
        font-size: 25px;
    }

    .npl-teams-empty h2 {
        margin: 0;
        color: #111827;
        font-size: 22px;
        font-weight: 900;
        letter-spacing: -.035em;
    }

    .npl-teams-empty p {
        max-width: 440px;
        margin: 12px auto 0;
        color: #6b7280;
        font-size: 14px;
        line-height: 1.8;
    }

    /* Pagination */
    .npl-teams-pagination {
        display: flex;
        justify-content: center;
        margin-top: 36px;
    }

    .npl-teams-pagination nav {
        max-width: 100%;
    }

    /* Responsive */
    @media (max-width: 1024px) {
        .npl-teams-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }

        .npl-teams-hero-art {
            flex-basis: 225px;
        }

        .npl-hero-orbit-one {
            width: 195px;
            height: 195px;
        }

        .npl-hero-orbit-two {
            width: 155px;
            height: 225px;
        }
    }

    @media (max-width: 640px) {
        .npl-teams-container {
            width: min(100% - 30px, 500px);
        }

        .npl-teams-hero-inner {
            min-height: 0;
            padding-block: 46px 43px;
        }

        .npl-teams-hero h1 {
            margin-top: 19px;
            font-size: clamp(35px, 10vw, 47px);
        }

        .npl-teams-hero-copy > p {
            margin-top: 15px;
            font-size: 13px;
            line-height: 1.8;
        }

        .npl-teams-hero-meta {
            gap: 12px;
            margin-top: 21px;
            font-size: 11px;
        }

        .npl-teams-hero-art {
            display: none;
        }

        .npl-teams-directory {
            padding: 36px 0 50px;
        }

        .npl-teams-section-heading {
            align-items: center;
            margin-bottom: 22px;
        }

        .npl-teams-section-heading h2 {
            font-size: 27px;
        }

        .npl-teams-section-heading p {
            max-width: 230px;
            font-size: 12px;
            line-height: 1.7;
        }

        .npl-team-count {
            gap: 8px;
            padding: 10px;
        }

        .npl-team-count-number {
            font-size: 24px;
        }

        .npl-team-count-label {
            font-size: 8px;
        }

        .npl-teams-grid {
            grid-template-columns: minmax(0, 1fr);
            gap: 15px;
        }

        .npl-team-card {
            display: grid;
            grid-template-columns: 125px minmax(0, 1fr);
            align-items: stretch;
            border-radius: 13px;
        }

        .npl-team-card-top {
            grid-column: 1 / -1;
            padding: 13px 14px 0;
        }

        .npl-team-logo-stage {
            grid-column: 1;
            grid-row: 2;
            min-height: 150px;
            margin: 10px 0 14px 12px;
        }

        .npl-team-logo {
            width: 105px;
            height: 105px;
            max-width: 90%;
        }

        .npl-team-logo-fallback {
            width: 65px;
            height: 65px;
            border-radius: 19px;
            font-size: 20px;
        }

        .npl-team-card-content {
            grid-column: 2;
            grid-row: 2;
            padding: 12px 13px 0;
        }

        .npl-team-card-content h3 {
            font-size: 16px;
            line-height: 1.35;
        }

        .npl-team-short-name {
            font-size: 9px;
        }

        .npl-team-venue {
            gap: 6px;
            margin-top: 10px;
            font-size: 11px;
        }

        .npl-team-card-footer {
            align-items: center;
            gap: 5px;
            margin-top: auto;
            padding: 12px 0;
            font-size: 10px;
        }

        .npl-team-footer-arrow {
            width: 24px;
            height: 24px;
            flex-basis: 24px;
        }
    }

    @media (max-width: 360px) {
        .npl-team-card {
            grid-template-columns: 105px minmax(0, 1fr);
        }

        .npl-team-logo-stage {
            min-height: 140px;
        }

        .npl-team-card-content {
            padding-inline: 10px;
        }

        .npl-team-card-content h3 {
            font-size: 14px;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .npl-teams-page *,
        .npl-teams-page *::before,
        .npl-teams-page *::after {
            scroll-behavior: auto !important;
            transition-duration: .01ms !important;
            animation-duration: .01ms !important;
        }
    }
</style>
@endsection
