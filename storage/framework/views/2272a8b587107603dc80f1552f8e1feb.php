<?php $__env->startSection('title', 'Nepal Premier League | Official Website'); ?>

<?php $__env->startSection('content'); ?>
<?php
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;

$teams = collect($teams ?? []);
$liveMatches = collect($liveMatches ?? []);
$recentResults = collect($recentResults ?? []);
$latestNews = collect($latestNews ?? []);
$liveScoreByFixture = $liveScoreByFixture ?? collect();
$standings = collect($standings ?? []);
$featuredPlayers = collect($featuredPlayers ?? []);
$sponsors = collect($sponsors ?? []);

$imageUrl = function ($path, $folder = null) {
if (blank($path)) return null;
$path = trim((string) $path);
if (filter_var($path, FILTER_VALIDATE_URL) || str_starts_with($path, 'data:image/')) return $path;
$path = ltrim($path, '/');
if (str_starts_with($path, 'public/')) $path = substr($path, 7);
if (str_starts_with($path, 'storage/')) return asset($path);
if ($folder && !str_starts_with($path, $folder . '/')) $path = $folder . '/' . $path;
return asset('storage/' . $path);
};

$teamLogo = fn ($team) => $imageUrl(data_get($team, 'logo'), 'teams');
$teamName = fn ($team, $fallback = 'Team') => data_get($team, 'name') ?: $fallback;
$teamShortName = fn ($team) => data_get($team, 'short_name')
?: data_get($team, 'short_code')
?: strtoupper(substr($teamName($team), 0, 3));

$matchDate = function ($match) {
$date = data_get($match, 'scheduled_at') ?? data_get($match, 'match_date')
?? data_get($match, 'start_time') ?? data_get($match, 'date');
if (!$date) return null;
try { return Carbon::parse($date); } catch (\Throwable $e) { return null; }
};
$formatDate = fn ($m) => ($d = $matchDate($m)) ? $d->format('d M Y') : 'TBA';
$formatTime = fn ($m) => ($d = $matchDate($m)) ? $d->format('h:i A') : 'TBA';

$isFinished = function ($match) {
$status = strtolower((string) (data_get($match, 'status') ?? data_get($match, 'match_status') ?? ''));
return in_array($status, ['completed','complete','finished','result','abandoned','no_result','no result'], true);
};
$isLive = function ($match) use ($isFinished) {
if ($isFinished($match)) return false;
$status = strtolower((string) (data_get($match, 'status') ?? data_get($match, 'match_status') ?? ''));
return in_array($status, ['live','in_progress','in progress','innings_break','innings break','break'], true);
};
$isUpcoming = function ($match) use ($isFinished, $isLive) {
if ($isFinished($match) || $isLive($match)) return false;
$status = strtolower((string) (data_get($match, 'status') ?? data_get($match, 'match_status') ?? ''));
return in_array($status, ['scheduled','upcoming','pending','fixture','not_started','not started'], true);
};

$inningsFor = function ($match) use ($liveScoreByFixture) {
$id = data_get($match, 'id');
$innings = $liveScoreByFixture instanceof \Illuminate\Support\Collection
? $liveScoreByFixture->get($id, collect())
: ($liveScoreByFixture[$id] ?? collect());
return collect($innings ?? [])->sortBy('innings_number')->values();
};

$scoreFor = function ($match, $team = null) use ($inningsFor) {
$innings = $inningsFor($match);
if ($team) {
$teamId = data_get($team, 'id');
$innings = $innings->filter(function ($i) use ($teamId) {
$battingTeamId = data_get($i, 'batting_team_id') ?? data_get($i, 'team_id') ?? data_get($i, 'battingTeam.id');
return (string) $battingTeamId === (string) $teamId;
});
}
$inning = $innings->sortByDesc(fn ($i) => (int) (data_get($i, 'is_current', false) ? 100000 : 0) + (int) data_get($i,
'innings_number', 0))->first();
if (!$inning) return null;
$runs = data_get($inning, 'total_runs', 0);
$wickets = data_get($inning, 'wickets', 0);
$legalBalls = (int) data_get($inning, 'legal_balls', 0);
$overs = intdiv($legalBalls, 6) . '.' . ($legalBalls % 6);
return ['runs' => $runs, 'wickets' => $wickets, 'overs' => $overs, 'score' => $runs . '/' . $wickets, 'inning' =>
$inning];
};

$matchStatusLabel = function ($match) use ($isLive, $isFinished, $isUpcoming) {
if ($isLive($match)) return 'LIVE';
if ($isFinished($match)) return 'RESULT';
if ($isUpcoming($match)) return 'UPCOMING';
return strtoupper(str_replace('_', ' ', (string) (data_get($match, 'status') ?? 'MATCH')));
};

$liveMatches = $liveMatches->filter(fn ($m) => $isLive($m))->values();
$liveMatch = $liveMatches->first();

$upcomingMatches = collect();
if (isset($nextMatch) && $nextMatch) $upcomingMatches->push($nextMatch);
$upcomingMatches = $upcomingMatches->filter(fn ($m) => $isUpcoming($m))->unique('id')->values();

$featuredMatch = $liveMatch ?: $upcomingMatches->first() ?: $recentResults->first();

$homeTeam = $featuredMatch ? (data_get($featuredMatch, 'homeTeam') ?? data_get($featuredMatch, 'home_team')) : null;
$awayTeam = $featuredMatch ? (data_get($featuredMatch, 'awayTeam') ?? data_get($featuredMatch, 'away_team')) : null;
$homeScore = $featuredMatch ? $scoreFor($featuredMatch, $homeTeam) : null;
$awayScore = $featuredMatch ? $scoreFor($featuredMatch, $awayTeam) : null;
$featuredIsLive = $featuredMatch && $isLive($featuredMatch);
$featuredIsFinished = $featuredMatch && $isFinished($featuredMatch);
$featuredResult = $featuredMatch ? (data_get($featuredMatch, 'result') ?? data_get($featuredMatch, 'result_summary') ??
data_get($featuredMatch, 'status_text')) : null;

$fixtureUrl = fn ($m) => $m && Route::has('fixtures.show') ? route('fixtures.show', $m) : '#';
$teamUrl = fn ($t) => $t && Route::has('teams.show') ? route('teams.show', $t) : '#';
$newsUrl = fn ($a) => $a && Route::has('news.show') ? route('news.show', $a) : '#';
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link
    href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700;800;900&family=Inter:wght@400;500;600;700;800&display=swap"
    rel="stylesheet">

<style>
.npl-home {
    --npl-navy: #0b1526;
    --npl-navy-2: #122036;
    --npl-navy-3: #1a2c47;
    --npl-red: #d81f39;
    --npl-red-dark: #b0172d;
    --npl-gold: #f0c75e;
    --npl-gold-dark: #d4a933;
    --npl-text: #16202f;
    --npl-muted: #6b7280;
    --npl-border: #e6e8ee;
    --npl-bg-soft: #f6f7fa;
    color: var(--npl-text);
    background: #fff;
    font-family: 'Inter', system-ui, sans-serif;
}

.npl-home *,
.npl-home *::before,
.npl-home *::after {
    box-sizing: border-box;
}

.npl-home h1,
.npl-home h2,
.npl-home h3,
.npl-display {
    font-family: 'Barlow Condensed', 'Inter', sans-serif;
}

.npl-container {
    width: min(1240px, calc(100% - 40px));
    margin: 0 auto;
}

.npl-section {
    padding: 56px 0;
}

.npl-section-heading {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 18px;
    margin-bottom: 26px;
}

.npl-section-heading h2 {
    margin: 0;
    color: var(--npl-navy);
    font-size: clamp(28px, 3.4vw, 40px);
    font-weight: 900;
    letter-spacing: .3px;
    text-transform: uppercase;
    line-height: 1;
}

.npl-section-heading h2 span {
    color: var(--npl-red);
}

.npl-section-heading p {
    margin: 8px 0 0;
    color: var(--npl-muted);
    font-size: 14px;
    font-family: 'Inter', sans-serif;
}

.npl-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: var(--npl-red);
    font-size: 12px;
    font-weight: 800;
    letter-spacing: 2px;
    text-transform: uppercase;
    margin-bottom: 10px;
    font-family: 'Inter', sans-serif;
}

.npl-eyebrow::before {
    content: "";
    width: 26px;
    height: 2px;
    background: var(--npl-red);
}

