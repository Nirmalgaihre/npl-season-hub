<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fixture;
use App\Models\ManualScorecard;
use App\Models\Season;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MatchController extends Controller
{
    /**
     * Display and filter fixtures.
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
        $manualScorecards = collect();

        return view('admin.fixtures.create', compact(
            'seasons',
            'teams',
            'fixture',
            'manualScorecards'
        ));
    }

    /**
     * Create a fixture.
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
     * Show the edit form with manual scorecards.
     */
    public function edit(Fixture $fixture): View
    {
        $seasons = Season::query()
            ->orderByDesc('season_number')
            ->get();

        $teams = Team::query()
            ->where(function ($query) use ($fixture) {
                $query->where('is_active', true)
                    ->orWhereIn('id', [
                        $fixture->home_team_id,
                        $fixture->away_team_id,
                    ]);
            })
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
     * Update fixture information and recalculate its result.
     */
    public function update(
        Request $request,
        Fixture $fixture
    ): RedirectResponse {
        $data = $this->validateFixture($request, $fixture);

        $this->validateFixtureTeams($data);

        $data = $this->prepareResultData(
            $data,
            $request,
            $fixture
        );

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
     * Validate fixture input.
     */
    private function validateFixture(
        Request $request,
        ?Fixture $fixture = null
    ): array {
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
                    ->ignore($fixture?->id),
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
     * Validate team selection.
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
            if (! in_array((int) $data['winner_team_id'], [
                (int) $data['home_team_id'],
                (int) $data['away_team_id'],
            ], true)) {
                abort(422, 'The winner must be one of the playing teams.');
            }
        }

        if (($data['status'] ?? '') === 'completed') {
            foreach ([
                'home_runs',
                'home_wickets',
                'home_overs',
                'away_runs',
                'away_wickets',
                'away_overs',
            ] as $field) {
                if (
                    ! array_key_exists($field, $data) ||
                    $data[$field] === null ||
                    $data[$field] === ''
                ) {
                    abort(
                        422,
                        'Enter both innings scores and overs before completing the match.'
                    );
                }
            }
        }
    }

    /**
     * Prepare result fields.
     *
     * Manual scorecards are authoritative when both innings exist.
     * A result is not verified automatically just because the match
     * has been marked completed.
     */
    private function prepareResultData(
        array $data,
        Request $request,
        ?Fixture $fixture = null
    ): array {
        $data['is_result_verified'] = false;
        $data['result_verified_by'] = null;

        if ($data['status'] !== 'completed') {
            $data['winner_team_id'] = null;
            $data['player_of_match'] = null;
            $data['result_summary'] = null;

            return $data;
        }

        $scorecards = $fixture
            ? ManualScorecard::query()
                ->where('fixture_id', $fixture->id)
                ->orderBy('innings_number')
                ->get()
            : collect();

        $result = $this->calculateManualScorecardResult(
            $scorecards,
            (int) $data['home_team_id'],
            (int) $data['away_team_id']
        );

        if ($result === null) {
            // Keep entered scores, but do not announce an unconfirmed winner.
            $data['winner_team_id'] = null;
            $data['result_summary'] =
                'Final result pending valid manual scorecards.';

            return $data;
        }

        $this->applyCalculatedResult($data, $result);

        // Verification must be an explicit action by an authorized admin.
        return $data;
    }

    /**
     * Calculate a result from the two teams' saved manual scorecards.
     */
    private function calculateManualScorecardResult(
        $scorecards,
        int $homeTeamId,
        int $awayTeamId
    ): ?array {
        $teamInnings = $scorecards
            ->filter(fn ($card) => in_array(
                (int) $card->batting_team_id,
                [$homeTeamId, $awayTeamId],
                true
            ))
            ->sortBy('innings_number')
            ->values();

        // Exactly one innings for each participating team is expected.
        if ($teamInnings->count() !== 2) {
            return null;
        }

        $first = $teamInnings[0];
        $second = $teamInnings[1];

        if (
            (int) $first->batting_team_id ===
            (int) $second->batting_team_id
        ) {
            return null;
        }

        if (
            (int) $first->innings_number >=
            (int) $second->innings_number
        ) {
            return null;
        }

        foreach ([$first, $second] as $innings) {
            if (
                $innings->total_runs === null ||
                $innings->wickets === null ||
                $innings->overs === null
            ) {
                return null;
            }

            if (
                (int) $innings->total_runs < 0 ||
                (int) $innings->wickets < 0 ||
                (int) $innings->wickets > 10 ||
                $this->oversToBalls($innings->overs) === null
            ) {
                return null;
            }
        }

        $firstTeamId = (int) $first->batting_team_id;
        $secondTeamId = (int) $second->batting_team_id;

        $firstRuns = (int) $first->total_runs;
        $secondRuns = (int) $second->total_runs;

        $firstTeam = Team::find($firstTeamId);
        $secondTeam = Team::find($secondTeamId);

        if (! $firstTeam || ! $secondTeam) {
            return null;
        }

        $winnerId = null;

        if ($firstRuns > $secondRuns) {
            $winnerId = $firstTeamId;

            $summary = $firstTeam->name . ' won by '
                . ($firstRuns - $secondRuns) . ' runs.';
        } elseif ($secondRuns > $firstRuns) {
            $winnerId = $secondTeamId;

            $wicketsRemaining = 10 - (int) $second->wickets;

            if ($wicketsRemaining > 0) {
                $summary = $secondTeam->name . ' won by '
                    . $wicketsRemaining . ' wickets.';
            } else {
                $summary = $secondTeam->name
                    . ' won after reaching the target with all wickets lost.';
            }
        } else {
            $summary = 'Match tied.';
        }

        return [
            'home_runs' => (int) (
                $firstTeamId === $homeTeamId
                    ? $first->total_runs
                    : $second->total_runs
            ),

            'home_wickets' => (int) (
                $firstTeamId === $homeTeamId
                    ? $first->wickets
                    : $second->wickets
            ),

            'home_overs' => (
                $firstTeamId === $homeTeamId
                    ? $first->overs
                    : $second->overs
            ),

            'away_runs' => (int) (
                $firstTeamId === $awayTeamId
                    ? $first->total_runs
                    : $second->total_runs
            ),

            'away_wickets' => (int) (
                $firstTeamId === $awayTeamId
                    ? $first->wickets
                    : $second->wickets
            ),

            'away_overs' => (
                $firstTeamId === $awayTeamId
                    ? $first->overs
                    : $second->overs
            ),

            'winner_team_id' => $winnerId,
            'result_summary' => $summary,
        ];
    }

    /**
     * Copy calculated values into the fixture data array.
     */
    private function applyCalculatedResult(
        array &$data,
        array $result
    ): void {
        $data['home_runs'] = $result['home_runs'];
        $data['home_wickets'] = $result['home_wickets'];
        $data['home_overs'] = $result['home_overs'];

        $data['away_runs'] = $result['away_runs'];
        $data['away_wickets'] = $result['away_wickets'];
        $data['away_overs'] = $result['away_overs'];

        $data['winner_team_id'] = $result['winner_team_id'];
        $data['result_summary'] = $result['result_summary'];
    }

    /**
     * Convert cricket overs into legal balls.
     *
     * Example: 19.4 means 118 balls, not 19.4 decimal overs.
     */
    private function oversToBalls($overs): ?int
    {
        $value = trim((string) $overs);

        if (! preg_match('/^(\d{1,2})(?:\.([0-5]))?$/', $value, $matches)) {
            return null;
        }

        return ((int) $matches[1] * 6)
            + (isset($matches[2]) ? (int) $matches[2] : 0);
    }

    /**
     * Verify the official result using the saved scorecards.
     */
    public function verify(Fixture $fixture): RedirectResponse
    {
        if ($fixture->is_result_verified) {
            return back()->with(
                'success',
                'This result is already verified.'
            );
        }

        if ($fixture->status !== 'completed') {
            return back()->with(
                'error',
                'Only completed matches can have their results verified.'
            );
        }

        $result = $this->calculateManualScorecardResult(
            ManualScorecard::query()
                ->where('fixture_id', $fixture->id)
                ->orderBy('innings_number')
                ->get(),
            (int) $fixture->home_team_id,
            (int) $fixture->away_team_id
        );

        if ($result === null) {
            return back()->with(
                'error',
                'Cannot verify this result. Both teams must have valid manual scorecards.'
            );
        }

        DB::transaction(function () use ($fixture, $result) {
            $data = [];

            $this->applyCalculatedResult($data, $result);

            $data['is_result_verified'] = true;
            $data['result_verified_by'] = request()->user()?->id;

            $fixture->update($data);
        });

        return back()->with(
            'success',
            'Official result calculated and verified successfully.'
        );
    }
}
