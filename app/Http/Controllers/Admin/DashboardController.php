<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fixture;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_fixtures' => Fixture::count(),
            'upcoming_fixtures' => Fixture::where('status', 'upcoming')->count(),
            'live_fixtures' => Fixture::where('status', 'live')->count(),
            'completed_fixtures' => Fixture::where('status', 'completed')->count(),
        ];

        $recentFixtures = Fixture::query()
            ->latest('id')
            ->take(8)
            ->get();

        return view('admin.dashboard.index', compact(
            'stats',
            'recentFixtures'
        ));
    }
}
