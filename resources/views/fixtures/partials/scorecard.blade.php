
@php
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Route;

    /*
    |--------------------------------------------------------------------------
    | NPL OFFICIAL MATCH CENTRE — INNINGS SCORECARD
    |--------------------------------------------------------------------------
    | Existing models, relationships, manual scorecards and routes are retained.
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

    $batters = is_array($batters) || $batters instanceof \Traversable
        ? collect($batters)->filter(fn ($item) => is_array($item) || is_object($item))->values()
        : collect();

    $bowlers = is_array($bowlers) || $bowlers instanceof \Traversable
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

    $extras = is_array($extras) ? $extras : (is_numeric($extras) ? (int) $extras : []);

    $extrasTotal = is_numeric($extras)
        ? (int) $extras
        : (int) data_get($extras, 'total', 0);

    /*
    |--------------------------------------------------------------------------
    | Innings statistics
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
    | Team and player images
    |--------------------------------------------------------------------------
    | Supports filenames, teams/filename, players/filename and storage paths.
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
        return $imageUrl(data_get($team, 'logo'), 'teams');
    };

    /*
    |--------------------------------------------------------------------------
    | Player profile links
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

    $teamInitials = static function ($team) {
        $name = data_get($team, 'short_name')
            ?: data_get($team, 'name')
            ?: 'NPL';

        return Str::upper(Str::substr($name, 0, 3));
    };
@endphp

<section class="npl-innings-card mt-7 overflow-hidden rounded-2xl border border-slate-200 bg-white">

    {{-- =========================================================
         SCOREBOARD HEADER
    ========================================================== --}}
    <header class="relative overflow-hidden bg-[#111b35] text-white">

        <div class="absolute inset-x-0 top-0 h-1 bg-[#e23b45]"></div>

        <div class="pointer-events-none absolute -right-16 -top-20 h-64 w-64 rounded-full border-[35px] border-white/[0.035]"></div>
        <div class="pointer-events-none absolute -bottom-28 right-1/4 h-56 w-56 rounded-full bg-[#e23b45]/[0.06]"></div>

        <div class="relative px-5 pb-5 pt-6 sm:px-7 sm:pb-7">

            {{-- League identity --}}
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-white/10 pb-4">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#e23b45] text-[11px] font-black tracking-tight text-white">
                        NPL
                    </span>

                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[0.18em] text-white">
                            Nepal Premier League
                        </p>
                        <p class="mt-0.5 text-[10px] font-medium text-slate-400">
                            Official Match Centre
                        </p>
                    </div>
                </div>

                <span class="inline-flex items-center gap-2 rounded-md border border-white/10 bg-white/[0.05] px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-300">
                    <span class="h-1.5 w-1.5 rounded-full bg-[#e23b45]"></span>
                    Innings {{ $inning->innings_number ?? 1 }}
                </span>
            </div>

            {{-- Team identity and score --}}
            <div class="grid grid-cols-1 items-center gap-5 py-6 sm:grid-cols-[minmax(0,1fr)_auto] sm:gap-8 sm:py-8">

                <div class="flex min-w-0 items-center gap-4 sm:gap-5">

                    <div class="flex h-[68px] w-[68px] shrink-0 items-center justify-center rounded-2xl border border-white/10 bg-white p-2 shadow-lg sm:h-[84px] sm:w-[84px]">
                        @if (data_get($battingTeam, 'logo'))
                            <img
                                src="{{ $teamLogoUrl($battingTeam) }}"
                                alt="{{ data_get($battingTeam, 'name', 'Batting team') }} logo"
                                class="h-full w-full object-contain"
                                loading="lazy"
                                onerror="this.onerror=null;this.style.display='none';this.nextElementSibling.style.display='flex';"
                            >
                            <span style="display:none" class="h-full w-full items-center justify-center text-lg font-black text-[#111b35]">
                                {{ $teamInitials($battingTeam) }}
                            </span>
                        @else
                            <span class="flex h-full w-full items-center justify-center text-lg font-black text-[#111b35]">
                                {{ $teamInitials($battingTeam) }}
                            </span>
                        @endif
                    </div>

                    <div class="min-w-0">
                        <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400">
                            Batting team
                        </p>

                        <h3 class="mt-1.5 break-words text-xl font-black leading-tight tracking-tight sm:text-2xl lg:text-3xl">
                            {{ data_get($battingTeam, 'name', 'Batting team') }}
                        </h3>

                        <p class="mt-2 text-xs text-slate-400 sm:text-sm">
                            Batting against
                            <span class="font-semibold text-slate-200">
                                {{ data_get($opponentTeam, 'name', 'Opponent') }}
                            </span>
                        </p>
                    </div>
                </div>

                <div class="flex items-end justify-between gap-5 border-t border-white/10 pt-5 sm:block sm:border-0 sm:pt-0 sm:text-right">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400">
                            Innings total
                        </p>
                        <p class="mt-1 text-4xl font-black leading-none tracking-tight tabular-nums sm:text-5xl lg:text-6xl">
                            {{ $totalRuns }}<span class="px-1 text-[#e23b45]">/</span>{{ $wickets }}
                        </p>
                        <p class="mt-2 text-xs font-semibold text-slate-300">
                            {{ $overs }} overs
                        </p>
                    </div>

                    <div class="sm:hidden">
                        <p class="text-right text-[10px] uppercase tracking-wider text-slate-400">
                            Run rate
                        </p>
                        <p class="mt-1 text-lg font-black tabular-nums">{{ $runRate }}</p>
                    </div>
                </div>
            </div>

            {{-- Key match figures --}}
            <div class="grid grid-cols-3 divide-x divide-white/10 border-t border-white/10 pt-4">

                <div class="pr-3 sm:pr-5">
                    <p class="text-[9px] font-bold uppercase tracking-[0.13em] text-slate-400 sm:text-[10px]">
                        Run rate
                    </p>
                    <p class="mt-1.5 text-lg font-black tabular-nums sm:text-xl">
                        {{ $runRate }}
                    </p>
                </div>

                <div class="px-3 sm:px-5">
                    <p class="text-[9px] font-bold uppercase tracking-[0.13em] text-slate-400 sm:text-[10px]">
                        Extras
                    </p>
                    <p class="mt-1.5 text-lg font-black tabular-nums sm:text-xl">
                        {{ $extrasTotal }}
                    </p>
                </div>

                <div class="pl-3 sm:pl-5">
                    <p class="text-[9px] font-bold uppercase tracking-[0.13em] text-slate-400 sm:text-[10px]">
                        Legal balls
                    </p>
                    <p class="mt-1.5 text-lg font-black tabular-nums sm:text-xl">
                        {{ $legalBalls }}
                    </p>
                </div>
            </div>
        </div>
    </header>

    {{-- =========================================================
         BATTING SCORECARD
    ========================================================== --}}
    <div class="px-4 py-6 sm:px-6 sm:py-7">

        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#fff0f0] text-[#c62835]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.5 5.5 18.5 9.5M5 19l4.2-1.1L19 8.1a2.83 2.83 0 0 0-4-4L5.2 13.9 5 19Z"/>
                    </svg>
                </div>

                <div>
                    <h4 class="text-lg font-black tracking-tight text-[#111b35] sm:text-xl">
                        Batting
                    </h4>
                    <p class="mt-0.5 text-xs text-slate-500">
                        Batting scorecard
                    </p>
                </div>
            </div>

            <span class="rounded-md border border-slate-200 bg-slate-50 px-3 py-1.5 text-[11px] font-bold text-slate-600">
                {{ $battingCount }} {{ $battingCount === 1 ? 'batter' : 'batters' }}
            </span>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[640px] border-collapse text-left text-sm">
                    <thead class="bg-[#f5f7fa]">
                        <tr class="text-[10px] font-extrabold uppercase tracking-wider text-slate-500">
                            <th scope="col" class="px-4 py-3.5 sm:px-5">Batter</th>
                            <th scope="col" class="px-3 py-3.5">Dismissal</th>
                            <th scope="col" class="px-3 py-3.5 text-center">R</th>
                            <th scope="col" class="px-3 py-3.5 text-center">B</th>
                            <th scope="col" class="px-3 py-3.5 text-center">4s</th>
                            <th scope="col" class="px-3 py-3.5 text-center">6s</th>
                            <th scope="col" class="px-4 py-3.5 text-right">SR</th>
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

                            <tr class="transition-colors hover:bg-slate-50">
                                <td class="px-4 py-3.5 sm:px-5">
                                    <div class="flex items-center gap-3">
                                        <img
                                            src="{{ $photoSource }}"
                                            alt="{{ $name }}"
                                            class="h-10 w-10 shrink-0 rounded-full border border-slate-200 bg-slate-100 object-cover"
                                            loading="lazy"
                                            onerror="this.onerror=null;this.src='{{ asset('images/player-placeholder.png') }}';"
                                        >

                                        <div class="min-w-0">
                                            @if ($profileUrl)
                                                <a href="{{ $profileUrl }}" class="font-bold leading-5 text-[#17264a] transition-colors hover:text-[#c62835]">
                                                    {{ $name }}
                                                </a>
                                            @else
                                                <p class="font-bold leading-5 text-[#17264a]">
                                                    {{ $name }}
                                                </p>
                                            @endif

                                            @if ($isNotOut)
                                                <span class="mt-1 inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                                    Not out
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <td class="max-w-[200px] px-3 py-3.5 text-xs leading-5 text-slate-500">
                                    {{ $dismissal ?: ($isNotOut ? 'Not out' : '—') }}
                                </td>

                                <td class="px-3 py-3.5 text-center font-black tabular-nums text-[#111b35]">
                                    {{ $runs }}
                                </td>

                                <td class="px-3 py-3.5 text-center tabular-nums text-slate-600">
                                    {{ $balls }}
                                </td>

                                <td class="px-3 py-3.5 text-center tabular-nums text-slate-600">
                                    {{ $fours }}
                                </td>

                                <td class="px-3 py-3.5 text-center tabular-nums text-slate-600">
                                    {{ $sixes }}
                                </td>

                                <td class="px-4 py-3.5 text-right font-semibold tabular-nums text-slate-700">
                                    {{ number_format($strikeRate, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-12 text-center">
                                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/>
                                        </svg>
                                    </div>
                                    <p class="mt-3 font-bold text-slate-700">Batting figures unavailable</p>
                                    <p class="mt-1 text-xs text-slate-500">Statistics will appear when batting data is recorded.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    @if ($battingCount > 0)
                        <tfoot class="border-t-2 border-slate-200 bg-[#f5f7fa]">
                            <tr>
                                <td colspan="2" class="px-4 py-4 font-black text-[#111b35] sm:px-5">
                                    Total
                                </td>

                                <td class="px-3 py-4 text-center font-black tabular-nums text-[#111b35]">
                                    {{ $totalRuns }}
                                </td>

                                <td colspan="4" class="px-4 py-4 text-right text-[11px] font-semibold text-slate-500">
                                    {{ $wickets }} wickets · {{ $overs }} overs
                                </td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>

        {{-- EXTRAS BREAKDOWN --}}
        <div class="mt-4 rounded-xl border border-slate-200 bg-white p-4">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                        Extras
                    </p>
                    <p class="mt-1 text-2xl font-black tabular-nums text-[#111b35]">
                        {{ $extrasTotal }}
                    </p>
                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-100 text-slate-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v8m-4-4h8M4.5 4.5h15v15h-15z"/>
                    </svg>
                </div>
            </div>

            <div class="mt-4 grid grid-cols-2 gap-x-5 gap-y-3 border-t border-slate-100 pt-4 sm:grid-cols-5">
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Wides</p>
                    <p class="mt-1 text-sm font-bold text-slate-800">{{ data_get($extras, 'wides', 0) }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">No-balls</p>
                    <p class="mt-1 text-sm font-bold text-slate-800">{{ data_get($extras, 'no_balls', 0) }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Byes</p>
                    <p class="mt-1 text-sm font-bold text-slate-800">{{ data_get($extras, 'byes', 0) }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Leg byes</p>
                    <p class="mt-1 text-sm font-bold text-slate-800">{{ data_get($extras, 'leg_byes', 0) }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Penalty</p>
                    <p class="mt-1 text-sm font-bold text-slate-800">{{ data_get($extras, 'penalty', 0) }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- =========================================================
         BOWLING SCORECARD
    ========================================================== --}}
    <div class="border-t border-slate-200 bg-[#fafbfd] px-4 py-6 sm:px-6 sm:py-7">

        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#edf1fa] text-[#253a70]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                        <circle cx="12" cy="12" r="8.5"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.5 5 2 3-1 2m6-4 1 3-2 2m-6 3 3 1 1 3m4-4 2 1"/>
                    </svg>
                </div>

                <div>
                    <h4 class="text-lg font-black tracking-tight text-[#111b35] sm:text-xl">
                        Bowling
                    </h4>
                    <p class="mt-0.5 text-xs text-slate-500">
                        Bowling figures
                    </p>
                </div>
            </div>

            <span class="rounded-md border border-slate-200 bg-white px-3 py-1.5 text-[11px] font-bold text-slate-600">
                {{ $bowlingCount }} {{ $bowlingCount === 1 ? 'bowler' : 'bowlers' }}
            </span>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[540px] border-collapse text-left text-sm">
                    <thead class="bg-[#f0f3f8]">
                        <tr class="text-[10px] font-extrabold uppercase tracking-wider text-slate-500">
                            <th scope="col" class="px-4 py-3.5 sm:px-5">Bowler</th>
                            <th scope="col" class="px-3 py-3.5 text-center">O</th>
                            <th scope="col" class="px-3 py-3.5 text-center">M</th>
                            <th scope="col" class="px-3 py-3.5 text-center">R</th>
                            <th scope="col" class="px-3 py-3.5 text-center">W</th>
                            <th scope="col" class="px-4 py-3.5 text-right">Econ</th>
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

                                /*
                                 * Cricket overs use base-six ball notation:
                                 * 4.2 means four overs and two balls, not 4.2 decimal overs.
                                 */
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

                            <tr class="transition-colors hover:bg-slate-50">
                                <td class="px-4 py-3.5 sm:px-5">
                                    <div class="flex items-center gap-3">
                                        <img
                                            src="{{ $photoSource }}"
                                            alt="{{ $name }}"
                                            class="h-10 w-10 shrink-0 rounded-full border border-slate-200 bg-slate-100 object-cover"
                                            loading="lazy"
                                            onerror="this.onerror=null;this.src='{{ asset('images/player-placeholder.png') }}';"
                                        >

                                        <div class="min-w-0">
                                            @if ($profileUrl)
                                                <a href="{{ $profileUrl }}" class="font-bold leading-5 text-[#17264a] transition-colors hover:text-[#c62835]">
                                                    {{ $name }}
                                                </a>
                                            @else
                                                <p class="font-bold leading-5 text-[#17264a]">
                                                    {{ $name }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <td class="px-3 py-3.5 text-center tabular-nums text-slate-600">{{ $bowlerOvers }}</td>
                                <td class="px-3 py-3.5 text-center tabular-nums text-slate-600">{{ $maidens }}</td>
                                <td class="px-3 py-3.5 text-center tabular-nums text-slate-600">{{ $runsConceded }}</td>
                                <td class="px-3 py-3.5 text-center font-black tabular-nums text-[#111b35]">{{ $wicketsTaken }}</td>
                                <td class="px-4 py-3.5 text-right font-semibold tabular-nums text-slate-700">{{ number_format($economy, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center">
                                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m5-2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                        </svg>
                                    </div>
                                    <p class="mt-3 font-bold text-slate-700">Bowling figures unavailable</p>
                                    <p class="mt-1 text-xs text-slate-500">Statistics will appear when bowling data is recorded.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- =========================================================
         INNINGS SUMMARY
    ========================================================== --}}
    <footer class="border-t border-slate-200 bg-white px-5 py-4 sm:px-6">
        <div class="flex flex-wrap items-center justify-between gap-4">

            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#111b35] text-[10px] font-black text-white">
                    NPL
                </div>

                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                        Innings summary
                    </p>
                    <p class="mt-0.5 text-sm font-black text-[#111b35]">
                        {{ data_get($battingTeam, 'short_name') ?: data_get($battingTeam, 'name', 'Batting team') }}
                        <span class="mx-1 text-slate-300">·</span>
                        {{ $totalRuns }}/{{ $wickets }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-5">
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">
                        Overs
                    </p>
                    <p class="mt-1 text-sm font-bold tabular-nums text-slate-700">
                        {{ $overs }}
                    </p>
                </div>

                <div class="h-8 w-px bg-slate-200"></div>

                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">
                        Run rate
                    </p>
                    <p class="mt-1 text-sm font-bold tabular-nums text-slate-700">
                        {{ $runRate }}
                    </p>
                </div>
            </div>
        </div>
    </footer>
</section>
