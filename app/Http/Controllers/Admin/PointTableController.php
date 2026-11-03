<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PointTableAdjustment;
use App\Models\Season;
use App\Services\PointTableCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PointTableController extends Controller
{
    public function index(
        Request $request,
        PointTableCalculator $calculator
    ) {
        $seasons = Season::query()
            ->orderByDesc('id')
            ->get();

        $requestedSeasonId = $request->input(
            'season',
            $request->input('season_id')
        );

        $selectedSeason = $requestedSeasonId
            ? $seasons->firstWhere('id', (int) $requestedSeasonId)
            : $seasons->first();

        $selectedSeason ??= $seasons->first();

        $data = $selectedSeason
            ? $calculator->calculate($selectedSeason)
            : [
                'fixtures' => collect(),
                'teams' => collect(),
                'adjustments' => collect(),
                'standings' => collect(),
                'completedMatches' => 0,
                'upcomingMatches' => 0,
                'totalTeams' => 0,
                'fixtureCount' => 0,
                'noResultPoints' => (int) config(
                    'pointtable.no_result_points',
                    1
                ),
            ];

        return view('admin.pointtable.index', [
            'seasons' => $seasons,
            'season' => $selectedSeason,
            'selectedSeason' => $selectedSeason,
            'fixtures' => $data['fixtures'],
            'fixtureCount' => $data['fixtureCount'],
            'teams' => $data['teams'],
            'adjustments' => $data['adjustments'],
            'standings' => $data['standings'],
            'noResultPoints' => $data['noResultPoints'],
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'season_id' => [
                'required',
                'integer',
                'exists:seasons,id',
            ],

            'adjustments' => ['nullable', 'array'],

            'adjustments.*.team_id' => [
                'required',
                'integer',
                'exists:teams,id',
            ],

            'adjustments.*.penalty_points' => [
                'required',
                'integer',
                'min:0',
                'max:100',
            ],

            'adjustments.*.notes' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        $seasonId = (int) $data['season_id'];
        $adjustments = $data['adjustments'] ?? [];

        $validTeamIds = DB::table('season_team')
            ->where('season_id', $seasonId)
            ->pluck('team_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $fixtureTeamIds = DB::table('fixtures')
            ->where('season_id', $seasonId)
            ->get(['home_team_id', 'away_team_id'])
            ->flatMap(fn ($fixture) => [
                (int) $fixture->home_team_id,
                (int) $fixture->away_team_id,
            ])
            ->unique()
            ->values()
            ->all();

        $validTeamIds = array_unique(array_merge(
            $validTeamIds,
            $fixtureTeamIds
        ));

        foreach ($adjustments as $adjustment) {
            if (!in_array((int) $adjustment['team_id'], $validTeamIds, true)) {
                throw ValidationException::withMessages([
                    'adjustments' => 'A selected team does not belong to this season.',
                ]);
            }
        }

        DB::transaction(function () use ($seasonId, $adjustments) {
            foreach ($adjustments as $adjustment) {
                PointTableAdjustment::updateOrCreate(
                    [
                        'season_id' => $seasonId,
                        'team_id' => (int) $adjustment['team_id'],
                    ],
                    [
                        'penalty_points' => (int) $adjustment['penalty_points'],
                        'notes' => trim((string) ($adjustment['notes'] ?? ''))
                            ?: null,
                    ]
                );
            }
        });

        return redirect()
            ->route('admin.pointtable.index', ['season' => $seasonId])
            ->with('success', 'Point-table penalty adjustments saved successfully.');
    }
}