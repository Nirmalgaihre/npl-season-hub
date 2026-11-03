<?php

namespace App\Http\Controllers;

use App\Models\ManualScorecard;
use App\Models\Player;
use Illuminate\Support\Collection;

class PlayerProfileController extends Controller
{
    public function show(Player $player)
    {
        $player->load('team');

        $team = $player->team;

        /*
         * Manual scorecards are the single source of truth.
         * Do not query live innings or separate batting/bowling tables.
         */
        $scorecards = ManualScorecard::query()
            ->with([
                'fixture.season',
                'fixture.homeTeam',
                'fixture.awayTeam',
                'fixture.winner',
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

        $normaliseName = static function ($value): string {
            return mb_strtolower(
                preg_replace('/\s+/u', ' ', trim((string) $value))
            );
        };

        $targetName = $normaliseName($player->name);
        $performances = collect();
        $battingRows = collect();
        $bowlingRows = collect();

        foreach ($scorecards as $scorecard) {
            $fixture = $scorecard->fixture;

            if (! $fixture) {
                continue;
            }

            $batters = is_array($scorecard->batters)
                ? $scorecard->batters
                : [];

            $bowlers = is_array($scorecard->bowlers)
                ? $scorecard->bowlers
                : [];

            $batterRow = null;
            $bowlerRow = null;

            /*
             * Match by player ID when the JSON contains one.
             * Otherwise, match the normalized player name.
             */
            foreach ($batters as $batter) {
                if (! is_array($batter)) {
                    continue;
                }

                $entryPlayerId = data_get($batter, 'player_id')
                    ?? data_get($batter, 'player.id');

                $entryName = data_get($batter, 'player.name')
                    ?? data_get($batter, 'name')
                    ?? data_get($batter, 'player_name')
                    ?? data_get($batter, 'batsman')
                    ?? '';

                $matchesById = $entryPlayerId !== null
                    && (int) $entryPlayerId === (int) $player->id;

                $matchesByName = $normaliseName($entryName) === $targetName;

                if ($matchesById || $matchesByName) {
                    $batterRow = $batter;
                    break;
                }
            }

            foreach ($bowlers as $bowler) {
                if (! is_array($bowler)) {
                    continue;
                }

                $entryPlayerId = data_get($bowler, 'player_id')
                    ?? data_get($bowler, 'player.id');

                $entryName = data_get($bowler, 'player.name')
                    ?? data_get($bowler, 'name')
                    ?? data_get($bowler, 'player_name')
                    ?? data_get($bowler, 'bowler')
                    ?? '';

                $matchesById = $entryPlayerId !== null
                    && (int) $entryPlayerId === (int) $player->id;

                $matchesByName = $normaliseName($entryName) === $targetName;

                if ($matchesById || $matchesByName) {
                    $bowlerRow = $bowler;
                    break;
                }
            }

            if (! $batterRow && ! $bowlerRow) {
                continue;
            }

            $battingTeam = $scorecard->battingTeam;
            $bowlingTeam = $scorecard->bowlingTeam;

            /*
             * Fallback team resolution for scorecards whose team
             * relationships are not available.
             */
            if (! $battingTeam || ! $bowlingTeam) {
                $battingTeamId = (int) $scorecard->batting_team_id;
                $homeTeamId = (int) $fixture->home_team_id;

                if ($battingTeamId === $homeTeamId) {
                    $battingTeam ??= $fixture->homeTeam;
                    $bowlingTeam ??= $fixture->awayTeam;
                } else {
                    $battingTeam ??= $fixture->awayTeam;
                    $bowlingTeam ??= $fixture->homeTeam;
                }
            }

            /*
             * Normalize batting data for the profile statistics.
             */
            if ($batterRow) {
                $runs = (int) ($batterRow['runs'] ?? 0);
                $balls = (int) ($batterRow['balls'] ?? 0);
                $fours = (int) ($batterRow['fours'] ?? 0);
                $sixes = (int) ($batterRow['sixes'] ?? 0);

                $dismissal = (string) ($batterRow['dismissal'] ?? '');

                $isNotOut = (bool) (
                    $batterRow['is_not_out']
                    ?? $batterRow['is_out'] === false
                    ?? str_contains(mb_strtolower($dismissal), 'not out')
                );

                // Explicit dismissal text is also used when available.
                if (str_contains(mb_strtolower($dismissal), 'not out')) {
                    $isNotOut = true;
                }

                $batterRow['runs'] = $runs;
                $batterRow['balls'] = $balls;
                $batterRow['fours'] = $fours;
                $batterRow['sixes'] = $sixes;
                $batterRow['is_not_out'] = $isNotOut;
                $batterRow['strike_rate'] = $balls > 0
                    ? round(($runs / $balls) * 100, 2)
                    : 0;

                $battingRows->push((object) [
                    'id' => $scorecard->id,
                    'fixture_id' => $fixture->id,
                    'fixture' => $fixture,
                    'scorecard' => $scorecard,
                    'runs' => $runs,
                    'balls' => $balls,
                    'fours' => $fours,
                    'sixes' => $sixes,
                    'dismissal' => $dismissal,
                    'is_not_out' => $isNotOut,
                    'is_out' => ! $isNotOut,
                    'strike_rate' => $batterRow['strike_rate'],
                    'batting_team' => $battingTeam,
                    'bowling_team' => $bowlingTeam,
                ]);
            }

            /*
             * Normalize bowling data.
             * Cricket overs are converted to legal balls first:
             * 4.2 overs means 26 balls, not 4.2 decimal overs.
             */
            if ($bowlerRow) {
                $oversText = (string) ($bowlerRow['overs'] ?? '0.0');
                $parts = explode('.', $oversText, 2);

                $completedOvers = max(0, (int) ($parts[0] ?? 0));
                $ballsInOver = min(
                    5,
                    max(0, (int) ($parts[1] ?? 0))
                );

                $legalBalls = ($completedOvers * 6) + $ballsInOver;
                $runsConceded = (int) ($bowlerRow['runs'] ?? 0);
                $wickets = (int) ($bowlerRow['wickets'] ?? 0);

                $bowlerRow['legal_balls'] = $legalBalls;
                $bowlerRow['runs_conceded'] = $runsConceded;
                $bowlerRow['wickets'] = $wickets;
                $bowlerRow['economy'] = $legalBalls > 0
                    ? round($runsConceded / ($legalBalls / 6), 2)
                    : 0;

                $bowlingRows->push((object) [
                    'id' => $scorecard->id,
                    'fixture_id' => $fixture->id,
                    'fixture' => $fixture,
                    'scorecard' => $scorecard,
                    'overs' => $oversText,
                    'legal_balls' => $legalBalls,
                    'runs' => $runsConceded,
                    'runs_conceded' => $runsConceded,
                    'wickets' => $wickets,
                    'maidens' => (int) ($bowlerRow['maidens'] ?? 0),
                    'economy' => $bowlerRow['economy'],
                    'bowling_team' => $bowlingTeam,
                    'batting_team' => $battingTeam,
                ]);
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

        /*
         * Batting aggregates.
         */
        $totalInnings = $battingRows->count();
        $totalRuns = (int) $battingRows->sum('runs');
        $totalBalls = (int) $battingRows->sum('balls');
        $totalFours = (int) $battingRows->sum('fours');
        $totalSixes = (int) $battingRows->sum('sixes');
        $notOuts = $battingRows->where('is_not_out', true)->count();
        $dismissals = $totalInnings - $notOuts;

        $highestScore = (int) ($battingRows->max('runs') ?? 0);

        $battingAvg = $dismissals > 0
            ? round($totalRuns / $dismissals, 2)
            : null;

        $strikeRate = $totalBalls > 0
            ? round(($totalRuns / $totalBalls) * 100, 2)
            : 0;

        /*
         * Bowling aggregates.
         */
        $totalBowlInnings = $bowlingRows->count();
        $totalWickets = (int) $bowlingRows->sum('wickets');
        $totalRunsConceded = (int) $bowlingRows->sum('runs_conceded');
        $totalLegalBalls = (int) $bowlingRows->sum('legal_balls');

        // Decimal overs are used for calculations only.
        $totalOvers = $totalLegalBalls / 6;

        $economy = $totalLegalBalls > 0
            ? round($totalRunsConceded / ($totalLegalBalls / 6), 2)
            : null;

        $bestFigures = $bowlingRows
            ->sort(function ($a, $b) {
                return ($b->wickets <=> $a->wickets)
                    ?: ($a->runs_conceded <=> $b->runs_conceded);
            })
            ->first();

        /*
         * Stats array used by the performance/profile Blade sections.
         */
        $battingStats = [
            'innings' => $totalInnings,
            'runs' => $totalRuns,
            'balls' => $totalBalls,
            'fours' => $totalFours,
            'sixes' => $totalSixes,
            'not_outs' => $notOuts,
            'dismissals' => $dismissals,
            'highest_score' => $highestScore,
            'average' => $battingAvg,
            'strike_rate' => $strikeRate,
        ];

        $bowlingStats = [
            'innings' => $totalBowlInnings,
            'legal_balls' => $totalLegalBalls,
            'overs' => intdiv($totalLegalBalls, 6)
                . '.' . ($totalLegalBalls % 6),
            'maidens' => (int) $bowlingRows->sum('maidens'),
            'runs' => $totalRunsConceded,
            'wickets' => $totalWickets,
            'economy' => $economy ?? 0,
        ];

        return view('players.show', compact(
            'player',
            'team',
            'performances',
            'battingStats',
            'bowlingStats',
            'battingRows',
            'bowlingRows',
            'totalInnings',
            'totalRuns',
            'totalBalls',
            'totalFours',
            'totalSixes',
            'notOuts',
            'dismissals',
            'highestScore',
            'battingAvg',
            'strikeRate',
            'totalBowlInnings',
            'totalWickets',
            'totalOvers',
            'totalRunsConceded',
            'bestFigures',
            'economy',
        ));
    }
}
