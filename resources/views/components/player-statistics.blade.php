
@php
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

            if ($value !== null && $value !== '') {
                return $value;
            }
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
        if (!$path) {
            return null;
        }

        if (Str::startsWith($path, ['https://', 'http://', '//'])) {
            return $path;
        }

        $path = ltrim(str_replace('\\', '/', $path), '/');
        $path = preg_replace('#^(public/|storage/)#', '', $path);

        if (Str::startsWith($path, ['assets/', 'images/'])) {
            return asset($path);
        }

        if (!Str::startsWith($path, $folder . '/')) {
            $path = $folder . '/' . $path;
        }

        return asset('storage/' . $path);
    };

    $statistics = [];

    $getRecord = function ($entry, $teamId, $kind) use (
        &$statistics,
        $players,
        $playersByName,
        $teams,
        $readValue,
        $assetUrl
    ) {
        if (!is_array($entry) && !is_object($entry)) {
            return;
        }

        $playerId = $readValue($entry, [
            'player_id', 'batter_id', 'bowler_id', 'id',
        ]);

        $name = $readValue($entry, [
            'name', 'player_name', 'batter_name', 'bowler_name',
            'batsman', 'batter', 'bowler', 'player',
        ]);

        if (is_array($name) || is_object($name)) {
            $name = $readValue($name, ['name', 'player_name']);
        }

        $player = $playerId ? $players->get($playerId) : null;

        if (!$player && is_string($name) && trim($name) !== '') {
            $player = $playersByName->get(Str::lower(trim($name)));
        }

        if ($player) {
            $playerId = $player->id;
            $name = $player->name;
        }

        if (!is_string($name) || trim($name) === '') {
            return;
        }

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
                'runs' => 0,
                'wickets' => 0,
                'sixes' => 0,
                'centuries' => 0,
                'half_centuries' => 0,
            ];
        }

        if ($kind === 'batter') {
            $runs = (int) $readValue($entry, [
                'runs', 'total_runs', 'score',
            ], 0);

            $statistics[$key]['runs'] += $runs;

            $sixes = $readValue($entry, [
                'sixes', 'six', 'six_count', '6s',
            ]);

            if ($sixes !== null) {
                $statistics[$key]['sixes'] += (int) $sixes;
            }

            if ($runs >= 100) {
                $statistics[$key]['centuries']++;
            } elseif ($runs >= 50) {
                $statistics[$key]['half_centuries']++;
            }
        }

        if ($kind === 'bowler') {
            $statistics[$key]['wickets'] += (int) $readValue(
                $entry,
                ['wickets', 'wicket', 'total_wickets'],
                0
            );
        }

        if ($player) {
            $statistics[$key]['photo'] = $assetUrl(
                $player->photo_path ?? null,
                'players'
            );

            $statistics[$key]['team_id'] = $player->team_id ?? $teamId;
            $team = $teams->get($statistics[$key]['team_id']);

            $statistics[$key]['team_name'] = $team->name ?? '';
            $statistics[$key]['team_short'] = $team->short_name ?? '';
            $statistics[$key]['team_logo'] = $assetUrl(
                $team->logo ?? null,
                'teams'
            );
        }
    };

    foreach ($scorecards as $scorecard) {
        foreach ($decodeList($scorecard->batters ?? null) as $batter) {
            $getRecord($batter, $scorecard->batting_team_id ?? null, 'batter');
        }

        foreach ($decodeList($scorecard->bowlers ?? null) as $bowler) {
            $getRecord($bowler, $scorecard->bowling_team_id ?? null, 'bowler');
        }
    }

    $allPlayers = collect(array_values($statistics));

    $leaderboardDefinitions = [
        [
            'key' => 'runs',
            'title' => 'MOST RUNS',
            'subtitle' => 'Leading run scorers',
            'unit' => 'RUNS',
            'number' => '01',
            'theme' => 'runs',
        ],
        [
            'key' => 'wickets',
            'title' => 'MOST WICKETS',
            'subtitle' => 'Leading wicket takers',
            'unit' => 'WICKETS',
            'number' => '02',
            'theme' => 'wickets',
        ],
        [
            'key' => 'sixes',
            'title' => 'MOST SIXES',
            'subtitle' => 'The big hitters',
            'unit' => 'SIXES',
            'number' => '03',
            'theme' => 'sixes',
        ],
        [
            'key' => 'centuries',
            'title' => 'MOST CENTURIES',
            'subtitle' => 'Hundreds scored',
            'unit' => 'CENTURIES',
            'number' => '04',
            'theme' => 'centuries',
        ],
        [
            'key' => 'half_centuries',
            'title' => 'MOST HALF-CENTURIES',
            'subtitle' => 'Fifties scored',
            'unit' => 'HALF-CENTURIES',
            'number' => '05',
            'theme' => 'fifties',
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

    $hasAnyStatistics = $leaderboards->contains(
        fn ($board) => $board['players']->isNotEmpty()
    );
@endphp

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

        @if ($hasAnyStatistics)
            <div class="npl-leaderboard-stack">
                @foreach ($leaderboards as $board)
                    @if ($board['players']->isNotEmpty())
                        <section class="npl-stat-group npl-stat-{{ $board['theme'] }}">

                            <header class="npl-stat-group-heading">
                                <div class="npl-group-number">
                                    {{ $board['number'] }}
                                </div>

                                <div class="npl-group-title">
                                    <h3>{{ $board['title'] }}</h3>
                                    <p>{{ $board['subtitle'] }}</p>
                                </div>

                                <div class="npl-group-rule"></div>

                                <span class="npl-group-season">SEASON STATS</span>
                            </header>

                            <div class="npl-trading-grid">
                                @foreach ($board['players'] as $index => $player)
                                    <article class="npl-trading-card npl-place-{{ $index + 1 }}">

                                        <div class="npl-card-topline">
                                            <span class="npl-card-series">NPL / PLAYER FILE</span>
                                            <span class="npl-card-edition">
                                                #{{ str_pad($index + 1, 02, '0', STR_PAD_LEFT) }}
                                            </span>
                                        </div>

                                        <div class="npl-card-art">
                                            <div class="npl-art-glow"></div>

                                            <span class="npl-art-wordmark">
                                                {{ Str::upper($player['team_short'] ?: 'NPL') }}
                                            </span>

                                            <div class="npl-card-country">
                                                <span class="npl-country-dot"></span>
                                                NEPAL PREMIER LEAGUE
                                            </div>

                                            <div class="npl-card-rank">
                                                <span>RANK</span>
                                                <strong>{{ $index + 1 }}</strong>
                                            </div>

                                            @if (!empty($player['photo']))
                                                <img
                                                    class="npl-card-player-photo"
                                                    src="{{ $player['photo'] }}"
                                                    alt="{{ $player['name'] }}"
                                                    loading="lazy"
                                                    onerror="this.style.display='none';this.nextElementSibling.style.display='flex';"
                                                >
                                                <div class="npl-card-photo-placeholder" style="display:none">
                                                    <i class="fa-solid fa-user"></i>
                                                </div>
                                            @else
                                                <div class="npl-card-photo-placeholder">
                                                    <i class="fa-solid fa-user"></i>
                                                </div>
                                            @endif

                                            <div class="npl-card-image-fade"></div>

                                            <div class="npl-card-nameplate">
                                                <span class="npl-card-player-label">FEATURED PLAYER</span>
                                                <h4>{{ $player['name'] }}</h4>

                                                <div class="npl-card-teamline">
                                                    @if (!empty($player['team_logo']))
                                                        <img
                                                            src="{{ $player['team_logo'] }}"
                                                            alt="{{ $player['team_name'] }}"
                                                            loading="lazy"
                                                            onerror="this.style.display='none'"
                                                        >
                                                    @endif

                                                    <span>
                                                        {{ $player['team_name'] ?: 'NPL PLAYER' }}
                                                    </span>
                                                </div>
                                            </div>

                                            <span class="npl-card-frame npl-frame-one"></span>
                                            <span class="npl-card-frame npl-frame-two"></span>
                                        </div>

                                        <div class="npl-card-stat-panel">
                                            <div class="npl-card-stat-label">
                                                <span>{{ $board['title'] }}</span>
                                                <i class="fa-solid fa-chart-line"></i>
                                            </div>

                                            <div class="npl-card-stat-value">
                                                <strong>
                                                    {{ number_format((int) $player[$board['key']]) }}
                                                </strong>

                                                <span>{{ $board['unit'] }}</span>
                                            </div>

                                            <div class="npl-card-stat-footer">
                                                <span class="npl-card-season-tag">
                                                    <i class="fa-solid fa-shield-halved"></i>
                                                    NPL SEASON HUB
                                                </span>

                                                <span class="npl-card-place">
                                                    TOP {{ $index + 1 }}
                                                </span>
                                            </div>
                                        </div>

                                        <div class="npl-card-bottom-edge"></div>
                                    </article>
                                @endforeach
                            </div>
                        </section>
                    @endif
                @endforeach
            </div>
        @else
            <div class="npl-collectibles-empty">
                <i class="fa-solid fa-chart-column"></i>
                <h3>Player rankings coming soon</h3>
                <p>Statistics will appear when scorecards have been recorded.</p>
            </div>
        @endif

    </div>
</section>

<style>
    .npl-collectibles {
        --npl-red: #c51f36;
        --npl-blue: #1557a6;
        --npl-dark: #101725;
        --npl-paper: #f1f2f4;
        padding: 62px 0 70px;
        overflow: hidden;
        background: #f6f6f7;
        color: #171b25;
        font-family: inherit;
    }

    .npl-collectibles *,
    .npl-collectibles *::before,
    .npl-collectibles *::after {
        box-sizing: border-box;
    }

    .npl-collectibles-inner {
        width: min(1220px, calc(100% - 40px));
        margin: 0 auto;
    }

    .npl-collectibles-heading {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 34px;
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
        font-size: clamp(30px, 4.5vw, 46px);
        line-height: 1;
        font-weight: 950;
        letter-spacing: -1.8px;
    }

    .npl-collectibles-heading h2 span {
        color: var(--npl-red);
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
        font-size: 28px;
        font-weight: 950;
        font-style: italic;
        letter-spacing: -2px;
    }

    .npl-heading-mark i {
        display: grid;
        width: 42px;
        height: 42px;
        place-items: center;
        border: 1px solid #e0d5d8;
        border-radius: 50%;
        font-size: 15px;
    }

    .npl-leaderboard-stack {
        display: flex;
        flex-direction: column;
        gap: 37px;
    }

    .npl-stat-group {
        min-width: 0;
    }

    .npl-stat-group-heading {
        display: flex;
        align-items: center;
        gap: 13px;
        margin-bottom: 19px;
    }

    .npl-group-number {
        display: grid;
        flex: 0 0 43px;
        width: 43px;
        height: 43px;
        place-items: center;
        background: #171d2a;
        color: #fff;
        font-size: 13px;
        font-weight: 900;
        transform: skewX(-7deg);
    }

    .npl-group-title {
        flex: 0 0 auto;
    }

    .npl-group-title h3 {
        margin: 0;
        color: #171b25;
        font-size: clamp(17px, 2vw, 22px);
        font-weight: 950;
        letter-spacing: -.45px;
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
        background: #dcdfe5;
    }

    .npl-group-season {
        color: #777e8c;
        font-size: 9px;
        font-weight: 900;
        letter-spacing: 1.1px;
        white-space: nowrap;
    }

    /* Trading-card layout: three independent player cards per category. */
    .npl-trading-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 23px;
        align-items: stretch;
    }

    .npl-trading-card {
        position: relative;
        min-width: 0;
        overflow: hidden;
        padding: 10px;
        background: #e5e7eb;
        isolation: isolate;
        box-shadow: 0 8px 24px rgba(19, 27, 43, .09);
        transition: transform .22s ease, box-shadow .22s ease;
    }

    .npl-trading-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 32px rgba(19, 27, 43, .16);
    }

    .npl-card-topline {
        position: relative;
        z-index: 2;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        padding: 5px 5px 9px;
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
        min-height: 360px;
        overflow: hidden;
        isolation: isolate;
        background:
            linear-gradient(145deg, rgba(255,255,255,.35), transparent 48%),
            linear-gradient(145deg, #e8edf5 0%, #b9cce7 48%, #e2e5ec 100%);
        clip-path: polygon(
            0 5%, 9% 0, 73% 0, 80% 3%, 100% 3%,
            100% 95%, 91% 100%, 20% 100%, 13% 97%, 0 97%
        );
    }

    .npl-card-art::before {
        content: "";
        position: absolute;
        z-index: 1;
        inset: 7px;
        border: 1px solid rgba(255,255,255,.82);
        clip-path: polygon(
            0 5%, 9% 0, 73% 0, 80% 3%, 100% 3%,
            100% 95%, 91% 100%, 20% 100%, 13% 97%, 0 97%
        );
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
        clip-path: polygon(
            0 5%, 9% 0, 73% 0, 80% 3%, 100% 3%,
            100% 95%, 91% 100%, 20% 100%, 13% 97%, 0 97%
        );
        pointer-events: none;
    }

    .npl-art-glow {
        position: absolute;
        top: 18%;
        left: 50%;
        width: 75%;
        aspect-ratio: 1;
        border-radius: 50%;
        background: rgba(255,255,255,.45);
        filter: blur(30px);
        transform: translateX(-50%);
    }

    .npl-art-wordmark {
        position: absolute;
        z-index: 0;
        top: 19%;
        left: 50%;
        width: 140%;
        overflow: hidden;
        color: rgba(255,255,255,.46);
        font-size: clamp(44px, 6vw, 83px);
        font-weight: 1000;
        line-height: 1;
        letter-spacing: -4px;
        text-align: center;
        white-space: nowrap;
        transform: translateX(-50%) rotate(-11deg);
        pointer-events: none;
    }

    .npl-card-country {
        position: absolute;
        z-index: 3;
        top: 15px;
        left: 17px;
        display: flex;
        align-items: center;
        gap: 6px;
        max-width: 70%;
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
        box-shadow: 0 0 0 3px rgba(197,31,54,.12);
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
        text-shadow: 0 1px 0 rgba(255,255,255,.35);
    }

    .npl-card-rank span {
        font-size: 8px;
        font-weight: 900;
        letter-spacing: 1.1px;
    }

    .npl-card-rank strong {
        font-size: 37px;
        line-height: 1;
        font-weight: 1000;
        letter-spacing: -2px;
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
        filter: drop-shadow(0 7px 9px rgba(13,24,45,.18));
        transform: translateX(-50%);
        transition: transform .35s ease;
    }

    .npl-trading-card:hover .npl-card-player-photo {
        transform: translateX(-50%) scale(1.035);
    }

    .npl-card-photo-placeholder {
        position: absolute;
        z-index: 1;
        inset: 24% 0 0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: rgba(25,48,82,.35);
        font-size: 100px;
    }

    .npl-card-image-fade {
        position: absolute;
        z-index: 2;
        inset: 53% 0 0;
        background: linear-gradient(
            to bottom,
            transparent 0%,
            rgba(16,23,37,.12) 40%,
            rgba(16,23,37,.87) 100%
        );
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
        color: rgba(255,255,255,.8);
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
        text-shadow: 0 2px 7px rgba(0,0,0,.2);
    }

    .npl-card-teamline {
        display: flex;
        align-items: center;
        gap: 7px;
        margin-top: 9px;
        color: rgba(255,255,255,.92);
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

    .npl-card-stat-panel {
        position: relative;
        margin-top: 10px;
        padding: 14px 14px 12px;
        border: 1px solid rgba(25,37,59,.11);
        background: #fff;
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
        font-size: clamp(35px, 4vw, 49px);
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

    .npl-card-stat-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 6px;
        margin-top: 13px;
        padding-top: 10px;
        border-top: 1px solid #e9ebf0;
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

    .npl-card-place {
        padding: 5px 7px;
        background: #172238;
        color: #fff;
        font-size: 8px;
        font-weight: 950;
        letter-spacing: .7px;
        white-space: nowrap;
    }

    .npl-card-bottom-edge {
        position: absolute;
        right: 10px;
        bottom: 0;
        left: 10px;
        height: 4px;
        background: linear-gradient(90deg, var(--npl-blue) 0 49%, var(--npl-red) 51% 100%);
    }

    /* Category-specific accents */
    .npl-stat-wickets .npl-card-art {
        background:
            linear-gradient(145deg, rgba(255,255,255,.3), transparent 48%),
            linear-gradient(145deg, #e6edf8, #b8cce8 50%, #e7e9ef);
    }

    .npl-stat-wickets .npl-card-stat-value strong,
    .npl-stat-wickets .npl-card-stat-label i {
        color: #1b58a6;
    }

    .npl-stat-sixes .npl-card-art {
        background:
            linear-gradient(145deg, rgba(255,255,255,.3), transparent 48%),
            linear-gradient(145deg, #f5eadb, #eccbaf 50%, #e8e4e2);
    }

    .npl-stat-sixes .npl-card-stat-value strong,
    .npl-stat-sixes .npl-card-stat-label i {
        color: #b65e1b;
    }

    .npl-stat-centuries .npl-card-art {
        background:
            linear-gradient(145deg, rgba(255,255,255,.3), transparent 48%),
            linear-gradient(145deg, #eee8f7, #d1c3e7 50%, #e8e5ee);
    }

    .npl-stat-centuries .npl-card-stat-value strong,
    .npl-stat-centuries .npl-card-stat-label i {
        color: #6b43a6;
    }

    .npl-stat-fifties .npl-card-art {
        background:
            linear-gradient(145deg, rgba(255,255,255,.3), transparent 48%),
            linear-gradient(145deg, #e5f1e9, #badbc6 50%, #e6ece8);
    }

    .npl-stat-fifties .npl-card-stat-value strong,
    .npl-stat-fifties .npl-card-stat-label i {
        color: #26754b;
    }

    .npl-collectibles-empty {
        padding: 55px 20px;
        background: #fff;
        border: 1px solid #e4e7ed;
        text-align: center;
    }

    .npl-collectibles-empty > i {
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
            gap: 27px;
        }

        .npl-card-art {
            min-height: 405px;
        }
    }

    @media (max-width: 760px) {
        .npl-collectibles {
            padding: 43px 0 50px;
        }

        .npl-collectibles-inner {
            width: calc(100% - 24px);
        }

        .npl-trading-grid {
            gap: 12px;
        }

        .npl-trading-card {
            padding: 7px;
        }

        .npl-card-art {
            min-height: 270px;
        }

        .npl-card-country {
            top: 12px;
            left: 11px;
            font-size: 7px;
        }

        .npl-card-rank {
            top: 35px;
            left: 11px;
        }

        .npl-card-rank strong {
            font-size: 29px;
        }

        .npl-card-nameplate {
            right: 11px;
            bottom: 15px;
            left: 11px;
        }

        .npl-card-nameplate h4 {
            font-size: clamp(13px, 2.4vw, 20px);
        }

        .npl-card-teamline {
            gap: 5px;
            font-size: 8px;
        }

        .npl-card-teamline img {
            width: 20px;
            height: 20px;
        }

        .npl-card-stat-panel {
            padding: 10px 9px;
        }

        .npl-card-stat-label {
            font-size: 7px;
            letter-spacing: .5px;
        }

        .npl-card-stat-value {
            gap: 5px;
        }

        .npl-card-stat-value strong {
            font-size: clamp(25px, 4.5vw, 36px);
        }

        .npl-card-stat-value span {
            font-size: 8px;
        }

        .npl-card-stat-footer {
            align-items: flex-start;
            flex-direction: column;
            gap: 7px;
        }

        .npl-card-season-tag {
            font-size: 7px;
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
            flex-basis: 35px;
            width: 35px;
            height: 35px;
        }

        .npl-group-title h3 {
            font-size: 16px;
        }

        .npl-trading-grid {
            gap: 7px;
        }

        .npl-trading-card {
            padding: 5px;
        }

        .npl-card-topline {
            padding: 4px 3px 7px;
            font-size: 6px;
            letter-spacing: .4px;
        }

        .npl-card-art {
            min-height: 215px;
        }

        .npl-card-country {
            top: 9px;
            left: 8px;
            max-width: 80%;
            font-size: 6px;
            letter-spacing: 0;
        }

        .npl-card-rank {
            top: 28px;
            left: 8px;
        }

        .npl-card-rank span {
            font-size: 6px;
        }

        .npl-card-rank strong {
            font-size: 24px;
        }

        .npl-card-player-photo {
            height: 80%;
        }

        .npl-card-nameplate {
            right: 8px;
            bottom: 12px;
            left: 8px;
        }

        .npl-card-player-label {
            font-size: 6px;
            letter-spacing: .5px;
        }

        .npl-card-nameplate h4 {
            font-size: 12px;
            letter-spacing: -.2px;
        }

        .npl-card-teamline {
            margin-top: 6px;
            font-size: 7px;
        }

        .npl-card-teamline img {
            width: 17px;
            height: 17px;
        }

        .npl-card-stat-panel {
            margin-top: 6px;
            padding: 8px 6px;
        }

        .npl-card-stat-label {
            font-size: 6px;
        }

        .npl-card-stat-value strong {
            font-size: clamp(20px, 6vw, 29px);
            letter-spacing: -1px;
        }

        .npl-card-stat-value span {
            font-size: 6px;
            letter-spacing: 0;
        }

        .npl-card-stat-footer {
            margin-top: 8px;
            padding-top: 7px;
        }

        .npl-card-season-tag {
            font-size: 6px;
            letter-spacing: 0;
        }

        .npl-card-place {
            padding: 4px 5px;
            font-size: 6px;
        }
    }
</style>
