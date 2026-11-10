<?php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| NPL Season Hub — Player Trading Card Leaderboards
|--------------------------------------------------------------------------
*/

$players = DB::table('players')->get()->keyBy('id');
$teams = DB::table('teams')->get()->keyBy('id');

$playersByName = $players->keyBy(
fn ($player) => Str::lower(trim($player->name ?? ''))
);

$scorecards = DB::table('manual_scorecards')
->orderBy('fixture_id')
->orderBy('innings_number')
->get();

$readValue = function ($item, array $keys, $default = null) {
foreach ($keys as $key) {
$value = data_get($item, $key);
if ($value !== null && $value !== '') return $value;
}
return $default;
};

$decodeList = function ($value) {
if (is_string($value)) {
$decoded = json_decode($value, true);
return is_array($decoded) ? $decoded : [];
}
return is_array($value) ? $value : [];
};

$assetUrl = function ($path, $folder) {
if (!$path) return null;
if (Str::startsWith($path, ['https://', 'http://', '//'])) return $path;
$path = ltrim(str_replace('\\', '/', $path), '/');
$path = preg_replace('#^(public/|storage/)#', '', $path);
if (Str::startsWith($path, ['assets/', 'images/'])) return asset($path);
if (!Str::startsWith($path, $folder . '/')) $path = $folder . '/' . $path;
return asset('storage/' . $path);
};

$statistics = [];

