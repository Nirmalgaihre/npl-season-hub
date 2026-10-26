<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fixture;
use App\Models\Season;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class FixtureController extends Controller
{
    public function index(Request $request): View
    {
        $fixtures = Fixture::with(['season', 'homeTeam', 'awayTeam', 'winner'])
            ->when($request->filled('season_id'), fn ($q) =>
                $q->where('season_id', $request->integer('season_id'))
            )
            ->when($request->filled('status'), fn ($q) =>
                $q->where('status', $request->input('status'))
            )
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = trim($request->input('search'));

                $q->where(function ($query) use ($search) {
                    $query->where('venue', 'like', "%{$search}%")
                        ->orWhereHas('homeTeam', fn ($team) =>
                            $team->where('name', 'like', "%{$search}%")
                                ->orWhere('short_name', 'like', "%{$search}%")
                        )
                        ->orWhereHas('awayTeam', fn ($team) =>
                            $team->where('name', 'like', "%{$search}%")
                                ->orWhere('short_name', 'like', "%{$search}%")
                        );
                });
            })
            ->orderByRaw('scheduled_at IS NULL')
            ->orderBy('scheduled_at')
            ->orderBy('match_number')
            ->paginate(15)
            ->withQueryString();

        $seasons = Season::orderByDesc('season_number')->get();

        $stats = [
            'total' => Fixture::count(),
            'scheduled' => Fixture::where('status', 'scheduled')->count(),
            'live' => Fixture::where('status', 'live')->count(),
            'completed' => Fixture::where('status', 'completed')->count(),
        ];

        return view('admin.fixtures.index', compact(
            'fixtures',
            'seasons',
            'stats'
        ));
    }

    public function create(): View
    {
        return view('admin.fixtures.create', [
            'fixture' => new Fixture(),
            'seasons' => Season::orderByDesc('season_number')->get(),
            'teams' => Team::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);

        $this->validateFixtureTeams($data);

        $data = $this->prepareResultData($request, $data);

        Fixture::create($data);

        return redirect()
            ->route('admin.fixtures.index')
            ->with('success', 'Fixture created successfully.');
    }

    public function edit(Fixture $fixture): View
    {
        return view('admin.fixtures.edit', [
            'fixture' => $fixture->load(['season', 'homeTeam', 'awayTeam']),
            'seasons' => Season::orderByDesc('season_number')->get(),
            'teams' => Team::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Fixture $fixture): RedirectResponse
    {
        $data = $this->validatedData($request, $fixture);

        $this->validateFixtureTeams($data, $fixture);

        $data = $this->prepareResultData($request, $data, $fixture);

        $fixture->update($data);

        return redirect()
            ->route('admin.fixtures.index')
            ->with('success', 'Fixture updated successfully.');
    }

    public function destroy(Fixture $fixture): RedirectResponse
    {
        $fixture->delete();

        return redirect()
            ->route('admin.fixtures.index')
            ->with('success', 'Fixture deleted successfully.');
    }

    private function validatedData(Request $request, ?Fixture $fixture = null): array
    {
        return $request->validate([
            'season_id' => ['required', 'integer', 'exists:seasons,id'],
            'home_team_id' => ['required', 'integer', 'exists:teams,id', 'different:away_team_id'],
            'away_team_id' => ['required', 'integer', 'exists:teams,id'],
            'match_number' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('fixtures', 'match_number')
                    ->where(fn ($q) => $q->where('season_id', $request->input('season_id')))
                    ->ignore($fixture?->id),
            ],
            'match_type' => ['required', Rule::in(['league', 'qualifier', 'eliminator', 'final', 'other'])],
            'scheduled_at' => ['nullable', 'date'],
            'venue' => ['nullable', 'string', 'max:180'],
            'status' => ['required', Rule::in(['scheduled', 'live', 'completed', 'abandoned', 'postponed'])],

            'home_runs' => ['nullable', 'integer', 'min:0', 'max:999'],
            'home_wickets' => ['nullable', 'integer', 'min:0', 'max:10'],
            'home_overs' => ['nullable', 'numeric', 'min:0', 'max:50'],
            'away_runs' => ['nullable', 'integer', 'min:0', 'max:999'],
            'away_wickets' => ['nullable', 'integer', 'min:0', 'max:10'],
            'away_overs' => ['nullable', 'numeric', 'min:0', 'max:50'],

            'winner_team_id' => ['nullable', 'integer', 'exists:teams,id'],
            'player_of_match' => ['nullable', 'string', 'max:150'],
            'result_summary' => ['nullable', 'string', 'max:2000'],
            'is_result_verified' => ['nullable', 'boolean'],
        ]);
    }

    private function validateFixtureTeams(array $data, ?Fixture $fixture = null): void
    {
        $homeTeamId = (int) $data['home_team_id'];
        $awayTeamId = (int) $data['away_team_id'];

        if ($homeTeamId === $awayTeamId) {
            throw ValidationException::withMessages([
                'away_team_id' => 'A team cannot play against itself.',
            ]);
        }

        $season = Season::findOrFail($data['season_id']);

        $participatingTeamIds = $season->teams()->pluck('teams.id')->map(fn ($id) => (int) $id);

        if (!$participatingTeamIds->contains($homeTeamId)
            || !$participatingTeamIds->contains($awayTeamId)) {
            throw ValidationException::withMessages([
                'season_id' => 'Both teams must be assigned to the selected season in Team Management.',
            ]);
        }

        if (!empty($data['winner_team_id'])
            && !in_array((int) $data['winner_team_id'], [$homeTeamId, $awayTeamId], true)) {
            throw ValidationException::withMessages([
                'winner_team_id' => 'The winner must be one of the two teams in this fixture.',
            ]);
        }

        foreach (['home_overs', 'away_overs'] as $field) {
            if (isset($data[$field])) {
                $fraction = round(((float) $data[$field] - floor((float) $data[$field])) * 10);

                if ($fraction < 0 || $fraction > 5) {
                    throw ValidationException::withMessages([
                        $field => 'Cricket overs must use balls 0–5, for example 19.4.',
                    ]);
                }
            }
        }

        if (($data['status'] ?? '') === 'completed') {
            foreach (['home_runs', 'home_wickets', 'home_overs', 'away_runs', 'away_wickets', 'away_overs'] as $field) {
                if (!isset($data[$field])) {
                    throw ValidationException::withMessages([
                        $field => 'Enter both teams’ innings scores before marking a match completed.',
                    ]);
                }
            }
        }
    }

    private function prepareResultData(
        Request $request,
        array $data,
        ?Fixture $fixture = null
    ): array {
        $verified = $request->boolean('is_result_verified');

        if (($data['status'] ?? '') !== 'completed') {
            $verified = false;
            $data['winner_team_id'] = null;
            $data['is_result_verified'] = false;
            $data['result_verified_by'] = null;

            return $data;
        }

        if ($verified) {
            $data['is_result_verified'] = true;
            $data['result_verified_by'] = $request->user()->id;
        } else {
            $data['is_result_verified'] = false;
            $data['result_verified_by'] = null;
        }

        return $data;
    }
}