<?php

namespace App\Http\Controllers;

use App\Models\Player;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PlayerProfileController extends Controller
{
    public function show(Player $player)
    {
        $team = $player->team;

        // -----------------------------------------------------------------
        // Discover the real scorecard tables in this project.
        // Adjust these two names once you confirm them.
        // -----------------------------------------------------------------
        $battingTable = $this->findTable([
            'manual_scorecard_batting',
            'manual_batting_scorecards',
            'manual_scorecard_batsmen',
            'scorecard_batting',
            'batting_scorecards',
        ]);

        $bowlingTable = $this->findTable([
            'manual_scorecard_bowling',
            'manual_bowling_scorecards',
            'manual_scorecard_bowlers',
            'scorecard_bowling',
            'bowling_scorecards',
        ]);

        // Batting rows
        $battingRows = $battingTable
            ? DB::table($battingTable)->where('player_id', $player->id)->orderByDesc('id')->get()
            : collect();

        // Bowling rows
        $bowlingRows = $bowlingTable
            ? DB::table($bowlingTable)->where('player_id', $player->id)->orderByDesc('id')->get()
            : collect();

        // -----------------------------------------------------------------
        // Batting aggregates (safe against missing columns)
        // -----------------------------------------------------------------
        $totalInnings = $battingRows->count();
        $totalRuns    = (int) $battingRows->sum(fn ($r) => (int) ($r->runs ?? 0));
        $totalBalls   = (int) $battingRows->sum(fn ($r) => (int) ($r->balls ?? 0));
        $totalFours   = (int) $battingRows->sum(fn ($r) => (int) ($r->fours ?? 0));
        $totalSixes   = (int) $battingRows->sum(fn ($r) => (int) ($r->sixes ?? 0));
        $notOuts      = $battingRows->filter(fn ($r) => empty($r->is_out))->count();
        $dismissals   = $totalInnings - $notOuts;
        $highestScore = (int) ($battingRows->max(fn ($r) => (int) ($r->runs ?? 0)) ?? 0);
        $battingAvg   = $dismissals > 0 ? round($totalRuns / $dismissals, 2) : null;
        $strikeRate   = $totalBalls > 0 ? round(($totalRuns / $totalBalls) * 100, 2) : null;

        // -----------------------------------------------------------------
        // Bowling aggregates (safe against missing columns)
        // -----------------------------------------------------------------
        $totalBowlInnings  = $bowlingRows->count();
        $totalWickets      = (int) $bowlingRows->sum(fn ($r) => (int) ($r->wickets ?? 0));
        $totalOvers        = (float) $bowlingRows->sum(fn ($r) => (float) ($r->overs ?? 0));
        $totalRunsConceded = (int) $bowlingRows->sum(fn ($r) => (int) ($r->runs_conceded ?? 0));
        $bestFigures       = $bowlingRows
            ->sortByDesc(fn ($r) => ((int) ($r->wickets ?? 0)) * 1000 - ((int) ($r->runs_conceded ?? 0)))
            ->first();
        $economy           = $totalOvers > 0 ? round($totalRunsConceded / $totalOvers, 2) : null;

        return view('players.show', compact(
            'player',
            'team',
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

    /**
     * Return the first table name that actually exists in the DB.
     */
    protected function findTable(array $candidates): ?string
    {
        foreach ($candidates as $name) {
            if (Schema::hasTable($name)) {
                return $name;
            }
        }

        return null;
    }
}