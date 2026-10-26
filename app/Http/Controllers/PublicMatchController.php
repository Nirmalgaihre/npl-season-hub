<?php

namespace App\Http\Controllers;

use App\Models\Fixture;
use App\Models\Season;
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
     * Display an individual match and its innings.
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
            $fixture->season &&
            in_array(
                $fixture->season->status,
                ['upcoming', 'active', 'completed'],
                true
            ),
            404
        );

        /*
         * Only expose score details after verification.
         * Live innings data is handled separately below.
         */
        if (! $fixture->is_result_verified) {
            $fixture->setAttribute('home_runs', null);
            $fixture->setAttribute('home_wickets', null);
            $fixture->setAttribute('home_overs', null);

            $fixture->setAttribute('away_runs', null);
            $fixture->setAttribute('away_wickets', null);
            $fixture->setAttribute('away_overs', null);

            $fixture->setAttribute('winner_team_id', null);
            $fixture->setAttribute('player_of_match', null);
            $fixture->setAttribute('result_summary', null);
        }

        /*
         * Always define $innings so the Blade view won't throw
         * "Undefined variable $innings".
         */
        $innings = DB::table('live_innings')
            ->where('fixture_id', $fixture->id)
            ->orderBy('innings_number')
            ->get();

        /*
         * Do not expose unverified innings scores or deliveries.
         */
        $canShowLiveDetails = $fixture->status === 'live'
            || $fixture->is_result_verified;

        if ($canShowLiveDetails && $innings->isNotEmpty()) {
            $deliveriesByInnings = DB::table('live_deliveries')
                ->whereIn('innings_id', $innings->pluck('id'))
                ->orderBy('id')
                ->get()
                ->groupBy('innings_id');

            foreach ($innings as $inning) {
                $inning->deliveries = $deliveriesByInnings->get(
                    $inning->id,
                    collect()
                );
            }
        } else {
            $innings = collect();
        }

        $currentInning = $innings->first(
            fn ($inning) => (bool) $inning->is_current
        );

        return view('fixtures.show', [
            'fixture' => $fixture,
            'innings' => $innings,
            'currentInning' => $currentInning,
        ]);
    }
}
