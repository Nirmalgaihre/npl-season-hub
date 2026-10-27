<?php

namespace App\Services;

use App\Models\LiveInnings;
use Illuminate\Support\Collection;

class ScorecardService
{
    /**
     * Build batting and bowling statistics for one innings.
     */
    public function build(LiveInnings $inning): array
    {
        $deliveries = $inning->relationLoaded('deliveries')
            ? $inning->deliveries->sortBy('id')
            : $inning->deliveries()->orderBy('id')->get();

        $batters = [];
        $bowlers = [];
        $extras = 0;

        foreach ($deliveries as $delivery) {
            $striker = trim((string) $delivery->striker);
            $bowler = trim((string) $delivery->bowler);

            $batterRuns = (int) $delivery->batter_runs;
            $extraRuns = (int) $delivery->extra_runs;

            $extraType = strtolower(
                trim((string) ($delivery->extra_type ?? 'none'))
            );

            $isWide = $extraType === 'wide';

            $isNoBall = in_array(
                $extraType,
                ['no_ball', 'noball', 'no-ball'],
                true
            );

            $isLegal = (bool) $delivery->counts_as_legal_ball;

            $extras += $extraRuns;

            /*
             * Batting statistics.
             */
            if ($striker !== '') {
                if (! isset($batters[$striker])) {
                    $batters[$striker] = [
                        'name' => $striker,
                        'player' => null,
                        'runs' => 0,
                        'balls' => 0,
                        'fours' => 0,
                        'sixes' => 0,
                        'dismissal' => 'not out',
                        'strike_rate' => 0,
                    ];
                }

                $batters[$striker]['runs'] += $batterRuns;

                // A wide is not counted as a ball faced.
                if (! $isWide) {
                    $batters[$striker]['balls']++;
                }

                if ($batterRuns === 4) {
                    $batters[$striker]['fours']++;
                }

                if ($batterRuns === 6) {
                    $batters[$striker]['sixes']++;
                }
            }

            /*
             * Bowling statistics.
             */
            if ($bowler !== '') {
                if (! isset($bowlers[$bowler])) {
                    $bowlers[$bowler] = [
                        'name' => $bowler,
                        'player' => null,
                        'legal_balls' => 0,
                        'maidens' => 0,
                        'runs' => 0,
                        'wickets' => 0,
                        'overs' => '0.0',
                        'economy' => 0,
                        '_overs' => [],
                    ];
                }

                if ($isLegal) {
                    $bowlers[$bowler]['legal_balls']++;
                }

                /*
                 * Byes and leg-byes are not charged to the bowler.
                 * Wides and no-balls are charged to the bowler.
                 */
                $bowlerRuns = $batterRuns;

                if ($isWide || $isNoBall) {
                    $bowlerRuns += $extraRuns;
                }

                $bowlers[$bowler]['runs'] += $bowlerRuns;

                /*
                 * ball_number is used as the over index.
                 */
                $overKey = (string) $delivery->ball_number;

                if (! isset($bowlers[$bowler]['_overs'][$overKey])) {
                    $bowlers[$bowler]['_overs'][$overKey] = [
                        'legal_balls' => 0,
                        'runs' => 0,
                    ];
                }

                if ($isLegal) {
                    $bowlers[$bowler]['_overs'][$overKey]['legal_balls']++;
                }

                $bowlers[$bowler]['_overs'][$overKey]['runs'] += $bowlerRuns;

                /*
                 * Do not credit run-outs and other non-bowler wickets
                 * to the bowler.
                 */
                if ((bool) $delivery->is_wicket) {
                    $wicketType = strtolower(
                        str_replace(
                            [' ', '-'],
                            '_',
                            trim((string) $delivery->wicket_type)
                        )
                    );

                    $notBowlerWickets = [
                        'run_out',
                        'retired_hurt',
                        'retired_out',
                        'obstructing_the_field',
                        'obstructing_field',
                        'timed_out',
                    ];

                    if (! in_array($wicketType, $notBowlerWickets, true)) {
                        $bowlers[$bowler]['wickets']++;
                    }
                }
            }

            /*
             * Record dismissals against the dismissed batter.
             */
            if (
                (bool) $delivery->is_wicket
                && $delivery->dismissed_batter
            ) {
                $dismissed = trim(
                    (string) $delivery->dismissed_batter
                );

                if ($dismissed !== '') {
                    if (! isset($batters[$dismissed])) {
                        $batters[$dismissed] = [
                            'name' => $dismissed,
                            'player' => null,
                            'runs' => 0,
                            'balls' => 0,
                            'fours' => 0,
                            'sixes' => 0,
                            'dismissal' => 'out',
                            'strike_rate' => 0,
                        ];
                    }

                    $type = trim((string) $delivery->wicket_type);

                    $batters[$dismissed]['dismissal'] = $type !== ''
                        ? ucwords(str_replace('_', ' ', $type))
                        : 'out';
                }
            }
        }

        /*
         * The current striker and non-striker are not out.
         */
        foreach ([
            $inning->striker,
            $inning->non_striker,
        ] as $notOutName) {
            $notOutName = trim((string) $notOutName);

            if (
                $notOutName !== ''
                && isset($batters[$notOutName])
            ) {
                $batters[$notOutName]['dismissal'] = 'not out';
            }
        }

        /*
         * Calculate overs, maidens and economy.
         */
        foreach ($bowlers as &$bowler) {
            $legalBalls = (int) $bowler['legal_balls'];

            $bowler['overs'] = intdiv($legalBalls, 6)
                . '.'
                . ($legalBalls % 6);

            foreach ($bowler['_overs'] as $over) {
                if (
                    $over['legal_balls'] === 6
                    && $over['runs'] === 0
                ) {
                    $bowler['maidens']++;
                }
            }

            $bowler['economy'] = $legalBalls > 0
                ? round(($bowler['runs'] * 6) / $legalBalls, 2)
                : 0;

            unset($bowler['_overs']);
        }

        unset($bowler);

        /*
         * Calculate batter strike rates.
         */
        foreach ($batters as &$batter) {
            $batter['strike_rate'] = $batter['balls'] > 0
                ? round(
                    ($batter['runs'] * 100) / $batter['balls'],
                    2
                )
                : 0;
        }

        unset($batter);

        return [
            'batters' => array_values($batters),
            'bowlers' => array_values($bowlers),
            'extras' => $extras,
        ];
    }

    /**
     * Build scorecards for all innings in a fixture.
     *
     * Returns a collection keyed by innings ID.
     */
    public function forInnings(Collection $innings): Collection
    {
        return $innings->mapWithKeys(
            function (LiveInnings $inning): array {
                return [
                    $inning->id => $this->build($inning),
                ];
            }
        );
    }
}
