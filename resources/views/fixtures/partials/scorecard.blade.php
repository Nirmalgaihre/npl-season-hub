
@php
    use Illuminate\Support\Str;

    $battingTeam = (int) $inning->batting_team_id === (int) $fixture->home_team_id
        ? $fixture->homeTeam
        : $fixture->awayTeam;

    $opponentTeam = (int) $inning->batting_team_id === (int) $fixture->home_team_id
        ? $fixture->awayTeam
        : $fixture->homeTeam;

    $scorecard = $scorecards->get($inning->id, []);

    $batters = data_get($scorecard, 'batters', []);
    $bowlers = data_get($scorecard, 'bowlers', []);
    $extras = data_get($scorecard, 'extras', 0);

    $batters = is_array($batters) || $batters instanceof \Traversable
        ? $batters
        : [];

    $bowlers = is_array($bowlers) || $bowlers instanceof \Traversable
        ? $bowlers
        : [];

    $extrasTotal = is_numeric($extras)
        ? (int) $extras
        : (int) data_get($extras, 'total', 0);

    $totalRuns = (int) ($inning->total_runs ?? 0);
    $wickets = (int) ($inning->wickets ?? 0);
    $legalBalls = (int) ($inning->legal_balls ?? 0);

    $overs = isset($inning->overs_display)
        ? (string) $inning->overs_display
        : intdiv(max(0, $legalBalls), 6) . '.' . (max(0, $legalBalls) % 6);

    $runRate = $legalBalls > 0
        ? number_format($totalRuns / ($legalBalls / 6), 2)
        : '0.00';

    /*
     * Convert stored local paths and full URLs into usable image URLs.
     */
    $imageUrl = static function ($path, $fallback = null) {
        $fallback = $fallback ?: asset('images/player-placeholder.png');

        if (!is_string($path) || trim($path) === '') {
            return $fallback;
        }

        $path = trim($path);

        if (Str::startsWith($path, ['https://', 'http://', 'data:'])) {
            return $path;
        }

        return asset('storage/' . ltrim($path, '/'));
    };

    $photoUrl = static function ($player) use ($imageUrl) {
        return $imageUrl(
            data_get($player, 'photo_url') ?: data_get($player, 'photo_path')
        );
    };

    $teamLogoUrl = static function ($team) use ($imageUrl) {
        return $imageUrl(
            data_get($team, 'logo'),
            asset('images/team-placeholder.png')
        );
    };

    /*
     * Link scorecard entries by player name, even when manual scorecards
     * do not have a database player ID.
     */
    $playerProfileUrl = static function ($entry, $fixture) {
        $name = data_get($entry, 'name')
            ?? data_get($entry, 'player.name')
            ?? data_get($entry, 'player_name')
            ?? data_get($entry, 'playerName')
            ?? data_get($entry, 'batsman')
            ?? data_get($entry, 'bowler');

        if (!is_string($name) || trim($name) === '') {
            return null;
        }

        if (!\Illuminate\Support\Facades\Route::has('players.performance')) {
            return null;
        }

        return route('players.performance', [
            'playerName' => trim($name),
        ]);
    };

    $battingCount = count($batters);
    $bowlingCount = count($bowlers);
@endphp

