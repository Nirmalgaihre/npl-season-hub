
@php
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Route;

    /*
    |--------------------------------------------------------------------------
    | NPL Match Centre — Responsive Innings Scorecard
    |--------------------------------------------------------------------------
    | Keeps the existing variables, relationships, routes and scorecard data.
    */

    $battingTeam = $inning->battingTeam
        ?? (
            (int) $inning->batting_team_id === (int) $fixture->home_team_id
                ? $fixture->homeTeam
                : $fixture->awayTeam
        );

    $opponentTeam = $inning->bowlingTeam
        ?? (
            (int) $inning->bowling_team_id === (int) $fixture->home_team_id
                ? $fixture->homeTeam
                : $fixture->awayTeam
        );

    $scorecard = $scorecards->get($inning->id, []);

    $batters = data_get($scorecard, 'batters', []);
    $bowlers = data_get($scorecard, 'bowlers', []);
    $extras = data_get($scorecard, 'extras', $inning->extras ?? 0);

    $batters = $batters instanceof \Illuminate\Support\Collection
        ? $batters->all()
        : $batters;

    $bowlers = $bowlers instanceof \Illuminate\Support\Collection
        ? $bowlers->all()
        : $bowlers;

    $batters = (is_array($batters) || $batters instanceof \Traversable)
        ? collect($batters)->filter(fn ($item) => is_array($item) || is_object($item))->values()
        : collect();

    $bowlers = (is_array($bowlers) || $bowlers instanceof \Traversable)
        ? collect($bowlers)->filter(fn ($item) => is_array($item) || is_object($item))->values()
        : collect();

    /*
    |--------------------------------------------------------------------------
    | Extras
    |--------------------------------------------------------------------------
    */

    if (is_string($extras)) {
        $decoded = json_decode($extras, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            $extras = $decoded;
        }
    }

    if ($extras instanceof \Illuminate\Support\Collection) {
        $extras = $extras->toArray();
    }

    $extras = is_array($extras)
        ? $extras
        : (is_numeric($extras) ? (int) $extras : []);

    $extrasTotal = is_numeric($extras)
        ? (int) $extras
        : (int) data_get($extras, 'total', 0);

    /*
    |--------------------------------------------------------------------------
    | Innings figures
    |--------------------------------------------------------------------------
    */

    $totalRuns = (int) ($inning->total_runs ?? 0);
    $wickets = (int) ($inning->wickets ?? 0);
    $legalBalls = max(0, (int) ($inning->legal_balls ?? 0));

    $overs = $inning->overs_display
        ?? (intdiv($legalBalls, 6) . '.' . ($legalBalls % 6));

    $runRate = $legalBalls > 0
        ? number_format($totalRuns * 6 / $legalBalls, 2)
        : '0.00';

    $battingCount = $batters->count();
    $bowlingCount = $bowlers->count();

    /*
    |--------------------------------------------------------------------------
    | Image URLs
    |--------------------------------------------------------------------------
    | Supports full URLs, storage paths and filenames in public storage.
    */

    $imageUrl = static function ($path, $folder = 'players') {
        $fallback = asset(
            $folder === 'teams'
                ? 'images/team-placeholder.png'
                : 'images/player-placeholder.png'
        );

        if (! is_string($path) || trim($path) === '') {
            return $fallback;
        }

        $path = trim($path);

        if (Str::startsWith($path, ['https://', 'http://', 'data:'])) {
            return $path;
        }

        $path = ltrim($path, '/');
        $path = preg_replace('#^public/#', '', $path);
        $path = preg_replace('#^storage/#', '', $path);

        if (! Str::startsWith($path, ['teams/', 'players/'])) {
            $path = $folder . '/' . $path;
        }

        return asset('storage/' . $path);
    };

    $photoUrl = static function ($player) use ($imageUrl) {
        return $imageUrl(
            data_get($player, 'photo_url')
                ?: data_get($player, 'photo_path')
                ?: data_get($player, 'photo')
                ?: data_get($player, 'image'),
            'players'
        );
    };

    $teamLogoUrl = static function ($team) use ($imageUrl) {
        return $imageUrl(
            data_get($team, 'logo')
                ?: data_get($team, 'logo_path')
                ?: data_get($team, 'image'),
            'teams'
        );
    };

    $teamInitials = static function ($team) {
        $name = data_get($team, 'short_name')
            ?: data_get($team, 'name')
            ?: 'NPL';

        return Str::upper(Str::substr($name, 0, 3));
    };

    /*
    |--------------------------------------------------------------------------
    | Player profile routes
    |--------------------------------------------------------------------------
    */

    $playerProfileUrl = static function ($entry) {
        $name = data_get($entry, 'name')
            ?: data_get($entry, 'player.name')
            ?: data_get($entry, 'player_name')
            ?: data_get($entry, 'playerName')
            ?: data_get($entry, 'batsman')
            ?: data_get($entry, 'bowler');

        if (! is_string($name) || trim($name) === '') {
            return null;
        }

        return Route::has('players.performance')
            ? route('players.performance', ['playerName' => trim($name)])
            : null;
    };
