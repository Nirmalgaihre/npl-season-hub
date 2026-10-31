<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fixture;
use App\Models\ManualScorecard;
use App\Models\Season;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MatchController extends Controller
{
    /**
     * Display and filter fixtures in the admin panel.
     */
    public function index(Request $request): View
    {
        $fixtures = Fixture::query()
            ->with(['season', 'homeTeam', 'awayTeam', 'winner'])
            ->when($request->filled('season'), function ($query) use ($request) {
                $query->where('season_id', $request->integer('season'));
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->input('status'));
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->input('search'));

                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('venue', 'like', "%{$search}%")
                        ->orWhere('match_number', 'like', "%{$search}%")
                        ->orWhereHas('homeTeam', function ($teamQuery) use ($search) {
                            $teamQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('short_name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('awayTeam', function ($teamQuery) use ($search) {
                            $teamQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('short_name', 'like', "%{$search}%");
                        });
                });
            })
            ->orderByDesc('scheduled_at')
            ->orderByDesc('match_number')
            ->paginate(15)
            ->withQueryString();

        $seasons = Season::query()
            ->orderByDesc('season_number')
            ->get();

        $stats = [
            'total' => Fixture::count(),
            'scheduled' => Fixture::where('status', 'scheduled')->count(),
            'live' => Fixture::where('status', 'live')->count(),
            'completed' => Fixture::where('status', 'completed')->count(),
            'unverified' => Fixture::where('status', 'completed')
                ->where('is_result_verified', false)
                ->count(),
        ];

        return view('admin.fixtures.index', compact(
            'fixtures',
            'seasons',
            'stats'
        ));
    }

    /**
     * Show the create form.
     */
    public function create(): View
    {
        $seasons = Season::query()
            ->orderByDesc('season_number')
            ->get();

        $teams = Team::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $fixture = new Fixture();

        // A new fixture has no saved manual scorecards yet.
        $manualScorecards = collect();

        return view('admin.fixtures.create', compact(
            'seasons',
            'teams',
            'fixture',
            'manualScorecards'
        ));
    }

    /**
     * Save a new fixture.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateFixture($request);

        $this->validateFixtureTeams($data);

        $data = $this->prepareResultData($data, $request);

        Fixture::create($data);

        return redirect()
            ->route('admin.fixtures.index')
            ->with('success', 'Match fixture created successfully.');
    }

    /**
     * Show the edit form with manual scoreboard data.
     */
    public function edit(Fixture $fixture): View
    {
        $seasons = Season::query()
            ->orderByDesc('season_number')
            ->get();

        $teams = Team::query()
            ->where('is_active', true)
            ->orWhereIn('id', [
                $fixture->home_team_id,
                $fixture->away_team_id,
            ])
            ->orderBy('name')
            ->get();

        $manualScorecards = ManualScorecard::query()
            ->where('fixture_id', $fixture->id)
            ->orderBy('innings_number')
            ->get();

        return view('admin.fixtures.edit', compact(
            'fixture',
            'seasons',
            'teams',
            'manualScorecards'
        ));
    }

    /**
     * Update a fixture or its result.
     */
    public function update(
        Request $request,
        Fixture $fixture
    ): RedirectResponse {
        $data = $this->validateFixture($request, $fixture);

        $this->validateFixtureTeams($data);

        $data = $this->prepareResultData($data, $request);

        $fixture->update($data);

        return redirect()
            ->route('admin.fixtures.index')
            ->with('success', 'Match fixture updated successfully.');
    }

    /**
     * Delete a fixture.
     */
    public function destroy(Fixture $fixture): RedirectResponse
    {
        $fixture->delete();

        return redirect()
            ->route('admin.fixtures.index')
            ->with('success', 'Match fixture deleted successfully.');
    }

    /**
     * Validate submitted fixture fields.
     */
    private function validateFixture(
        Request $request,
        ?Fixture $fixture = null
    ): array {
        $fixtureId = $fixture?->id;

        return $request->validate([
            'season_id' => [
                'required',
                'integer',
                'exists:seasons,id',
            ],

            'match_number' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('fixtures', 'match_number')
                    ->where(fn ($query) => $query->where(
                        'season_id',
                        $request->input('season_id')
                    ))
                    ->ignore($fixtureId),
            ],

            'home_team_id' => [
                'required',
                'integer',
                'exists:teams,id',
                'different:away_team_id',
            ],

            'away_team_id' => [
                'required',
                'integer',
                'exists:teams,id',
                'different:home_team_id',
            ],

            'match_type' => [
                'required',
                Rule::in([
                    'league',
                    'qualifier',
                    'eliminator',
                    'final',
                    'other',
                ]),
            ],

            'scheduled_at' => [
                'nullable',
                'date',
            ],

            'venue' => [
                'nullable',
                'string',
                'max:255',
            ],

            'status' => [
                'required',
                Rule::in([
                    'scheduled',
                    'live',
                    'completed',
                    'abandoned',
                    'postponed',
                ]),
            ],

            'home_runs' => [
                'nullable',
                'integer',
                'min:0',
                'max:999',
            ],

            'home_wickets' => [
                'nullable',
                'integer',
                'min:0',
                'max:10',
            ],

            'home_overs' => [
                'nullable',
                'regex:/^\d{1,2}(\.[0-5])?$/',
            ],

            'away_runs' => [
                'nullable',
                'integer',
                'min:0',
                'max:999',
            ],

            'away_wickets' => [
                'nullable',
                'integer',
                'min:0',
                'max:10',
            ],

            'away_overs' => [
                'nullable',
                'regex:/^\d{1,2}(\.[0-5])?$/',
            ],

            'winner_team_id' => [
                'nullable',
                'integer',
                'exists:teams,id',
            ],

            'player_of_match' => [
                'nullable',
                'string',
                'max:255',
            ],

            'result_summary' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'is_result_verified' => [
                'nullable',
                'boolean',
            ],
        ]);
    }

    /**
     * Ensure selected teams belong to the selected season.
     */
    private function validateFixtureTeams(array $data): void
    {
        if ((int) $data['home_team_id'] === (int) $data['away_team_id']) {
            abort(422, 'Home and away teams must be different.');
        }

        $season = Season::findOrFail($data['season_id']);

        $seasonTeamIds = $season->teams()
            ->pluck('teams.id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if (! in_array((int) $data['home_team_id'], $seasonTeamIds, true)) {
            abort(422, 'The home team is not assigned to this season.');
        }

        if (! in_array((int) $data['away_team_id'], $seasonTeamIds, true)) {
            abort(422, 'The away team is not assigned to this season.');
        }

        if (! empty($data['winner_team_id'])) {
            $winnerId = (int) $data['winner_team_id'];

            if (! in_array($winnerId, [
                (int) $data['home_team_id'],
                (int) $data['away_team_id'],
            ], true)) {
                abort(422, 'The winner must be one of the two playing teams.');
            }
        }

        if (($data['status'] ?? '') === 'completed') {
            $requiredScoreFields = [
                'home_runs',
                'home_wickets',
                'home_overs',
                'away_runs',
                'away_wickets',
                'away_overs',
            ];

            foreach ($requiredScoreFields as $field) {
                if (
                    ! array_key_exists($field, $data) ||
                    $data[$field] === null ||
                    $data[$field] === ''
                ) {
                    abort(
                        422,
                        'Enter both teams’ innings scores and overs before completing the match.'
                    );
                }
            }
        }
    }
    public function verify(\App\Models\Fixture $fixture)
{
    if ($fixture->is_result_verified) {
        return back()->with('success', 'This result is already verified.');
    }

    if ($fixture->status !== 'completed') {
        return back()->with(
            'error',
            'Only completed matches can have their results verified.'
        );
    }

    $scorecards = \App\Models\ManualScorecard::query()
        ->where('fixture_id', $fixture->id)
        ->get();

    $homeInnings = $scorecards->first(
        fn ($scorecard) =>
            (int) $scorecard->batting_team_id === (int) $fixture->home_team_id
    );

    $awayInnings = $scorecards->first(
        fn ($scorecard) =>
            (int) $scorecard->batting_team_id === (int) $fixture->away_team_id
    );

    if (!$homeInnings || !$awayInnings) {
        return back()->with(
            'error',
            'Cannot verify this result. Both teams must have a saved manual scorecard.'
        );
    }

    $fixture->is_result_verified = true;
    $fixture->save();

    return back()->with(
        'success',
        'Match result verified successfully.'
    );
}

    /**
     * Prepare result verification and audit fields.
     */
    private function prepareResultData(
        array $data,
        Request $request
    ): array {
        $data['is_result_verified'] = false;
        $data['result_verified_by'] = null;

        if ($data['status'] !== 'completed') {
            $data['winner_team_id'] = null;
            $data['player_of_match'] = null;
            $data['result_summary'] = null;

            return $data;
        }

        $verified = $request->boolean('is_result_verified');

        $data['is_result_verified'] = $verified;
        $data['result_verified_by'] = $verified
            ? $request->user()?->id
            : null;

        return $data;
    }
}
