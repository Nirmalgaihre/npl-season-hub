<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ScorecardController extends Controller
{
    public function edit($fixtureId)
    {
        $fixture = DB::table('fixtures')
            ->where('id', $fixtureId)
            ->first();

        abort_if(!$fixture, 404, 'Fixture not found.');

        $teamIds = [
            (int) $fixture->home_team_id,
            (int) $fixture->away_team_id,
        ];

        $teams = DB::table('teams')
            ->whereIn('id', $teamIds)
            ->orderBy('name')
            ->get();

        $players = DB::table('players')
            ->whereIn('team_id', $teamIds)
            ->where('is_active', 1)
            ->orderBy('name')
            ->get([
                'id',
                'team_id',
                'name',
                'role',
            ]);

        $scorecards = DB::table('manual_scorecards')
            ->where('fixture_id', $fixtureId)
            ->orderBy('innings_number')
            ->get()
            ->map(function ($scorecard) {
                foreach (['batters', 'bowlers'] as $field) {
                    $decoded = is_array($scorecard->{$field})
                        ? $scorecard->{$field}
                        : json_decode($scorecard->{$field} ?? '[]', true);

                    $scorecard->{$field} = is_array($decoded)
                        ? array_values($decoded)
                        : [];
                }

                return $scorecard;
            });

        if ($scorecards->isEmpty()) {
            $scorecards = collect([
                (object) [
                    'innings_number' => 1,
                    'batting_team_id' => $fixture->home_team_id,
                    'bowling_team_id' => $fixture->away_team_id,
                    'total_runs' => 0,
                    'wickets' => 0,
                    'overs' => '0.0',
                    'extras' => 0,
                    'batters' => [],
                    'bowlers' => [],
                ],
            ]);
        }

        return view('admin.scorecards.edit', compact(
            'fixture',
            'teams',
            'players',
            'scorecards'
        ));
    }

    public function update(Request $request, $fixtureId)
    {
        $fixture = DB::table('fixtures')
            ->where('id', $fixtureId)
            ->first();

        abort_if(!$fixture, 404, 'Fixture not found.');

        $teamIds = [
            (int) $fixture->home_team_id,
            (int) $fixture->away_team_id,
        ];

        $data = $request->validate([
            'innings' => ['required', 'array', 'min:1'],

            'innings.*.innings_number' => [
                'required',
                'integer',
                'min:1',
            ],

            'innings.*.batting_team_id' => [
                'required',
                'integer',
            ],

            'innings.*.bowling_team_id' => [
                'required',
                'integer',
            ],

            'innings.*.total_runs' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'innings.*.wickets' => [
                'nullable',
                'integer',
                'min:0',
                'max:10',
            ],

            'innings.*.overs' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'innings.*.extras' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'innings.*.batters' => ['nullable', 'array'],
            'innings.*.batters.*.name' => ['nullable', 'string', 'max:255'],
            'innings.*.batters.*.custom_name' => ['nullable', 'string', 'max:255'],
            'innings.*.batters.*.runs' => ['nullable', 'integer', 'min:0'],
            'innings.*.batters.*.balls' => ['nullable', 'integer', 'min:0'],
            'innings.*.batters.*.fours' => ['nullable', 'integer', 'min:0'],
            'innings.*.batters.*.sixes' => ['nullable', 'integer', 'min:0'],
            'innings.*.batters.*.strike_rate' => ['nullable', 'numeric', 'min:0'],

            'innings.*.bowlers' => ['nullable', 'array'],
            'innings.*.bowlers.*.name' => ['nullable', 'string', 'max:255'],
            'innings.*.bowlers.*.custom_name' => ['nullable', 'string', 'max:255'],
            'innings.*.bowlers.*.overs' => ['nullable', 'numeric', 'min:0'],
            'innings.*.bowlers.*.maidens' => ['nullable', 'integer', 'min:0'],
            'innings.*.bowlers.*.runs' => ['nullable', 'integer', 'min:0'],
            'innings.*.bowlers.*.wickets' => ['nullable', 'integer', 'min:0'],
            'innings.*.bowlers.*.economy' => ['nullable', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($data, $fixture, $teamIds) {
            foreach ($data['innings'] as $inning) {
                $battingTeamId = (int) $inning['batting_team_id'];
                $bowlingTeamId = (int) $inning['bowling_team_id'];

                if (
                    !in_array($battingTeamId, $teamIds, true) ||
                    !in_array($bowlingTeamId, $teamIds, true) ||
                    $battingTeamId === $bowlingTeamId
                ) {
                    throw ValidationException::withMessages([
                        'innings' => 'Choose two different teams from this fixture.',
                    ]);
                }

                $batters = collect($inning['batters'] ?? [])
                    ->map(function ($row) {
                        $name = ($row['name'] ?? '') === '__other__'
                            ? trim((string) ($row['custom_name'] ?? ''))
                            : trim((string) ($row['name'] ?? ''));

                        return [
                            'name' => $name,
                            'runs' => (int) ($row['runs'] ?? 0),
                            'balls' => (int) ($row['balls'] ?? 0),
                            'fours' => (int) ($row['fours'] ?? 0),
                            'sixes' => (int) ($row['sixes'] ?? 0),
                            'strike_rate' => (float) ($row['strike_rate'] ?? 0),
                        ];
                    })
                    ->filter(fn ($row) => $row['name'] !== '')
                    ->values()
                    ->all();

                $bowlers = collect($inning['bowlers'] ?? [])
                    ->map(function ($row) {
                        $name = ($row['name'] ?? '') === '__other__'
                            ? trim((string) ($row['custom_name'] ?? ''))
                            : trim((string) ($row['name'] ?? ''));

                        return [
                            'name' => $name,
                            'overs' => (float) ($row['overs'] ?? 0),
                            'maidens' => (int) ($row['maidens'] ?? 0),
                            'runs' => (int) ($row['runs'] ?? 0),
                            'wickets' => (int) ($row['wickets'] ?? 0),
                            'economy' => (float) ($row['economy'] ?? 0),
                        ];
                    })
                    ->filter(fn ($row) => $row['name'] !== '')
                    ->values()
                    ->all();

                DB::table('manual_scorecards')->updateOrInsert(
                    [
                        'fixture_id' => $fixture->id,
                        'innings_number' => (int) $inning['innings_number'],
                    ],
                    [
                        'batting_team_id' => $battingTeamId,
                        'bowling_team_id' => $bowlingTeamId,
                        'total_runs' => (int) ($inning['total_runs'] ?? 0),
                        'wickets' => (int) ($inning['wickets'] ?? 0),
                        'overs' => $inning['overs'] ?? '0.0',
                        'extras' => (int) ($inning['extras'] ?? 0),
                        'batters' => json_encode($batters, JSON_UNESCAPED_UNICODE),
                        'bowlers' => json_encode($bowlers, JSON_UNESCAPED_UNICODE),
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        });

        return redirect()
            ->route('admin.scorecards.edit', $fixtureId)
            ->with('success', 'Scorecard saved successfully.');
    }
}