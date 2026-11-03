<?php

namespace App\Services;

use App\Models\Fixture;
use App\Models\PointTableAdjustment;
use App\Models\Season;
use App\Models\Team;
use Illuminate\Support\Collection;

class PointTableCalculator
{
    private const NO_RESULT_STATUSES = [
        'abandoned', 'no_result', 'no result', 'washed out', 'washout',
    ];

    private const CANCELLED_STATUSES = [
        'cancelled', 'canceled', 'postponed',
    ];

    private const COMPLETED_STATUSES = [
        'completed', 'complete', 'finished', 'result', 'result_verified',
    ];

    private const TIE_STATUSES = ['tie', 'tied'];

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
            ->whereHas('seasons', fn ($q) => $q->where('seasons.id', $seasonId))
            ->pluck('id')
            ->merge($fixtures->flatMap(fn ($f) => [$f->home_team_id, $f->away_team_id]))
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

        $noResultPoints = max(1, min(4, (int) config('pointtable.no_result_points', 1)));

        $standings = $teams->map(function (Team $team) use ($adjustments) {
            $penalty = (int) ($adjustments->get($team->id)?->penalty_points ?? 0);

            return [
                'team' => $team,
                // Canonical keys (used by both admin & public templates)
                'played' => 0,
                'won' => 0,
                'lost' => 0,
                'tied' => 0,
                'no_result' => 0,
                'base_points' => 0,
                'penalty' => $penalty,
                'points' => 0,
                // Legacy aliases
                'gp' => 0, 'w' => 0, 'l' => 0, 't' => 0, 'nr' => 0,
                'basePts' => 0,
                'pts' => 0,
                'penalty_points' => $penalty,
                // NRR data
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

            if (!$standings->has($homeId) || !$standings->has($awayId)) {
                continue;
            }

            $status = strtolower(trim((string) $fixture->status));
            $summary = strtolower(trim((string) $fixture->result_summary));

            if (in_array($status, self::CANCELLED_STATUSES, true)) {
                continue;
            }

            $isAbandoned = in_array($status, self::NO_RESULT_STATUSES, true)
                || str_contains($summary, 'no result')
                || str_contains($summary, 'washed out')
                || str_contains($summary, 'washout')
                || str_contains($summary, 'abandoned');

            $isCompleted = in_array($status, self::COMPLETED_STATUSES, true);

            // Abandoned / no result — both teams get points, but it's NOT a "played" match.
            if ($isAbandoned) {
                $home = $standings->get($homeId);
                $away = $standings->get($awayId);

                $this->recordNoResult($home, $noResultPoints);
                $this->recordNoResult($away, $noResultPoints);

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

            $explicitTie = in_array($status, self::TIE_STATUSES, true)
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
                $this->recordTie($home);
                $this->recordTie($away);
            } else {
                $standings->put($homeId, $home);
                $standings->put($awayId, $away);
                continue;
            }

            $completedMatches++;

            // NRR: use ICC all-out rule — if a team was bowled out,
            // their full quota (20 overs) counts. Otherwise, actual balls.
            $homeBalls = $this->nrrBalls(
                $homeRuns,
                (int) ($fixture->home_wickets ?? 0),
                $fixture->home_overs,
                (int) config('pointtable.overs_per_innings', 20)
            );

            $awayBalls = $this->nrrBalls(
                $awayRuns,
                (int) ($fixture->away_wickets ?? 0),
                $fixture->away_overs,
                (int) config('pointtable.overs_per_innings', 20)
            );

            if ($homeRuns !== null && $awayRuns !== null && $homeBalls > 0 && $awayBalls > 0) {
                $home['runs_for']    += (int) $homeRuns;
                $home['balls_faced'] += $homeBalls;
                $home['runs_against'] += (int) $awayRuns;
                $home['balls_bowled'] += $awayBalls;

                $away['runs_for']    += (int) $awayRuns;
                $away['balls_faced'] += $awayBalls;
                $away['runs_against'] += (int) $homeRuns;
                $away['balls_bowled'] += $homeBalls;
            }

            $standings->put($homeId, $home);
            $standings->put($awayId, $away);
        }

        // Finalize points + NRR
        $standings = $standings->map(function ($row) {
            $row['pts'] = $row['basePts'] - $row['penalty'];
            $row['points'] = $row['pts'];

            $row['nrr_available'] = $row['balls_faced'] > 0 && $row['balls_bowled'] > 0;

            if ($row['nrr_available']) {
                $for     = $row['runs_for']    / ($row['balls_faced']  / 6);
                $against = $row['runs_against'] / ($row['balls_bowled'] / 6);
                $row['nrr'] = round($for - $against, 3);
            }

            return $row;
        });

        // Sort: Points → Wins → NRR → Head-to-head → Alphabetical
        $standings = $standings->values();
        $sorted = $standings->sort(function ($a, $b) use ($fixtures) {
            if ($a['pts'] !== $b['pts']) return $b['pts'] <=> $a['pts'];
            if ($a['w']   !== $b['w'])   return $b['w']   <=> $a['w'];

            $aNrr = $a['nrr'] ?? -INF;
            $bNrr = $b['nrr'] ?? -INF;
            if ($aNrr !== $bNrr) return $bNrr <=> $aNrr;

            $h2h = $this->headToHeadWinner($a['team']->id, $b['team']->id, $fixtures);
            if ($h2h !== 0) return $h2h;

            return strcasecmp($a['team']->name, $b['team']->name);
        })->values();

        return [
            'fixtures' => $fixtures,
            'teams' => $teams,
            'adjustments' => $adjustments,
            'standings' => $sorted,
            'completedMatches' => $completedMatches,
            'upcomingMatches' => $upcomingMatches,
            'totalTeams' => $teams->count(),
            'fixtureCount' => $fixtures->count(),
            'noResultPoints' => $noResultPoints,
        ];
    }

    private function recordWin(array &$row): void
    {
        $row['played']++; $row['gp']++;
        $row['won']++;    $row['w']++;
        $row['basePts'] += 2;
    }

    private function recordLoss(array &$row): void
    {
        $row['played']++; $row['gp']++;
        $row['lost']++;   $row['l']++;
    }

    private function recordTie(array &$row): void
    {
        $row['played']++; $row['gp']++;
        $row['tied']++;   $row['t']++;
        $row['basePts'] += 1;
    }

    private function recordNoResult(array &$row, int $points): void
    {
        // NR does NOT increment 'played' or 'gp' — matches official tables.
        $row['nr']++;         $row['no_result']++;
        $row['basePts'] += $points;
    }

    /**
     * Return balls bowled for NRR purposes, applying the ICC all-out rule:
     * if a team was bowled out (10 wickets), the full overs quota counts.
     */
    private function nrrBalls(mixed $runs, int $wickets, mixed $overs, int $quota): int
    {
        if ($runs === null) {
            return 0;
        }
        if ($wickets >= 10) {
            return $quota * 6;
        }
        return $this->oversToBalls($overs);
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

    private function headToHeadWinner(int $teamA, int $teamB, Collection $fixtures): int
    {
        $aWins = 0;
        $bWins = 0;

        foreach ($fixtures as $f) {
            if (!$f->is_result_verified) continue;
            if (!in_array(strtolower(trim((string) $f->status)), self::COMPLETED_STATUSES, true)) continue;

            $winner = $f->winner_team_id ? (int) $f->winner_team_id : null;
            if (!$winner) continue;

            $home = (int) $f->home_team_id;
            $away = (int) $f->away_team_id;

            $involvesA = $home === $teamA || $away === $teamA;
            $involvesB = $home === $teamB || $away === $teamB;
            if (!$involvesA || !$involvesB) continue;

            if ($winner === $teamA) $aWins++;
            if ($winner === $teamB) $bWins++;
        }

        if ($aWins > $bWins) return -1;
        if ($bWins > $aWins) return 1;
        return 0;
    }
}