.npl-link {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    color: var(--npl-red);
    font-size: 13px;
    font-weight: 800;
    text-decoration: none;
    text-transform: uppercase;
    letter-spacing: .5px;
    white-space: nowrap;
    font-family: 'Inter', sans-serif;
}

.npl-link:hover {
    color: var(--npl-red-dark);
}

.npl-button {
    display: inline-flex;
    justify-content: center;
    align-items: center;
    gap: 9px;
    min-height: 46px;
    padding: 12px 24px;
    border: 1px solid transparent;
    border-radius: 4px;
    background: var(--npl-red);
    color: #fff;
    font-size: 13px;
    font-weight: 800;
    letter-spacing: .6px;
    text-transform: uppercase;
    text-decoration: none;
    transition: all .2s ease;
    font-family: 'Inter', sans-serif;
}

.npl-button:hover {
    background: var(--npl-red-dark);
    color: #fff;
    transform: translateY(-1px);
}

.npl-button-gold {
    background: var(--npl-gold);
    color: var(--npl-navy);
}

.npl-button-gold:hover {
    background: #fff;
    color: var(--npl-navy);
}

.npl-button-outline {
    border-color: rgba(255, 255, 255, .5);
    background: transparent;
    color: #fff;
}

.npl-button-outline:hover {
    background: rgba(255, 255, 255, .12);
}

/* HERO */
.npl-hero {
    position: relative;
    isolation: isolate;
    overflow: hidden;
    background: var(--npl-navy);
    color: #fff;
    min-height: 620px;
    display: flex;
    align-items: center;
}

.npl-hero-bg {
    position: absolute;
    inset: 0;
    z-index: -2;
}

.npl-hero-bg img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center 35%;
}

.npl-hero-overlay {
    position: absolute;
    inset: 0;
    z-index: -1;
    background: linear-gradient(100deg, rgba(8, 16, 30, .96) 0%, rgba(10, 20, 37, .85) 45%, rgba(10, 20, 37, .35) 100%), radial-gradient(circle at 80% 20%, rgba(216, 31, 57, .35), transparent 55%);
}

.npl-hero-inner {
    display: grid;
    grid-template-columns: 1.15fr .85fr;
    align-items: center;
    gap: 48px;
    padding: 70px 0;
    width: 100%;
}

.npl-hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 7px 14px;
    border: 1px solid rgba(240, 199, 94, .4);
    border-radius: 100px;
    background: rgba(240, 199, 94, .08);
    color: var(--npl-gold);
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 2.4px;
    text-transform: uppercase;
    margin-bottom: 20px;
    font-family: 'Inter', sans-serif;
}

.npl-hero-badge .dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: var(--npl-red);
    box-shadow: 0 0 0 4px rgba(216, 31, 57, .25);
    animation: nplPulse 1.6s infinite;
}

@keyframes nplPulse {

    0%,
    100% {
        opacity: 1;
    }

    50% {
        opacity: .35;
    }
}

.npl-hero h1 {
    margin: 0;
    color: #fff;
    font-size: clamp(44px, 6.4vw, 88px);
    font-weight: 900;
    line-height: .95;
    letter-spacing: .5px;
    text-transform: uppercase;
}

.npl-hero h1 .gold {
    color: var(--npl-gold);
    display: block;
}

.npl-hero h1 .outline {
    color: transparent;
    -webkit-text-stroke: 2px rgba(255, 255, 255, .55);
}

.npl-hero-copy {
    max-width: 520px;
    margin: 22px 0 28px;
    color: #c6d0de;
    font-size: 15.5px;
    line-height: 1.8;
    font-family: 'Inter', sans-serif;
}

.npl-hero-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}

.npl-hero-stats {
    display: flex;
    flex-wrap: wrap;
    gap: 30px;
    margin-top: 38px;
    padding-top: 28px;
    border-top: 1px solid rgba(255, 255, 255, .12);
}

.npl-hero-stat strong {
    display: block;
    color: var(--npl-gold);
    font-family: 'Barlow Condensed', sans-serif;
    font-size: 34px;
    font-weight: 900;
    line-height: 1;
}

.npl-hero-stat span {
    color: #94a3b8;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 1.6px;
    text-transform: uppercase;
    font-family: 'Inter', sans-serif;
}

/* FEATURED MATCH CARD */
.npl-match-card {
    overflow: hidden;
    border-radius: 10px;
    background: #fff;
    color: var(--npl-text);
    box-shadow: 0 30px 70px rgba(0, 0, 0, .45);
    border: 1px solid rgba(255, 255, 255, .1);
}

.npl-match-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 16px 20px;
    background: linear-gradient(90deg, var(--npl-navy), var(--npl-navy-3));
    color: #fff;
}

.npl-match-top strong {
    color: #fff;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 1.4px;
    text-transform: uppercase;
    font-family: 'Inter', sans-serif;
}

.npl-status {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 6px 10px;
    border-radius: 3px;
    background: rgba(255, 255, 255, .14);
    color: #fff;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 1px;
    font-family: 'Inter', sans-serif;
}

.npl-status-live {
    background: var(--npl-red);
}

.npl-status-live::before {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #fff;
    content: "";
    animation: nplPulse 1.4s infinite;
}

.npl-match-body {
    padding: 26px 22px 20px;
}

.npl-match-teams {
    display: grid;
    grid-template-columns: 1fr 46px 1fr;
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
    width: 82px;
    height: 82px;
    margin: 0 auto 12px;
    overflow: hidden;
    border: 2px solid var(--npl-border);
    border-radius: 50%;
    background: #fafbfc;
}

.npl-team-logo img {
    width: 100%;
    height: 100%;
    padding: 8px;
    object-fit: contain;
}

.npl-team-placeholder {
    color: var(--npl-navy);
    font-family: 'Barlow Condensed', sans-serif;
    font-size: 22px;
    font-weight: 900;
}

.npl-match-team h3 {
    margin: 0;
    color: var(--npl-navy);
    font-family: 'Barlow Condensed', sans-serif;
    font-size: 19px;
    font-weight: 800;
    line-height: 1.15;
    text-transform: uppercase;
    overflow-wrap: anywhere;
}

.npl-team-short {
    margin-top: 4px;
    color: var(--npl-muted);
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 1.2px;
    font-family: 'Inter', sans-serif;
}

.npl-team-score {
    margin-top: 10px;
    color: var(--npl-navy);
    font-family: 'Barlow Condensed', sans-serif;
    font-size: 28px;
    font-weight: 900;
    letter-spacing: .5px;
    line-height: 1;
}

.npl-team-overs {
    margin-top: 3px;
    color: var(--npl-muted);
    font-size: 11px;
    font-family: 'Inter', sans-serif;
}

.npl-versus {
    color: #b8c0cc;
    font-family: 'Barlow Condensed', sans-serif;
    font-size: 15px;
    font-weight: 900;
    text-align: center;
}

.npl-match-info {
    margin-top: 22px;
    padding-top: 16px;
    border-top: 1px dashed var(--npl-border);
    color: var(--npl-muted);
    font-size: 12px;
    line-height: 1.7;
    text-align: center;
    font-family: 'Inter', sans-serif;
}

.npl-match-footer {
    padding: 0 22px 22px;
}

.npl-match-footer .npl-button {
    width: 100%;
}

.npl-empty-match {
    padding: 34px 22px;
    text-align: center;
}

.npl-empty-match h3 {
    margin: 0 0 8px;
    color: var(--npl-navy);
    font-family: 'Barlow Condensed', sans-serif;
    font-size: 26px;
    text-transform: uppercase;
}

.npl-empty-match p {
    margin: 0 0 18px;
    color: var(--npl-muted);
    font-size: 14px;
    line-height: 1.7;
    font-family: 'Inter', sans-serif;
}


/* PREMIUM SPONSORS AND OFFICIAL PARTNERS */

/* =========================================================
   NPL OFFICIAL SPONSORS — CLEAN, PROFESSIONAL DESIGN
   ========================================================= */

.npl-sponsor-strip {
    background: #101b2d;
    color: #fff;
    border-top: 3px solid #d51f35;
    border-bottom: 1px solid #e5e7eb;
    padding: 18px 0;
}

.npl-sponsor-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 28px;
    flex-wrap: wrap;
}

.npl-sponsor-label {
    flex: 0 0 auto;
    min-width: 150px;
    color: #fff;
    font-size: 12px;
    font-weight: 800;
    line-height: 1.6;
    letter-spacing: 1.5px;
    text-transform: uppercase;
}

