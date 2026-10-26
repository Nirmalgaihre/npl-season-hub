<?php

namespace App\Http\Controllers;

use App\Models\Season;
use Illuminate\View\View;

class PublicSeasonController extends Controller
{
    public function index(): View
    {
        $seasons = Season::publiclyVisible()
            ->orderByDesc('season_number')
            ->paginate(12);

        return view('seasons.index', compact('seasons'));
    }

    public function show(Season $season): View
    {
        abort_unless(
            in_array($season->status, ['active', 'completed'], true),
            404
        );

        return view('seasons.show', compact('season'));
    }
}