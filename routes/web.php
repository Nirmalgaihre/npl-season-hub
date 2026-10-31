<?php

use App\Http\Controllers\StaticPageController;
use App\Http\Controllers\Admin\ArticleController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MatchController;
use App\Http\Controllers\Admin\PlayerController;
use App\Http\Controllers\Admin\ScorecardController;
use App\Http\Controllers\Admin\SeasonController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\TeamController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LiveScoreController;
use App\Http\Controllers\PublicMatchController;
use App\Http\Controllers\PublicNewsController;
use App\Http\Controllers\PublicSeasonController;
use App\Http\Controllers\PublicTeamController;
use App\Http\Controllers\SeoController;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Public Home
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])
    ->name('home');

/*
|--------------------------------------------------------------------------
| SEO (Sitemap & Robots)
|--------------------------------------------------------------------------
*/

Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])
    ->name('seo.sitemap');

Route::get('/robots.txt', [SeoController::class, 'robots'])
    ->name('seo.robots');

/*
|--------------------------------------------------------------------------
| Public News
|--------------------------------------------------------------------------
*/

Route::get('/news', [PublicNewsController::class, 'index'])
    ->name('news.index');

Route::get('/news/category/{category:slug}', [
    PublicNewsController::class,
    'category',
])->name('news.category');

Route::get('/news/tag/{tag:slug}', [
    PublicNewsController::class,
    'tag',
])->name('news.tag');

Route::get('/news/{article:slug}', [
    PublicNewsController::class,
    'show',
])->name('news.show');

/*
|--------------------------------------------------------------------------
| Public Season Archive
|--------------------------------------------------------------------------
*/

Route::get('/seasons', [PublicSeasonController::class, 'index'])
    ->name('seasons.index');

Route::get('/seasons/{season:slug}', [
    PublicSeasonController::class,
    'show',
])->name('seasons.show');

/*
|--------------------------------------------------------------------------
| Public Teams
|--------------------------------------------------------------------------
*/

Route::get('/teams', [PublicTeamController::class, 'index'])
    ->name('teams.index');

Route::get('/teams/{team:slug}', [
    PublicTeamController::class,
    'show',
])->name('teams.show');

/*
|--------------------------------------------------------------------------
| Public Players Performance
|--------------------------------------------------------------------------
*/

Route::get('/players/performance/{playerName}', [
    PublicMatchController::class,
    'playerPerformance',
])->name('players.performance');

/*
|--------------------------------------------------------------------------
| Public Fixtures & Results
|--------------------------------------------------------------------------
*/

Route::get('/fixtures', [PublicMatchController::class, 'index'])
    ->name('fixtures.index');

Route::get('/fixtures/{fixture}', [
    PublicMatchController::class,
    'show',
])->whereNumber('fixture')
    ->name('fixtures.show');

  

Route::get('/fixtures/{fixture}/players/{player}', [
    PublicMatchController::class,
    'playerPerformance',
])->whereNumber('fixture')
    ->whereNumber('player')
    ->name('fixtures.players.show');

/*
|--------------------------------------------------------------------------
| Public Live Match
|--------------------------------------------------------------------------
*/

Route::get('/fixtures/{fixture}/live', [
    LiveScoreController::class,
    'show',
])->whereNumber('fixture')
    ->name('live.show');

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

// Login (Protected with rate limiting throttle for security against brute-force attacks)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])
        ->name('login');

    Route::post('/login', [LoginController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('login.store');
});

// Logout
Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Protected Admin Management
|--------------------------------------------------------------------------
|
| All routes in this group require authentication and the
| manage-seasons authorization ability.
|
*/

