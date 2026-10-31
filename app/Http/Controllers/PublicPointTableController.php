<?php

namespace App\Http\Controllers;

use App\Models\Season;
use App\Services\PointTableCalculator;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicPointTableController extends Controller
{
    public function index(
        Request $request,
        PointTableCalculator $calculator
    ): View {
        $seasons = Season::query()
            ->orderByDesc('id')
            ->get();

        $selectedSeason = null;

        if ($request->filled('season')) {
            $selectedSeason = $seasons->firstWhere(
                'id',
                (int) $request->input('season')
            );
        }

        $selectedSeason ??= $seasons->first();

        $data = $selectedSeason
            ? $calculator->calculate($selectedSeason)
            : [
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

        return view('pointtable.index', [
            'seasons' => $seasons,
            'selectedSeason' => $selectedSeason,
            'standings' => $data['standings'],
            'completedMatches' => $data['completedMatches'],
            'upcomingMatches' => $data['upcomingMatches'],
            'totalTeams' => $data['totalTeams'],
            'noResultPoints' => $data['noResultPoints'],
        ]);
    }
}