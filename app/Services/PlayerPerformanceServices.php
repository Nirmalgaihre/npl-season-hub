<?php

namespace App\Services;

use App\Models\ManualScorecard;
use App\Models\Player;
use Illuminate\Support\Collection;

class PlayerPerformanceService
{
    /**
     * Aggregate all manual-scorecard performances for a player.
     *
     * Match is done by normalised name (case-insensitive, whitespace-trimmed).
     * This mirrors the previous `PublicMatchController@playerPerformance` logic
     * so both the profile page and the performance page show identical totals.
     *
     * @return array{
     *     performances: Collection,
     *     battingStats: array,
     *     bowlingStats: array
     * }
     */
    public function forPlayer(Player $player): array
    {
        return $this->forPlayerName($player->name ?? '');
    }

    /**
     * Same aggregation, but keyed by display name. Useful when
     * the caller has a slug or name but not a Player model.
     */
    public function forPlayerName(string $playerName): array
    {
        $playerName = trim(urldecode($playerName));

        if ($playerName === '') {
            return $this->emptyResult();
        }

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
                if (!is_array($batter)) continue;
                if ($normaliseName($getPlayerName($batter)) === $targetName) {
                    $batterRow = $batter;
                    break;
                }
            }

            foreach ($bowlers as $bowler) {
                if (!is_array($bowler)) continue;
                if ($normaliseName($getPlayerName($bowler)) === $targetName) {
                    $bowlerRow = $bowler;
                    break;
                }
            }

            if (!$batterRow && !$bowlerRow) continue;

            $fixture = $scorecard->fixture;
            if (!$fixture) continue;

            $battingTeam = $scorecard->battingTeam;
            $bowlingTeam = $scorecard->bowlingTeam;

            if (!$battingTeam || !$bowlingTeam) {
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
                'scorecard'    => $scorecard,
                'fixture'      => $fixture,
                'batting_team' => $battingTeam,
                'bowling_team' => $bowlingTeam,
                'batter'       => $batterRow,
                'bowler'       => $bowlerRow,
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
                $runs  = (int) ($batter['runs']  ?? 0);
                $balls = (int) ($batter['balls'] ?? 0);
                $fours = (int) ($batter['fours'] ?? 0);
                $sixes = (int) ($batter['sixes'] ?? 0);

                $dismissal = (string) ($batter['dismissal'] ?? '');
                $isNotOut  = (bool) (
                    $batter['is_not_out']
                    ?? str_contains(mb_strtolower($dismissal), 'not out')
                );

                $battingStats['innings']++;
                $battingStats['runs']   += $runs;
                $battingStats['balls']  += $balls;
                $battingStats['fours']  += $fours;
                $battingStats['sixes']  += $sixes;
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
                $ballsInOver    = min(5, max(0, (int) ($parts[1] ?? 0)));
                $legalBalls     = ($completedOvers * 6) + $ballsInOver;

                $bowlingStats['innings']++;
                $bowlingStats['legal_balls'] += $legalBalls;
                $bowlingStats['maidens']     += (int) ($bowler['maidens'] ?? 0);
                $bowlingStats['runs']        += (int) ($bowler['runs'] ?? 0);
                $bowlingStats['wickets']     += (int) ($bowler['wickets'] ?? 0);
            }
        }

        $battingStats['average'] = $battingStats['dismissals'] > 0
            ? round($battingStats['runs'] / $battingStats['dismissals'], 2)
            : null;

        $battingStats['strike_rate'] = $battingStats['balls'] > 0
            ? round(($battingStats['runs'] / $battingStats['balls']) * 100, 2)
            : 0;

        $bowlingStats['overs'] = intdiv($bowlingStats['legal_balls'], 6)
            . '.' . ($bowlingStats['legal_balls'] % 6);

        $bowlingStats['economy'] = $bowlingStats['legal_balls'] > 0
            ? round(
                $bowlingStats['runs'] / ($bowlingStats['legal_balls'] / 6),
                2
            )
            : 0;

        return [
            'performances'  => $performances,
            'battingStats'  => $battingStats,
            'bowlingStats'  => $bowlingStats,
        ];
    }

    /**
     * Empty shape so view code can always assume the keys exist.
     */
    public function emptyResult(): array
    {
        return [
            'performances' => collect(),
            'battingStats' => [
                'innings' => 0, 'runs' => 0, 'balls' => 0, 'fours' => 0, 'sixes' => 0,
                'not_outs' => 0, 'dismissals' => 0, 'highest_score' => 0,
                'average' => null, 'strike_rate' => 0,
            ],
            'bowlingStats' => [
                'innings' => 0, 'legal_balls' => 0, 'overs' => '0.0',
                'maidens' => 0, 'runs' => 0, 'wickets' => 0, 'economy' => 0,
            ],
        ];
    }
}