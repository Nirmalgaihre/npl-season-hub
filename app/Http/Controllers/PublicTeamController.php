<?php

namespace App\Http\Controllers;

use App\Models\Fixture;
use App\Models\Team;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class PublicTeamController extends Controller
{
    /**
     * Display the public teams listing.
     */
    public function index(): View
    {
        $teams = Team::query()
            ->withCount('seasons')
            ->where('is_active', true)
            ->orderBy('name')
            ->paginate(12);

        return view('teams.index', compact('teams'));
    }

    /**
     * Display a team profile and its fixtures.
     */
    public function show(Team $team): View
    {
        $team->load([
            'seasons' => fn ($query) => $query
                ->orderByDesc('season_number'),
        ]);

        $seasonIds = $team->seasons->pluck('id');

        $fixtures = Fixture::query()
            ->with([
                'season',
                'homeTeam',
                'awayTeam',
                'winner',
            ])
            ->whereIn('season_id', $seasonIds)
            ->where(function (Builder $query) use ($team) {
                $query->where('home_team_id', $team->id)
                    ->orWhere('away_team_id', $team->id);
            })
            ->orderByRaw("
                CASE
                    WHEN status = 'live' THEN 0
                    WHEN status = 'scheduled' THEN 1
                    WHEN status = 'completed' THEN 2
                    ELSE 3
                END
            ")
            ->orderByRaw('scheduled_at IS NULL')
            ->orderBy('scheduled_at')
            ->orderBy('match_number')
            ->get();

        return view('teams.show', compact('team', 'fixtures'));
    }
}