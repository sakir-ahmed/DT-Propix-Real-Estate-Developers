<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Redirect;
use App\Support\Website\WebsiteCache;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Serves the editor-managed redirects (changed slugs, retired URLs) before
 * routing continues. Hits are counted without firing model events so a
 * redirect never purges the website cache.
 */
class HandleWebsiteRedirects
{
    public function __construct(private readonly WebsiteCache $cache) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethod('GET')) {
            return $next($request);
        }

        $path = Redirect::normalizePath($request->getPathInfo());

        /** @var array<string, array{to: string, status: int}> $redirects */
        $redirects = $this->cache->remember('redirects', fn (): array => Redirect::query()
            ->get(['from_path', 'to_path', 'status_code'])
            ->mapWithKeys(fn (Redirect $redirect): array => [
                $redirect->from_path => ['to' => $redirect->to_path, 'status' => $redirect->status_code],
            ])
            ->all());

        $match = $redirects[$path] ?? null;

        if ($match === null) {
            return $next($request);
        }

        Redirect::query()->where('from_path', $path)->increment('hits');

        return redirect($match['to'], $match['status']);
    }
}
