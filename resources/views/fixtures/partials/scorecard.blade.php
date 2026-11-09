@php
    $scorecard = $scorecards->get($inning->id, $inning);

    $batters = is_array($scorecard->batters ?? null)
        ? $scorecard->batters
        : [];

    $bowlers = is_array($scorecard->bowlers ?? null)
        ? $scorecard->bowlers
        : [];

    $extras = (int) ($scorecard->extras ?? 0);
@endphp

<div class="space-y-7 p-4 sm:p-6">

    {{-- Batting --}}
    <div>
        <h4 class="mb-3 text-sm font-black uppercase tracking-wider text-gray-800">
            Batting
        </h4>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[600px] text-left text-sm">
                <thead class="border-y border-gray-200 bg-gray-50 text-xs text-gray-500">
                    <tr>
                        <th class="px-3 py-3 font-bold">Batter</th>
                        <th class="px-3 py-3 font-bold">Dismissal</th>
                        <th class="px-3 py-3 text-right font-bold">R</th>
                        <th class="px-3 py-3 text-right font-bold">B</th>
                        <th class="px-3 py-3 text-right font-bold">4s</th>
                        <th class="px-3 py-3 text-right font-bold">6s</th>
                        <th class="px-3 py-3 text-right font-bold">SR</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @forelse ($batters as $batter)
                        @php
                            $batterName = $batter['custom_name']
                                ?? $batter['name']
                                ?? 'Unknown batter';

                            $dismissal = $batter['dismissal_type'] ?? 'not out';

                            $dismissedBy = $batter['dismissed_by_bowler']
                                ?? $batter['dismissed_by']
                                ?? null;

                            $fielder = $batter['dismissed_by_fielder'] ?? null;

                            $dismissalText = strtolower((string) $dismissal);

                            if (in_array($dismissalText, ['not out', 'not_out', 'retired hurt', 'retired_hurt'], true)) {
                                $dismissalText = str_replace('_', ' ', $dismissalText);
                            } else {
                                $dismissalText = str_replace('_', ' ', $dismissalText);

                                if ($dismissedBy) {
                                    $dismissalText .= ' b ' . $dismissedBy;
                                }

                                if ($fielder) {
                                    $dismissalText .= ' (' . $fielder . ')';
                                }
                            }

                            $runs = (int) ($batter['runs'] ?? 0);
                            $balls = (int) ($batter['balls'] ?? 0);
                            $fours = (int) ($batter['fours'] ?? 0);
                            $sixes = (int) ($batter['sixes'] ?? 0);

                            $strikeRate = $batter['strike_rate']
                                ?? ($balls > 0 ? number_format(($runs / $balls) * 100, 2) : '0.00');
                        @endphp

                        <tr>
                            <td class="px-3 py-3 font-bold text-gray-900">
                                <a
                                    href="{{ route('players.performance', ['playerName' => $batterName]) }}"
                                    class="transition hover:text-rose-600 hover:underline underline-offset-4"
                                    title="View {{ $batterName }}'s player profile"
                                >{{ $batterName }}</a>
                            </td>
                            <td class="px-3 py-3 text-xs text-gray-500">
                                {{ $dismissalText ?: 'not out' }}
                            </td>
                            <td class="px-3 py-3 text-right font-black text-gray-900">
                                {{ $runs }}
                            </td>
                            <td class="px-3 py-3 text-right text-gray-600">
                                {{ $balls }}
                            </td>
                            <td class="px-3 py-3 text-right text-gray-600">
                                {{ $fours }}
                            </td>
                            <td class="px-3 py-3 text-right text-gray-600">
                                {{ $sixes }}
                            </td>
                            <td class="px-3 py-3 text-right text-gray-600">
                                {{ $strikeRate }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-3 py-6 text-center text-sm text-gray-500">
                                No batting figures have been entered for this innings.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                @if (count($batters))
                    <tfoot class="border-t border-gray-200 bg-gray-50">
                        <tr>
                            <td colspan="2" class="px-3 py-3 font-black text-gray-800">
                                Extras
                            </td>
                            <td colspan="5" class="px-3 py-3 text-right font-bold text-gray-700">
                                {{ $extras }}
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" class="px-3 py-3 font-black text-gray-900">
                                Total
                            </td>
                            <td colspan="5" class="px-3 py-3 text-right font-black text-gray-900">
                                {{ $scorecard->total_runs ?? 0 }}/{{ $scorecard->wickets ?? 0 }}
                                ({{ $scorecard->overs ?? '0.0' }} overs)
                            </td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    {{-- Bowling --}}
    <div>
        <h4 class="mb-3 text-sm font-black uppercase tracking-wider text-gray-800">
            Bowling
        </h4>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[520px] text-left text-sm">
                <thead class="border-y border-gray-200 bg-gray-50 text-xs text-gray-500">
                    <tr>
                        <th class="px-3 py-3 font-bold">Bowler</th>
                        <th class="px-3 py-3 text-right font-bold">O</th>
                        <th class="px-3 py-3 text-right font-bold">M</th>
                        <th class="px-3 py-3 text-right font-bold">R</th>
                        <th class="px-3 py-3 text-right font-bold">W</th>
                        <th class="px-3 py-3 text-right font-bold">Econ</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @forelse ($bowlers as $bowler)
                        @php
                            $bowlerName = $bowler['custom_name']
                                ?? $bowler['name']
                                ?? 'Unknown bowler';

                            $bowlerRuns = (int) ($bowler['runs'] ?? 0);
                            $bowlerOvers = $bowler['overs'] ?? '0.0';
                            $bowlerWickets = (int) ($bowler['wickets'] ?? 0);

                            $economy = $bowler['economy'] ?? null;

                            if ($economy === null) {
                                $overParts = explode('.', (string) $bowlerOvers);
                                $completeOvers = (int) ($overParts[0] ?? 0);
                                $extraBalls = (int) ($overParts[1] ?? 0);
                                $legalBalls = ($completeOvers * 6) + min($extraBalls, 5);

                                $economy = $legalBalls > 0
                                    ? number_format($bowlerRuns / ($legalBalls / 6), 2)
                                    : '0.00';
                            }
                        @endphp

                        <tr>
                            <td class="px-3 py-3 font-bold text-gray-900">
                                <a
                                    href="{{ route('players.performance', ['playerName' => $bowlerName]) }}"
                                    class="transition hover:text-rose-600 hover:underline underline-offset-4"
                                    title="View {{ $bowlerName }}'s player profile"
                                >{{ $bowlerName }}</a>
                            </td>
                            <td class="px-3 py-3 text-right text-gray-600">
                                {{ $bowlerOvers }}
                            </td>
                            <td class="px-3 py-3 text-right text-gray-600">
                                {{ $bowler['maidens'] ?? 0 }}
                            </td>
                            <td class="px-3 py-3 text-right text-gray-600">
                                {{ $bowlerRuns }}
                            </td>
                            <td class="px-3 py-3 text-right font-black text-gray-900">
                                {{ $bowlerWickets }}
                            </td>
                            <td class="px-3 py-3 text-right text-gray-600">
                                {{ $economy }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 py-6 text-center text-sm text-gray-500">
                                No bowling figures have been entered for this innings.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