.npl-sponsor-logos {
    display: flex;
    flex: 1 1 420px;
    align-items: center;
    justify-content: flex-end;
    gap: 28px;
    flex-wrap: wrap;
}

.npl-sponsor-logos > a,
.npl-sponsor-logos > span {
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 90px;
    min-height: 44px;
    padding: 5px 10px;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 3px;
    transition: border-color .2s ease;
}

.npl-sponsor-logos > a:hover {
    border-color: #d51f35;
}

.npl-sponsor-logos img {
    display: block;
    max-width: 135px;
    max-height: 43px;
    width: auto;
    height: auto;
    object-fit: contain;
}

/* Main official partners section */
.npl-partners-section {
    position: relative;
    isolation: isolate;
    overflow: hidden;
    padding: clamp(44px, 6vw, 76px) 0;
    background: #f5f6f8;
    color: #172033;
}

.npl-partners-section::before {
    content: none !important;
}

.npl-partners-section .npl-container {
    position: relative;
    z-index: 1;
}

.npl-partners-intro {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 24px;
    margin-bottom: 34px;
    padding-bottom: 24px;
    border-bottom: 1px solid #dce1e8;
}

.npl-partners-intro-main {
    max-width: 720px;
}

.npl-partners-intro .npl-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 9px;
    margin-bottom: 12px;
    color: #c51d32;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 1.8px;
    line-height: 1.5;
    text-transform: uppercase;
}

.npl-partners-intro .npl-eyebrow::before {
    content: "";
    display: inline-block;
    width: 22px;
    height: 2px;
    background: #c51d32;
}

.npl-partners-intro h2 {
    margin: 0;
    color: #142035;
    font-size: clamp(27px, 3.2vw, 42px);
    font-weight: 800;
    letter-spacing: -1.1px;
    line-height: 1.15;
}

.npl-partners-intro h2 span {
    color: #c51d32;
}

.npl-partners-intro p {
    max-width: 570px;
    margin: 13px 0 0;
    color: #596579;
    font-size: 14px;
    line-height: 1.8;
}

.npl-partners-season {
    flex: 0 0 auto;
    color: #354156;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 1.2px;
    text-transform: uppercase;
}

/* Consistent, restrained sponsor cards */
.npl-partners-section [class*="sponsor"],
.npl-partners-section [class*="partner"] {
    box-shadow: none;
}

.npl-partners-section a {
    text-decoration: none;
}

.npl-partners-section img {
    max-width: 100%;
    object-fit: contain;
}

.npl-partners-section [class*="grid"] {
    gap: 16px;
}

.npl-partners-section [class*="card"],
.npl-partners-section [class*="logo"] {
    transition: border-color .18s ease, background-color .18s ease;
}

.npl-partners-section [class*="card"] {
    border-radius: 4px;
}

.npl-partners-section [class*="card"]:hover {
    transform: none;
}

/* Tablet and mobile */
@media (max-width: 768px) {
    .npl-sponsor-strip {
        padding: 15px 0;
    }

    .npl-sponsor-inner {
        align-items: flex-start;
        gap: 14px;
    }

    .npl-sponsor-label {
        min-width: 0;
        width: 100%;
    }

    .npl-sponsor-logos {
        justify-content: flex-start;
        gap: 10px;
        width: 100%;
    }

    .npl-sponsor-logos > a,
    .npl-sponsor-logos > span {
        min-width: 78px;
        min-height: 42px;
        padding: 5px 8px;
    }

    .npl-sponsor-logos img {
        max-width: 105px;
        max-height: 36px;
    }

    .npl-partners-section {
        padding: 42px 0;
    }

    .npl-partners-intro {
        align-items: flex-start;
        flex-direction: column;
        gap: 14px;
        margin-bottom: 24px;
        padding-bottom: 20px;
    }

    .npl-partners-intro h2 {
        font-size: clamp(26px, 7vw, 34px);
        letter-spacing: -.6px;
    }

    .npl-partners-intro p {
        font-size: 13px;
        line-height: 1.75;
    }

    .npl-partners-season {
        font-size: 10px;
    }

    .npl-partners-section [class*="grid"] {
        gap: 10px;
    }
}

@media (prefers-reduced-motion: reduce) {
    .npl-sponsor-strip *,
    .npl-partners-section * {
        transition: none !important;
        animation: none !important;
    }
}


/* PROFESSIONAL SEASON PARTNERS */
.npl-partner-strip {
    background: #fff;
    border-top: 1px solid #e5e7eb;
    border-bottom: 1px solid #e5e7eb;
}

.npl-partner-strip-inner {
    display: flex;
    align-items: center;
    gap: 30px;
    min-height: 92px;
    padding-top: 18px;
    padding-bottom: 18px;
}

.npl-partner-strip-label {
    flex: 0 0 auto;
    max-width: 145px;
    color: #172b4d;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 1.4px;
    line-height: 1.6;
}

.npl-partner-strip-logos {
    display: flex;
    flex: 1;
    align-items: center;
    justify-content: space-between;
    gap: 26px;
    min-width: 0;
}

.npl-partner-strip-logos a,
.npl-partner-strip-logo {
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 0;
}

.npl-partner-strip-logos img {
    display: block;
    width: auto;
    max-width: 130px;
    height: 48px;
    object-fit: contain;
    filter: grayscale(100%);
    opacity: .78;
    transition: opacity .18s ease, filter .18s ease;
}

.npl-partner-strip-logos a:hover img {
    filter: none;
    opacity: 1;
}

.npl-partner-strip-name {
    color: #26354a;
    font-size: 12px;
    font-weight: 700;
}

.npl-partners-section {
    padding: 62px 0 54px;
    background: #f5f7fa;
    color: #172b4d;
}

.npl-partners-heading {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 24px;
    margin-bottom: 34px;
    padding-bottom: 24px;
    border-bottom: 1px solid #dce2e9;
}

.npl-partners-eyebrow,
.npl-partner-type {
    display: inline-block;
    color: #a51f35;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 1.5px;
}

.npl-partners-heading h2 {
    margin: 8px 0 8px;
    color: #142541;
    font-size: clamp(26px, 3vw, 36px);
    font-weight: 800;
    line-height: 1.15;
    letter-spacing: -.7px;
}

.npl-partners-heading p {
    margin: 0;
    color: #68758a;
    font-size: 14px;
    line-height: 1.7;
}

.npl-partners-count {
    flex: 0 0 auto;
    padding: 9px 12px;
    border: 1px solid #d8dfe8;
    background: #fff;
    color: #526177;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: .8px;
}

.npl-partner-section-label {
    display: flex;
    align-items: center;
    gap: 11px;
    margin: 0 0 18px;
}

.npl-partner-section-label > span {
    display: block;
    width: 4px;
    height: 22px;
    background: #b3233b;
}

.npl-partner-section-label h3 {
    margin: 0;
    color: #172b4d;
    font-size: 17px;
    font-weight: 800;
    letter-spacing: -.2px;
}

.npl-title-partner-block {
    margin-bottom: 38px;
}

.npl-title-partner-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 14px;
}

.npl-title-partner-card {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(180px, 32%);
    align-items: center;
    gap: 30px;
    min-height: 190px;
    padding: 30px 34px;
    border: 1px solid #dce2e9;
    border-top: 3px solid #b3233b;
    background: #fff;
}

.npl-title-partner-copy h4 {
    margin: 10px 0;
    color: #142541;
    font-size: clamp(23px, 3vw, 32px);
    font-weight: 800;
    line-height: 1.25;
}

.npl-title-partner-copy h4 a,
.npl-partner-card-details h4 a {
    color: inherit;
    text-decoration: none;
}

.npl-title-partner-copy h4 a:hover,
.npl-partner-card-details h4 a:hover {
    color: #a51f35;
}

.npl-title-partner-copy p {
    max-width: 650px;
    margin: 0;
    color: #68758a;
    font-size: 14px;
    line-height: 1.75;
}

.npl-title-partner-logo {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 115px;
    padding: 16px;
    border-left: 1px solid #e7ebf0;
}

.npl-title-partner-logo img {
    display: block;
    width: auto;
    max-width: 100%;
    max-height: 100px;
    object-fit: contain;
}

.npl-partner-initials {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 54px;
    height: 54px;
    border: 1px solid #d9e0e9;
    background: #f5f7fa;
    color: #263a59;
    font-size: 16px;
    font-weight: 800;
}

.npl-partner-group {
    margin-top: 34px;
}

.npl-partner-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 14px;
}