$getRecord = function ($entry, $teamId, $kind, $fixtureId = null) use (
&$statistics, $players, $playersByName, $teams, $readValue, $assetUrl
) {
if (!is_array($entry) && !is_object($entry)) return;

$playerId = $readValue($entry, ['player_id', 'batter_id', 'bowler_id', 'id']);
$name = $readValue($entry, ['name', 'player_name', 'batter_name', 'bowler_name', 'batsman', 'batter', 'bowler',
'player']);

if (is_array($name) || is_object($name)) {
$name = $readValue($name, ['name', 'player_name']);
}

$player = $playerId ? $players->get($playerId) : null;
if (!$player && is_string($name) && trim($name) !== '') {
$player = $playersByName->get(Str::lower(trim($name)));
}

if ($player) { $playerId = $player->id; $name = $player->name; }
if (!is_string($name) || trim($name) === '') return;

$name = trim($name);
$teamId = $player->team_id ?? $teamId;
$team = $teamId ? $teams->get($teamId) : null;

$key = $player
? 'player_' . $player->id
: 'name_' . Str::lower($name) . '_team_' . ($teamId ?? 'unknown');

if (!isset($statistics[$key])) {
$statistics[$key] = [
'id' => $player->id ?? $playerId,
'name' => $name,
'team_id' => $teamId,
'team_name' => $team->name ?? '',
'team_short' => $team->short_name ?? '',
'team_logo' => $assetUrl($team->logo ?? null, 'teams'),
'photo' => $assetUrl($player->photo_path ?? null, 'players'),
'runs' => 0, 'wickets' => 0, 'sixes' => 0,
'centuries' => 0, 'half_centuries' => 0,
'fastest_fifty_balls' => null,
'fastest_century_balls' => null,
'matches' => 0,
'fixtures' => [],
];
}

// Count each fixture only once per player.
if ($fixtureId) {
$statistics[$key]['fixtures'][(string) $fixtureId] = true;
}

if ($kind === 'batter') {
$runs = (int) $readValue($entry, ['runs', 'total_runs', 'score'], 0);
$statistics[$key]['runs'] += $runs;

$sixes = $readValue($entry, ['sixes', 'six', 'six_count', '6s']);
if ($sixes !== null) $statistics[$key]['sixes'] += (int) $sixes;

// Balls faced — try common keys.
$balls = $readValue($entry, ['balls', 'balls_faced', 'balls_played', 'deliveries', 'bf']);
$balls = $balls !== null ? (int) $balls : null;

if ($runs >= 100) {
$statistics[$key]['centuries']++;
if ($balls !== null && $balls > 0) {
$current = $statistics[$key]['fastest_century_balls'];
if ($current === null || $balls < $current) { $statistics[$key]['fastest_century_balls']=$balls; } } } elseif ($runs>=
    50) {
    $statistics[$key]['half_centuries']++;
    if ($balls !== null && $balls > 0) {
    $current = $statistics[$key]['fastest_fifty_balls'];
    if ($current === null || $balls < $current) { $statistics[$key]['fastest_fifty_balls']=$balls; } } } } if
        ($kind==='bowler' ) { $statistics[$key]['wickets'] +=(int) $readValue($entry, ['wickets', 'wicket'
        , 'total_wickets' ], 0); } if ($player) { $statistics[$key]['photo']=$assetUrl($player->photo_path ?? null,
        'players');
        $statistics[$key]['team_id'] = $player->team_id ?? $teamId;
        $team = $teams->get($statistics[$key]['team_id']);
        $statistics[$key]['team_name'] = $team->name ?? '';
        $statistics[$key]['team_short'] = $team->short_name ?? '';
        $statistics[$key]['team_logo'] = $assetUrl($team->logo ?? null, 'teams');
        }
        };

        foreach ($scorecards as $scorecard) {
        $fixtureId = $scorecard->fixture_id ?? null;

        foreach ($decodeList($scorecard->batters ?? null) as $batter) {
        $getRecord($batter, $scorecard->batting_team_id ?? null, 'batter', $fixtureId);
        }
        foreach ($decodeList($scorecard->bowlers ?? null) as $bowler) {
        $getRecord($bowler, $scorecard->bowling_team_id ?? null, 'bowler', $fixtureId);
        }
        }

        // Finalise the matches-played count.
        foreach ($statistics as $k => $row) {
        $statistics[$k]['matches'] = count($row['fixtures']);
        unset($statistics[$k]['fixtures']);
        }

        $allPlayers = collect(array_values($statistics));

        /*
        |--------------------------------------------------------------------------
        | Leaderboard definitions
        |--------------------------------------------------------------------------
        | Order requested:
        | 01 Most Half-Centuries (with fastest fifty balls)
        | 02 Most Centuries (with fastest century balls) ← replaced Most Matches
        | 03 Most Runs
        | 04 Most Wickets
        | 05 Most Sixes
        | 06 Most Matches
        */

        $leaderboardDefinitions = [
        [
        'key' => 'half_centuries',
        'title' => 'MOST HALF-CENTURIES',
        'subtitle' => 'Fifties scored · fastest fifty highlighted',
        'unit' => 'HALF-CENTURIES',
        'number' => '01',
        'theme' => 'fifties',
        'icon' => 'fa-solid fa-star',
        'balls_key' => 'fastest_fifty_balls',
        'balls_label' => 'FASTEST 50',
        ],
        [
        'key' => 'centuries',
        'title' => 'MOST CENTURIES',
        'subtitle' => 'Hundreds scored · fastest century highlighted',
        'unit' => 'CENTURIES',
        'number' => '02',
        'theme' => 'centuries',
        'icon' => 'fa-solid fa-crown',
        'balls_key' => 'fastest_century_balls',
        'balls_label' => 'FASTEST 100',
        ],
        [
        'key' => 'runs',
        'title' => 'MOST RUNS',
        'subtitle' => 'Leading run scorers',
        'unit' => 'RUNS',
        'number' => '03',
        'theme' => 'runs',
        'icon' => 'fa-solid fa-baseball-bat-ball',
        ],
        [
        'key' => 'wickets',
        'title' => 'MOST WICKETS',
        'subtitle' => 'Leading wicket takers',
        'unit' => 'WICKETS',
        'number' => '04',
        'theme' => 'wickets',
        'icon' => 'fa-solid fa-bullseye',
        ],
        [
        'key' => 'sixes',
        'title' => 'MOST SIXES',
        'subtitle' => 'The big hitters',
        'unit' => 'SIXES',
        'number' => '05',
        'theme' => 'sixes',
        'icon' => 'fa-solid fa-fire',
        ],
        [
        'key' => 'matches',
        'title' => 'MOST MATCHES',
        'subtitle' => 'Most appearances',
        'unit' => 'MATCHES',
        'number' => '06',
        'theme' => 'matches',
        'icon' => 'fa-solid fa-flag-checkered',
        ],
        ];

        $leaderboards = collect($leaderboardDefinitions)->map(function ($board) use ($allPlayers) {
        $board['players'] = $allPlayers
        ->filter(fn ($player) => (int) $player[$board['key']] > 0)
        ->sortByDesc($board['key'])
        ->take(3)
        ->values();
        return $board;
        });

        $hasAnyStatistics = $leaderboards->contains(fn ($board) => $board['players']->isNotEmpty());
        ?>

        <section class="npl-collectibles" id="player-statistics">
            <div class="npl-collectibles-inner">

                <header class="npl-collectibles-heading">
                    <div>
                        <span class="npl-kicker">
                            <span class="npl-kicker-line"></span>
                            NEPAL PREMIER LEAGUE
                        </span>
                        <h2>SEASON <span>LEADERS</span></h2>
                        <p>Celebrating the players shaping the season.</p>
                    </div>
                    <div class="npl-heading-mark">
                        <span>NPL</span>
                        <i class="fa-solid fa-arrow-trend-up"></i>
                    </div>
                </header>

                <?php if($hasAnyStatistics): ?>
                <div class="npl-leaderboard-stack">
                    <?php $__currentLoopData = $leaderboards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $board): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if($board['players']->isNotEmpty()): ?>
                    <section class="npl-stat-group npl-stat-<?php echo e($board['theme']); ?>">

                        <header class="npl-stat-group-heading">
                            <div class="npl-group-number">
                                <span><?php echo e($board['number']); ?></span>
                            </div>
                            <div class="npl-group-title">
                                <h3><i class="<?php echo e($board['icon']); ?>"></i><?php echo e($board['title']); ?></h3>
                                <p><?php echo e($board['subtitle']); ?></p>
                            </div>
                            <div class="npl-group-rule"></div>
                            <span class="npl-group-season">SEASON STATS</span>
                        </header>

                        <div class="npl-trading-grid">
                            <?php $__currentLoopData = $board['players']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $player): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                            $ballsKey = $board['balls_key'] ?? null;
                            $ballsLabel = $board['balls_label'] ?? null;
                            $fastestBalls = $ballsKey ? ($player[$ballsKey] ?? null) : null;
                            ?>
                            <article class="npl-trading-card npl-place-<?php echo e($index + 1); ?>" data-rank="<?php echo e($index + 1); ?>">
                                <div class="npl-card-holo"></div>
                                <div class="npl-card-shine"></div>

                                <div class="npl-card-topline">
                                    <span class="npl-card-series">NPL / PLAYER FILE</span>
                                    <span class="npl-card-edition">
                                        #<?php echo e(str_pad($index + 1, 02, '0', STR_PAD_LEFT)); ?>

                                    </span>
                                </div>

                                <div class="npl-card-art">
                                    <div class="npl-art-glow"></div>

                                    <?php if($index === 0): ?>
                                    <div class="npl-sparkles">
                                        <span></span><span></span><span></span>
                                        <span></span><span></span><span></span>
                                    </div>
                                    <?php endif; ?>

                                    <div class="npl-art-wordmark">
                                        <span class="npl-wordmark-line npl-wordmark-main">
                                            <?php echo e(Str::upper($player['team_short'] ?: 'NPL')); ?>

                                        </span>
                                        <span class="npl-wordmark-line npl-wordmark-hashtag">
                                            #HamroJeet
                                        </span>
                                    </div>

                                    <div class="npl-card-country">
                                        <span class="npl-country-dot"></span>
                                        NEPAL PREMIER LEAGUE
                                    </div>

                                    <div class="npl-card-rank">
                                        <span>RANK</span>
                                        <strong><?php echo e($index + 1); ?></strong>
                                    </div>

                                    <div class="npl-card-match-badge" title="<?php echo e($player['matches']); ?> matches played">
                                        <i class="fa-solid fa-flag-checkered"></i>
                                        <span><?php echo e((int) $player['matches']); ?></span>
                                        <small>PLAYED</small>
                                    </div>

                                    <?php if(!empty($player['photo'])): ?>
                                    <img class="npl-card-player-photo" src="<?php echo e($player['photo']); ?>"
                                        alt="<?php echo e($player['name']); ?>" loading="lazy"
                                        onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                                    <div class="npl-card-photo-placeholder" style="display:none">
                                        <i class="fa-solid fa-user"></i>
                                    </div>
                                    <?php else: ?>
                                    <div class="npl-card-photo-placeholder">
                                        <i class="fa-solid fa-user"></i>
                                    </div>
                                    <?php endif; ?>

                                    <div class="npl-card-image-fade"></div>

                                    <div class="npl-card-nameplate">
                                        <span class="npl-card-player-label">FEATURED PLAYER</span>
                                        <h4><?php echo e($player['name']); ?></h4>
                                        <div class="npl-card-teamline">
                                            <?php if(!empty($player['team_logo'])): ?>
                                            <img src="<?php echo e($player['team_logo']); ?>" alt="<?php echo e($player['team_name']); ?>"
                                                loading="lazy" onerror="this.style.display='none'">
                                            <?php endif; ?>
                                            <span><?php echo e($player['team_name'] ?: 'NPL PLAYER'); ?></span>
                                        </div>
                                    </div>

                                    <span class="npl-card-frame npl-frame-one"></span>
                                    <span class="npl-card-frame npl-frame-two"></span>
                                </div>

                                <div class="npl-card-stat-panel">
                                    <div class="npl-card-stat-label">
                                        <span><?php echo e($board['title']); ?></span>
                                        <i class="<?php echo e($board['icon']); ?>"></i>
                                    </div>
                                    <div class="npl-card-stat-value">
                                        <strong><?php echo e(number_format((int) $player[$board['key']])); ?></strong>
                                        <span><?php echo e($board['unit']); ?></span>
                                    </div>

                                    <?php if($fastestBalls): ?>
                                    <div class="npl-card-fastest">
                                        <i class="fa-solid fa-bolt"></i>
                                        <span><?php echo e($ballsLabel); ?></span>
                                        <strong><?php echo e((int) $fastestBalls); ?></strong>
                                        <em>balls</em>
                                    </div>
                                    <?php endif; ?>

                                    <div class="npl-card-stat-footer">
                                        <span class="npl-card-season-tag">
                                            <i class="fa-solid fa-shield-halved"></i>
                                            NPL SEASON HUB
                                        </span>
                                        <div class="npl-card-meta">
                                            <span class="npl-card-matches">
                                                <i class="fa-solid fa-flag-checkered"></i>
                                                <?php echo e($player['matches']); ?> <?php echo e(Str::plural('MATCH', $player['matches'])); ?>

                                            </span>
                                            <span class="npl-card-place">TOP <?php echo e($index + 1); ?></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="npl-card-bottom-edge"></div>
                            </article>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </section>
                    <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <?php else: ?>
                <div class="npl-collectibles-empty">
                    <i class="fa-solid fa-chart-column"></i>
                    <h3>Player rankings coming soon</h3>
                    <p>Statistics will appear when scorecards have been recorded.</p>
                </div>
                <?php endif; ?>

            </div>
        </section>

        <style>
        .npl-collectibles {
            --npl-red: #c51f36;
            --npl-blue: #1557a6;
            --npl-dark: #101725;
            --npl-paper: #f1f2f4;
            --npl-gold: #d4a543;
            padding: 70px 0 80px;
            overflow: hidden;
            background:
                radial-gradient(ellipse at top left, rgba(197, 31, 54, .04), transparent 50%),
                radial-gradient(ellipse at bottom right, rgba(21, 87, 166, .05), transparent 50%),
                #f6f6f7;
            color: #171b25;
            font-family: inherit;
            position: relative;
        }

        .npl-collectibles::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(0, 0, 0, .02) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0, 0, 0, .02) 1px, transparent 1px);
            background-size: 40px 40px;
            pointer-events: none;
            mask-image: radial-gradient(ellipse at center, black 30%, transparent 80%);
            -webkit-mask-image: radial-gradient(ellipse at center, black 30%, transparent 80%);
        }

        .npl-collectibles *,
        .npl-collectibles *::before,
        .npl-collectibles *::after {
            box-sizing: border-box;
        }

        .npl-collectibles-inner {
            width: min(1220px, calc(100% - 40px));
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        .npl-collectibles-heading {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 40px;
        }

        .npl-kicker {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 13px;
            color: #51596a;
            font-size: 10px;
            font-weight: 900;
            letter-spacing: 2.2px;
        }

        .npl-kicker-line {
            width: 28px;
            height: 3px;
            background: var(--npl-red);
        }

        .npl-collectibles-heading h2 {
            margin: 0;
            color: #151a26;
            font-size: clamp(32px, 4.8vw, 52px);
            line-height: 1;
            font-weight: 950;
            letter-spacing: -2px;
            background: linear-gradient(135deg, #151a26 0%, #151a26 60%, #c51f36 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .npl-collectibles-heading h2 span {
            background: linear-gradient(135deg, #c51f36, #e84461);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .npl-collectibles-heading p {
            margin: 12px 0 0;
            color: #727887;
            font-size: 14px;
        }

        .npl-heading-mark {
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--npl-red);
        }

        .npl-heading-mark span {
            font-size: 30px;
            font-weight: 950;
            font-style: italic;
            letter-spacing: -2px;
        }

        .npl-heading-mark i {
            display: grid;
            width: 44px;
            height: 44px;
            place-items: center;
            border: 1px solid #e0d5d8;
            border-radius: 50%;
            font-size: 15px;
            transition: all .3s ease;
        }

        .npl-heading-mark:hover i {
            transform: rotate(-8deg) scale(1.08);
            background: var(--npl-red);
            color: #fff;
            border-color: var(--npl-red);
        }

        .npl-leaderboard-stack {
            display: flex;
            flex-direction: column;
            gap: 45px;
        }

        .npl-stat-group {
            min-width: 0;
        }

        .npl-stat-group-heading {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 22px;
        }

        .npl-group-number {
            position: relative;
            display: grid;
            flex: 0 0 46px;
            width: 46px;
            height: 46px;
            place-items: center;
            background: linear-gradient(135deg, #1a2231 0%, #0e141f 100%);
            color: #fff;
            font-size: 14px;
            font-weight: 900;
            transform: skewX(-7deg);
            box-shadow: 0 4px 10px rgba(16, 23, 37, .25);
        }

        .npl-group-number::before {
            content: "";
            position: absolute;
            inset: 2px;
            border: 1px solid rgba(255, 255, 255, .15);
            transform: skewX(7deg);
        }

        .npl-group-number span {
            transform: skewX(7deg);
        }

        .npl-group-title h3 {
            margin: 0;
            color: #171b25;
            font-size: clamp(17px, 2vw, 22px);
            font-weight: 950;
            letter-spacing: -.45px;
            display: flex;
            align-items: center;
            gap: 9px;
        }

        .npl-group-title h3 i {
            color: var(--npl-red);
            font-size: .8em;
        }

        .npl-stat-wickets .npl-group-title h3 i {
            color: #1b58a6;
        }

        .npl-stat-sixes .npl-group-title h3 i {
            color: #b65e1b;
        }

        .npl-stat-centuries .npl-group-title h3 i {
            color: #6b43a6;
        }

        .npl-stat-fifties .npl-group-title h3 i {
            color: #26754b;
        }

        .npl-stat-matches .npl-group-title h3 i {
            color: #7c4a2d;
        }

        .npl-group-title p {
            margin: 4px 0 0;
            color: #737987;
            font-size: 12px;
        }

        .npl-group-rule {
            flex: 1;
            height: 1px;
            min-width: 12px;
            background: linear-gradient(90deg, #dcdfe5, transparent);
        }

        .npl-group-season {
            color: #777e8c;
            font-size: 9px;
            font-weight: 900;
            letter-spacing: 1.1px;
            white-space: nowrap;
        }

        .npl-trading-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 25px;
            align-items: stretch;
            perspective: 1400px;
        }

        .npl-trading-card {
            position: relative;
            min-width: 0;
            overflow: hidden;
            padding: 11px;
            background: #e5e7eb;
            isolation: isolate;
            box-shadow:
                0 4px 12px rgba(19, 27, 43, .08),
                0 16px 32px -12px rgba(19, 27, 43, .12);
            transition: transform .3s cubic-bezier(.2, .9, .3, 1.2), box-shadow .3s ease;
            transform-style: preserve-3d;
            will-change: transform;
        }

        .npl-trading-card::before {
            content: "";
            position: absolute;
            inset: 0;
            padding: 1px;
            background: linear-gradient(135deg, rgba(255, 255, 255, .9), rgba(197, 31, 54, .4), rgba(21, 87, 166, .5), rgba(255, 255, 255, .9));
            background-size: 300% 300%;
            -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            animation: npl-border-flow 6s linear infinite;
            pointer-events: none;
            z-index: 10;
            opacity: .8;
        }

        @keyframes npl-border-flow {
            0% {
                background-position: 0% 50%;
            }

            100% {
                background-position: 300% 50%;
            }
        }

        .npl-trading-card:hover {
            transform: translateY(-8px) rotateX(2deg) rotateY(-2deg);
            box-shadow:
                0 6px 20px rgba(19, 27, 43, .12),
                0 30px 50px -15px rgba(19, 27, 43, .25),
                0 0 60px -10px rgba(197, 31, 54, .25);
        }

        .npl-card-holo {
            position: absolute;
            inset: 0;
            background: conic-gradient(from 0deg,
                    rgba(255, 0, 128, .12), rgba(0, 200, 255, .12),
                    rgba(255, 220, 0, .12), rgba(180, 0, 255, .12),
                    rgba(255, 0, 128, .12));
            opacity: 0;
            transition: opacity .4s ease;
            mix-blend-mode: color-dodge;
            pointer-events: none;
            z-index: 5;
            animation: npl-holo-spin 8s linear infinite;
        }

        @keyframes npl-holo-spin {
            to {
                transform: rotate(360deg) scale(1.5);
            }
        }

        .npl-trading-card:hover .npl-card-holo {
            opacity: 1;
        }

        .npl-card-shine {
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at var(--mx, 50%) var(--my, 50%),
                    rgba(255, 255, 255, .35) 0%,
                    rgba(255, 255, 255, .08) 25%,
                    transparent 55%);
            opacity: 0;
            transition: opacity .3s ease;
            pointer-events: none;
            z-index: 6;
        }

        .npl-trading-card:hover .npl-card-shine {
            opacity: 1;
        }

        .npl-card-topline {
            position: relative;
            z-index: 7;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            padding: 6px 6px 10px;
            color: #4d5666;
            font-size: 8px;
            font-weight: 900;
            letter-spacing: 1.1px;
        }

        .npl-card-edition {
            color: var(--npl-red);
        }

        .npl-card-art {
            position: relative;
            min-height: 380px;
            overflow: hidden;
            isolation: isolate;
            background:
                linear-gradient(145deg, rgba(255, 255, 255, .35), transparent 48%),
                linear-gradient(145deg, #e8edf5 0%, #b9cce7 48%, #e2e5ec 100%);
            clip-path: polygon(0 5%, 9% 0, 73% 0, 80% 3%, 100% 3%, 100% 95%, 91% 100%, 20% 100%, 13% 97%, 0 97%);
        }

        .npl-card-art::before {
            content: "";
            position: absolute;
            z-index: 1;
            inset: 7px;
            border: 1px solid rgba(255, 255, 255, .82);
            clip-path: polygon(0 5%, 9% 0, 73% 0, 80% 3%, 100% 3%, 100% 95%, 91% 100%, 20% 100%, 13% 97%, 0 97%);
            pointer-events: none;
        }

        .npl-card-art::after {
            content: "";
            position: absolute;
            z-index: 2;
            inset: 0;
            border-top: 5px solid var(--npl-blue);
            border-right: 5px solid var(--npl-red);
            border-bottom: 5px solid var(--npl-red);
            border-left: 5px solid var(--npl-blue);
            clip-path: polygon(0 5%, 9% 0, 73% 0, 80% 3%, 100% 3%, 100% 95%, 91% 100%, 20% 100%, 13% 97%, 0 97%);
            pointer-events: none;
        }

        .npl-art-glow {
            position: absolute;
            top: 18%;
            left: 50%;
            width: 75%;
            aspect-ratio: 1;
            border-radius: 50%;
            background: rgba(255, 255, 255, .45);
            filter: blur(30px);
            transform: translateX(-50%);
            animation: npl-glow-pulse 4s ease-in-out infinite;
        }

        @keyframes npl-glow-pulse {

            0%,
            100% {
                opacity: .7;
                transform: translateX(-50%) scale(1);
            }

            50% {
                opacity: 1;
                transform: translateX(-50%) scale(1.1);
            }
        }

        .npl-art-wordmark {
            position: absolute;
            z-index: 0;
            top: 16%;
            left: 50%;
            width: 150%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 2px;
            pointer-events: none;
            transform: translateX(-50%) rotate(-11deg);
            text-align: center;
            white-space: nowrap;
            overflow: hidden;
        }

        .npl-wordmark-line {
            display: block;
            line-height: .95;
            font-weight: 1000;
            letter-spacing: -3px;
            color: rgba(255, 255, 255, .42);
            text-shadow: 0 2px 8px rgba(255, 255, 255, .35);
        }

        .npl-wordmark-main {
            font-size: clamp(48px, 6.6vw, 92px);
        }

        .npl-wordmark-hashtag {
            font-size: clamp(22px, 2.9vw, 40px);
            font-style: italic;
            font-weight: 950;
            letter-spacing: -1.5px;
            color: rgba(197, 31, 54, .28);
            text-shadow: 0 2px 6px rgba(197, 31, 54, .15);
        }

        .npl-place-1 .npl-wordmark-line {
            color: rgba(255, 255, 255, .5);
        }

        .npl-place-1 .npl-wordmark-hashtag {
            color: rgba(212, 165, 67, .35);
            text-shadow: 0 2px 6px rgba(212, 165, 67, .2);
        }

        .npl-card-country {
            position: absolute;
            z-index: 3;
            top: 15px;
            left: 17px;
            display: flex;
            align-items: center;
            gap: 6px;
            max-width: 60%;
            color: #23314a;
            font-size: 8px;
            font-weight: 950;
            letter-spacing: .65px;
        }

        .npl-country-dot {
            width: 7px;
            height: 7px;
            flex: 0 0 7px;
            border-radius: 50%;
            background: var(--npl-red);
            box-shadow: 0 0 0 3px rgba(197, 31, 54, .12);
            animation: npl-dot-pulse 2s ease-in-out infinite;
        }

        @keyframes npl-dot-pulse {

            0%,
            100% {
                box-shadow: 0 0 0 3px rgba(197, 31, 54, .12);
            }

            50% {
                box-shadow: 0 0 0 5px rgba(197, 31, 54, .05);
            }
        }

        .npl-card-rank {
            position: absolute;
            z-index: 4;
            top: 42px;
            left: 17px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 0;
            color: #163f7d;
            text-shadow: 0 1px 0 rgba(255, 255, 255, .35);
        }

        .npl-card-rank span {
            font-size: 8px;
            font-weight: 900;
            letter-spacing: 1.1px;
        }

        .npl-card-rank strong {
            font-size: 40px;
            line-height: 1;
            font-weight: 1000;
            letter-spacing: -2px;
            background: linear-gradient(180deg, #163f7d, #0d2a58);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .npl-place-1 .npl-card-rank strong {
            background: linear-gradient(180deg, #d4a543, #8a6a1f);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .npl-card-match-badge {
            position: absolute;
            z-index: 4;
            top: 42px;
            right: 17px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 1px;
            min-width: 44px;
            padding: 6px 8px;
            background: rgba(255, 255, 255, .82);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            border: 1px solid rgba(21, 87, 166, .25);
            box-shadow: 0 4px 12px rgba(13, 24, 45, .12);
            color: #0f3c78;
            text-align: center;
        }

        .npl-card-match-badge i {
            font-size: 9px;
            color: var(--npl-red);
            margin-bottom: 1px;
        }

        .npl-card-match-badge span {
            font-size: 16px;
            font-weight: 1000;
            line-height: 1;
            letter-spacing: -.5px;
            color: #0f3c78;
        }

        .npl-card-match-badge small {
            font-size: 6px;
            font-weight: 900;
            letter-spacing: .8px;
            color: #4c5c78;
        }

        .npl-place-1 .npl-card-match-badge {
            background: linear-gradient(135deg, rgba(255, 248, 220, .95), rgba(255, 235, 180, .85));
            border-color: rgba(212, 165, 67, .5);
            color: #6a4d10;
        }

        .npl-place-1 .npl-card-match-badge i,
        .npl-place-1 .npl-card-match-badge small {
            color: #8a6a1f;
        }

        .npl-place-1 .npl-card-match-badge span {
            color: #6a4d10;
        }

        .npl-card-player-photo {
            position: absolute;
            z-index: 1;
            bottom: 0;
            left: 50%;
            display: block;
            width: 100%;
            height: 84%;
            object-fit: contain;
            object-position: center bottom;
            filter: drop-shadow(0 7px 9px rgba(13, 24, 45, .18));
            transform: translateX(-50%);
            transition: transform .4s cubic-bezier(.2, .9, .3, 1.2);
        }

        .npl-trading-card:hover .npl-card-player-photo {
            transform: translateX(-50%) scale(1.05) translateY(-3px);
        }

        .npl-card-photo-placeholder {
            position: absolute;
            z-index: 1;
            inset: 24% 0 0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(25, 48, 82, .35);
            font-size: 100px;
        }

        .npl-card-image-fade {
            position: absolute;
            z-index: 2;
            inset: 53% 0 0;
            background: linear-gradient(to bottom, transparent 0%, rgba(16, 23, 37, .12) 40%, rgba(16, 23, 37, .87) 100%);
            pointer-events: none;
        }

        .npl-card-nameplate {
            position: absolute;
            z-index: 3;
            right: 17px;
            bottom: 20px;
            left: 17px;
            color: #fff;
        }

        .npl-card-player-label {
            display: inline-block;
            margin-bottom: 5px;
            color: rgba(255, 255, 255, .8);
            font-size: 8px;
            font-weight: 900;
            letter-spacing: 1.2px;
        }

        .npl-card-nameplate h4 {
            margin: 0;
            color: #fff;
            font-size: clamp(18px, 2.1vw, 27px);
            line-height: 1.05;
            font-weight: 1000;
            letter-spacing: -.65px;
            overflow-wrap: anywhere;
            text-shadow: 0 2px 7px rgba(0, 0, 0, .2);
        }

        .npl-card-teamline {
            display: flex;
            align-items: center;
            gap: 7px;
            margin-top: 9px;
            color: rgba(255, 255, 255, .92);
            font-size: 10px;
            font-weight: 750;
        }

        .npl-card-teamline img {
            display: block;
            width: 25px;
            height: 25px;
            padding: 2px;
            border-radius: 50%;
            background: #fff;
            object-fit: contain;
            box-shadow: 0 2px 6px rgba(0, 0, 0, .15);
        }

        .npl-card-frame {
            position: absolute;
            z-index: 4;
            width: 30px;
            height: 13px;
            pointer-events: none;
        }

        .npl-frame-one {
            top: 0;
            right: 0;
            border-top: 4px solid var(--npl-red);
            border-right: 4px solid var(--npl-red);
        }

        .npl-frame-two {
            bottom: 0;
            left: 0;
            border-bottom: 4px solid var(--npl-blue);
            border-left: 4px solid var(--npl-blue);
        }

        .npl-sparkles {
            position: absolute;
            inset: 0;
            pointer-events: none;
            z-index: 2;
        }

        .npl-sparkles span {
            position: absolute;
            width: 4px;
            height: 4px;
            background: #fff;
            border-radius: 50%;
            box-shadow: 0 0 6px 1px rgba(255, 255, 255, .9);
            animation: npl-sparkle 3s ease-in-out infinite;
        }

        .npl-sparkles span:nth-child(1) {
            top: 15%;
            left: 20%;
            animation-delay: 0s;
        }

        .npl-sparkles span:nth-child(2) {
            top: 25%;
            right: 25%;
            animation-delay: .5s;
        }

        .npl-sparkles span:nth-child(3) {
            top: 40%;
            left: 15%;
            animation-delay: 1s;
        }

        .npl-sparkles span:nth-child(4) {
            top: 55%;
            right: 15%;
            animation-delay: 1.5s;
        }

        .npl-sparkles span:nth-child(5) {
            top: 70%;
            left: 30%;
            animation-delay: 2s;
        }

        .npl-sparkles span:nth-child(6) {
            top: 12%;
            right: 35%;
            animation-delay: 2.5s;
        }

        @keyframes npl-sparkle {

            0%,
            100% {
                opacity: 0;
                transform: scale(0);
            }

            50% {
                opacity: 1;
                transform: scale(1.5);
            }
        }

        .npl-place-1 .npl-card-art::after {
            border-color: var(--npl-gold);
            box-shadow: inset 0 0 20px rgba(212, 165, 67, .15);
        }

        .npl-card-stat-panel {
            position: relative;
            margin-top: 10px;
            padding: 15px 15px 13px;
            border: 1px solid rgba(25, 37, 59, .11);
            background: #fff;
            overflow: hidden;
        }

        .npl-card-stat-panel::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, var(--npl-red), var(--npl-blue));
            transition: width .5s cubic-bezier(.2, .9, .3, 1.2);
        }

        .npl-trading-card:hover .npl-card-stat-panel::before {
            width: 100%;
        }

        .npl-card-stat-label {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            color: #596273;
            font-size: 9px;
            font-weight: 900;
            letter-spacing: 1px;
        }

        .npl-card-stat-label i {
            color: var(--npl-red);
            font-size: 12px;
        }

        .npl-card-stat-value {
            display: flex;
            align-items: baseline;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 5px;
            color: #141b29;
        }

        .npl-card-stat-value strong {
            font-size: clamp(35px, 4vw, 52px);
            line-height: 1;
            font-weight: 1000;
            letter-spacing: -2px;
            font-variant-numeric: tabular-nums;
        }

        .npl-card-stat-value span {
            color: var(--npl-red);
            font-size: 10px;
            font-weight: 950;
            letter-spacing: .8px;
        }

        /* Fastest 50 / 100 balls strip */
        .npl-card-fastest {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 10px;
            padding: 6px 10px;
            background: linear-gradient(135deg, rgba(255, 248, 220, .9), rgba(255, 235, 180, .7));
            border: 1px solid rgba(212, 165, 67, .45);
            color: #6a4d10;
            font-size: 8px;
            font-weight: 900;
            letter-spacing: .7px;
            border-radius: 2px;
        }

        .npl-card-fastest i {
            color: #b8860b;
            font-size: 10px;
        }

        .npl-card-fastest strong {
            font-size: 14px;
            font-weight: 1000;
            color: #4a3408;
            letter-spacing: -.3px;
            font-variant-numeric: tabular-nums;
        }

        .npl-card-fastest em {
            font-style: normal;
            font-size: 7px;
            font-weight: 800;
            color: #8a6a1f;
            letter-spacing: .5px;
        }

        .npl-card-stat-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 6px;
            margin-top: 14px;
            padding-top: 11px;
            border-top: 1px solid #e9ebf0;
            flex-wrap: wrap;
        }

        .npl-card-season-tag {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            color: #7b8290;
            font-size: 8px;
            font-weight: 900;
            letter-spacing: .45px;
        }

        .npl-card-season-tag i {
            color: var(--npl-red);
        }

        .npl-card-meta {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }

        .npl-card-matches {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 7px;
            background: rgba(21, 87, 166, .08);
            color: #1557a6;
            border: 1px solid rgba(21, 87, 166, .18);
            font-size: 8px;
            font-weight: 950;
            letter-spacing: .55px;
            white-space: nowrap;
            border-radius: 2px;
        }

        .npl-card-matches i {
            font-size: 9px;
            color: #1557a6;
        }

        .npl-card-place {
            padding: 5px 8px;
            background: linear-gradient(135deg, #1a2231, #0e141f);
            color: #fff;
            font-size: 8px;
            font-weight: 950;
            letter-spacing: .7px;
            white-space: nowrap;
            box-shadow: 0 2px 6px rgba(16, 23, 37, .2);
        }

        .npl-card-bottom-edge {
            position: absolute;
            right: 11px;
            bottom: 0;
            left: 11px;
            height: 4px;
            background: linear-gradient(90deg, var(--npl-blue) 0 49%, var(--npl-red) 51% 100%);
            z-index: 8;
        }

        .npl-stat-wickets .npl-card-art {
            background: linear-gradient(145deg, rgba(255, 255, 255, .3), transparent 48%), linear-gradient(145deg, #e6edf8, #b8cce8 50%, #e7e9ef);
        }

        .npl-stat-wickets .npl-card-stat-value strong,
        .npl-stat-wickets .npl-card-stat-label i {
            color: #1b58a6;
        }

        .npl-stat-sixes .npl-card-art {
            background: linear-gradient(145deg, rgba(255, 255, 255, .3), transparent 48%), linear-gradient(145deg, #f5eadb, #eccbaf 50%, #e8e4e2);
        }

        .npl-stat-sixes .npl-card-stat-value strong,
        .npl-stat-sixes .npl-card-stat-label i {
            color: #b65e1b;
        }

        .npl-stat-centuries .npl-card-art {
            background: linear-gradient(145deg, rgba(255, 255, 255, .3), transparent 48%), linear-gradient(145deg, #eee8f7, #d1c3e7 50%, #e8e5ee);
        }

        .npl-stat-centuries .npl-card-stat-value strong,
        .npl-stat-centuries .npl-card-stat-label i {
            color: #6b43a6;
        }

        .npl-stat-fifties .npl-card-art {
            background: linear-gradient(145deg, rgba(255, 255, 255, .3), transparent 48%), linear-gradient(145deg, #e5f1e9, #badbc6 50%, #e6ece8);
        }

        .npl-stat-fifties .npl-card-stat-value strong,
        .npl-stat-fifties .npl-card-stat-label i {
            color: #26754b;
        }

        .npl-stat-matches .npl-card-art {
            background: linear-gradient(145deg, rgba(255, 255, 255, .3), transparent 48%), linear-gradient(145deg, #f0e6dc, #d8c0a8 50%, #e9e2dd);
        }

        .npl-stat-matches .npl-card-stat-value strong,
        .npl-stat-matches .npl-card-stat-label i {
            color: #7c4a2d;
        }

        .npl-collectibles-empty {
            padding: 55px 20px;
            background: #fff;
            border: 1px solid #e4e7ed;
            text-align: center;
            border-radius: 4px;
        }

        .npl-collectibles-empty>i {
            margin-bottom: 15px;
            color: var(--npl-red);
            font-size: 35px;
        }

        .npl-collectibles-empty h3 {
            margin: 0;
            font-size: 21px;
            font-weight: 900;
        }

        .npl-collectibles-empty p {
            margin: 8px 0 0;
            color: #737987;
            font-size: 13px;
        }

        @media (min-width: 1000px) {
            .npl-trading-grid {
                gap: 28px;
            }

            .npl-card-art {
                min-height: 415px;
            }
        }

        /* ------------------------------------------------------------------ */
        /* Tablet: 2 cards per row                                            */
        /* ------------------------------------------------------------------ */
        @media (max-width: 900px) {
            .npl-trading-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 16px;
            }

            .npl-card-art {
                min-height: 340px;
            }
        }

        @media (max-width: 760px) {
            .npl-collectibles {
                padding: 46px 0 52px;
            }

            .npl-collectibles-inner {
                width: calc(100% - 24px);
            }

            .npl-leaderboard-stack {
                gap: 36px;
            }

            .npl-trading-grid {
                gap: 14px;
                perspective: none;
            }

            .npl-trading-card {
                padding: 7px;
            }

            .npl-trading-card:hover {
                transform: translateY(-4px);
            }

            .npl-card-art {
                min-height: 300px;
            }

            .npl-card-country {
                top: 12px;
                left: 11px;
                font-size: 7px;
                max-width: 55%;
            }

            .npl-card-rank {
                top: 35px;
                left: 11px;
            }

            .npl-card-rank strong {
                font-size: 30px;
            }

            .npl-card-match-badge {
                top: 35px;
                right: 11px;
                min-width: 38px;
                padding: 5px 6px;
            }

            .npl-card-match-badge span {
                font-size: 13px;
            }

            .npl-card-match-badge small {
                font-size: 5px;
            }

            .npl-card-nameplate {
                right: 11px;
                bottom: 15px;
                left: 11px;
            }

            .npl-card-nameplate h4 {
                font-size: clamp(15px, 2.6vw, 22px);
            }

            .npl-card-teamline {
                gap: 5px;
                font-size: 9px;
            }

            .npl-card-teamline img {
                width: 20px;
                height: 20px;
            }

            .npl-card-stat-panel {
                padding: 12px 11px;
            }

            .npl-card-stat-label {
                font-size: 8px;
                letter-spacing: .6px;
            }

            .npl-card-stat-value {
                gap: 6px;
            }

            .npl-card-stat-value strong {
                font-size: clamp(30px, 5vw, 42px);
            }

            .npl-card-stat-value span {
                font-size: 9px;
            }

            .npl-card-stat-footer {
                align-items: flex-start;
                flex-direction: column;
                gap: 7px;
            }

            .npl-card-season-tag {
                font-size: 7px;
            }

            .npl-card-matches {
                font-size: 7px;
                padding: 3px 5px;
            }

            .npl-card-fastest {
                font-size: 7px;
                padding: 5px 8px;
            }

            .npl-card-fastest strong {
                font-size: 12px;
            }
        }

        /* ------------------------------------------------------------------ */
        /* Phone: single column                                              */
        /* ------------------------------------------------------------------ */
        @media (max-width: 560px) {
            .npl-trading-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }

            .npl-card-art {
                min-height: 360px;
            }
        }

        @media (max-width: 480px) {
            .npl-collectibles-heading {
                margin-bottom: 26px;
            }

            .npl-heading-mark {
                gap: 6px;
            }

            .npl-heading-mark span {
                font-size: 22px;
            }

            .npl-heading-mark i {
                width: 34px;
                height: 34px;
            }

            .npl-group-season {
                display: none;
            }

            .npl-group-number {
                flex-basis: 36px;
                width: 36px;
                height: 36px;
                font-size: 11px;
            }

            .npl-group-title h3 {
                font-size: 16px;
            }

            .npl-trading-card {
                padding: 9px;
            }

            .npl-card-topline {
                padding: 5px 4px 8px;
                font-size: 7px;
                letter-spacing: .6px;
            }

            .npl-card-art {
                min-height: 330px;
            }

            .npl-card-country {
                top: 10px;
                left: 10px;
                max-width: 60%;
                font-size: 7px;
                letter-spacing: .3px;
            }

            .npl-card-rank {
                top: 32px;
                left: 10px;
            }

            .npl-card-rank span {
                font-size: 7px;
            }

            .npl-card-rank strong {
                font-size: 30px;
            }

            .npl-card-match-badge {
                top: 32px;
                right: 10px;
                min-width: 38px;
                padding: 5px 6px;
            }

            .npl-card-match-badge i {
                font-size: 8px;
            }

            .npl-card-match-badge span {
                font-size: 13px;
            }

            .npl-card-match-badge small {
                font-size: 6px;
                letter-spacing: .6px;
            }

            .npl-card-player-photo {
                height: 82%;
            }

            .npl-card-nameplate {
                right: 10px;
                bottom: 14px;
                left: 10px;
            }

            .npl-card-player-label {
                font-size: 7px;
                letter-spacing: .8px;
            }

            .npl-card-nameplate h4 {
                font-size: clamp(18px, 5vw, 24px);
                letter-spacing: -.4px;
            }

            .npl-card-teamline {
                margin-top: 7px;
                font-size: 10px;
            }

            .npl-card-teamline img {
                width: 20px;
                height: 20px;
            }

            .npl-card-stat-panel {
                margin-top: 8px;
                padding: 12px 10px 10px;
            }

            .npl-card-stat-label {
                font-size: 8px;
                letter-spacing: .6px;
            }

            .npl-card-stat-value strong {
                font-size: clamp(30px, 8vw, 40px);
                letter-spacing: -1.2px;
            }

            .npl-card-stat-value span {
                font-size: 8px;
                letter-spacing: .3px;
            }

            .npl-card-fastest {
                font-size: 8px;
                padding: 6px 9px;
                gap: 5px;
            }

            .npl-card-fastest strong {
                font-size: 13px;
            }

            .npl-card-fastest em {
                font-size: 7px;
            }

            .npl-card-stat-footer {
                margin-top: 10px;
                padding-top: 8px;
            }

            .npl-card-season-tag {
                font-size: 7px;
                letter-spacing: .2px;
            }

            .npl-card-place {
                padding: 5px 6px;
                font-size: 7px;
            }

            .npl-card-matches {
                font-size: 7px;
                padding: 4px 5px;
                letter-spacing: .4px;
            }

            .npl-card-matches i {
                font-size: 8px;
            }

            .npl-art-wordmark {
                top: 14%;
                gap: 1px;
                transform: translateX(-50%) rotate(-9deg);
            }

            .npl-wordmark-main {
                font-size: clamp(34px, 11vw, 52px);
                letter-spacing: -1.8px;
            }

            .npl-wordmark-hashtag {
                font-size: clamp(14px, 4.8vw, 24px);
                letter-spacing: -0.6px;
            }
        }
        </style>

        <script>
        (function() {
            const init = () => {
                document.querySelectorAll('.npl-trading-card').forEach((card) => {
                    let rafId = null;

                    card.addEventListener('mousemove', (e) => {
                        if (rafId) cancelAnimationFrame(rafId);
                        rafId = requestAnimationFrame(() => {
                            const rect = card.getBoundingClientRect();
                            const x = e.clientX - rect.left;
                            const y = e.clientY - rect.top;
                            const px = (x / rect.width) - 0.5;
                            const py = (y / rect.height) - 0.5;

                            card.style.setProperty('--mx', x + 'px');
                            card.style.setProperty('--my', y + 'px');

                            if (window.matchMedia('(max-width: 900px)').matches) return;

                            card.style.transform =
                                `translateY(-8px) rotateX(${(-py * 6).toFixed(2)}deg) rotateY(${(px * 6).toFixed(2)}deg)`;
                        });
                    });

                    card.addEventListener('mouseleave', () => {
                        if (rafId) cancelAnimationFrame(rafId);
                        card.style.transform = '';
                    });
                });
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', init);
            } else {
                init();
            }
        })();
        </script><?php /**PATH /opt/lampp/htdocs/npl-season-hub/resources/views/components/player-statistics.blade.php ENDPATH**/ ?>