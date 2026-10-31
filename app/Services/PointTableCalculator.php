<?php

namespace App\Services;

use App\Models\Fixture;
use App\Models\PointTableAdjustment;
use App\Models\Season;
use App\Models\Team;
use Illuminate\Support\Collection;

class PointTableCalculator
{
    public function calculate(Season $season): array
    {
        $seasonId = (int) $season->id;

        $fixtures = Fixture::query()
            ->where('season_id', $seasonId)
            ->with(['homeTeam', 'awayTeam'])
            ->orderBy('match_number')
            ->orderBy('scheduled_at')
            ->get();

        $teamIds = Team::query()
            ->whereHas('seasons', fn ($q) =>
                $q->where('seasons.id', $seasonId)
            )
            ->pluck('id')
            ->merge(
                $fixtures->flatMap(fn ($f) => [
                    $f->home_team_id,
                    $f->away_team_id,
                ])
            )
            ->filter()
            ->unique()
            ->values();

        $teams = Team::query()
            ->whereIn('id', $teamIds)
            ->orderBy('name')
            ->get();

        $adjustments = PointTableAdjustment::query()
            ->where('season_id', $seasonId)
            ->get()
            ->keyBy('team_id');

        $noResultPoints = (int) config(
            'pointtable.no_result_points',
            1
        );

        // Keep the configured award within the allowed 1–4 range.
        $noResultPoints = max(1, min(4, $noResultPoints));

        $standings = $teams->map(function (Team $team) use ($adjustments) {
            $penalty = (int) (
                $adjustments->get($team->id)?->penalty_points ?? 0
            );

            return [
                'team' => $team,

                // Admin table keys
                'gp' => 0,
                'w' => 0,
                't' => 0,
                'l' => 0,
                'nr' => 0,
                'basePts' => 0,
                'penalty' => $penalty,
                'pts' => 0,

                // Public table keys
                'played' => 0,
                'won' => 0,
                'tied' => 0,
                'lost' => 0,
                'no_result' => 0,
                'points' => 0,
                'penalty_points' => $penalty,

                // NRR statistics
                'runs_for' => 0,
                'balls_faced' => 0,
                'runs_against' => 0,
                'balls_bowled' => 0,
                'nrr' => null,
                'nrr_available' => false,
            ];
        })->keyBy(fn ($row) => (int) $row['team']->id);

        $completedMatches = 0;
        $upcomingMatches = 0;

        foreach ($fixtures as $fixture) {
            $homeId = (int) $fixture->home_team_id;
            $awayId = (int) $fixture->away_team_id;

            if (
                !$standings->has($homeId)
                || !$standings->has($awayId)
            ) {
                continue;
            }

            $status = strtolower(trim((string) $fixture->status));
            $summary = strtolower(trim((string) $fixture->result_summary));

            $isAbandoned = in_array($status, [
                'abandoned',
                'no_result',
                'no result',
                'washed out',
                'washout',
            ], true)
                || str_contains($summary, 'no result')
                || str_contains($summary, 'washed out')
                || str_contains($summary, 'washout')
                || str_contains($summary, 'abandoned');

            $isCancelled = in_array($status, [
                'cancelled',
                'canceled',
                'postponed',
            ], true);

            // Postponed and cancelled matches do not count as played.
            if ($isCancelled) {
                continue;
            }

            $isCompleted = in_array($status, [
                'completed',
                'complete',
                'finished',
                'result',
                'result_verified',
            ], true);

            /*
             * Abandoned/no-result matches use their saved status.
             * Regular completed matches require explicit verification.
             */
            if ($isAbandoned) {
                $home = $standings->get($homeId);
                $away = $standings->get($awayId);

                $this->recordTie($home, $noResultPoints);
                $this->recordTie($away, $noResultPoints);

                $standings->put($homeId, $home);
                $standings->put($awayId, $away);

                continue;
            }

            if (!$isCompleted || !$fixture->is_result_verified) {
                $upcomingMatches++;
                continue;
            }

            $winnerId = $fixture->winner_team_id !== null
                ? (int) $fixture->winner_team_id
                : null;

            $home = $standings->get($homeId);
            $away = $standings->get($awayId);

            $homeRuns = $fixture->home_runs;
            $awayRuns = $fixture->away_runs;

            $explicitTie = in_array($status, ['tie', 'tied'], true)
                || str_contains($summary, 'tie')
                || str_contains($summary, 'draw');

            $equalScores = $homeRuns !== null
                && $awayRuns !== null
                && (int) $homeRuns === (int) $awayRuns;

            $isTie = !$winnerId && ($explicitTie || $equalScores);

            if ($winnerId === $homeId) {
                $this->recordWin($home);
                $this->recordLoss($away);
            } elseif ($winnerId === $awayId) {
                $this->recordWin($away);
                $this->recordLoss($home);
            } elseif ($isTie) {
                $this->recordTie($home, 1);
                $this->recordTie($away, 1);
            } else {
                /*
                 * Do not guess a winner if a verified match has
                 * incomplete or conflicting result information.
                 */
                $standings->put($homeId, $home);
                $standings->put($awayId, $away);
                continue;
            }

            $completedMatches++;

            $homeBalls = $this->oversToBalls($fixture->home_overs);
            $awayBalls = $this->oversToBalls($fixture->away_overs);

            if (
                $homeRuns !== null
                && $awayRuns !== null
                && $homeBalls > 0
                && $awayBalls > 0
            ) {
                $home['runs_for'] += (int) $homeRuns;
                $home['balls_faced'] += $homeBalls;
                $home['runs_against'] += (int) $awayRuns;
                $home['balls_bowled'] += $awayBalls;

                $away['runs_for'] += (int) $awayRuns;
                $away['balls_faced'] += $awayBalls;
                $away['runs_against'] += (int) $homeRuns;
                $away['balls_bowled'] += $homeBalls;
            }

            $standings->put($homeId, $home);
            $standings->put($awayId, $away);
        }

        $standings = $standings->map(function ($row) {
            $row['pts'] = $row['basePts'] - $row['penalty'];
            $row['points'] = $row['pts'];

            $row['nrr_available'] = $row['balls_faced'] > 0
                && $row['balls_bowled'] > 0;

            if ($row['nrr_available']) {
                $for = $row['runs_for'] / ($row['balls_faced'] / 6);
                $against = $row['runs_against'] / ($row['balls_bowled'] / 6);

                $row['nrr'] = round($for - $against, 3);
            }

            return $row;
        })->sort(function ($a, $b) {
            return ($b['pts'] <=> $a['pts'])
                ?: (($b['w'] <=> $a['w']))
                ?: (($b['nrr'] ?? -INF) <=> ($a['nrr'] ?? -INF))
                ?: strcasecmp($a['team']->name, $b['team']->name);
        })->values();

        return [
            'fixtures' => $fixtures,
            'teams' => $teams,
            'adjustments' => $adjustments,
            'standings' => $standings,
            'completedMatches' => $completedMatches,
            'upcomingMatches' => $upcomingMatches,
            'totalTeams' => $teams->count(),
            'fixtureCount' => $fixtures->count(),
            'noResultPoints' => $noResultPoints,
        ];
    }

    private function recordWin(array &$row): void
    {
        $row['gp']++;
        $row['played']++;
        $row['w']++;
        $row['won']++;
        $row['basePts'] += 2;
    }

    private function recordLoss(array &$row): void
    {
        $row['gp']++;
        $row['played']++;
        $row['l']++;
        $row['lost']++;
    }

    private function recordTie(array &$row, int $points): void
    {
        $row['gp']++;
        $row['played']++;
        $row['t']++;
        $row['tied']++;
        $row['basePts'] += $points;
    }

    private function oversToBalls(mixed $overs): int
    {
        if ($overs === null || $overs === '') {
            return 0;
        }

        $value = (string) $overs;

        if (!str_contains($value, '.')) {
            return max(0, (int) $value * 6);
        }

        [$whole, $balls] = array_pad(explode('.', $value, 2), 2, '0');

        $whole = max(0, (int) $whole);
        $balls = (int) $balls;

        if ($balls < 0 || $balls > 5) {
            return 0;
        }

        return ($whole * 6) + $balls;
    }
}