.npl-partner-card {
    display: flex;
    align-items: flex-start;
    gap: 17px;
    min-width: 0;
    min-height: 145px;
    padding: 22px;
    border: 1px solid #dce2e9;
    background: #fff;
    transition: border-color .18s ease;
}

.npl-partner-card:hover {
    border-color: #aab7c8;
}

.npl-partner-card-logo {
    display: flex;
    flex: 0 0 88px;
    align-items: center;
    justify-content: center;
    width: 88px;
    height: 78px;
    padding: 8px;
    border: 1px solid #edf0f4;
    background: #fff;
}

.npl-partner-card-logo img {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.npl-partner-card-logo .npl-partner-initials {
    width: 44px;
    height: 44px;
}

.npl-partner-card-details {
    min-width: 0;
}

.npl-partner-card-details h4 {
    margin: 1px 0 8px;
    color: #172b4d;
    font-size: 15px;
    font-weight: 800;
    line-height: 1.45;
    overflow-wrap: anywhere;
}

.npl-partner-card-details p {
    margin: 0 0 10px;
    color: #68758a;
    font-size: 12px;
    line-height: 1.7;
    overflow-wrap: anywhere;
}

.npl-partner-visit {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    color: #a51f35;
    font-size: 11px;
    font-weight: 800;
    text-decoration: none;
}

.npl-partner-visit:hover {
    color: #172b4d;
}

.npl-partner-empty {
    padding: 34px 24px;
    border: 1px solid #dce2e9;
    background: #fff;
    text-align: center;
}

.npl-partner-empty strong {
    color: #172b4d;
    font-size: 15px;
}

.npl-partner-empty p {
    margin: 8px 0 0;
    color: #68758a;
    font-size: 13px;
}

.npl-partner-note {
    margin: 30px 0 0;
    padding-top: 17px;
    border-top: 1px solid #dce2e9;
    color: #7b8798;
    font-size: 11px;
    line-height: 1.7;
}

@media (max-width: 1000px) {
    .npl-partner-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .npl-partner-strip-inner {
        gap: 20px;
    }

    .npl-partner-strip-logos {
        justify-content: flex-start;
        flex-wrap: wrap;
    }
}

@media (max-width: 640px) {
    .npl-partner-strip-inner {
        align-items: flex-start;
        flex-direction: column;
        gap: 14px;
        padding-top: 17px;
        padding-bottom: 17px;
    }

    .npl-partner-strip-logos {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        width: 100%;
        gap: 18px 12px;
    }

    .npl-partner-strip-logos img {
        max-width: 100%;
        height: 38px;
    }

    .npl-partners-section {
        padding: 38px 0;
    }

    .npl-partners-heading {
        align-items: flex-start;
        flex-direction: column;
        gap: 15px;
        margin-bottom: 26px;
        padding-bottom: 20px;
    }

    .npl-partners-heading h2 {
        font-size: 27px;
    }

    .npl-title-partner-card {
        grid-template-columns: 1fr;
        gap: 18px;
        padding: 22px;
    }

    .npl-title-partner-logo {
        justify-content: flex-start;
        min-height: 75px;
        padding: 15px 0 0;
        border-top: 1px solid #e7ebf0;
        border-left: 0;
    }

    .npl-title-partner-logo img {
        max-height: 75px;
    }

    .npl-partner-grid {
        grid-template-columns: 1fr;
        gap: 10px;
    }

    .npl-partner-card {
        min-height: 0;
        padding: 16px;
        gap: 14px;
    }

    .npl-partner-card-logo {
        flex-basis: 74px;
        width: 74px;
        height: 68px;
    }
}

/* TICKER */
.npl-ticker {
    background: var(--npl-navy);
    color: #fff;
}

.npl-ticker-inner {
    display: flex;
    align-items: center;
    gap: 18px;
    min-height: 52px;
}

.npl-ticker-label {
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 4px 10px;
    border-radius: 3px;
    background: var(--npl-red);
    color: #fff;
    font-size: 10px;
    font-weight: 900;
    letter-spacing: 1.4px;
    text-transform: uppercase;
    font-family: 'Inter', sans-serif;
}

.npl-ticker-label::before {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #fff;
    content: "";
    animation: nplPulse 1.4s infinite;
}

.npl-ticker-items {
    display: flex;
    flex-wrap: wrap;
    gap: 8px 22px;
    padding: 11px 0;
}

.npl-ticker-items a {
    color: #dbe3ee;
    font-size: 12px;
    font-weight: 600;
    text-decoration: none;
    font-family: 'Inter', sans-serif;
}

.npl-ticker-items a:hover {
    color: var(--npl-gold);
}

/* RESULTS */
.npl-results-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 20px;
}

.npl-result-card {
    overflow: hidden;
    border: 1px solid var(--npl-border);
    border-radius: 8px;
    background: #fff;
    transition: all .2s ease;
}

.npl-result-card:hover {
    border-color: #c3c9d4;
    box-shadow: 0 14px 30px rgba(16, 28, 48, .08);
}

.npl-result-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 13px 16px;
    border-bottom: 1px solid #eef0f4;
    background: var(--npl-bg-soft);
    color: var(--npl-muted);
    font-size: 11px;
    font-family: 'Inter', sans-serif;
}

.npl-result-header strong {
    color: #374151;
    font-weight: 800;
}

.npl-result-header span {
    font-weight: 800;
    letter-spacing: .8px;
    color: var(--npl-red);
}

.npl-result-teams {
    padding: 12px 16px;
}

.npl-result-team {
    display: grid;
    grid-template-columns: 38px minmax(0, 1fr) auto;
    align-items: center;
    gap: 11px;
    padding: 9px 0;
}

.npl-result-team+.npl-result-team {
    border-top: 1px solid #f1f2f5;
}

.npl-result-logo {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
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
    font-family: 'Barlow Condensed', sans-serif;
}

.npl-result-team-name {
    color: #374151;
    font-size: 12.5px;
    font-weight: 700;
    overflow-wrap: anywhere;
    font-family: 'Inter', sans-serif;
}

.npl-result-score {
    color: var(--npl-navy);
    font-family: 'Barlow Condensed', sans-serif;
    font-size: 17px;
    font-weight: 900;
    white-space: nowrap;
}

.npl-result-footer {
    padding: 12px 16px;
    border-top: 1px solid #eef0f4;
    color: var(--npl-muted);
    font-size: 12px;
    line-height: 1.6;
    font-family: 'Inter', sans-serif;
}

.npl-result-footer a {
    color: var(--npl-red);
    font-weight: 800;
    text-decoration: none;
}

/* STANDINGS */
.npl-standings-section {
    background: var(--npl-bg-soft);
}

.npl-table-wrap {
    overflow-x: auto;
    border: 1px solid var(--npl-border);
    border-radius: 8px;
    background: #fff;
}

.npl-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 620px;
    font-family: 'Inter', sans-serif;
}

.npl-table thead th {
    padding: 13px 12px;
    background: var(--npl-navy);
    color: #c8d2df;
    font-size: 10.5px;
    font-weight: 800;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    text-align: center;
    white-space: nowrap;
}

.npl-table thead th:first-child {
    text-align: left;
    padding-left: 16px;
}

.npl-table tbody td {
    padding: 12px;
    border-top: 1px solid #eef0f4;
    color: #374151;
    font-size: 13px;
    text-align: center;
}

.npl-table tbody td:first-child {
    text-align: left;
    padding-left: 16px;
}

.npl-table tbody tr:hover {
    background: #fafbfc;
}

.npl-pos {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    border-radius: 4px;
    background: #eef1f6;
    color: #4b5563;
    font-family: 'Barlow Condensed', sans-serif;
    font-size: 15px;
    font-weight: 900;
}

.npl-pos-top {
    background: var(--npl-gold);
    color: var(--npl-navy);
}

.npl-table-team {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 170px;
}

.npl-table-logo {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    flex-shrink: 0;
    overflow: hidden;
    border: 1px solid var(--npl-border);
    border-radius: 50%;
    background: #fff;
}

.npl-table-logo img {
    width: 100%;
    height: 100%;
    padding: 2px;
    object-fit: contain;
}

.npl-table-logo span {
    color: var(--npl-navy);
    font-family: 'Barlow Condensed', sans-serif;
    font-size: 9px;
    font-weight: 900;
}

.npl-table-name {
    font-weight: 700;
    color: var(--npl-navy);
    font-size: 13px;
}

.npl-table-pts {
    color: var(--npl-navy);
    font-family: 'Barlow Condensed', sans-serif;
    font-size: 17px;
    font-weight: 900;
}

