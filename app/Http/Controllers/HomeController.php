<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Fixture;
use App\Models\ManualScorecard;
use App\Models\Season;
use App\Models\Team;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        /*
        |--------------------------------------------------------------------------
        | Active season
        |--------------------------------------------------------------------------
        */

        $activeSeason = Season::query()
            ->where('status', 'active')
            ->orderByDesc('season_number')
            ->first();

        $season = $activeSeason ?? Season::query()
            ->orderByDesc('season_number')
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Live fixtures
        |--------------------------------------------------------------------------
        */

        $liveMatches = Fixture::query()
            ->with(['season', 'homeTeam', 'awayTeam', 'winner'])
            ->where('status', 'live')
            ->when($season, function (Builder $query) use ($season) {
                $query->where('season_id', $season->id);
            })
            ->orderBy('scheduled_at')
            ->orderBy('match_number')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Next scheduled match
        |--------------------------------------------------------------------------
        */

        $nextMatch = null;

        if ($season) {
            $nextMatch = Fixture::query()
                ->with(['season', 'homeTeam', 'awayTeam'])
                ->where('season_id', $season->id)
                ->where('status', 'scheduled')
                ->whereNotNull('scheduled_at')
                ->where('scheduled_at', '>=', now())
                ->orderBy('scheduled_at')
                ->orderBy('match_number')
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

        /*
        |--------------------------------------------------------------------------
        | Recent completed matches
        |--------------------------------------------------------------------------
        |
        | Only verified completed matches are shown.
        | Higher match numbers are shown first, followed by the latest date.
        |
        */

        $recentResults = Fixture::query()
            ->with([
                'season',
                'homeTeam',
                'awayTeam',
                'winner',
            ])
            ->where('status', 'completed')
            ->where('is_result_verified', true)
            ->when($season, function (Builder $query) use ($season) {
                $query->where('season_id', $season->id);
            })
            ->orderByDesc('match_number')
            ->orderByDesc('scheduled_at')
            ->orderByDesc('id')
            ->take(6)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Manual scoreboard data
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        | The homepage scoreboards read ManualScorecard records here.
        | They no longer depend on LiveInnings for completed-match scores.
        |
        | The view already reads:
        | $liveScoreByFixture->get($result->id, collect())
        |
        | We preserve that variable name so the existing homepage Blade
        | can use the manually entered scores without redesigning the page.
        |
        */

        $recentFixtureIds = $recentResults->modelKeys();

        $liveScoreByFixture = collect();

        if (! empty($recentFixtureIds)) {
            $manualScorecards = ManualScorecard::query()
                ->whereIn('fixture_id', $recentFixtureIds)
                ->orderBy('fixture_id')
                ->orderBy('innings_number')
                ->get();

            $liveScoreByFixture = $manualScorecards
                ->groupBy('fixture_id');
        }

        /*
        |--------------------------------------------------------------------------
        | Teams in the selected season
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | Latest published news
        |--------------------------------------------------------------------------
        */

        $latestNews = collect();

        if (Schema::hasTable('articles')) {
            $articleQuery = Article::query();

            if (Schema::hasColumn('articles', 'status')) {
                $articleQuery->where('status', 'published');
            } elseif (Schema::hasColumn('articles', 'is_published')) {
                $articleQuery->where('is_published', true);
            } else {
                // Do not publish articles if their publication status is unknown.
                $articleQuery->whereRaw('1 = 0');
            }

            if (Schema::hasColumn('articles', 'published_at')) {
                $articleQuery
                    ->where(function (Builder $query) {
                        $query->whereNull('published_at')
                            ->orWhere('published_at', '<=', now());
                    })
                    ->orderByDesc('published_at')
                    ->orderByDesc('created_at');
            } else {
                $articleQuery->orderByDesc('created_at');
            }

            $latestNews = $articleQuery
                ->take(3)
                ->get();
        }

        /*
        |--------------------------------------------------------------------------
        | Homepage
        |--------------------------------------------------------------------------
        */

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
