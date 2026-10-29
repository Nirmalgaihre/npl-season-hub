<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\Fixture;
use App\Models\Season;
use App\Models\Tag;
use App\Models\Team;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    public function sitemap(): Response
    {
        $urls = [];

        $addUrl = static function (
            string $loc,
            mixed $lastmod = null
        ) use (&$urls): void {
            $urls[] = [
                'loc' => $loc,
                'lastmod' => $lastmod?->toAtomString(),
            ];
        };

        // Homepage and public listing pages.
        $addUrl($this->canonicalUrl('/'));
        $addUrl(route('fixtures.index'));
        $addUrl(route('news.index'));
        $addUrl(route('teams.index'));
        $addUrl(route('seasons.index'));

        // Active teams only: inactive teams are not listed publicly.
        Team::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'slug', 'updated_at'])
            ->each(function (Team $team) use ($addUrl): void {
                $addUrl(
                    route('teams.show', $team->slug),
                    $team->updated_at
                );
            });

        // Public seasons only.
        Season::query()
            ->whereIn('status', ['active', 'completed'])
            ->orderByDesc('season_number')
            ->get(['id', 'slug', 'updated_at'])
            ->each(function (Season $season) use ($addUrl): void {
                $addUrl(
                    route('seasons.show', $season->slug),
                    $season->updated_at
                );
            });

        // Public fixtures belonging to visible seasons.
        Fixture::query()
            ->whereHas('season', function ($query): void {
                $query->whereIn('status', [
                    'upcoming',
                    'active',
                    'completed',
                ]);
            })
            ->orderByDesc('scheduled_at')
            ->get(['id', 'season_id', 'updated_at'])
            ->each(function (Fixture $fixture) use ($addUrl): void {
                $addUrl(
                    route('fixtures.show', $fixture->id),
                    $fixture->updated_at
                );
            });

        // Published news articles only.
        Article::published()
            ->orderByDesc('published_at')
            ->get(['id', 'slug', 'updated_at'])
            ->each(function (Article $article) use ($addUrl): void {
                $addUrl(
                    route('news.show', $article->slug),
                    $article->updated_at
                );
            });

        // Categories that contain published articles.
        Category::query()
            ->whereHas('articles', function ($query): void {
                $query->published();
            })
            ->orderBy('name')
            ->get(['id', 'slug', 'updated_at'])
            ->each(function (Category $category) use ($addUrl): void {
                $addUrl(
                    route('news.category', $category->slug),
                    $category->updated_at
                );
            });

        // Tags that contain published articles.
        Tag::query()
            ->whereHas('articles', function ($query): void {
                $query->published();
            })
            ->orderBy('name')
            ->get(['id', 'slug', 'updated_at'])
            ->each(function (Tag $tag) use ($addUrl): void {
                $addUrl(
                    route('news.tag', $tag->slug),
                    $tag->updated_at
                );
            });

        $urls = collect($urls)
            ->unique('loc')
            ->values()
            ->all();

        return response()
            ->view('seo.sitemap', compact('urls'))
            ->header('Content-Type', 'application/xml; charset=UTF-8')
            ->header('X-Content-Type-Options', 'nosniff');
    }

    public function robots(): Response
    {
        $content = implode("\n", [
            'User-agent: *',
            'Disallow: /admin',
            'Disallow: /login',
            'Disallow: /logout',
            'Sitemap: ' . $this->canonicalUrl('/sitemap.xml'),
            '',
        ]);

        return response($content, 200)
            ->header('Content-Type', 'text/plain; charset=UTF-8')
            ->header('X-Content-Type-Options', 'nosniff');
    }

    private function canonicalUrl(string $path): string
    {
        $base = rtrim((string) config('app.url'), '/');

        return $base . '/' . ltrim($path, '/');
    }
}
