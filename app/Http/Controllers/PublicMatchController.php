<?php

namespace App\Http\Controllers;

use App\Models\Fixture;
use App\Models\Season;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

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
     * Display an individual match.
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
        in_array($fixture->season->status, ['upcoming', 'active', 'completed'], true),
        404
    );

    if (! $fixture->is_result_verified) {
        foreach ([
            'home_runs',
            'home_wickets',
            'home_overs',
            'away_runs',
            'away_wickets',
            'away_overs',
            'winner_team_id',
            'player_of_match',
            'result_summary',
        ] as $attribute) {
            $fixture->setAttribute($attribute, null);
        }
    }

    $innings = collect();

    // Live score details are public during a live match.
    // Completed-match details require result verification.
    $canShowLiveDetails = $fixture->status === 'live'
        || (bool) $fixture->is_result_verified;

    if ($canShowLiveDetails) {
        $innings = DB::table('live_innings')
            ->where('fixture_id', $fixture->id)
            ->orderBy('innings_number')
            ->get();

        if ($innings->isNotEmpty()) {
            $deliveriesByInnings = DB::table('live_deliveries')
                ->whereIn('innings_id', $innings->pluck('id'))
                ->orderBy('id')
                ->get()
                ->groupBy('innings_id');

            foreach ($innings as $inning) {
                $deliveries = $deliveriesByInnings->get(
                    $inning->id,
                    collect()
                );

                // ball_number is the zero-based over number.
                $inning->overs = $deliveries
                    ->groupBy('ball_number')
                    ->map(function ($balls, $overNumber) {
                        $legalBallsInOver = 0;

                        $balls = $balls->map(function ($ball) use (
                            &$legalBallsInOver,
                            $overNumber
                        ) {
                            $ball->over_number = (int) $overNumber;

                            // Illegal deliveries do not advance the
                            // legal-ball count.
                            $ball->display_ball = ((int) $overNumber)
                                . '.'
                                . min($legalBallsInOver + 1, 6);

                            $ball->total_delivery_runs =
                                (int) ($ball->batter_runs ?? 0)
                                + (int) ($ball->extra_runs ?? 0);

                            if ((bool) $ball->counts_as_legal_ball) {
                                $legalBallsInOver++;
                            }

                            return $ball;
                        });

                        return (object) [
                            'number' => (int) $overNumber,
                            'balls' => $balls,
                            'runs' => $balls->sum('total_delivery_runs'),
                            'wickets' => $balls->filter(
                                fn ($ball) => (bool) $ball->is_wicket
                            )->count(),
                        ];
                    })
                    ->sortKeys();

                $inning->deliveries = $deliveries;
            }
        }
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