@endphp

<section class="mt-6 overflow-hidden rounded-xl border border-slate-200 bg-white text-slate-900 shadow-sm sm:mt-8">

    {{-- MATCH SCORE HEADER --}}
    <div class="border-b border-slate-200 bg-white">

        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-4 py-3 sm:px-6">
            <div class="flex items-center gap-2">
                <span class="rounded bg-red-600 px-2 py-1 text-xs font-extrabold text-white">
                    NPL
                </span>
                <span class="text-xs font-semibold text-slate-500">
                    Match scorecard
                </span>
            </div>

            <span class="rounded-md bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600">
                Innings {{ $inning->innings_number ?? 1 }}
            </span>
        </div>

        <div class="px-4 py-5 sm:px-6 sm:py-6">

            {{-- TEAM VS SCORE --}}
            <div class="grid grid-cols-1 items-center gap-5 sm:grid-cols-[1fr_auto_1fr] sm:gap-4">

                {{-- Batting team --}}
                <div class="flex min-w-0 items-center gap-3 sm:gap-4">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white p-1.5 sm:h-[68px] sm:w-[68px]">
                        <img
                            src="{{ $teamLogoUrl($battingTeam) }}"
                            alt="{{ data_get($battingTeam, 'name', 'Batting team') }} logo"
                            class="h-full w-full object-contain"
                            loading="lazy"
                            onerror="this.onerror=null;this.style.display='none';"
                        >
                    </div>

                    <div class="min-w-0">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-red-600">
                            Batting
                        </p>
                        <h3 class="mt-1 break-words text-base font-extrabold leading-tight text-slate-900 sm:text-lg">
                            {{ data_get($battingTeam, 'name', 'Batting team') }}
                        </h3>
                        <p class="mt-1 text-xs text-slate-500">
                            {{ data_get($battingTeam, 'short_name', '') }}
                        </p>
                    </div>
                </div>

                {{-- Score --}}
                <div class="rounded-xl bg-slate-50 px-4 py-4 text-center sm:min-w-[190px] sm:bg-white sm:px-5">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500">
                        Innings score
                    </p>

                    <div class="mt-1.5 whitespace-nowrap text-4xl font-black tracking-tight text-slate-900 tabular-nums sm:text-5xl">
                        {{ $totalRuns }}<span class="mx-0.5 text-red-600">/</span>{{ $wickets }}
                    </div>

                    <p class="mt-2 text-sm font-semibold text-slate-600">
                        {{ $overs }} overs
                    </p>
                </div>

                {{-- Opponent --}}
                <div class="flex min-w-0 items-center gap-3 sm:flex-row-reverse sm:gap-4 sm:text-right">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white p-1.5 sm:h-[68px] sm:w-[68px]">
                        <img
                            src="{{ $teamLogoUrl($opponentTeam) }}"
                            alt="{{ data_get($opponentTeam, 'name', 'Opponent') }} logo"
                            class="h-full w-full object-contain"
                            loading="lazy"
                            onerror="this.onerror=null;this.style.display='none';"
                        >
                    </div>

                    <div class="min-w-0">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">
                            Opposition
                        </p>
                        <h3 class="mt-1 break-words text-base font-extrabold leading-tight text-slate-900 sm:text-lg">
                            {{ data_get($opponentTeam, 'name', 'Opponent') }}
                        </h3>
                        <p class="mt-1 text-xs text-slate-500">
                            {{ data_get($opponentTeam, 'short_name', '') }}
                        </p>
                    </div>
                </div>

            </div>

            {{-- QUICK STATS --}}
            <div class="mt-5 grid grid-cols-3 divide-x divide-slate-200 rounded-lg border border-slate-200 bg-white">
                <div class="px-3 py-3 text-center sm:py-4">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500 sm:text-xs">
                        Run rate
                    </p>
                    <p class="mt-1 text-lg font-extrabold tabular-nums sm:text-xl">
                        {{ $runRate }}
                    </p>
                </div>

                <div class="px-3 py-3 text-center sm:py-4">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500 sm:text-xs">
                        Extras
                    </p>
                    <p class="mt-1 text-lg font-extrabold tabular-nums sm:text-xl">
                        {{ $extrasTotal }}
                    </p>
                </div>

                <div class="px-3 py-3 text-center sm:py-4">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500 sm:text-xs">
                        Legal balls
                    </p>
                    <p class="mt-1 text-lg font-extrabold tabular-nums sm:text-xl">
                        {{ $legalBalls }}
                    </p>
                </div>
            </div>

        </div>
    </div>

    {{-- BATTING --}}
    <div class="px-4 py-5 sm:px-6 sm:py-6">

        <div class="mb-4 flex items-center justify-between gap-3">
            <div>
                <h3 class="text-lg font-extrabold text-slate-900">Batting</h3>
                <p class="mt-0.5 text-xs text-slate-500">
                    {{ data_get($battingTeam, 'name', 'Batting team') }} batting figures
                </p>
            </div>

            <span class="rounded-md bg-slate-100 px-2.5 py-1.5 text-xs font-bold text-slate-600">
                {{ $battingCount }} {{ $battingCount === 1 ? 'batter' : 'batters' }}
            </span>
        </div>

        {{-- Desktop/tablet table --}}
        <div class="hidden overflow-hidden rounded-lg border border-slate-200 md:block">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-sm">
                    <thead class="bg-slate-50 text-left text-[11px] font-bold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th scope="col" class="px-4 py-3">Batter</th>
                            <th scope="col" class="px-3 py-3">Dismissal</th>
                            <th scope="col" class="px-3 py-3 text-center">R</th>
                            <th scope="col" class="px-3 py-3 text-center">B</th>
                            <th scope="col" class="px-3 py-3 text-center">4s</th>
                            <th scope="col" class="px-3 py-3 text-center">6s</th>
                            <th scope="col" class="px-4 py-3 text-right">SR</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @forelse ($batters as $batter)
                            @php
                                $player = data_get($batter, 'player');

                                $name = data_get($batter, 'name')
                                    ?: data_get($player, 'name')
                                    ?: data_get($batter, 'player_name')
                                    ?: data_get($batter, 'playerName')
                                    ?: 'Unknown batter';

                                $profileUrl = $playerProfileUrl($batter);
                                $runs = (int) data_get($batter, 'runs', 0);
                                $balls = (int) data_get($batter, 'balls', 0);
                                $fours = (int) data_get($batter, 'fours', 0);
                                $sixes = (int) data_get($batter, 'sixes', 0);

                                $strikeRate = data_get($batter, 'strike_rate');
                                $strikeRate = $strikeRate !== null
                                    ? (float) $strikeRate
                                    : ($balls > 0 ? ($runs / $balls) * 100 : 0);

                                $dismissal = trim((string) data_get($batter, 'dismissal', ''));
                                $notOutValue = data_get($batter, 'is_not_out');

                                $isNotOut = $notOutValue !== null
                                    ? filter_var($notOutValue, FILTER_VALIDATE_BOOLEAN)
                                    : str_contains(strtolower($dismissal), 'not out');

                                $photoSource = $photoUrl($player ?: $batter);
                            @endphp

                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <img
                                            src="{{ $photoSource }}"
                                            alt="{{ $name }}"
                                            class="h-9 w-9 shrink-0 rounded-full border border-slate-200 bg-slate-100 object-cover"
                                            loading="lazy"
                                            onerror="this.onerror=null;this.style.display='none';"
                                        >
                                        <div class="min-w-0">
                                            @if ($profileUrl)
                                                <a href="{{ $profileUrl }}" class="font-bold text-slate-900 hover:text-red-600">
                                                    {{ $name }}
                                                </a>
                                            @else
                                                <span class="font-bold text-slate-900">{{ $name }}</span>
                                            @endif

                                            @if ($isNotOut)
                                                <span class="ml-1 text-xs font-semibold text-emerald-700">not out</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <td class="max-w-[230px] px-3 py-3 text-xs leading-5 text-slate-500">
                                    {{ $dismissal ?: ($isNotOut ? 'Not out' : '—') }}
                                </td>
                                <td class="px-3 py-3 text-center font-extrabold tabular-nums">{{ $runs }}</td>
                                <td class="px-3 py-3 text-center tabular-nums text-slate-600">{{ $balls }}</td>
                                <td class="px-3 py-3 text-center tabular-nums text-slate-600">{{ $fours }}</td>
                                <td class="px-3 py-3 text-center tabular-nums text-slate-600">{{ $sixes }}</td>
                                <td class="px-4 py-3 text-right font-semibold tabular-nums">{{ number_format($strikeRate, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-10 text-center text-sm text-slate-500">
                                    Batting figures have not been recorded for this innings.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    @if ($battingCount > 0)
                        <tfoot class="border-t border-slate-200 bg-slate-50">
                            <tr>
                                <td colspan="2" class="px-4 py-3 font-extrabold">Total</td>
                                <td class="px-3 py-3 text-center font-extrabold tabular-nums">{{ $totalRuns }}</td>
                                <td colspan="4" class="px-4 py-3 text-right text-xs font-semibold text-slate-500">
                                    {{ $wickets }} wickets · {{ $overs }} overs
                                </td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>

        {{-- Mobile batting rows --}}
        <div class="space-y-2 md:hidden">
            @forelse ($batters as $batter)
                @php
                    $player = data_get($batter, 'player');

                    $name = data_get($batter, 'name')
                        ?: data_get($player, 'name')
                        ?: data_get($batter, 'player_name')
                        ?: data_get($batter, 'playerName')
                        ?: 'Unknown batter';

                    $profileUrl = $playerProfileUrl($batter);
                    $runs = (int) data_get($batter, 'runs', 0);
                    $balls = (int) data_get($batter, 'balls', 0);
                    $fours = (int) data_get($batter, 'fours', 0);
                    $sixes = (int) data_get($batter, 'sixes', 0);

                    $strikeRate = data_get($batter, 'strike_rate');
                    $strikeRate = $strikeRate !== null
                        ? (float) $strikeRate
                        : ($balls > 0 ? ($runs / $balls) * 100 : 0);

                    $dismissal = trim((string) data_get($batter, 'dismissal', ''));
                    $notOutValue = data_get($batter, 'is_not_out');

                    $isNotOut = $notOutValue !== null
                        ? filter_var($notOutValue, FILTER_VALIDATE_BOOLEAN)
                        : str_contains(strtolower($dismissal), 'not out');

                    $photoSource = $photoUrl($player ?: $batter);
                @endphp

                <article class="rounded-lg border border-slate-200 bg-white p-3">
                    <div class="flex items-center gap-3">
                        <img
                            src="{{ $photoSource }}"
                            alt="{{ $name }}"
                            class="h-10 w-10 shrink-0 rounded-full border border-slate-200 bg-slate-100 object-cover"
                            loading="lazy"
                            onerror="this.onerror=null;this.style.display='none';"
                        >

                        <div class="min-w-0 flex-1">
                            @if ($profileUrl)
                                <a href="{{ $profileUrl }}" class="break-words text-sm font-bold text-slate-900 hover:text-red-600">
                                    {{ $name }}
                                </a>
                            @else
                                <p class="break-words text-sm font-bold text-slate-900">{{ $name }}</p>
                            @endif

                            <p class="mt-1 truncate text-xs text-slate-500">
                                {{ $dismissal ?: ($isNotOut ? 'Not out' : 'Dismissal not recorded') }}
                            </p>
                        </div>

                        <div class="shrink-0 text-right">
                            <p class="text-xl font-black tabular-nums text-slate-900">{{ $runs }}</p>
                            <p class="text-[10px] text-slate-500">runs</p>
                        </div>
                    </div>

                    <div class="mt-3 grid grid-cols-4 gap-2 border-t border-slate-100 pt-3">
                        <div>
                            <p class="text-[10px] text-slate-500">Balls</p>
                            <p class="mt-0.5 text-sm font-bold tabular-nums">{{ $balls }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] text-slate-500">4s</p>
                            <p class="mt-0.5 text-sm font-bold tabular-nums">{{ $fours }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] text-slate-500">6s</p>
                            <p class="mt-0.5 text-sm font-bold tabular-nums">{{ $sixes }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] text-slate-500">Strike rate</p>
                            <p class="mt-0.5 text-sm font-bold tabular-nums">{{ number_format($strikeRate, 2) }}</p>
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-lg border border-slate-200 px-4 py-8 text-center text-sm text-slate-500">
                    Batting figures have not been recorded for this innings.
                </div>
            @endforelse

            @if ($battingCount > 0)
                <div class="flex items-center justify-between rounded-lg bg-slate-50 px-4 py-3">
                    <span class="text-sm font-extrabold text-slate-900">Team total</span>
                    <span class="text-sm font-extrabold tabular-nums">{{ $totalRuns }}/{{ $wickets }} <span class="font-medium text-slate-500">({{ $overs }} ov)</span></span>
                </div>
            @endif
        </div>

        {{-- EXTRAS --}}
        <div class="mt-5 rounded-lg border border-slate-200 p-4">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h4 class="text-sm font-extrabold text-slate-900">Extras</h4>
                    <p class="mt-1 text-xs text-slate-500">Additional runs awarded to the batting team</p>
                </div>
                <span class="text-2xl font-black tabular-nums text-slate-900">{{ $extrasTotal }}</span>
            </div>

            <div class="mt-4 grid grid-cols-2 gap-4 border-t border-slate-100 pt-4 sm:grid-cols-5">
                <div>
                    <p class="text-xs text-slate-500">Wides</p>
                    <p class="mt-1 font-bold tabular-nums">{{ data_get($extras, 'wides', 0) }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500">No-balls</p>
                    <p class="mt-1 font-bold tabular-nums">{{ data_get($extras, 'no_balls', 0) }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500">Byes</p>
                    <p class="mt-1 font-bold tabular-nums">{{ data_get($extras, 'byes', 0) }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500">Leg byes</p>
                    <p class="mt-1 font-bold tabular-nums">{{ data_get($extras, 'leg_byes', 0) }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500">Penalty</p>
                    <p class="mt-1 font-bold tabular-nums">{{ data_get($extras, 'penalty', 0) }}</p>
                </div>
            </div>
        </div>

    </div>

    {{-- BOWLING --}}
    <div class="border-t border-slate-200 bg-slate-50/70 px-4 py-5 sm:px-6 sm:py-6">

        <div class="mb-4 flex items-center justify-between gap-3">
            <div>
                <h3 class="text-lg font-extrabold text-slate-900">Bowling</h3>
                <p class="mt-0.5 text-xs text-slate-500">
                    {{ data_get($opponentTeam, 'name', 'Opposition') }} bowling figures
                </p>
            </div>

            <span class="rounded-md border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-bold text-slate-600">
                {{ $bowlingCount }} {{ $bowlingCount === 1 ? 'bowler' : 'bowlers' }}
            </span>
        </div>

        {{-- Desktop/tablet bowling table --}}
        <div class="hidden overflow-hidden rounded-lg border border-slate-200 bg-white md:block">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-sm">
                    <thead class="bg-slate-100 text-left text-[11px] font-bold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th scope="col" class="px-4 py-3">Bowler</th>
                            <th scope="col" class="px-3 py-3 text-center">O</th>
                            <th scope="col" class="px-3 py-3 text-center">M</th>
                            <th scope="col" class="px-3 py-3 text-center">R</th>
                            <th scope="col" class="px-3 py-3 text-center">W</th>
                            <th scope="col" class="px-4 py-3 text-right">Econ</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @forelse ($bowlers as $bowler)
                            @php
                                $player = data_get($bowler, 'player');

                                $name = data_get($bowler, 'name')
                                    ?: data_get($player, 'name')
                                    ?: data_get($bowler, 'player_name')
                                    ?: data_get($bowler, 'playerName')
                                    ?: 'Unknown bowler';

                                $profileUrl = $playerProfileUrl($bowler);
                                $bowlerOvers = (string) data_get($bowler, 'overs', '0.0');
                                $maidens = (int) data_get($bowler, 'maidens', 0);
                                $runsConceded = (int) data_get($bowler, 'runs', 0);
                                $wicketsTaken = (int) data_get($bowler, 'wickets', 0);

                                $explicitBalls = data_get($bowler, 'legal_balls')
                                    ?? data_get($bowler, 'balls_bowled');

                                if ($explicitBalls !== null) {
                                    $bowlerLegalBalls = max(0, (int) $explicitBalls);
                                } else {
                                    $overParts = explode('.', $bowlerOvers);
                                    $completedOvers = max(0, (int) ($overParts[0] ?? 0));
                                    $ballsInOver = min(5, max(0, (int) ($overParts[1] ?? 0)));
                                    $bowlerLegalBalls = ($completedOvers * 6) + $ballsInOver;
                                }

                                $economy = data_get($bowler, 'economy');
                                $economy = $economy !== null
                                    ? (float) $economy
                                    : ($bowlerLegalBalls > 0
                                        ? $runsConceded * 6 / $bowlerLegalBalls
                                        : 0);

                                $photoSource = $photoUrl($player ?: $bowler);
                            @endphp

                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <img
                                            src="{{ $photoSource }}"
                                            alt="{{ $name }}"
                                            class="h-9 w-9 shrink-0 rounded-full border border-slate-200 bg-slate-100 object-cover"
                                            loading="lazy"
                                            onerror="this.onerror=null;this.style.display='none';"
                                        >
                                        <div>
                                            @if ($profileUrl)
                                                <a href="{{ $profileUrl }}" class="font-bold text-slate-900 hover:text-red-600">
                                                    {{ $name }}
                                                </a>
                                            @else
                                                <span class="font-bold text-slate-900">{{ $name }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-3 text-center tabular-nums text-slate-600">{{ $bowlerOvers }}</td>
                                <td class="px-3 py-3 text-center tabular-nums text-slate-600">{{ $maidens }}</td>
                                <td class="px-3 py-3 text-center tabular-nums text-slate-600">{{ $runsConceded }}</td>
                                <td class="px-3 py-3 text-center font-extrabold tabular-nums">{{ $wicketsTaken }}</td>
                                <td class="px-4 py-3 text-right font-semibold tabular-nums">{{ number_format($economy, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-sm text-slate-500">
                                    Bowling figures have not been recorded for this innings.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Mobile bowling rows --}}
        <div class="space-y-2 md:hidden">
            @forelse ($bowlers as $bowler)
                @php
                    $player = data_get($bowler, 'player');

                    $name = data_get($bowler, 'name')
                        ?: data_get($player, 'name')
                        ?: data_get($bowler, 'player_name')
                        ?: data_get($bowler, 'playerName')
                        ?: 'Unknown bowler';

                    $profileUrl = $playerProfileUrl($bowler);
                    $bowlerOvers = (string) data_get($bowler, 'overs', '0.0');
                    $maidens = (int) data_get($bowler, 'maidens', 0);
                    $runsConceded = (int) data_get($bowler, 'runs', 0);
                    $wicketsTaken = (int) data_get($bowler, 'wickets', 0);

                    $explicitBalls = data_get($bowler, 'legal_balls')
                        ?? data_get($bowler, 'balls_bowled');

                    if ($explicitBalls !== null) {
                        $bowlerLegalBalls = max(0, (int) $explicitBalls);
                    } else {
                        $overParts = explode('.', $bowlerOvers);
                        $completedOvers = max(0, (int) ($overParts[0] ?? 0));
                        $ballsInOver = min(5, max(0, (int) ($overParts[1] ?? 0)));
                        $bowlerLegalBalls = ($completedOvers * 6) + $ballsInOver;
                    }

                    $economy = data_get($bowler, 'economy');
                    $economy = $economy !== null
                        ? (float) $economy
                        : ($bowlerLegalBalls > 0
                            ? $runsConceded * 6 / $bowlerLegalBalls
                            : 0);

                    $photoSource = $photoUrl($player ?: $bowler);
                @endphp

                <article class="rounded-lg border border-slate-200 bg-white p-3">
                    <div class="flex items-center gap-3">
                        <img
                            src="{{ $photoSource }}"
                            alt="{{ $name }}"
                            class="h-10 w-10 shrink-0 rounded-full border border-slate-200 bg-slate-100 object-cover"
                            loading="lazy"
                            onerror="this.onerror=null;this.style.display='none';"
                        >

                        <div class="min-w-0 flex-1">
                            @if ($profileUrl)
                                <a href="{{ $profileUrl }}" class="break-words text-sm font-bold text-slate-900 hover:text-red-600">
                                    {{ $name }}
                                </a>
                            @else
                                <p class="break-words text-sm font-bold text-slate-900">{{ $name }}</p>
                            @endif
                        </div>

                        <div class="shrink-0 text-right">
                            <p class="text-xl font-black tabular-nums text-slate-900">{{ $wicketsTaken }}/{{ $runsConceded }}</p>
                            <p class="text-[10px] text-slate-500">Wickets / Runs</p>
                        </div>
                    </div>

                    <div class="mt-3 grid grid-cols-4 gap-2 border-t border-slate-100 pt-3">
                        <div>
                            <p class="text-[10px] text-slate-500">Overs</p>
                            <p class="mt-0.5 text-sm font-bold tabular-nums">{{ $bowlerOvers }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] text-slate-500">Maidens</p>
                            <p class="mt-0.5 text-sm font-bold tabular-nums">{{ $maidens }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] text-slate-500">Runs</p>
                            <p class="mt-0.5 text-sm font-bold tabular-nums">{{ $runsConceded }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] text-slate-500">Economy</p>
                            <p class="mt-0.5 text-sm font-bold tabular-nums">{{ number_format($economy, 2) }}</p>
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-lg border border-slate-200 bg-white px-4 py-8 text-center text-sm text-slate-500">
                    Bowling figures have not been recorded for this innings.
                </div>
            @endforelse
        </div>

    </div>

    {{-- COMPACT SUMMARY --}}
    <footer class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 bg-white px-4 py-4 sm:px-6">
        <div class="flex min-w-0 items-center gap-3">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-red-600 text-[10px] font-black text-white">
                NPL
            </div>

            <div class="min-w-0">
                <p class="text-xs font-semibold text-slate-500">Innings summary</p>
                <p class="mt-0.5 break-words text-sm font-extrabold text-slate-900">
                    {{ data_get($battingTeam, 'name', 'Batting team') }}
                    <span class="font-medium text-slate-400">vs</span>
                    {{ data_get($opponentTeam, 'name', 'Opponent') }}
                </p>
            </div>
        </div>

        <div class="text-right">
            <p class="text-xl font-black tabular-nums text-slate-900">
                {{ $totalRuns }}/{{ $wickets }}
            </p>
            <p class="text-xs text-slate-500">{{ $overs }} overs · RR {{ $runRate }}</p>
        </div>
    </footer>

</section>
