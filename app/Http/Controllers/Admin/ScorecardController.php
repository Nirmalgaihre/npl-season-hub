<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ScorecardController extends Controller
{
    public function index(Request $request)
    {
        $seasons = DB::table('seasons')->orderByDesc('id')->get();
        $teams = DB::table('teams')->orderBy('name')->get();

        $fixturesQuery = DB::table('fixtures as f')
            ->leftJoin('teams as home', 'home.id', '=', 'f.home_team_id')
            ->leftJoin('teams as away', 'away.id', '=', 'f.away_team_id')
            ->leftJoin('seasons as s', 's.id', '=', 'f.season_id')
            ->select([
                'f.id',
                'f.season_id',
                'f.home_team_id',
                'f.away_team_id',
                'f.match_number',
                'f.scheduled_at',
                'f.status',
                'f.match_type',
                'f.home_runs',
                'f.home_wickets',
                'f.home_overs',
                'f.away_runs',
                'f.away_wickets',
                'f.away_overs',
                'f.winner_team_id',
                'f.result_summary',
                'home.name as home_team_name',
                'home.short_name as home_team_short_name',
                'away.name as away_team_name',
                'away.short_name as away_team_short_name',
                's.name as season_name',
            ]);

        if ($request->filled('season_id')) {
            $fixturesQuery->where(
                'f.season_id',
                $request->integer('season_id')
            );
        }

        if ($request->filled('team_id')) {
            $teamId = $request->integer('team_id');

            $fixturesQuery->where(function ($query) use ($teamId) {
                $query->where('f.home_team_id', $teamId)
                    ->orWhere('f.away_team_id', $teamId);
            });
        }

        if ($request->filled('match_number')) {
            $fixturesQuery->where(
                'f.match_number',
                $request->integer('match_number')
            );
        }

        $fixtures = $fixturesQuery
            ->orderByDesc('f.scheduled_at')
            ->orderByDesc('f.id')
            ->paginate(20)
            ->withQueryString();

        $fixtureIds = $fixtures->getCollection()->pluck('id');

        $savedFixtures = DB::table('manual_scorecards')
            ->whereIn('fixture_id', $fixtureIds)
            ->distinct()
            ->pluck('fixture_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $fixtures->getCollection()->transform(function ($fixture) {
            $fixture->homeTeam = (object) [
                'id' => $fixture->home_team_id,
                'name' => $fixture->home_team_name,
                'short_name' => $fixture->home_team_short_name,
            ];

            $fixture->awayTeam = (object) [
                'id' => $fixture->away_team_id,
                'name' => $fixture->away_team_name,
                'short_name' => $fixture->away_team_short_name,
            ];

            $fixture->season = (object) [
                'id' => $fixture->season_id,
                'name' => $fixture->season_name,
                'title' => $fixture->season_name,
            ];

            return $fixture;
        });

        return view('admin.scorecards.index', compact(
            'seasons',
            'teams',
            'fixtures',
            'savedFixtures'
        ));
    }

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
            ->get(['id', 'team_id', 'name', 'role']);

        $scorecards = DB::table('manual_scorecards')
            ->where('fixture_id', $fixtureId)
            ->orderBy('innings_number')
            ->get()
            ->map(function ($scorecard) {
                foreach (['batters', 'bowlers'] as $field) {
                    $decoded = is_array($scorecard->{$field})
                        ? $scorecard->{$field}
                        : json_decode(
                            $scorecard->{$field} ?? '[]',
                            true
                        );

                    $scorecard->{$field} = is_array($decoded)
                        ? array_values($decoded)
                        : [];
                }

                return $scorecard;
            });

        if ($scorecards->isEmpty()) {
            $scorecards = collect([
                (object) [
                    'id' => null,
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
            'innings' => ['required', 'array', 'min:1', 'max:2'],

            'innings.*.innings_number' => [
                'required',
                'integer',
                'in:1,2',
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
            'innings.*.batters.*.name' => [
                'nullable',
                'string',
                'max:255',
            ],
            'innings.*.batters.*.custom_name' => [
                'nullable',
                'string',
                'max:255',
            ],
            'innings.*.batters.*.runs' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'innings.*.batters.*.balls' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'innings.*.batters.*.fours' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'innings.*.batters.*.sixes' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'innings.*.batters.*.strike_rate' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'innings.*.batters.*.dismissal_type' => [
                'nullable',
                'in:not_out,bowled,caught,lbw,run_out,stumped,hit_wicket,retired_out,obstructing_field,other',
            ],

            // New separate dismissal fields
            'innings.*.batters.*.dismissed_by_bowler' => [
                'nullable',
                'string',
                'max:255',
            ],

            'innings.*.batters.*.dismissed_by_fielder' => [
                'nullable',
                'string',
                'max:255',
            ],

            // Keep accepting old saved/form data
            'innings.*.batters.*.dismissed_by' => [
                'nullable',
                'string',
                'max:255',
            ],

            'innings.*.bowlers' => ['nullable', 'array'],
            'innings.*.bowlers.*.name' => [
                'nullable',
                'string',
                'max:255',
            ],
            'innings.*.bowlers.*.custom_name' => [
                'nullable',
                'string',
                'max:255',
            ],
            'innings.*.bowlers.*.overs' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'innings.*.bowlers.*.maidens' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'innings.*.bowlers.*.runs' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'innings.*.bowlers.*.wickets' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'innings.*.bowlers.*.economy' => [
                'nullable',
                'numeric',
                'min:0',
            ],
        ]);

        DB::transaction(function () use ($data, $fixture, $teamIds) {
            $seenInnings = [];

            foreach ($data['innings'] as $inning) {
                $inningsNumber = (int) $inning['innings_number'];

                if (isset($seenInnings[$inningsNumber])) {
                    throw ValidationException::withMessages([
                        'innings' => 'Each innings can only be submitted once.',
                    ]);
                }

                $seenInnings[$inningsNumber] = true;

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

                        $dismissalType = $row['dismissal_type']
                            ?? 'not_out';

                        $legacyDismissedBy = trim(
                            (string) ($row['dismissed_by'] ?? '')
                        );

                        $dismissedByBowler = trim(
                            (string) ($row['dismissed_by_bowler'] ?? '')
                        );

                        $dismissedByFielder = trim(
                            (string) ($row['dismissed_by_fielder'] ?? '')
                        );

                        // Backward compatibility with existing scorecards.
                        if ($legacyDismissedBy !== '') {
                            if (
                                $dismissedByBowler === '' &&
                                in_array($dismissalType, [
                                    'bowled',
                                    'caught',
                                    'lbw',
                                    'stumped',
                                    'hit_wicket',
                                ], true)
                            ) {
                                $dismissedByBowler = $legacyDismissedBy;
                            }

                            if (
                                $dismissedByFielder === '' &&
                                in_array($dismissalType, [
                                    'caught',
                                    'run_out',
                                    'stumped',
                                ], true)
                            ) {
                                $dismissedByFielder = $legacyDismissedBy;
                            }
                        }

                        $dismissedBy = match ($dismissalType) {
                            'caught' => $dismissedByFielder !== ''
                                ? $dismissedByFielder
                                : null,

                            'run_out' => $dismissedByFielder !== ''
                                ? $dismissedByFielder
                                : null,

                            'stumped' => $dismissedByFielder !== ''
                                ? $dismissedByFielder
                                : null,

                            'not_out', 'retired_out' => null,

                            default => $dismissedByBowler !== ''
                                ? $dismissedByBowler
                                : (
                                    $dismissedByFielder !== ''
                                        ? $dismissedByFielder
                                        : null
                                ),
                        };

                        return [
                            'name' => $name,
                            'runs' => (int) ($row['runs'] ?? 0),
                            'balls' => (int) ($row['balls'] ?? 0),
                            'fours' => (int) ($row['fours'] ?? 0),
                            'sixes' => (int) ($row['sixes'] ?? 0),
                            'strike_rate' => round(
                                (float) ($row['strike_rate'] ?? 0),
                                2
                            ),
                            'dismissal_type' => $dismissalType,
                            'dismissed_by' => $dismissedBy,
                            'dismissed_by_bowler' =>
                                $dismissedByBowler !== ''
                                    ? $dismissedByBowler
                                    : null,
                            'dismissed_by_fielder' =>
                                $dismissedByFielder !== ''
                                    ? $dismissedByFielder
                                    : null,
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
                            'economy' => round(
                                (float) ($row['economy'] ?? 0),
                                2
                            ),
                        ];
                    })
                    ->filter(fn ($row) => $row['name'] !== '')
                    ->values()
                    ->all();

                $existing = DB::table('manual_scorecards')
                    ->where('fixture_id', $fixture->id)
                    ->where('innings_number', $inningsNumber)
                    ->first();

                $values = [
                    'batting_team_id' => $battingTeamId,
                    'bowling_team_id' => $bowlingTeamId,
                    'total_runs' => (int) ($inning['total_runs'] ?? 0),
                    'wickets' => (int) ($inning['wickets'] ?? 0),
                    'overs' => $inning['overs'] ?? '0.0',
                    'extras' => (int) ($inning['extras'] ?? 0),
                    'batters' => json_encode(
                        $batters,
                        JSON_UNESCAPED_UNICODE
                    ),
                    'bowlers' => json_encode(
                        $bowlers,
                        JSON_UNESCAPED_UNICODE
                    ),
                    'updated_at' => now(),
                ];

                if ($existing) {
                    // Update only the innings that was submitted.
                    DB::table('manual_scorecards')
                        ->where('id', $existing->id)
                        ->update($values);
                } else {
                    DB::table('manual_scorecards')->insert(
                        array_merge($values, [
                            'fixture_id' => $fixture->id,
                            'innings_number' => $inningsNumber,
                            'created_at' => now(),
                        ])
                    );
                }
            }
        });

        return redirect()
            ->route('admin.scorecards.edit', $fixtureId)
            ->with('success', 'Submitted innings saved successfully.');
    }
}
