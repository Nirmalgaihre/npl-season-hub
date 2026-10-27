<?php

namespace App\Http\Controllers;

use App\Models\Fixture;
use App\Models\LiveInnings;
use App\Models\ManualScorecard;
use App\Models\Season;
use App\Services\ScorecardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PublicMatchController extends Controller
{
    /**
     * Display public fixtures and verified results.
     */
    public function index(Request $request): View
    {
        $seasons = Season::query()
            ->whereIn('status', ['upcoming', 'active', 'completed'])
            ->orderByDesc('season_number')
            ->get();

        $fixtures = Fixture::query()
            ->with(['season', 'homeTeam', 'awayTeam', 'winner'])
            ->whereHas('season', function ($query) {
                $query->whereIn('status', [
                    'upcoming',
                    'active',
                    'completed',
                ]);
            })
            ->when($request->filled('season'), function ($query) use ($request) {
                $query->where('season_id', $request->integer('season'));
            })
            ->when($request->input('status') === 'upcoming', function ($query) {
                $query->whereIn('status', ['scheduled', 'postponed']);
            })
            ->when($request->input('status') === 'live', function ($query) {
                $query->where('status', 'live');
            })
            ->when($request->input('status') === 'completed', function ($query) {
                $query->where('status', 'completed')
                    ->where('is_result_verified', true);
            })
            ->orderByRaw("
                CASE
                    WHEN status = 'live' THEN 0
                    WHEN status = 'scheduled' THEN 1
                    WHEN status = 'postponed' THEN 2
                    WHEN status = 'completed' THEN 3
                    ELSE 4
                END
            ")
            ->orderBy('scheduled_at')
            ->orderBy('match_number')
            ->paginate(12)
            ->withQueryString();

        return view('fixtures.index', compact('fixtures', 'seasons'));
    }

    /**
     * Display an individual match and its scorecards.
     */
    public function show(
        Fixture $fixture,
        ScorecardService $scorecardService
    ): View {
        $fixture->load([
            'season',
            'homeTeam',
            'awayTeam',
            'winner',
        ]);

        abort_unless(
            $fixture->season &&
            in_array(
                $fixture->season->status,
                ['upcoming', 'active', 'completed'],
                true
            ),
            404
        );

        /*
         * Keep unverified result details private.
         * Scorecard visibility is handled separately below.
         */
        if (! $fixture->is_result_verified) {
            foreach ([
                'home_runs',
                'home_wickets',
                'home_overs',
                'away_runs',
                'away_wickets',
                'away_overs',
                'winner_team_id',
                'player_of_match',
                'result_summary',
            ] as $attribute) {
                $fixture->setAttribute($attribute, null);
            }
        }

        $innings = collect();
        $scorecards = collect();

        $liveInnings = collect();
        $manualInnings = collect();

        /*
         * Read both scorecard sources.
         */
        $canShowLiveScorecard = $fixture->status === 'live'
            || (bool) $fixture->is_result_verified;

        if ($canShowLiveScorecard) {
            $liveInnings = DB::table('live_innings')
                ->where('fixture_id', $fixture->id)
                ->orderBy('innings_number')
                ->get();
        }

        /*
         * Manual scorecards may be published for scheduled fixtures
         * when staff have entered the scorecard in advance.
         *
         * For completed matches, only show manual scorecards after
         * the result has been verified.
         */
        $canShowManualScorecard = $fixture->status !== 'completed'
            || (bool) $fixture->is_result_verified;

        if ($canShowManualScorecard) {
            $manualInnings = ManualScorecard::query()
                ->where('fixture_id', $fixture->id)
                ->with(['battingTeam', 'bowlingTeam'])
                ->orderBy('innings_number')
                ->get();
        }

        /*
         * Prefer ball-by-ball live data when available.
         * Otherwise, use the manually entered scorecards.
         */
        if ($liveInnings->isNotEmpty()) {
            $innings = $liveInnings;

            $deliveriesByInnings = DB::table('live_deliveries')
                ->whereIn('innings_id', $innings->pluck('id'))
                ->orderBy('id')
                ->get()
                ->groupBy('innings_id');

            foreach ($innings as $inning) {
                $deliveries = $deliveriesByInnings->get(
                    $inning->id,
                    collect()
                );

                $inning->overs = $deliveries
                    ->groupBy('ball_number')
                    ->map(function ($balls, $overNumber) {
                        $legalBallsInOver = 0;

                        $balls = $balls->map(function ($ball) use (
                            &$legalBallsInOver,
                            $overNumber
                        ) {
                            $ball->over_number = (int) $overNumber;

                            $ball->display_ball = ((int) $overNumber)
                                . '.'
                                . min($legalBallsInOver + 1, 6);

                            $ball->total_delivery_runs =
                                (int) ($ball->batter_runs ?? 0)
                                + (int) ($ball->extra_runs ?? 0);

                            if ((bool) $ball->counts_as_legal_ball) {
                                $legalBallsInOver++;
                            }

                            return $ball;
                        });

                        return (object) [
                            'number' => (int) $overNumber,
                            'balls' => $balls,
                            'runs' => $balls->sum('total_delivery_runs'),
                            'wickets' => $balls->filter(
                                fn ($ball) => (bool) $ball->is_wicket
                            )->count(),
                        ];
                    })
                    ->sortKeys();

                $inning->deliveries = $deliveries;
            }

            $scorecardInnings = LiveInnings::query()
                ->where('fixture_id', $fixture->id)
                ->whereIn('id', $innings->pluck('id'))
                ->with('deliveries')
                ->orderBy('innings_number')
                ->get();

            $scorecards = $scorecardService->forInnings(
                $scorecardInnings
            );
        } elseif ($manualInnings->isNotEmpty()) {
            /*
             * Adapt ManualScorecard records to the properties
             * expected by fixtures.partials.scorecard.
             */
            $innings = $manualInnings->map(function ($inning) {
                $oversText = (string) $inning->getRawOriginal('overs');

                if (preg_match('/^(\d+)\.(\d+)$/', $oversText, $matches)) {
                    $completedOvers = (int) $matches[1];
                    $ballsInOver = (int) $matches[2];

                    $legalBalls = ($completedOvers * 6)
                        + min($ballsInOver, 5);
                } else {
                    $legalBalls = (int) round((float) $oversText) * 6;
                }

                $inning->legal_balls = $legalBalls;
                $inning->overs_display = $oversText;
                $inning->is_current = false;
                $inning->deliveries = collect();

                return $inning;
            });

            $scorecards = $innings->mapWithKeys(function ($inning) {
                $batters = collect($inning->batters ?? [])
                    ->map(function ($batter) {
                        $dismissal = (string) ($batter['dismissal'] ?? '');

                        $batter['is_not_out'] = str_contains(
                            strtolower($dismissal),
                            'not out'
                        );

                        $runs = (int) ($batter['runs'] ?? 0);
                        $balls = (int) ($batter['balls'] ?? 0);

                        $batter['strike_rate'] = $balls > 0
                            ? round(($runs / $balls) * 100, 2)
                            : 0;

                        return $batter;
                    })
                    ->all();

                $bowlers = collect($inning->bowlers ?? [])
                    ->map(function ($bowler) {
                        $oversText = (string) ($bowler['overs'] ?? '0.0');
                        $parts = explode('.', $oversText);

                        $completedOvers = max(
                            0,
                            (int) ($parts[0] ?? 0)
                        );

                        $ballsInOver = min(
                            5,
                            max(0, (int) ($parts[1] ?? 0))
                        );

                        $legalBalls = ($completedOvers * 6) + $ballsInOver;
                        $runs = (int) ($bowler['runs'] ?? 0);

                        $bowler['economy'] = $legalBalls > 0
                            ? round($runs / ($legalBalls / 6), 2)
                            : 0;

                        return $bowler;
                    })
                    ->all();

                return [
                    $inning->id => [
                        'batters' => $batters,
                        'bowlers' => $bowlers,
                        'extras' => (int) ($inning->extras ?? 0),
                    ],
                ];
            });
        }

        $currentInning = $innings->first(
            fn ($inning) => (bool) ($inning->is_current ?? false)
        );

        return view('fixtures.show', [
            'fixture' => $fixture,
            'innings' => $innings,
            'currentInning' => $currentInning,
            'scorecards' => $scorecards,
        ]);
    }

/**
 * Display a player's performance across all saved manual scorecards.
 */
public function playerPerformance(string $playerName): \Illuminate\View\View
{
    $playerName = trim(urldecode($playerName));

    abort_if($playerName === '', 404);

    $normaliseName = static function ($value): string {
        $value = preg_replace('/\s+/', ' ', trim((string) $value));

        return mb_strtolower($value);
    };

    $targetName = $normaliseName($playerName);

    $scorecards = ManualScorecard::query()
        ->with([
            'fixture.season',
            'fixture.homeTeam',
            'fixture.awayTeam',
            'battingTeam',
            'bowlingTeam',
        ])
        ->whereHas('fixture', function ($query) {
            $query->whereHas('season', function ($seasonQuery) {
                $seasonQuery->whereIn('status', [
                    'upcoming',
                    'active',
                    'completed',
                ]);
            });
        })
        ->orderByDesc('fixture_id')
        ->orderBy('innings_number')
        ->get();

    $getPlayerName = static function (array $entry): string {
        return (string) (
            data_get($entry, 'player.name')
            ?? data_get($entry, 'name')
            ?? data_get($entry, 'player_name')
            ?? data_get($entry, 'batsman')
            ?? data_get($entry, 'bowler')
            ?? ''
        );
    };

    $performances = collect();

    foreach ($scorecards as $scorecard) {
        $batters = is_array($scorecard->batters)
            ? $scorecard->batters
            : [];

        $bowlers = is_array($scorecard->bowlers)
            ? $scorecard->bowlers
            : [];

        $batterRow = null;
        $bowlerRow = null;

        foreach ($batters as $batter) {
            if (! is_array($batter)) {
                continue;
            }

            if ($normaliseName($getPlayerName($batter)) === $targetName) {
                $batterRow = $batter;
                break;
            }
        }

        foreach ($bowlers as $bowler) {
            if (! is_array($bowler)) {
                continue;
            }

            if ($normaliseName($getPlayerName($bowler)) === $targetName) {
                $bowlerRow = $bowler;
                break;
            }
        }

        if (! $batterRow && ! $bowlerRow) {
            continue;
        }

        $fixture = $scorecard->fixture;

        if (! $fixture) {
            continue;
        }

        /*
         * Use the scorecard's actual batting/bowling teams.
         * Fall back to the fixture home/away teams if necessary.
         */
        $battingTeam = $scorecard->battingTeam;
        $bowlingTeam = $scorecard->bowlingTeam;

        if (! $battingTeam || ! $bowlingTeam) {
            $homeTeamId = (int) $fixture->home_team_id;
            $battingTeamId = (int) $scorecard->batting_team_id;

            if ($battingTeamId === $homeTeamId) {
                $battingTeam = $battingTeam ?: $fixture->homeTeam;
                $bowlingTeam = $bowlingTeam ?: $fixture->awayTeam;
            } else {
                $battingTeam = $battingTeam ?: $fixture->awayTeam;
                $bowlingTeam = $bowlingTeam ?: $fixture->homeTeam;
            }
        }

        $performances->push([
            'scorecard' => $scorecard,
            'fixture' => $fixture,
            'batting_team' => $battingTeam,
            'bowling_team' => $bowlingTeam,
            'batter' => $batterRow,
            'bowler' => $bowlerRow,
        ]);
    }

    abort_if($performances->isEmpty(), 404, 'Player performance not found.');

    $battingStats = [
        'innings' => 0,
        'runs' => 0,
        'balls' => 0,
        'fours' => 0,
        'sixes' => 0,
        'not_outs' => 0,
        'dismissals' => 0,
        'highest_score' => 0,
        'average' => null,
        'strike_rate' => 0,
    ];

    $bowlingStats = [
        'innings' => 0,
        'legal_balls' => 0,
        'overs' => '0.0',
        'maidens' => 0,
        'runs' => 0,
        'wickets' => 0,
        'economy' => 0,
    ];

    foreach ($performances as $performance) {
        $batter = $performance['batter'];
        $bowler = $performance['bowler'];

        if ($batter) {
            $runs = (int) ($batter['runs'] ?? 0);
            $balls = (int) ($batter['balls'] ?? 0);
            $fours = (int) ($batter['fours'] ?? 0);
            $sixes = (int) ($batter['sixes'] ?? 0);

            $dismissal = (string) ($batter['dismissal'] ?? '');

            $isNotOut = (bool) (
                $batter['is_not_out']
                ?? str_contains(mb_strtolower($dismissal), 'not out')
            );

            $battingStats['innings']++;
            $battingStats['runs'] += $runs;
            $battingStats['balls'] += $balls;
            $battingStats['fours'] += $fours;
            $battingStats['sixes'] += $sixes;
            $battingStats['highest_score'] = max(
                $battingStats['highest_score'],
                $runs
            );

            if ($isNotOut) {
                $battingStats['not_outs']++;
            } else {
                $battingStats['dismissals']++;
            }
        }

        if ($bowler) {
            /*
             * Cricket overs are not decimal numbers:
             * 3.2 means 3 overs and 2 balls.
             */
            $oversText = (string) ($bowler['overs'] ?? '0.0');
            $parts = explode('.', $oversText);

            $completedOvers = max(0, (int) ($parts[0] ?? 0));
            $ballsInOver = min(5, max(0, (int) ($parts[1] ?? 0)));
            $legalBalls = ($completedOvers * 6) + $ballsInOver;

            $bowlingStats['innings']++;
            $bowlingStats['legal_balls'] += $legalBalls;
            $bowlingStats['maidens'] += (int) ($bowler['maidens'] ?? 0);
            $bowlingStats['runs'] += (int) ($bowler['runs'] ?? 0);
            $bowlingStats['wickets'] += (int) ($bowler['wickets'] ?? 0);
        }
    }

    $battingStats['average'] = $battingStats['dismissals'] > 0
        ? round(
            $battingStats['runs'] / $battingStats['dismissals'],
            2
        )
        : null;

    $battingStats['strike_rate'] = $battingStats['balls'] > 0
        ? round(
            ($battingStats['runs'] / $battingStats['balls']) * 100,
            2
        )
        : 0;

    $bowlingStats['overs'] = intdiv(
        $bowlingStats['legal_balls'],
        6
    ) . '.' . ($bowlingStats['legal_balls'] % 6);

    $bowlingStats['economy'] = $bowlingStats['legal_balls'] > 0
        ? round(
            $bowlingStats['runs'] / ($bowlingStats['legal_balls'] / 6),
            2
        )
        : 0;

    return view('fixtures.player-performance', [
        'playerName' => $playerName,
        'performances' => $performances,
        'battingStats' => $battingStats,
        'bowlingStats' => $bowlingStats,
    ]);
}

}