<section class="mt-8 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

    {{-- INNINGS HEADER --}}
    <div class="relative overflow-hidden bg-[#202766] px-5 py-6 text-white sm:px-7">
        <div class="pointer-events-none absolute -right-12 -top-16 h-48 w-48 rounded-full border-[24px] border-white/5"></div>
        <div class="pointer-events-none absolute -bottom-20 right-24 h-40 w-40 rounded-full bg-[#f06445]/10"></div>

        <div class="relative flex flex-col justify-between gap-5 sm:flex-row sm:items-center">
            <div class="flex min-w-0 items-center gap-4">
                @if ($battingTeam?->logo)
                    <img
                        src="{{ $teamLogoUrl($battingTeam) }}"
                        alt="{{ $battingTeam->name }} logo"
                        class="h-14 w-14 shrink-0 rounded-xl bg-white p-1.5 object-contain sm:h-16 sm:w-16"
                        onerror="this.onerror=null;this.src='{{ asset('images/team-placeholder.png') }}';"
                    >
                @else
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-white/10 text-lg font-black text-white sm:h-16 sm:w-16">
                        {{ strtoupper(substr($battingTeam?->short_name ?? $battingTeam?->name ?? 'NPL', 0, 3)) }}
                    </div>
                @endif

                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-md bg-[#f06445] px-2.5 py-1 text-[10px] font-black uppercase tracking-widest text-white">
                            Innings {{ $inning->innings_number ?? 1 }}
                        </span>

                        <span class="text-[10px] font-bold uppercase tracking-widest text-indigo-200">
                            NPL Scorecard
                        </span>
                    </div>

                    <h3 class="mt-2 text-xl font-black leading-tight sm:text-2xl">
                        {{ $battingTeam?->name ?? 'Batting team' }}
                    </h3>

                    <p class="mt-1 text-xs text-indigo-200 sm:text-sm">
                        vs {{ $opponentTeam?->name ?? 'Opponent' }}
                    </p>
                </div>
            </div>

            <div class="flex items-center justify-between gap-5 rounded-xl border border-white/10 bg-white/5 px-4 py-3 sm:min-w-[190px] sm:justify-end sm:text-right">
                <div class="sm:hidden">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-indigo-200">
                        Total
                    </p>
                </div>

                <div>
                    <p class="text-3xl font-black tracking-tight sm:text-4xl">
                        {{ $totalRuns }}<span class="text-indigo-300">/</span>{{ $wickets }}
                    </p>

                    <p class="mt-1 text-xs font-medium text-indigo-200">
                        {{ $overs }} overs
                    </p>
                </div>
            </div>
        </div>

        {{-- QUICK STATS --}}
        <div class="relative mt-6 grid grid-cols-3 gap-2 border-t border-white/10 pt-5">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-indigo-200 sm:text-xs">Run rate</p>
                <p class="mt-1 text-lg font-extrabold sm:text-xl">{{ $runRate }}</p>
            </div>

            <div class="border-l border-white/10 pl-3 sm:pl-5">
                <p class="text-[10px] font-bold uppercase tracking-wider text-indigo-200 sm:text-xs">Extras</p>
                <p class="mt-1 text-lg font-extrabold sm:text-xl">{{ $extrasTotal }}</p>
            </div>

            <div class="border-l border-white/10 pl-3 sm:pl-5">
                <p class="text-[10px] font-bold uppercase tracking-wider text-indigo-200 sm:text-xs">Legal balls</p>
                <p class="mt-1 text-lg font-extrabold sm:text-xl">{{ $legalBalls }}</p>
            </div>
        </div>
    </div>

    {{-- BATTING SECTION --}}
    <div class="p-4 sm:p-6">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-[#202766]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.5 5.5 18.5 9.5M5 19l4.2-1.1L19 8.1a2.83 2.83 0 0 0-4-4L5.2 13.9 5 19Z"/>
                    </svg>
                </div>

                <div>
                    <h4 class="text-lg font-black text-[#202766]">Batting</h4>
                    <p class="text-xs text-slate-500">Individual batting performance</p>
                </div>
            </div>

            <span class="rounded-lg bg-slate-100 px-3 py-2 text-xs font-bold text-slate-600">
                {{ $battingCount }} {{ $battingCount === 1 ? 'batter' : 'batters' }}
            </span>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[660px] text-left text-sm">
                    <thead class="bg-slate-50 text-[10px] font-extrabold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-4 py-3.5 sm:px-5">Batter</th>
                            <th class="px-3 py-3.5">Dismissal</th>
                            <th class="px-3 py-3.5 text-center">R</th>
                            <th class="px-3 py-3.5 text-center">B</th>
                            <th class="px-3 py-3.5 text-center">4s</th>
                            <th class="px-3 py-3.5 text-center">6s</th>
                            <th class="px-4 py-3.5 text-right">SR</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @forelse ($batters as $batter)
                            @php
                                $player = data_get($batter, 'player');

                                $name = data_get($batter, 'name')
                                    ?: data_get($player, 'name')
                                    ?: 'Unknown batter';

                                $profileUrl = $playerProfileUrl($batter, $fixture);

                                $runs = (int) data_get($batter, 'runs', 0);
                                $balls = (int) data_get($batter, 'balls', 0);

                                $strikeRate = data_get($batter, 'strike_rate');

                                if ($strikeRate === null) {
                                    $strikeRate = $balls > 0 ? ($runs / $balls) * 100 : 0;
                                }

                                $dismissal = data_get($batter, 'dismissal', '');

                                $isNotOut = data_get($batter, 'is_not_out')
                                    ?? str_contains(strtolower((string) $dismissal), 'not out');
                            @endphp

                            <tr class="transition-colors hover:bg-slate-50/80">
                                <td class="px-4 py-3 sm:px-5">
                                    <div class="flex items-center gap-3">
                                        <img
                                            src="{{ $photoUrl($player) }}"
                                            alt="{{ $name }}"
                                            loading="lazy"
                                            class="h-10 w-10 shrink-0 rounded-full border border-slate-200 bg-slate-100 object-cover"
                                            onerror="this.onerror=null;this.src='{{ asset('images/player-placeholder.png') }}';"
                                        >

                                        <div class="min-w-0">
                                            @if ($profileUrl)
                                                <a href="{{ $profileUrl }}"
                                                   class="group inline-flex items-center gap-1 font-extrabold text-[#202766] transition hover:text-[#e65d43]">
                                                    <span>{{ $name }}</span>
                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                         class="h-3.5 w-3.5 opacity-60 transition group-hover:translate-x-0.5"
                                                         viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                        <path fill-rule="evenodd" d="M3 10a1 1 0 0 1 1-1h9.586l-3.293-3.293a1 1 0 0 1 1.414-1.414l5 5a1 1 0 0 1 0 1.414l-5 5a1 1 0 1 1-1.414-1.414L13.586 11H4a1 1 0 0 1-1-1Z" clip-rule="evenodd"/>
                                                    </svg>
                                                </a>
                                            @else
                                                <p class="font-extrabold text-[#202766]">{{ $name }}</p>
                                            @endif

                                            @if ($isNotOut)
                                                <span class="mt-0.5 inline-flex items-center gap-1 text-[10px] font-bold text-emerald-600">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                                    Not out
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <td class="max-w-[200px] px-3 py-3 text-xs leading-5 text-slate-500">
                                    {{ $dismissal ?: 'Not out / —' }}
                                </td>

                                <td class="px-3 py-3 text-center font-black text-slate-900">{{ $runs }}</td>
                                <td class="px-3 py-3 text-center text-slate-600">{{ $balls }}</td>
                                <td class="px-3 py-3 text-center text-slate-600">{{ data_get($batter, 'fours', 0) }}</td>
                                <td class="px-3 py-3 text-center text-slate-600">{{ data_get($batter, 'sixes', 0) }}</td>
                                <td class="px-4 py-3 text-right font-bold text-slate-800">{{ number_format((float) $strikeRate, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-10 text-center">
                                    <p class="font-bold text-slate-700">No batting figures yet</p>
                                    <p class="mt-1 text-xs text-slate-500">Player-wise batting statistics will appear here when available.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    @if ($battingCount > 0)
                        <tfoot class="border-t-2 border-slate-200 bg-slate-50">
                            <tr>
                                <td colspan="2" class="px-4 py-4 font-black text-slate-900 sm:px-5">Total</td>
                                <td class="px-3 py-4 text-center font-black text-[#202766]">{{ $totalRuns }}</td>
                                <td colspan="4" class="px-4 py-4 text-right text-xs font-bold text-slate-500">
                                    {{ $wickets }} wickets · {{ $overs }} overs
                                </td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>

        {{-- EXTRAS --}}
        @if (is_array($extras))
            <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-2 rounded-xl bg-slate-50 px-4 py-3 text-xs text-slate-600">
                <span class="font-extrabold text-slate-800">Extras: {{ $extrasTotal }}</span>
                <span>Wides: {{ data_get($extras, 'wides', 0) }}</span>
                <span>No-balls: {{ data_get($extras, 'no_balls', 0) }}</span>
                <span>Byes: {{ data_get($extras, 'byes', 0) }}</span>
                <span>Leg byes: {{ data_get($extras, 'leg_byes', 0) }}</span>
                <span>Penalty: {{ data_get($extras, 'penalty', 0) }}</span>
            </div>
        @else
            <div class="mt-4 rounded-xl bg-slate-50 px-4 py-3 text-sm text-slate-600">
                <span class="font-extrabold text-slate-800">Extras:</span> {{ $extrasTotal }}
            </div>
        @endif
    </div>

    {{-- BOWLING SECTION --}}
    <div class="border-t border-slate-200 p-4 sm:p-6">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-orange-50 text-[#e65d43]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <circle cx="12" cy="12" r="8.5"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.5 5 2 3-1 2m6-4 1 3-2 2m-6 3 3 1 1 3m4-4 2 1"/>
                    </svg>
                </div>

                <div>
                    <h4 class="text-lg font-black text-[#202766]">Bowling</h4>
                    <p class="text-xs text-slate-500">Individual bowling performance</p>
                </div>
            </div>

            <span class="rounded-lg bg-slate-100 px-3 py-2 text-xs font-bold text-slate-600">
                {{ $bowlingCount }} {{ $bowlingCount === 1 ? 'bowler' : 'bowlers' }}
            </span>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[570px] text-left text-sm">
                    <thead class="bg-slate-50 text-[10px] font-extrabold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-4 py-3.5 sm:px-5">Bowler</th>
                            <th class="px-3 py-3.5 text-center">O</th>
                            <th class="px-3 py-3.5 text-center">M</th>
                            <th class="px-3 py-3.5 text-center">R</th>
                            <th class="px-3 py-3.5 text-center">W</th>
                            <th class="px-4 py-3.5 text-right">Econ</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @forelse ($bowlers as $bowler)
                            @php
                                $player = data_get($bowler, 'player');

                                $name = data_get($bowler, 'name')
                                    ?: data_get($player, 'name')
                                    ?: 'Unknown bowler';

                                $profileUrl = $playerProfileUrl($bowler, $fixture);

                                $bowlerOvers = (string) data_get($bowler, 'overs', '0.0');
                                $maidens = (int) data_get($bowler, 'maidens', 0);
                                $runsConceded = (int) data_get($bowler, 'runs', 0);
                                $wicketsTaken = (int) data_get($bowler, 'wickets', 0);

                                $overParts = explode('.', $bowlerOvers);
                                $completedOvers = max(0, (int) ($overParts[0] ?? 0));
                                $ballsInOver = min(5, max(0, (int) ($overParts[1] ?? 0)));
                                $bowlerLegalBalls = ($completedOvers * 6) + $ballsInOver;

                                $economy = data_get($bowler, 'economy');

                                if ($economy === null) {
                                    $economy = $bowlerLegalBalls > 0
                                        ? $runsConceded / ($bowlerLegalBalls / 6)
                                        : 0;
                                }
                            @endphp

                            <tr class="transition-colors hover:bg-slate-50/80">
                                <td class="px-4 py-3 sm:px-5">
                                    <div class="flex items-center gap-3">
                                        <img
                                            src="{{ $photoUrl($player) }}"
                                            alt="{{ $name }}"
                                            loading="lazy"
                                            class="h-10 w-10 shrink-0 rounded-full border border-slate-200 bg-slate-100 object-cover"
                                            onerror="this.onerror=null;this.src='{{ asset('images/player-placeholder.png') }}';"
                                        >

                                        @if ($profileUrl)
                                            <a href="{{ $profileUrl }}"
                                               class="group inline-flex items-center gap-1 font-extrabold text-[#202766] transition hover:text-[#e65d43]">
                                                <span>{{ $name }}</span>
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                     class="h-3.5 w-3.5 opacity-60 transition group-hover:translate-x-0.5"
                                                     viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                    <path fill-rule="evenodd" d="M3 10a1 1 0 0 1 1-1h9.586l-3.293-3.293a1 1 0 0 1 1.414-1.414l5 5a1 1 0 0 1 0 1.414l-5 5a1 1 0 1 1-1.414-1.414L13.586 11H4a1 1 0 0 1-1-1Z" clip-rule="evenodd"/>
                                                </svg>
                                            </a>
                                        @else
                                            <p class="font-extrabold text-[#202766]">{{ $name }}</p>
                                        @endif
                                    </div>
                                </td>

                                <td class="px-3 py-3 text-center text-slate-700">{{ $bowlerOvers }}</td>
                                <td class="px-3 py-3 text-center text-slate-700">{{ $maidens }}</td>
                                <td class="px-3 py-3 text-center text-slate-700">{{ $runsConceded }}</td>
                                <td class="px-3 py-3 text-center font-black text-slate-900">{{ $wicketsTaken }}</td>
                                <td class="px-4 py-3 text-right font-bold text-slate-800">{{ number_format((float) $economy, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-10 text-center">
                                    <p class="font-bold text-slate-700">No bowling figures yet</p>
                                    <p class="mt-1 text-xs text-slate-500">Bowling statistics will appear here when available.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- FOOTER SUMMARY --}}
    <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 bg-slate-50 px-5 py-4 sm:px-6">
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Innings total</p>
            <p class="mt-1 text-lg font-black text-[#202766]">{{ $totalRuns }}/{{ $wickets }}</p>
        </div>

        <div class="text-right">
            <p class="text-xs text-slate-500">{{ $legalBalls }} legal balls bowled</p>
            <p class="mt-1 text-xs font-bold text-slate-600">Run rate {{ $runRate }}</p>
        </div>
    </div>
</section>