.npl-table-form {
    display: inline-flex;
    gap: 3px;
}

.npl-form-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 20px;
    height: 20px;
    border-radius: 3px;
    font-size: 9px;
    font-weight: 900;
    color: #fff;
    font-family: 'Inter', sans-serif;
}

.npl-form-w {
    background: #16a34a;
}

.npl-form-l {
    background: var(--npl-red);
}

.npl-form-n {
    background: #94a3b8;
}

/* TEAMS */
.npl-teams-grid {
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 16px;
}

.npl-team-card {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-width: 0;
    min-height: 178px;
    padding: 22px 14px;
    border: 1px solid var(--npl-border);
    border-radius: 8px;
    background: #fff;
    color: var(--npl-text);
    text-align: center;
    text-decoration: none;
    overflow: hidden;
    transition: all .25s ease;
}

.npl-team-card::before {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: var(--npl-red);
    content: "";
    transform: scaleX(0);
    transform-origin: left;
    transition: transform .25s ease;
}

.npl-team-card:hover {
    transform: translateY(-4px);
    border-color: #cbd5e1;
    box-shadow: 0 18px 36px rgba(16, 28, 48, .1);
}

.npl-team-card:hover::before {
    transform: scaleX(1);
}

.npl-team-card .npl-team-logo {
    width: 72px;
    height: 72px;
    margin-bottom: 12px;
}

.npl-team-card h3 {
    margin: 0;
    color: var(--npl-navy);
    font-family: 'Barlow Condensed', sans-serif;
    font-size: 17px;
    font-weight: 800;
    line-height: 1.15;
    text-transform: uppercase;
    overflow-wrap: anywhere;
}

.npl-team-card small {
    margin-top: 5px;
    color: var(--npl-muted);
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 1.2px;
    font-family: 'Inter', sans-serif;
}

/* NEWS */
.npl-news-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 22px;
}

.npl-news-card {
    overflow: hidden;
    border: 1px solid var(--npl-border);
    border-radius: 8px;
    background: #fff;
    transition: all .25s ease;
}

.npl-news-card:hover {
    border-color: #c3c9d4;
    box-shadow: 0 18px 36px rgba(16, 28, 48, .1);
    transform: translateY(-3px);
}

.npl-news-image {
    display: block;
    aspect-ratio: 16/9;
    overflow: hidden;
    background: #eef0f4;
}

.npl-news-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform .35s ease;
}

.npl-news-card:hover .npl-news-image img {
    transform: scale(1.04);
}

.npl-news-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    color: #9ca3af;
    font-family: 'Barlow Condensed', sans-serif;
    font-size: 16px;
    font-weight: 800;
    letter-spacing: 2px;
}

.npl-news-content {
    padding: 18px;
}

.npl-news-date {
    color: var(--npl-red);
    font-size: 10.5px;
    font-weight: 800;
    letter-spacing: 1px;
    text-transform: uppercase;
    font-family: 'Inter', sans-serif;
}

.npl-news-content h3 {
    margin: 9px 0;
    font-family: 'Barlow Condensed', sans-serif;
    font-size: 22px;
    font-weight: 800;
    line-height: 1.2;
    text-transform: uppercase;
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
    font-family: 'Inter', sans-serif;
}

/* PLAYERS */
.npl-players-section {
    background: linear-gradient(180deg, var(--npl-navy), var(--npl-navy-2));
    color: #fff;
}

.npl-players-section .npl-section-heading h2 {
    color: #fff;
}

.npl-players-section .npl-section-heading p {
    color: #94a3b8;
}

.npl-players-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 18px;
}

.npl-player-card {
    position: relative;
    overflow: hidden;
    border-radius: 8px;
    background: rgba(255, 255, 255, .05);
    border: 1px solid rgba(255, 255, 255, .1);
    transition: all .25s ease;
}

.npl-player-card:hover {
    transform: translateY(-4px);
    border-color: rgba(240, 199, 94, .5);
}

.npl-player-image {
    aspect-ratio: 4/5;
    overflow: hidden;
    background: rgba(255, 255, 255, .04);
    display: flex;
    align-items: center;
    justify-content: center;
}

.npl-player-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: top center;
}

.npl-player-initials {
    color: rgba(255, 255, 255, .16);
    font-family: 'Barlow Condensed', sans-serif;
    font-size: 64px;
    font-weight: 900;
}

.npl-player-info {
    padding: 14px 16px 16px;
}

.npl-player-role {
    color: var(--npl-gold);
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 1.4px;
    text-transform: uppercase;
    font-family: 'Inter', sans-serif;
}

.npl-player-info h3 {
    margin: 6px 0 0;
    color: #fff;
    font-family: 'Barlow Condensed', sans-serif;
    font-size: 20px;
    font-weight: 800;
    text-transform: uppercase;
    line-height: 1.1;
}

.npl-player-team {
    margin-top: 6px;
    color: #94a3b8;
    font-size: 11.5px;
    font-family: 'Inter', sans-serif;
}

/* NEWSLETTER */
.npl-newsletter {
    background: var(--npl-red);
    color: #fff;
    position: relative;
    overflow: hidden;
}

.npl-newsletter::after {
    content: "";
    position: absolute;
    top: -60px;
    right: -60px;
    width: 260px;
    height: 260px;
    border: 1px solid rgba(255, 255, 255, .18);
    border-radius: 50%;
}

.npl-newsletter-inner {
    position: relative;
    z-index: 1;
    display: grid;
    grid-template-columns: 1fr 1fr;
    align-items: center;
    gap: 30px;
    padding: 44px 0;
}

.npl-newsletter h2 {
    margin: 0;
    color: #fff;
    font-size: clamp(26px, 3.6vw, 40px);
    font-weight: 900;
    text-transform: uppercase;
    line-height: 1;
}

.npl-newsletter p {
    margin: 10px 0 0;
    color: rgba(255, 255, 255, .85);
    font-size: 14px;
    line-height: 1.7;
    font-family: 'Inter', sans-serif;
}

.npl-newsletter-form {
    display: flex;
    gap: 10px;
}

.npl-newsletter-form input {
    flex: 1;
    min-width: 0;
    padding: 13px 16px;
    border: 0;
    border-radius: 4px;
    background: #fff;
    color: var(--npl-navy);
    font-size: 14px;
    font-family: 'Inter', sans-serif;
    outline: none;
}

.npl-newsletter-form button {
    padding: 13px 22px;
    border: 0;
    border-radius: 4px;
    background: var(--npl-navy);
    color: #fff;
    font-size: 12px;
    font-weight: 800;
    letter-spacing: 1px;
    text-transform: uppercase;
    cursor: pointer;
    font-family: 'Inter', sans-serif;
    transition: background .2s ease;
}

.npl-newsletter-form button:hover {
    background: #000;
}

/* EMPTY */
.npl-empty {
    grid-column: 1 / -1;
    padding: 30px 20px;
    border: 1px dashed #d1d5db;
    border-radius: 8px;
    color: var(--npl-muted);
    font-size: 14px;
    text-align: center;
    font-family: 'Inter', sans-serif;
}

