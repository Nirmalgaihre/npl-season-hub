<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fixture;
use App\Models\Team;
use App\Models\Season;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function index(): View
    {
        $now = now();

        $stats = [
            'total_fixtures' => Fixture::count(),

            'upcoming_fixtures' => Fixture::where('status', 'scheduled')
                ->where(fn ($q) => $q->whereNull('scheduled_at')
                    ->orWhere('scheduled_at', '>=', $now))
                ->count(),

            'live_fixtures' => Fixture::where('status', 'live')->count(),

            'completed_fixtures' => Fixture::where('status', 'completed')->count(),

            'abandoned_fixtures' => Fixture::where('status', 'abandoned')->count(),

            'postponed_fixtures' => Fixture::where('status', 'postponed')->count(),

            'verified_results' => Fixture::where('status', 'completed')
                ->where('is_result_verified', true)
                ->count(),

            'pending_verification' => Fixture::where('status', 'completed')
                ->where(fn ($q) => $q->where('is_result_verified', false)
                    ->orWhereNull('is_result_verified'))
                ->count(),

            'total_teams' => Team::count(),

            'total_seasons' => Season::count(),
        ];

        $upcomingFixtures = Fixture::with(['homeTeam', 'awayTeam', 'season'])
            ->where('status', 'scheduled')
            ->where(fn ($q) => $q->whereNull('scheduled_at')
                ->orWhere('scheduled_at', '>=', $now))
            ->orderByRaw('scheduled_at IS NULL')
            ->orderBy('scheduled_at')
            ->orderBy('id')
            ->take(6)
            ->get();

        $recentMatches = Fixture::with(['homeTeam', 'awayTeam', 'season'])
            ->where('status', 'completed')
            ->orderByRaw('scheduled_at IS NULL')
            ->orderByDesc('scheduled_at')
            ->orderByDesc('id')
            ->take(6)
            ->get();

        $liveFixtures = Fixture::with(['homeTeam', 'awayTeam', 'season'])
            ->where('status', 'live')
            ->orderBy('scheduled_at')
            ->take(4)
            ->get();

        $recentFixtures = Fixture::with(['homeTeam', 'awayTeam', 'season'])
            ->latest('id')
            ->take(8)
            ->get();

        /*
         * Dynamically scan:
         * storage/app/public/teams
         *
         * No individual filenames are hardcoded.
         */
        $files = collect(Storage::disk('public')->files('teams'))
            ->filter(fn ($file) => preg_match(
                '/\.(png|jpe?g|webp|gif|svg)$/i',
                $file
            ))
            ->values();

        $normalise = function ($value) {
            return Str::slug(
                pathinfo(basename((string) $value), PATHINFO_FILENAME)
            );
        };

        $teamLogoUrls = [];

        foreach (Team::all() as $team) {
            $logo = null;

            // First choice: the logo path already saved for this team.
            if (!empty($team->logo_path)) {
                $savedPath = preg_replace(
                    '#^/?storage/#',
                    '',
                    $team->logo_path
                );

                $savedPath = ltrim($savedPath, '/');

                // Accept either "logo.png" or "teams/logo.png".
                if (!Str::startsWith($savedPath, 'teams/')) {
                    $savedPath = 'teams/' . $savedPath;
                }

                if (in_array($savedPath, $files->all(), true)) {
                    $logo = Storage::disk('public')->url($savedPath);
                } elseif (Str::startsWith($team->logo_path, ['http://', 'https://'])) {
                    $logo = $team->logo_path;
                }
            }

            // Second choice: match an image filename to the team name.
            if (!$logo) {
                $teamName = $normalise($team->name ?? '');

                $matchedFile = $files->first(function ($file) use (
                    $normalise,
                    $teamName
                ) {
                    $filename = $normalise($file);

                    return $filename === $teamName;
                });

                if ($matchedFile) {
                    $logo = Storage::disk('public')->url($matchedFile);
                }
            }

            // Keep the result keyed by team ID for easy Blade lookup.
            $teamLogoUrls[$team->id] = $logo;
        }

        return view('admin.dashboard.index', compact(
            'stats',
            'upcomingFixtures',
            'recentMatches',
            'liveFixtures',
            'recentFixtures',
            'teamLogoUrls'
        ));
    }
}