Route::middleware(['auth', 'can:manage-seasons'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Dashboard (First entry after login)
        |--------------------------------------------------------------------------
        */

        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');

        /*
        |--------------------------------------------------------------------------
        | Fixtures
        |--------------------------------------------------------------------------
        */

        Route::resource('fixtures', MatchController::class)
            ->except(['show']);

        Route::post('/fixtures/{fixture}/verify', [MatchController::class, 'verify'])
            ->name('fixtures.verify');

        /*
        |--------------------------------------------------------------------------
        | Seasons
        |--------------------------------------------------------------------------
        */

        Route::resource('seasons', SeasonController::class);

        /*
        |--------------------------------------------------------------------------
        | Teams
        |--------------------------------------------------------------------------
        */

        Route::resource('teams', TeamController::class)
            ->except(['show']);

        /*
        |--------------------------------------------------------------------------
        | Players
        |--------------------------------------------------------------------------
        */

        Route::resource('players', PlayerController::class)
            ->except(['show']);

        /*
        |--------------------------------------------------------------------------
        | Articles / News Management
        |--------------------------------------------------------------------------
        */

        Route::resource('articles', ArticleController::class)
            ->except(['show']);

        /*
        |--------------------------------------------------------------------------
        | Live Match Scoring
        |--------------------------------------------------------------------------
        */

        Route::get('/live', [
            LiveScoreController::class,
            'index',
        ])->name('live.index');

        Route::post('/fixtures/{fixture}/live-score/toss', [
            LiveScoreController::class,
            'saveToss',
        ])->whereNumber('fixture')
            ->name('live.toss');

        Route::get('/fixtures/{fixture}/live-score', [
            LiveScoreController::class,
            'admin',
        ])->whereNumber('fixture')
            ->name('live.admin');

        Route::post('/fixtures/{fixture}/live-score/innings', [
            LiveScoreController::class,
            'startInnings',
        ])->whereNumber('fixture')
            ->name('live.start');

        Route::post('/fixtures/{fixture}/live-score/ball', [
            LiveScoreController::class,
            'ball',
        ])->whereNumber('fixture')
            ->name('live.ball');

        Route::delete('/fixtures/{fixture}/live-score/{delivery}', [
            LiveScoreController::class,
            'undo',
        ])->whereNumber('fixture')
            ->whereNumber('delivery')
            ->name('live.undo');
            

        /*
        |--------------------------------------------------------------------------
        | Scorecards Management
        |--------------------------------------------------------------------------
        */

        Route::get('/scorecards', [
            ScorecardController::class,
            'index',
        ])->name('scorecards.index');

        Route::get('/fixtures/{fixture}/scorecard', [
            ScorecardController::class,
            'edit',
        ])->whereNumber('fixture')
            ->name('scorecards.edit');

        Route::put('/fixtures/{fixture}/scorecard', [
            ScorecardController::class,
            'update',
        ])->whereNumber('fixture')
            ->name('scorecards.update');

        /*
        |--------------------------------------------------------------------------
        | Categories
        |--------------------------------------------------------------------------
        */

        Route::get('/categories', [
            CategoryController::class,
            'index',
        ])->name('categories.index');

        Route::post('/categories', [
            CategoryController::class,
            'store',
        ])->name('categories.store');

        Route::put('/categories/{category}', [
            CategoryController::class,
            'update',
        ])->name('categories.update');

        Route::delete('/categories/{category}', [
            CategoryController::class,
            'destroy',
        ])->name('categories.destroy');

        /*
        |--------------------------------------------------------------------------
        | Tags
        |--------------------------------------------------------------------------
        */

        Route::get('/tags', [
            TagController::class,
            'index',
        ])->name('tags.index');

        Route::post('/tags', [
            TagController::class,
            'store',
        ])->name('tags.store');

        Route::delete('/tags/{tag}', [
            TagController::class,
            'destroy',
        ])->name('tags.destroy');
    });

// Public information pages
Route::get('/about', [StaticPageController::class, 'about'])->name('about');
Route::get('/contact', [StaticPageController::class, 'contact'])->name('contact');
Route::post('/contact', [StaticPageController::class, 'sendContact'])
    ->middleware('throttle:5,1')
    ->name('contact.send');
Route::get('/privacy-policy', [StaticPageController::class, 'privacyPolicy'])
    ->name('privacy-policy');