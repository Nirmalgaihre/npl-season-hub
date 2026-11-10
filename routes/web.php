
<?php

use App\Http\Controllers\StaticPageController;
use App\Http\Controllers\Admin\ArticleController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MatchController;
use App\Http\Controllers\Admin\PlayerController;
use App\Http\Controllers\Admin\PointTableController;
use App\Http\Controllers\Admin\ScorecardController;
use App\Http\Controllers\Admin\SeasonController;
use App\Http\Controllers\Admin\SponsorController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\TeamController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PublicMatchController;
use App\Http\Controllers\PublicNewsController;
use App\Http\Controllers\PublicPointTableController;
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
| SEO: Sitemap and Robots
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
| Public Fixtures and Results
|--------------------------------------------------------------------------
*/

Route::get('/fixtures', [PublicMatchController::class, 'index'])
    ->name('fixtures.index');

Route::get('/fixtures/{fixture}/players/{player}', [
    PublicMatchController::class,
    'playerPerformance',
])->whereNumber('fixture')
    ->whereNumber('player')
    ->name('fixtures.players.show');

Route::get('/fixtures/{fixture}', [
    PublicMatchController::class,
    'show',
])->whereNumber('fixture')
    ->name('fixtures.show');

/*
|--------------------------------------------------------------------------
| Public Player Performance
|--------------------------------------------------------------------------
*/

Route::get('/players/performance/{playerName}', [
    PublicMatchController::class,
    'playerPerformance',
])->name('players.performance');

/*
|--------------------------------------------------------------------------
| Public Points Table
|--------------------------------------------------------------------------
*/

Route::get('/pointtable', [PublicPointTableController::class, 'index'])
    ->name('pointtable.index');

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])
        ->name('login');

    Route::post('/login', [LoginController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('login.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Protected Admin Management
|--------------------------------------------------------------------------
|
| All routes require authentication and the manage-seasons ability.
|
*/

Route::middleware(['auth', 'can:manage-seasons'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */

        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');

        /*
        |--------------------------------------------------------------------------
        | Fixtures and Results Management
        |--------------------------------------------------------------------------
        */

        Route::resource('fixtures', MatchController::class)
            ->except(['show']);

        Route::post('/fixtures/{fixture}/verify', [
            MatchController::class,
            'verify',
        ])->whereNumber('fixture')
            ->name('fixtures.verify');

        /*
        |--------------------------------------------------------------------------
        | Seasons Management
        |--------------------------------------------------------------------------
        */

        Route::resource('seasons', SeasonController::class);

        // Sponsors and Partners Management
        Route::get('/sponsors', [SponsorController::class, 'index'])
            ->name('sponsors.index');

        Route::post('/sponsors', [SponsorController::class, 'store'])
            ->name('sponsors.store');

        Route::put('/sponsors/{sponsor}', [SponsorController::class, 'update'])
            ->whereNumber('sponsor')
            ->name('sponsors.update');

        Route::delete('/sponsors/{sponsor}', [SponsorController::class, 'destroy'])
            ->whereNumber('sponsor')
            ->name('sponsors.destroy');


        /*
        |--------------------------------------------------------------------------
        | Teams Management
        |--------------------------------------------------------------------------
        */

        Route::resource('teams', TeamController::class)
            ->except(['show']);

        /*
        |--------------------------------------------------------------------------
        | Players Management
        |--------------------------------------------------------------------------
        */

        Route::resource('players', PlayerController::class)
            ->except(['show']);

        /*
        |--------------------------------------------------------------------------
        | Articles and News Management
        |--------------------------------------------------------------------------
        */

        Route::resource('articles', ArticleController::class)
            ->except(['show']);

        /*
        |--------------------------------------------------------------------------
        | Points Table Management
        |--------------------------------------------------------------------------
        */

        Route::get('/pointtable', [
            PointTableController::class,
            'index',
        ])->name('pointtable.index');

        Route::put('/pointtable', [
            PointTableController::class,
            'update',
        ])->name('pointtable.update');

        /*
        |--------------------------------------------------------------------------
        | Manual Scorecards Management
        |--------------------------------------------------------------------------
        |
        | This is the retained scorecard system.
        | It is independent of the retired live-scoring routes.
        |
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
        | Categories Management
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
        ])->whereNumber('category')
            ->name('categories.update');

        Route::delete('/categories/{category}', [
            CategoryController::class,
            'destroy',
        ])->whereNumber('category')
            ->name('categories.destroy');

        /*
        |--------------------------------------------------------------------------
        | Tags Management
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
        ])->whereNumber('tag')
            ->name('tags.destroy');
    });

/*
|--------------------------------------------------------------------------
| Public Information Pages
|--------------------------------------------------------------------------
*/

Route::get('/about', [StaticPageController::class, 'about'])
    ->name('about');

Route::get('/contact', [StaticPageController::class, 'contact'])
    ->name('contact');

Route::post('/contact', [StaticPageController::class, 'sendContact'])
    ->middleware('throttle:5,1')
    ->name('contact.send');

Route::get('/privacy-policy', [
    StaticPageController::class,
    'privacyPolicy',
])->name('privacy-policy');