/* RESPONSIVE */
@media (max-width: 1024px) {
    .npl-hero-inner {
        grid-template-columns: 1fr;
        gap: 34px;
        padding: 50px 0;
    }

    .npl-hero {
        min-height: 0;
    }

    .npl-match-card {
        max-width: 600px;
    }

    .npl-teams-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }

    .npl-players-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .npl-results-grid,
    .npl-news-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .npl-newsletter-inner {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 640px) {
    .npl-container {
        width: calc(100% - 28px);
    }

    .npl-section {
        padding: 38px 0;
    }

    .npl-hero h1 {
        font-size: clamp(38px, 12vw, 56px);
    }

    .npl-hero-copy {
        font-size: 14px;
    }

    .npl-hero-stats {
        gap: 20px;
    }

    .npl-hero-stat strong {
        font-size: 26px;
    }

    .npl-section-heading {
        align-items: flex-start;
        flex-direction: column;
        gap: 8px;
    }

    .npl-section-heading h2 {
        font-size: 28px;
    }

    .npl-results-grid,
    .npl-news-grid,
    .npl-players-grid {
        grid-template-columns: 1fr;
    }

    .npl-teams-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .npl-team-card {
        min-height: 150px;
    }

    .npl-match-teams {
        grid-template-columns: 1fr 34px 1fr;
    }

    .npl-team-logo {
        width: 64px;
        height: 64px;
    }

    .npl-match-team h3 {
        font-size: 16px;
    }

    .npl-team-score {
        font-size: 24px;
    }

    .npl-ticker-inner {
        align-items: flex-start;
        gap: 10px;
    }

    .npl-ticker-label {
        margin-top: 12px;
    }

    .npl-newsletter-form {
        flex-direction: column;
    }

    .npl-sponsor-inner {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
}

@media (prefers-reduced-motion: reduce) {

    .npl-home *,
    .npl-home *::before,
    .npl-home *::after {
        transition: none !important;
        animation: none !important;
    }
}
</style>

<div class="npl-home">

    
    <section class="npl-hero">
        <div class="npl-hero-bg">
            <img src="https://images.unsplash.com/photo-1540747913346-19e32dc3e97e?auto=format&fit=crop&w=1920&q=80"
                alt="Cricket stadium under floodlights">
        </div>
        <div class="npl-hero-overlay"></div>

        <div class="npl-container npl-hero-inner">
            <div>
                <div class="npl-hero-badge">
                    <span class="dot"></span>
                    Fan  Website of the Nepal Premier League
                </div>

                <h1>
                    Nepal Premier
                    <span class="gold">League</span>
                    <span class="outline">2026</span>
                </h1>

                <p class="npl-hero-copy">
                    The biggest names in Nepali cricket. The loudest crowds in the Himalayas.
                    Follow every six, every wicket and every moment of the NPL season — live.
                </p>

                <div class="npl-hero-actions">
                    <?php if(Route::has('fixtures.index')): ?>
                    <a href="<?php echo e(route('fixtures.index')); ?>" class="npl-button">View Fixtures</a>
                    <?php endif; ?>
                    <?php if(Route::has('teams.index')): ?>
                    <a href="<?php echo e(route('teams.index')); ?>" class="npl-button npl-button-outline">Explore Teams</a>
                    <?php endif; ?>
                </div>

                <div class="npl-hero-stats">
                    <div class="npl-hero-stat">
                        <strong><?php echo e($teams->count() ?: 8); ?></strong>
                        <span>Franchises</span>
                    </div>
                    <div class="npl-hero-stat">
                        <strong><?php echo e($liveMatches->count() + $recentResults->count() + $upcomingMatches->count()); ?></strong>
                        <span>Matches</span>
                    </div>
                    <div class="npl-hero-stat">
                        <strong><?php echo e($latestNews->count()); ?></strong>
                        <span>News Stories</span>
                    </div>
                </div>
            </div>

            <div>
                <div class="npl-match-card">
                    <?php if($featuredMatch): ?>
                    <div class="npl-match-top">
                        <strong>
                            <?php if($featuredIsLive): ?> Live Match Centre
                            <?php elseif($featuredIsFinished): ?> Latest Result
                            <?php else: ?> Featured Fixture <?php endif; ?>
                        </strong>
                        <span class="npl-status <?php echo e($featuredIsLive ? 'npl-status-live' : ''); ?>">
                            <?php echo e($matchStatusLabel($featuredMatch)); ?>

                        </span>
                    </div>

                    <div class="npl-match-body">
                        <div class="npl-match-teams">
                            <div class="npl-match-team">
                                <div class="npl-team-logo">
                                    <?php if($teamLogo($homeTeam)): ?>
                                    <img src="<?php echo e($teamLogo($homeTeam)); ?>" alt="<?php echo e($teamName($homeTeam, 'Home Team')); ?>"
                                        loading="lazy">
                                    <?php else: ?>
                                    <span class="npl-team-placeholder"><?php echo e($teamShortName($homeTeam)); ?></span>
                                    <?php endif; ?>
                                </div>
                                <h3><?php echo e($teamName($homeTeam, 'Home Team')); ?></h3>
                                <div class="npl-team-short"><?php echo e($teamShortName($homeTeam)); ?></div>
                                <?php if($homeScore): ?>
                                <div class="npl-team-score"><?php echo e($homeScore['score']); ?></div>
                                <div class="npl-team-overs">Overs <?php echo e($homeScore['overs']); ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="npl-versus">VS</div>

                            <div class="npl-match-team">
                                <div class="npl-team-logo">
                                    <?php if($teamLogo($awayTeam)): ?>
                                    <img src="<?php echo e($teamLogo($awayTeam)); ?>" alt="<?php echo e($teamName($awayTeam, 'Away Team')); ?>"
                                        loading="lazy">
                                    <?php else: ?>
                                    <span class="npl-team-placeholder"><?php echo e($teamShortName($awayTeam)); ?></span>
                                    <?php endif; ?>
                                </div>
                                <h3><?php echo e($teamName($awayTeam, 'Away Team')); ?></h3>
                                <div class="npl-team-short"><?php echo e($teamShortName($awayTeam)); ?></div>
                                <?php if($awayScore): ?>
                                <div class="npl-team-score"><?php echo e($awayScore['score']); ?></div>
                                <div class="npl-team-overs">Overs <?php echo e($awayScore['overs']); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="npl-match-info">
                            <div><?php echo e($formatDate($featuredMatch)); ?> &nbsp;·&nbsp; <?php echo e($formatTime($featuredMatch)); ?></div>
                            <?php if($featuredResult): ?>
                            <div><?php echo e($featuredResult); ?></div>
                            <?php elseif($featuredIsLive): ?>
                            <div>Follow the match for the latest available score.</div>
                            <?php elseif(!$featuredIsFinished): ?>
                            <div>Match details and updates</div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="npl-match-footer">
                        <a href="<?php echo e($fixtureUrl($featuredMatch)); ?>" class="npl-button">
                            <?php echo e($featuredIsLive ? 'Open Live Match' : ($featuredIsFinished ? 'View Scorecard' : 'View Match Details')); ?>

                        </a>
                    </div>
                    <?php else: ?>
                    <div class="npl-empty-match">
                        <h3>No Featured Match</h3>
                        <p>There is no live match or upcoming fixture available to display right now.</p>
                        <?php if(Route::has('fixtures.index')): ?>
                        <a href="<?php echo e(route('fixtures.index')); ?>" class="npl-button">View All Fixtures</a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    
    <?php if($sponsors->isNotEmpty()): ?>
        <section class="npl-partner-strip" aria-label="Season partners">
            <div class="npl-container npl-partner-strip-inner">
                <span class="npl-partner-strip-label">SEASON PARTNERS</span>

                <div class="npl-partner-strip-logos">
                    <?php $__currentLoopData = $sponsors->take(8); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sponsor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if($sponsor->logo_path): ?>
                            <?php if($sponsor->website_url): ?>
                                <a href="<?php echo e($sponsor->website_url); ?>"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   aria-label="<?php echo e($sponsor->name); ?>">
                                    <img src="<?php echo e($imageUrl($sponsor->logo_path, 'sponsors')); ?>"
                                         alt="<?php echo e($sponsor->name); ?>"
                                         loading="lazy">
                                </a>
                            <?php else: ?>
                                <span class="npl-partner-strip-logo">
                                    <img src="<?php echo e($imageUrl($sponsor->logo_path, 'sponsors')); ?>"
                                         alt="<?php echo e($sponsor->name); ?>"
                                         loading="lazy">
                                </span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="npl-partner-strip-name">
                                <?php echo e($sponsor->name); ?>

                            </span>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    
    <section class="npl-partners-section" id="official-partners">
        <div class="npl-container">

            <div class="npl-partners-heading">
                <div>
                    <span class="npl-partners-eyebrow">OUR COMMERCIAL PARTNERS</span>
                    <h2>Partners &amp; Sponsors</h2>
                    <p>Organisations supporting this season, as configured in the season records.</p>
                </div>
                <span class="npl-partners-count">
                    <?php echo e($sponsors->count()); ?> <?php echo e($sponsors->count() === 1 ? 'PARTNER' : 'PARTNERS'); ?>

                </span>
            </div>

            <?php
                $titleSponsors = $sponsors->filter(function ($sponsor) {
                    return strtolower(trim((string) $sponsor->category)) === 'title sponsor';
                });

                $partnerGroups = $sponsors
                    ->reject(function ($sponsor) {
                        return strtolower(trim((string) $sponsor->category)) === 'title sponsor';
                    })
                    ->groupBy(function ($sponsor) {
                        return trim((string) ($sponsor->category ?: 'Official Partner')) ?: 'Official Partner';
                    });
            ?>

            <?php if($titleSponsors->isNotEmpty()): ?>
                <div class="npl-title-partner-block">
                    <div class="npl-partner-section-label">
                        <span></span>
                        <h3>Title Partner</h3>
                    </div>

                    <div class="npl-title-partner-grid">
                        <?php $__currentLoopData = $titleSponsors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $titleSponsor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <article class="npl-title-partner-card">
                                <div class="npl-title-partner-copy">
                                    <span class="npl-partner-type">TITLE SPONSOR</span>

                                    <h4>
                                        <?php if($titleSponsor->website_url): ?>
                                            <a href="<?php echo e($titleSponsor->website_url); ?>"
                                               target="_blank"
                                               rel="noopener noreferrer">
                                                <?php echo e($titleSponsor->name); ?>

                                            </a>
                                        <?php else: ?>
                                            <?php echo e($titleSponsor->name); ?>

                                        <?php endif; ?>
                                    </h4>

                                    <?php if($titleSponsor->description): ?>
                                        <p><?php echo e($titleSponsor->description); ?></p>
                                    <?php endif; ?>
                                </div>

                                <div class="npl-title-partner-logo">
                                    <?php if($titleSponsor->logo_path): ?>
                                        <?php if($titleSponsor->website_url): ?>
                                            <a href="<?php echo e($titleSponsor->website_url); ?>"
                                               target="_blank"
                                               rel="noopener noreferrer"
                                               aria-label="Visit <?php echo e($titleSponsor->name); ?>">
                                                <img src="<?php echo e($imageUrl($titleSponsor->logo_path, 'sponsors')); ?>"
                                                     alt="<?php echo e($titleSponsor->name); ?>"
                                                     loading="lazy">
                                            </a>
                                        <?php else: ?>
                                            <img src="<?php echo e($imageUrl($titleSponsor->logo_path, 'sponsors')); ?>"
                                                 alt="<?php echo e($titleSponsor->name); ?>"
                                                 loading="lazy">
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="npl-partner-initials">
                                            <?php echo e(strtoupper(substr($titleSponsor->name, 0, 2))); ?>

                                        </span>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php $__currentLoopData = $partnerGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category => $partners): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="npl-partner-group">
                    <div class="npl-partner-section-label">
                        <span></span>
                        <h3><?php echo e($category); ?></h3>
                    </div>

                    <div class="npl-partner-grid">
                        <?php $__currentLoopData = $partners; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $partner): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <article class="npl-partner-card">
                                <div class="npl-partner-card-logo">
                                    <?php if($partner->logo_path): ?>
                                        <img src="<?php echo e($imageUrl($partner->logo_path, 'sponsors')); ?>"
                                             alt="<?php echo e($partner->name); ?>"
                                             loading="lazy">
                                    <?php else: ?>
                                        <span class="npl-partner-initials">
                                            <?php echo e(strtoupper(substr($partner->name, 0, 2))); ?>

                                        </span>
                                    <?php endif; ?>
                                </div>

                                <div class="npl-partner-card-details">
                                    <h4>
                                        <?php if($partner->website_url): ?>
                                            <a href="<?php echo e($partner->website_url); ?>"
                                               target="_blank"
                                               rel="noopener noreferrer">
                                                <?php echo e($partner->name); ?>

                                            </a>
                                        <?php else: ?>
                                            <?php echo e($partner->name); ?>

                                        <?php endif; ?>
                                    </h4>

                                    <?php if($partner->description): ?>
                                        <p><?php echo e($partner->description); ?></p>
                                    <?php endif; ?>

                                    <?php if($partner->website_url): ?>
                                        <a class="npl-partner-visit"
                                           href="<?php echo e($partner->website_url); ?>"
                                           target="_blank"
                                           rel="noopener noreferrer">
                                            Visit website <span aria-hidden="true">↗</span>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <?php if($sponsors->isEmpty()): ?>
                <div class="npl-partner-empty">
                    <strong>No partners have been configured for this season.</strong>
                    <p>Season partner listings will appear here when records are added through the admin panel.</p>
                </div>
            <?php endif; ?>

            <p class="npl-partner-note">
                Partner listings reflect the information configured for this season.
                This independently operated fan website does not imply official
                affiliation with any listed organisation.
            </p>

        </div>
    </section>


    <?php if($liveMatches->isNotEmpty() || $upcomingMatches->isNotEmpty()): ?>
    <section class="npl-ticker">
        <div class="npl-container npl-ticker-inner">
            <div class="npl-ticker-label"><?php echo e($liveMatches->isNotEmpty() ? 'Live Now' : 'Upcoming'); ?></div>
            <div class="npl-ticker-items">
                <?php $__currentLoopData = $liveMatches->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $match): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                $th = data_get($match, 'homeTeam') ?? data_get($match, 'home_team');
                $ta = data_get($match, 'awayTeam') ?? data_get($match, 'away_team');
                ?>
                <a href="<?php echo e($fixtureUrl($match)); ?>">
                    <span style="color:#f0c75e;font-weight:800;">LIVE</span>
                    &nbsp;<?php echo e($teamShortName($th)); ?> vs <?php echo e($teamShortName($ta)); ?>

                </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php $__currentLoopData = $upcomingMatches->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $match): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                $th = data_get($match, 'homeTeam') ?? data_get($match, 'home_team');
                $ta = data_get($match, 'awayTeam') ?? data_get($match, 'away_team');
                ?>
                <a href="<?php echo e($fixtureUrl($match)); ?>">
                    <?php echo e($teamShortName($th)); ?> vs <?php echo e($teamShortName($ta)); ?> · <?php echo e($formatDate($match)); ?>

                </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    
    <section class="npl-section">
        <div class="npl-container">
            <div class="npl-section-heading">
                <div>
                    <div class="npl-eyebrow">Match Centre</div>
                    <h2>Recent <span>Results</span></h2>
                    <p>Scores and outcomes from completed matches.</p>
                </div>
                <?php if(Route::has('fixtures.index')): ?>
                <a class="npl-link" href="<?php echo e(route('fixtures.index')); ?>">All Fixtures →</a>
                <?php endif; ?>
            </div>

            <div class="npl-results-grid">
                <?php $__empty_1 = true; $__currentLoopData = $recentResults->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $match): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                $rh = data_get($match, 'homeTeam') ?? data_get($match, 'home_team');
                $ra = data_get($match, 'awayTeam') ?? data_get($match, 'away_team');
                $rhs = $scoreFor($match, $rh);
                $ras = $scoreFor($match, $ra);
                $resultText = data_get($match, 'result') ?? data_get($match, 'result_summary') ?? data_get($match,
                'status_text');
                ?>
                <article class="npl-result-card">
                    <div class="npl-result-header">
                        <strong><?php echo e($formatDate($match)); ?></strong>
                        <span>FINAL</span>
                    </div>
                    <div class="npl-result-teams">
                        <div class="npl-result-team">
                            <div class="npl-result-logo">
                                <?php if($teamLogo($rh)): ?>
                                <img src="<?php echo e($teamLogo($rh)); ?>" alt="<?php echo e($teamName($rh)); ?>" loading="lazy">
                                <?php else: ?>
                                <span><?php echo e($teamShortName($rh)); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="npl-result-team-name"><?php echo e($teamName($rh, 'Home Team')); ?></div>
                            <div class="npl-result-score"><?php echo e($rhs['score'] ?? '—'); ?></div>
                        </div>
                        <div class="npl-result-team">
                            <div class="npl-result-logo">
                                <?php if($teamLogo($ra)): ?>
                                <img src="<?php echo e($teamLogo($ra)); ?>" alt="<?php echo e($teamName($ra)); ?>" loading="lazy">
                                <?php else: ?>
                                <span><?php echo e($teamShortName($ra)); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="npl-result-team-name"><?php echo e($teamName($ra, 'Away Team')); ?></div>
                            <div class="npl-result-score"><?php echo e($ras['score'] ?? '—'); ?></div>
                        </div>
                    </div>
                    <div class="npl-result-footer">
                        <?php if($resultText): ?>
                        <?php echo e($resultText); ?>

                        <?php else: ?>
                        <a href="<?php echo e($fixtureUrl($match)); ?>">View match scorecard →</a>
                        <?php endif; ?>
                    </div>
                </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="npl-empty">No completed match results are available yet.</div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    
    <?php if($standings->isNotEmpty()): ?>
    <section class="npl-section npl-standings-section">
        <div class="npl-container">
            <div class="npl-section-heading">
                <div>
                    <div class="npl-eyebrow">League Table</div>
                    <h2>NPL <span>Standings</span></h2>
                    <p>Points table and recent form of every franchise.</p>
                </div>
                <?php if(Route::has('standings.index')): ?>
                <a class="npl-link" href="<?php echo e(route('standings.index')); ?>">Full Table →</a>
                <?php endif; ?>
            </div>

            <div class="npl-table-wrap">
                <table class="npl-table">
                    <thead>
                        <tr>
                            <th>Pos</th>
                            <th>Team</th>
                            <th>P</th>
                            <th>W</th>
                            <th>L</th>
                            <th>NR</th>
                            <th>Pts</th>
                            <th>NRR</th>
                            <th>Form</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $standings->take(8); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                        $rowTeam = data_get($row, 'team');
                        $form = collect(data_get($row, 'form', []))->takeLast(5);
                        ?>
                        <tr>
                            <td><span
                                    class="npl-pos <?php echo e($loop->index < 4 ? 'npl-pos-top' : ''); ?>"><?php echo e($loop->iteration); ?></span>
                            </td>
                            <td>
                                <div class="npl-table-team">
                                    <div class="npl-table-logo">
                                        <?php if($teamLogo($rowTeam)): ?>
                                        <img src="<?php echo e($teamLogo($rowTeam)); ?>" alt="<?php echo e($teamName($rowTeam)); ?>"
                                            loading="lazy">
                                        <?php else: ?>
                                        <span><?php echo e($teamShortName($rowTeam)); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <span class="npl-table-name"><?php echo e($teamName($rowTeam)); ?></span>
                                </div>
                            </td>
                            <td><?php echo e(data_get($row, 'played', 0)); ?></td>
                            <td><?php echo e(data_get($row, 'won', 0)); ?></td>
                            <td><?php echo e(data_get($row, 'lost', 0)); ?></td>
                            <td><?php echo e(data_get($row, 'no_result', 0)); ?></td>
                            <td class="npl-table-pts"><?php echo e(data_get($row, 'points', 0)); ?></td>
                            <td><?php echo e(data_get($row, 'nrr', '0.000')); ?></td>
                            <td>
                                <span class="npl-table-form">
                                    <?php $__empty_1 = true; $__currentLoopData = $form; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <?php
                                    $fl = strtolower((string) $f);
                                    $cls = $fl === 'w' ? 'npl-form-w' : ($fl === 'l' ? 'npl-form-l' : 'npl-form-n');
                                    ?>
                                    <span class="npl-form-badge <?php echo e($cls); ?>"><?php echo e(strtoupper($fl)); ?></span>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <span style="color:#9ca3af;">—</span>
                                    <?php endif; ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <?php echo $__env->make('components.player-statistics', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    
    <section class="npl-section">
        <div class="npl-container">
            <div class="npl-section-heading">
                <div>
                    <div class="npl-eyebrow">Franchises</div>
                    <h2>Meet the <span>Teams</span></h2>
                    <p>Eight cities. One trophy. Pick your side.</p>
                </div>
                <?php if(Route::has('teams.index')): ?>
                <a class="npl-link" href="<?php echo e(route('teams.index')); ?>">All Teams →</a>
                <?php endif; ?>
            </div>

            <div class="npl-teams-grid">
                <?php $__empty_1 = true; $__currentLoopData = $teams->take(10); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $team): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <a href="<?php echo e($teamUrl($team)); ?>" class="npl-team-card">
                    <div class="npl-team-logo">
                        <?php if($teamLogo($team)): ?>
                        <img src="<?php echo e($teamLogo($team)); ?>" alt="<?php echo e($teamName($team)); ?> logo" loading="lazy">
                        <?php else: ?>
                        <span class="npl-team-placeholder"><?php echo e($teamShortName($team)); ?></span>
                        <?php endif; ?>
                    </div>
                    <h3><?php echo e($teamName($team)); ?></h3>
                    <small><?php echo e($teamShortName($team)); ?></small>
                </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="npl-empty">Team information will appear here when available.</div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    
    <section class="npl-section" style="background: var(--npl-bg-soft);">
        <div class="npl-container">
            <div class="npl-section-heading">
                <div>
                    <div class="npl-eyebrow">Newsroom</div>
                    <h2>Latest <span>News</span></h2>
                    <p>Match reports, interviews and everything NPL.</p>
                </div>
                <?php if(Route::has('news.index')): ?>
                <a class="npl-link" href="<?php echo e(route('news.index')); ?>">All News →</a>
                <?php endif; ?>
            </div>

            <div class="npl-news-grid">
                <?php $__empty_1 = true; $__currentLoopData = $latestNews->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $article): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                $articleImage = $imageUrl(
    data_get($article, 'featured_image')
    ?? data_get($article, 'thumbnail')
    ?? data_get($article, 'image')
    ?? data_get($article, 'image_path')
);

