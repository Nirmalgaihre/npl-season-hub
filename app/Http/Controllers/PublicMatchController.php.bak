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
    
/**
 * Display a public match using manual_scorecards only.
 *
 * Live innings and live deliveries are deliberately not used.
 */
public function show(Fixture $fixture): View
{
    $fixture->load([
        'season',
        'homeTeam',
        'awayTeam',
        'winner',
    ]);

    abort_unless(
        $fixture->season
            && in_array(
                $fixture->season->status,
                ['upcoming', 'active', 'completed'],
                true
            ),
        404
    );

    /*
     * Fetch only manually entered scorecards for this fixture.
     * Never query live_innings or live_deliveries here.
     */
    $manualInnings = ManualScorecard::query()
        ->where('fixture_id', $fixture->id)
        ->with(['battingTeam', 'bowlingTeam'])
        ->orderBy('innings_number')
        ->get();

    /*
     * Prepare the manual innings for the existing Blade view.
     * Cricket overs such as 17.2 mean 17 overs and 2 balls,
     * not 17.2 decimal overs.
     */
    $innings = $manualInnings->map(function ($inning) {
        $oversText = (string) (
            $inning->getRawOriginal('overs')
            ?? $inning->overs
            ?? '0.0'
        );

        if (preg_match('/^(\d+)\.(\d+)$/', $oversText, $matches)) {
            $completedOvers = max(0, (int) $matches[1]);
            $ballsInOver = (int) $matches[2];

            // A valid cricket over contains 0–5 balls after the dot.
            $legalBalls = ($completedOvers * 6)
                + min($ballsInOver, 5);
        } else {
            $legalBalls = max(0, (int) $oversText) * 6;
            $oversText = (string) max(0, (int) $oversText) . '.0';
        }

        $inning->legal_balls = $legalBalls;
        $inning->overs_display = $oversText;
        $inning->is_current = false;
        $inning->deliveries = collect();

        return $inning;
    });

    /*
     * Key each scorecard by its own database ID.
     * The existing fixtures.partials.scorecard view uses this ID.
     */
    $scorecards = $innings->mapWithKeys(function ($inning) {
        $batters = collect($inning->batters ?? [])
            ->map(function ($batter) {
                if (! is_array($batter)) {
                    return null;
                }

                $dismissal = (string) ($batter['dismissal'] ?? '');
                $runs = (int) ($batter['runs'] ?? 0);
                $balls = (int) ($batter['balls'] ?? 0);

                $batter['is_not_out'] = (bool) (
                    $batter['is_not_out']
                    ?? str_contains(
                        mb_strtolower($dismissal),
                        'not out'
                    )
                );

                $batter['strike_rate'] = $balls > 0
                    ? round(($runs / $balls) * 100, 2)
                    : 0;

                return $batter;
            })
            ->filter()
            ->values()
            ->all();

        $bowlers = collect($inning->bowlers ?? [])
            ->map(function ($bowler) {
                if (! is_array($bowler)) {
                    return null;
                }

                $oversText = (string) ($bowler['overs'] ?? '0.0');
                $parts = explode('.', $oversText, 2);

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
            ->filter()
            ->values()
            ->all();

        return [
            $inning->id => [
                'batters' => $batters,
                'bowlers' => $bowlers,
                'extras' => $inning->extras ?? 0,
            ],
        ];
    });

    $currentInning = null;

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
