<?php

namespace App\Http\Controllers;

use App\Models\Fixture;
use App\Models\Season;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicFixtureController extends Controller
{
    public function index(Request $request): View
    {
        $seasons = Season::whereIn('status', ['active', 'completed', 'upcoming'])
            ->orderByDesc('season_number')
            ->get();

        $fixtures = Fixture::with(['season', 'homeTeam', 'awayTeam', 'winner'])
            ->whereHas('season', fn ($q) =>
                $q->whereIn('status', ['active', 'completed'])
            )
            ->when($request->filled('season'), fn ($q) =>
                $q->where('season_id', $request->integer('season'))
            )
            ->when($request->input('status') === 'upcoming', fn ($q) =>
                $q->whereIn('status', ['scheduled', 'postponed'])
            )
            ->when($request->input('status') === 'live', fn ($q) =>
                $q->where('status', 'live')
            )
            ->when($request->input('status') === 'completed', fn ($q) =>
                $q->where('status', 'completed')
                    ->where('is_result_verified', true)
            )
            ->orderByRaw("CASE WHEN status = 'live' THEN 0 WHEN status = 'scheduled' THEN 1 ELSE 2 END")
            ->orderBy('scheduled_at')
            ->orderBy('match_number')
            ->paginate(12)
            ->withQueryString();

        return view('fixtures.index', compact('fixtures', 'seasons'));
    }

    public function show(Fixture $fixture): View
{
    // Safe season check – prevents "Trying to get property of null"
    abort_unless(
        $fixture->season && in_array($fixture->season->status, ['active', 'completed'], true),
        404
    );

    $fixture->load(['season', 'homeTeam', 'awayTeam', 'winner']);

    // Hide unverified scores on the public site
    if (!$fixture->is_result_verified) {
        $fixture->setAttribute('home_runs', null);
        $fixture->setAttribute('home_wickets', null);
        $fixture->setAttribute('home_overs', null);
        $fixture->setAttribute('away_runs', null);
        $fixture->setAttribute('away_wickets', null);
        $fixture->setAttribute('away_overs', null);
        $fixture->setAttribute('winner_team_id', null);
        $fixture->setAttribute('player_of_match', null);
        $fixture->setAttribute('result_summary', null);
        $fixture->setRelation('winner', null);
    }

    // ---------------------------------------------------------------
    // Load innings data
    // Prefer live_innings (for live matches), fall back to manual_scorecards
    // ---------------------------------------------------------------
    $innings = collect();

    $liveInnings = \Illuminate\Support\Facades\DB::table('live_innings')
        ->where('fixture_id', $fixture->id)
        ->orderBy('innings_number')
        ->get();

    if ($liveInnings->isNotEmpty()) {
        $innings = $liveInnings;
    } else {
        // Completed matches store scores here
        $innings = \App\Models\ManualScorecard::query()
            ->where('fixture_id', $fixture->id)
            ->orderBy('innings_number')
            ->get();
    }

    // Detailed player scorecards (the partial expects this)
    // Leave empty for now – the view already handles the “unavailable” case
    $scorecards = collect();

    return view('fixtures.show', compact('fixture', 'innings', 'scorecards'));
}
}