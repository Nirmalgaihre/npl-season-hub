<?php

namespace App\Http\Controllers;

use App\Models\Fixture;
use App\Models\ManualScorecard;
use App\Models\Season;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PublicMatchController extends Controller
{
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

    public function show(Fixture $fixture): View
    {
        $fixture->load(['season', 'homeTeam', 'awayTeam', 'winner']);

        abort_unless(
            $fixture->season && in_array($fixture->season->status, ['upcoming', 'active', 'completed'], true),
            404
        );

        if (! $fixture->is_result_verified) {
            $fixture->setAttribute('home_runs', null);
            $fixture->setAttribute('home_wickets', null);
            $fixture->setAttribute('home_overs', null);
            $fixture->setAttribute('away_runs', null);
            $fixture->setAttribute('away_wickets', null);
            $fixture->setAttribute('away_overs', null);
            $fixture->setAttribute('winner_team_id', null);
            $fixture->setAttribute('result_summary', null);
            $fixture->setRelation('winner', null);
        }

        $rows = DB::table('manual_scorecards')
            ->where('fixture_id', $fixture->id)
            ->orderBy('innings_number')
            ->get();

        $innings = $rows->map(function ($row) {
            $obj = new \stdClass();
            $obj->id              = $row->id;
            $obj->innings_number  = $row->innings_number ?? 1;
            $obj->batting_team_id = $row->batting_team_id ?? null;
            $obj->total_runs      = (int) ($row->total_runs ?? $row->runs ?? 0);
            $obj->wickets         = (int) ($row->wickets ?? 0);
            $obj->overs_display   = (string) ($row->overs ?? '0.0');
            $obj->legal_balls     = 0;
            $obj->batters         = [];
            $obj->bowlers         = [];
            $obj->extras          = $row->extras ?? 0;
            return $obj;
        });

        return view('fixtures.show', [
            'fixture'       => $fixture,
            'innings'       => $innings,
            'currentInning' => null,
            'scorecards'    => collect(),
        ]);
    }

    public function playerPerformance(string $playerName): View
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
            $batters = is_array($scorecard->batters) ? $scorecard->batters : [];
            $bowlers = is_array($scorecard->bowlers) ? $scorecard->bowlers : [];

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
                'scorecard'     => $scorecard,
                'fixture'       => $fixture,
                'batting_team'  => $battingTeam,
                'bowling_team'  => $bowlingTeam,
                'batter'        => $batterRow,
                'bowler'        => $bowlerRow,
            ]);
        }

        $battingStats = [
            'innings'       => 0,
            'runs'          => 0,
            'balls'         => 0,
            'fours'         => 0,
            'sixes'         => 0,
            'not_outs'      => 0,
            'dismissals'    => 0,
            'highest_score' => 0,
            'average'       => null,
            'strike_rate'   => 0,
        ];

        $bowlingStats = [
            'innings'     => 0,
            'legal_balls' => 0,
            'overs'       => '0.0',
            'maidens'     => 0,
            'runs'        => 0,
            'wickets'     => 0,
            'economy'     => 0,
        ];

        foreach ($performances as $performance) {
            $batter = $performance['batter'];
            $bowler = $performance['bowler'];

            if ($batter) {
                $runs  = (int) ($batter['runs'] ?? 0);
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
                $battingStats['highest_score'] = max($battingStats['highest_score'], $runs);

                if ($isNotOut) {
                    $battingStats['not_outs']++;
                } else {
                    $battingStats['dismissals']++;
                }
            }

            if ($bowler) {
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
            ? round($battingStats['runs'] / $battingStats['dismissals'], 2)
            : null;

        $battingStats['strike_rate'] = $battingStats['balls'] > 0
            ? round(($battingStats['runs'] / $battingStats['balls']) * 100, 2)
            : 0;

        $bowlingStats['overs'] = intdiv($bowlingStats['legal_balls'], 6) . '.' . ($bowlingStats['legal_balls'] % 6);

        $bowlingStats['economy'] = $bowlingStats['legal_balls'] > 0
            ? round($bowlingStats['runs'] / ($bowlingStats['legal_balls'] / 6), 2)
            : 0;

        return view('fixtures.player-performance', [
            'playerName'    => $playerName,
            'performances'  => $performances,
            'battingStats'  => $battingStats,
            'bowlingStats'  => $bowlingStats,
        ]);
    }
}