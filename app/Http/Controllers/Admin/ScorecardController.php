<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fixture;
use App\Models\ManualScorecard;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ScorecardController extends Controller
{
    public function index(): View
    {
        $fixtures = Fixture::with(['season', 'homeTeam', 'awayTeam'])
            ->orderByDesc('scheduled_at')
            ->orderByDesc('match_number')
            ->paginate(15);

        $savedFixtures = ManualScorecard::query()
            ->select('fixture_id')
            ->distinct()
            ->pluck('fixture_id')
            ->all();

        return view('admin.scorecards.index', compact(
            'fixtures',
            'savedFixtures'
        ));
    }

    public function edit(Fixture $fixture): View
    {
        $fixture->load(['season', 'homeTeam', 'awayTeam']);

        $teams = Team::whereIn('id', [
            $fixture->home_team_id,
            $fixture->away_team_id,
        ])->orderBy('name')->get();

        $saved = ManualScorecard::where('fixture_id', $fixture->id)
            ->get()
            ->keyBy('innings_number');

        $innings = [];

        foreach ([1, 2] as $number) {
            $innings[$number] = $saved->get($number);
        }

        return view('admin.scorecards.edit', compact(
            'fixture',
            'teams',
            'innings'
        ));
    }

    public function update(
        Request $request,
        Fixture $fixture
    ): RedirectResponse {
        $data = $request->validate([
            'innings' => ['required', 'array'],
            'innings.1' => ['required', 'array'],
            'innings.2' => ['required', 'array'],

            'innings.*.batting_team_id' => [
                'required',
                'integer',
                'in:' . $fixture->home_team_id . ',' . $fixture->away_team_id,
            ],

            'innings.*.total_runs' => [
                'required', 'integer', 'min:0', 'max:999',
            ],
            'innings.*.wickets' => [
                'required', 'integer', 'min:0', 'max:10',
            ],
            'innings.*.overs' => [
                'required', 'numeric', 'min:0', 'max:100',
            ],
            'innings.*.extras' => [
                'required', 'integer', 'min:0', 'max:999',
            ],

            'innings.*.batters' => ['nullable', 'array', 'max:11'],
            'innings.*.batters.*.name' => ['nullable', 'string', 'max:150'],
            'innings.*.batters.*.dismissal' => ['nullable', 'string', 'max:150'],
            'innings.*.batters.*.runs' => ['nullable', 'integer', 'min:0', 'max:999'],
            'innings.*.batters.*.balls' => ['nullable', 'integer', 'min:0', 'max:999'],
            'innings.*.batters.*.fours' => ['nullable', 'integer', 'min:0', 'max:99'],
            'innings.*.batters.*.sixes' => ['nullable', 'integer', 'min:0', 'max:99'],

            'innings.*.bowlers' => ['nullable', 'array', 'max:11'],
            'innings.*.bowlers.*.name' => ['nullable', 'string', 'max:150'],
            'innings.*.bowlers.*.overs' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'innings.*.bowlers.*.maidens' => ['nullable', 'integer', 'min:0', 'max:99'],
            'innings.*.bowlers.*.runs' => ['nullable', 'integer', 'min:0', 'max:999'],
            'innings.*.bowlers.*.wickets' => ['nullable', 'integer', 'min:0', 'max:10'],
        ]);

        DB::transaction(function () use ($data, $fixture) {
            foreach ([1, 2] as $number) {
                $row = $data['innings'][$number];

                $battingTeamId = (int) $row['batting_team_id'];
                $otherTeamId = $battingTeamId === (int) $fixture->home_team_id
                    ? (int) $fixture->away_team_id
                    : (int) $fixture->home_team_id;

                $batters = collect($row['batters'] ?? [])
                    ->filter(fn ($b) => trim($b['name'] ?? '') !== '')
                    ->map(fn ($b) => [
                        'name' => trim($b['name']),
                        'dismissal' => trim($b['dismissal'] ?? '') ?: 'not out',
                        'runs' => (int) ($b['runs'] ?? 0),
                        'balls' => (int) ($b['balls'] ?? 0),
                        'fours' => (int) ($b['fours'] ?? 0),
                        'sixes' => (int) ($b['sixes'] ?? 0),
                    ])->values()->all();

                $bowlers = collect($row['bowlers'] ?? [])
                    ->filter(fn ($b) => trim($b['name'] ?? '') !== '')
                    ->map(fn ($b) => [
                        'name' => trim($b['name']),
                        'overs' => (float) ($b['overs'] ?? 0),
                        'maidens' => (int) ($b['maidens'] ?? 0),
                        'runs' => (int) ($b['runs'] ?? 0),
                        'wickets' => (int) ($b['wickets'] ?? 0),
                    ])->values()->all();

                ManualScorecard::updateOrCreate(
                    [
                        'fixture_id' => $fixture->id,
                        'innings_number' => $number,
                    ],
                    [
                        'batting_team_id' => $battingTeamId,
                        'bowling_team_id' => $otherTeamId,
                        'total_runs' => (int) $row['total_runs'],
                        'wickets' => (int) $row['wickets'],
                        'overs' => (float) $row['overs'],
                        'extras' => (int) $row['extras'],
                        'batters' => $batters,
                        'bowlers' => $bowlers,
                    ]
                );
            }
        });

        return redirect()
            ->route('admin.scorecards.edit', $fixture)
            ->with('success', 'Manual scorecard saved successfully.');
    }
}
