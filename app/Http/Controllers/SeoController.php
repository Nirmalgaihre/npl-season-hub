<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;

class SeoController extends Controller
{
    /**
     * Serve the XML sitemap.
     *
     * This safe starter includes only the homepage and public,
     * parameter-free routes that actually exist in the app.
     *
     * Add dynamic player/team/fixture URLs after confirming
     * the real models and route names.
     */
    public function sitemap(): Response
    {
        abort_unless(app()->environment('production'), 404);

        $urls = [];

        $urls[] = [
            'loc' => $this->canonicalUrl('/'),
            'lastmod' => now()->toAtomString(),
        ];

        // Add only confirmed public routes that require no parameters.
        $publicRouteNames = [
            // 'teams.index',
            // 'players.index',
            // 'fixtures.index',
            // 'news.index',
        ];

        foreach ($publicRouteNames as $routeName) {
            if (! Route::has($routeName)) {
                continue;
            }

            $route = Route::getRoutes()->getByName($routeName);

            if (! $route || $route->methods() !== ['GET', 'HEAD']) {
                continue;
            }

            if (count($route->parameterNames()) > 0) {
                continue;
            }

            $urls[] = [
                'loc' => route($routeName),
                'lastmod' => now()->toAtomString(),
            ];
        }

        $urls = collect($urls)
            ->unique('loc')
            ->values();

        return response()
            ->view('seo.sitemap', compact('urls'))
            ->header('Content-Type', 'application/xml; charset=UTF-8')
            ->header('X-Content-Type-Options', 'nosniff');
    }

    /**
     * Serve robots.txt.
     */
    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            'Disallow: /admin/',
            'Disallow: /login',
            'Sitemap: ' . $this->canonicalUrl('/sitemap.xml'),
        ];

        return response(implode("\n", $lines) . "\n", 200)
            ->header('Content-Type', 'text/plain; charset=UTF-8')
            ->header('X-Content-Type-Options', 'nosniff');
    }

    /**
     * Build canonical URLs using the configured production domain.
     */
    private function canonicalUrl(string $path): string
    {
        $base = rtrim((string) config('app.url'), '/');

        return $base . '/' . ltrim($path, '/');
    }
}