$articleImage = $articleImage
    ?: 'https://blog.nirmalgaihre.com.np/images/npl.png';$articleTitle = data_get($article, 'title') ?? 'NPL News';
                $articleExcerpt = data_get($article, 'excerpt')
                ?? data_get($article, 'description')
                ?? data_get($article, 'meta_description');
                $articleDate = data_get($article, 'published_at') ?? data_get($article, 'created_at');
                ?>

                <article class="npl-news-card">
                    <a href="<?php echo e($newsUrl($article)); ?>" class="npl-news-image">
    <img
        src="<?php echo e($articleImage); ?>"
        alt="<?php echo e($articleTitle); ?>"
        loading="lazy"
        onerror="this.onerror=null;this.src='https://blog.nirmalgaihre.com.np/images/npl.png';"
    >
</a>
                    <div class="npl-news-content">
                        <div class="npl-news-date">
                            <?php if($articleDate): ?>
                            <?php echo e(\Illuminate\Support\Carbon::parse($articleDate)->format('d M Y')); ?>

                            <?php else: ?>
                            NPL News
                            <?php endif; ?>
                        </div>
                        <h3><a href="<?php echo e($newsUrl($article)); ?>"><?php echo e($articleTitle); ?></a></h3>
                        <?php if($articleExcerpt): ?>
                        <p><?php echo e(\Illuminate\Support\Str::limit(strip_tags($articleExcerpt), 150)); ?></p>
                        <?php endif; ?>
                        <a class="npl-link" href="<?php echo e($newsUrl($article)); ?>">Read Article →</a>
                    </div>
                </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="npl-empty">No published news articles are available at the moment.</div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    
    <?php if($featuredPlayers->isNotEmpty()): ?>
    <section class="npl-section npl-players-section">
        <div class="npl-container">
            <div class="npl-section-heading">
                <div>
                    <div class="npl-eyebrow" style="color: var(--npl-gold);">Stars of the League</div>
                    <h2>Featured <span style="color: var(--npl-gold);">Players</span></h2>
                    <p>The names lighting up the NPL this season.</p>
                </div>
            </div>

            <div class="npl-players-grid">
                <?php $__currentLoopData = $featuredPlayers->take(4); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $player): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                $playerImage = $imageUrl(data_get($player, 'photo') ?? data_get($player, 'image'), 'players');
                $playerName = data_get($player, 'name') ?? data_get($player, 'full_name') ?? 'Player';
                $playerTeam = data_get($player, 'team');
                ?>
                <div class="npl-player-card">
                    <div class="npl-player-image">
                        <?php if($playerImage): ?>
                        <img src="<?php echo e($playerImage); ?>" alt="<?php echo e($playerName); ?>" loading="lazy">
                        <?php else: ?>
                        <span class="npl-player-initials"><?php echo e(strtoupper(substr($playerName, 0, 2))); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="npl-player-info">
                        <div class="npl-player-role"><?php echo e(data_get($player, 'role', 'All-Rounder')); ?></div>
                        <h3><?php echo e($playerName); ?></h3>
                        <?php if($playerTeam): ?>
                        <div class="npl-player-team"><?php echo e($teamName($playerTeam)); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    
    <section class="npl-newsletter">
        <div class="npl-container npl-newsletter-inner">
            <div>
                <h2>Never Miss a Ball</h2>
                <p>Get NPL match alerts, ticket drops and breaking news delivered to your inbox.</p>
            </div>
            <form class="npl-newsletter-form"
                onsubmit="event.preventDefault(); this.reset(); alert('Thanks for subscribing to NPL updates!');">
                <input type="email" placeholder="Enter your email address" required>
                <button type="submit">Subscribe</button>
            </form>
        </div>
    </section>

</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /opt/lampp/htdocs/npl-season-hub/resources/views/home.blade.php ENDPATH**/ ?>