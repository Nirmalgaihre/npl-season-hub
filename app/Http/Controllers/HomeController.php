<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Fixture;
use App\Models\LiveInnings;
use App\Models\Season;
use App\Models\Team;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        // Active season, or latest available season.
        $activeSeason = Season::query()
            ->where('status', 'active')
            ->orderByDesc('season_number')
            ->first();

        $season = $activeSeason ?? Season::query()
            ->orderByDesc('season_number')
            ->first();

        // Real live fixtures only.
        $liveMatches = Fixture::query()
            ->with(['season', 'homeTeam', 'awayTeam', 'winner'])
            ->where('status', 'live')
            ->when($season, function (Builder $query) use ($season) {
                $query->where('season_id', $season->id);
            })
            ->orderBy('scheduled_at')
            ->orderBy('match_number')
            ->get();

        // Real innings and team information for live fixtures.
        $liveScoreByFixture = LiveInnings::query()
            ->with(['battingTeam', 'bowlingTeam'])
            ->whereIn('fixture_id', $liveMatches->modelKeys())
            ->orderBy('fixture_id')
            ->orderBy('innings_number')
            ->get()
            ->groupBy('fixture_id');

        // Next scheduled match.
        $nextMatch = null;

        if ($season) {
            $nextMatch = Fixture::query()
                ->with(['season', 'homeTeam', 'awayTeam'])
                ->where('season_id', $season->id)
                ->where('status', 'scheduled')
                ->whereNotNull('scheduled_at')
                ->where('scheduled_at', '>=', now())
                ->orderBy('scheduled_at')
                ->first();

            if (! $nextMatch) {
                $nextMatch = Fixture::query()
                    ->with(['season', 'homeTeam', 'awayTeam'])
                    ->where('season_id', $season->id)
                    ->where('status', 'scheduled')
                    ->whereNull('scheduled_at')
                    ->orderBy('match_number')
                    ->first();
            }
        }

        // Verified completed results only.
        $recentResults = Fixture::query()
            ->with(['season', 'homeTeam', 'awayTeam', 'winner'])
            ->where('status', 'completed')
            ->where('is_result_verified', true)
            ->when($season, function (Builder $query) use ($season) {
                $query->where('season_id', $season->id);
            })
            ->orderByDesc('scheduled_at')
            ->orderByDesc('match_number')
            ->take(4)
            ->get();

        // Teams participating in the selected season.
        $teams = $season
            ? $season->teams()
                ->where('teams.is_active', true)
                ->orderBy('teams.name')
                ->take(8)
                ->get()
            : Team::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->take(8)
                ->get();

        // Published articles only.
        $latestNews = collect();

        if (Schema::hasTable('articles')) {
            $articleQuery = Article::query();

            if (Schema::hasColumn('articles', 'status')) {
                $articleQuery->where('status', 'published');
            } elseif (Schema::hasColumn('articles', 'is_published')) {
                $articleQuery->where('is_published', true);
            } else {
                // Never expose articles if publication status is unknown.
                $articleQuery->whereRaw('1 = 0');
            }

            if (Schema::hasColumn('articles', 'published_at')) {
                $articleQuery
                    ->where(function (Builder $query) {
                        $query->whereNull('published_at')
                            ->orWhere('published_at', '<=', now());
                    })
                    ->orderByDesc('published_at');
            } else {
                $articleQuery->orderByDesc('created_at');
            }

            $latestNews = $articleQuery
                ->take(3)
                ->get();
        }

        return view('home', compact(
            'season',
            'liveMatches',
            'liveScoreByFixture',
            'nextMatch',
            'recentResults',
            'teams',
            'latestNews'
        ));
    }
}
