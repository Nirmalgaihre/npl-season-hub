<?php

use App\Http\Controllers\Admin\ArticleController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\MatchController;
use App\Http\Controllers\Admin\SeasonController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\TeamController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PublicMatchController;
use App\Http\Controllers\PublicNewsController;
use App\Http\Controllers\PublicSeasonController;
use App\Http\Controllers\PublicTeamController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LiveScoreController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Home
Route::get('/', [HomeController::class, 'index'])->name('home');
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
| Public Fixtures & Results
|--------------------------------------------------------------------------
*/

Route::get('/fixtures', [PublicMatchController::class, 'index'])
    ->name('fixtures.index');

Route::get('/fixtures/{fixture}', [
    PublicMatchController::class,
    'show',
])->whereNumber('fixture')->name('fixtures.show');

// Public Live Match View Route
Route::get('/fixtures/{fixture}/live', [
    LiveScoreController::class,
    'show',
])->whereNumber('fixture')->name('live.show');

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

// Login: guests only
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])
        ->name('login');

    Route::post('/login', [LoginController::class, 'store'])
        ->name('login.store');
});

// Logout: authenticated users only
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

        // Seasons
        Route::resource('seasons', SeasonController::class);

        // Articles
        Route::resource('articles', ArticleController::class)
            ->except(['show']);

        // Teams
        Route::resource('teams', TeamController::class)
            ->except(['show']);

        // Fixtures and match results
        Route::resource('fixtures', MatchController::class)
            ->except(['show']);

        // Admin Live Match Scoring Routes
        Route::get('/live', [LiveScoreController::class, 'index'])
            ->name('live.index');

        Route::post('/fixtures/{fixture}/live-score/toss', [
            LiveScoreController::class,
            'saveToss',
        ])->whereNumber('fixture')->name('live.toss');

        Route::get('/fixtures/{fixture}/live-score', [
            LiveScoreController::class,
            'admin',
        ])->whereNumber('fixture')->name('live.admin');

        Route::post('/fixtures/{fixture}/live-score/innings', [
            LiveScoreController::class,
            'startInnings',
        ])->whereNumber('fixture')->name('live.start');

        Route::post('/fixtures/{fixture}/live-score/ball', [
            LiveScoreController::class,
            'ball',
        ])->whereNumber('fixture')->name('live.ball');

        Route::delete('/fixtures/{fixture}/live-score/{delivery}', [
            LiveScoreController::class,
            'undo',
        ])->whereNumber('fixture')->whereNumber('delivery')->name('live.undo');

        // Categories
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

        // Tags
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