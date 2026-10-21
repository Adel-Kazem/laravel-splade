<?php

namespace ProtoneMedia\Splade\Navigation;

use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Throwable;

/**
 * Smart "Up" navigation for a header arrow (<x-splade-up-link>, route splade.navUp).
 *
 * Unlike the plain history "back" (NavBackController, used by device/browser back),
 * Up never ping-pongs:
 *   - deep page (/recordings/123)  -> one level up: strip the last URL segment,
 *     skipping levels that are not a real GET route (/admin alone), never "/".
 *   - main page (/recordings)      -> the last DIFFERENT place this tab visited,
 *     skipping pages of the same section (first URL segment); nothing -> fallback.
 *   - going Up POPS the tab history (the step being left / everything after the
 *     target is erased), so the landing page is never a new step to bounce back to.
 *   - history is kept PER BROWSER TAB, keyed by the X-Splade-Tab header that the
 *     @spladeNavTab inline script adds to every same-origin XHR. Requests without a
 *     tab id are not recorded per tab (they still feed the legacy back stack).
 */
class UpNavigator
{
    public const TAB_HEADER = 'X-Splade-Tab';

    public static function tabId(Request $request): ?string
    {
        $id = (string) $request->headers->get(static::TAB_HEADER, '');

        return preg_match('/^[A-Za-z0-9_-]{6,64}$/', $id) ? $id : null;
    }

    public static function sessionKey(): string
    {
        return config('splade.navigation.up_session_key', 'splade_nav_tabs');
    }

    public static function fallbackUrl(): string
    {
        return url(config('splade.navigation.up_fallback', config('splade.navigation.fallback', '/')));
    }

    /** Normalised path: "/" or "/a/b" (no trailing slash, no query). */
    public static function path(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH) ?: '/';

        return '/' . trim($path, '/');
    }

    public static function section(string $path): string
    {
        return explode('/', trim($path, '/'))[0] ?? '';
    }

    // ------------------------------------------------------------------ session

    public static function stack(Session $session, ?string $tab): array
    {
        if (!$tab) {
            return [];
        }

        return $session->get(static::sessionKey() . '.' . $tab . '.s', []);
    }

    public static function putStack(Session $session, string $tab, array $stack): void
    {
        $key  = static::sessionKey();
        $tabs = $session->get($key, []);

        $max = (int) config('splade.navigation.max_depth', 30);
        if ($max > 0 && count($stack) > $max) {
            $stack = array_slice($stack, -$max);
        }

        $tabs[$tab] = ['t' => time(), 's' => array_values($stack)];

        // Keep only the most recently used tabs.
        $maxTabs = (int) config('splade.navigation.max_tabs', 12);
        if ($maxTabs > 0 && count($tabs) > $maxTabs) {
            uasort($tabs, fn ($a, $b) => ($b['t'] ?? 0) <=> ($a['t'] ?? 0));
            $tabs = array_slice($tabs, 0, $maxTabs, true);
        }

        $session->put($key, $tabs);
    }

    /** Called by SpladeMiddleware for every recordable page view. */
    public static function record(Request $request, Session $session): void
    {
        if (!$tab = static::tabId($request)) {
            return;
        }

        $stack   = static::seedFromReferer($request, static::stack($session, $tab));
        $current = $request->fullUrl();

        if (end($stack) === $current) {
            return;
        }

        $stack[] = $current;
        static::putStack($session, $tab, $stack);
    }

    /**
     * A full page load (first visit, F5, browser back restored without XHR) carries
     * no tab header, so it is not recorded. The next XHR from that tab has the page
     * as its Referer: push it first when it is missing from the stack end.
     */
    public static function seedFromReferer(Request $request, array $stack): array
    {
        $referer = (string) $request->headers->get('referer', '');

        if ($referer === '' || parse_url($referer, PHP_URL_HOST) !== $request->getHost()) {
            return $stack;
        }

        $path = static::path($referer);
        if (!empty($stack) && static::path(end($stack)) === $path) {
            return $stack;
        }

        foreach ((array) config('splade.navigation.except', []) as $pattern) {
            if ($pattern && Str::is(trim($pattern, '/'), trim($path, '/'))) {
                return $stack;
            }
        }

        $stack[] = $referer;

        return $stack;
    }

    // -------------------------------------------------------------------- logic

    /**
     * Pure resolver (unit-testable). Returns [targetUrl, newStack] or null when
     * there is no Up (the current page IS the fallback / home).
     *
     * @param  callable(string $path): bool  $isPage   true when $path is a real GET page
     * @param  array<string,string>          $parents  optional path-glob => parent path overrides
     */
    public static function resolveUp(string $currentUrl, array $stack, callable $isPage, string $fallbackUrl, array $parents = []): ?array
    {
        $current  = static::path($currentUrl);
        $fallback = static::path($fallbackUrl);

        if ($current === $fallback) {
            return null;
        }

        // Drop the step being left (the current page may already be recorded).
        while (!empty($stack) && static::path(end($stack)) === $current) {
            array_pop($stack);
        }

        // 1) Deep page -> nearest real parent (configured override first).
        $parent = null;
        foreach ($parents as $glob => $to) {
            if (Str::is(trim($glob, '/'), trim($current, '/'))) {
                $parent = '/' . trim($to, '/');
                break;
            }
        }

        if ($parent === null) {
            $segments = explode('/', trim($current, '/'));
            for ($i = count($segments) - 1; $i >= 1; $i--) {
                $candidate = '/' . implode('/', array_slice($segments, 0, $i));
                if ($isPage($candidate)) {
                    $parent = $candidate;
                    break;
                }
            }
        }

        if ($parent !== null && $parent !== $current) {
            // Re-use the parent's last visited URL (keeps its filters/page) if known.
            // When the parent was visited, history after it is erased too, so a
            // detour (A -> B -> A/1) can't make Up bounce A -> B -> A.
            for ($k = count($stack) - 1; $k >= 0; $k--) {
                if (static::path($stack[$k]) === $parent) {
                    return [$stack[$k], array_slice($stack, 0, $k)];
                }
            }

            return [static::absolute($parent, $fallbackUrl), $stack];
        }

        // 2) Main page -> last different place, skipping the same section.
        $section = static::section($current);
        for ($j = count($stack) - 1; $j >= 0; $j--) {
            $path = static::path($stack[$j]);
            if ($path === $current || static::section($path) === $section) {
                continue;
            }

            // Going Up LEAVES this section: erase its older visits as well, so
            // A -> B -> A then Up, Up ends at B -> home, never back to A.
            $rest = array_values(array_filter(
                array_slice($stack, 0, $j),
                fn ($entry) => static::section(static::path($entry)) !== $section
            ));

            return [$stack[$j], $rest];
        }

        return [$fallbackUrl, []];
    }

    private static function absolute(string $path, string $reference): string
    {
        $scheme = parse_url($reference, PHP_URL_SCHEME);
        $host   = parse_url($reference, PHP_URL_HOST);
        $port   = parse_url($reference, PHP_URL_PORT);

        if (!$scheme || !$host) {
            return $path;
        }

        return $scheme . '://' . $host . ($port ? ':' . $port : '') . $path;
    }

    /** True when $path matches a real (non-fallback) GET route of the app. */
    public static function isPage(string $path): bool
    {
        if ($path === '/' || $path === '') {
            return false;
        }

        try {
            $route = Route::getRoutes()->match(Request::create($path, 'GET'));
        } catch (Throwable $e) {
            return false;
        }

        if (!$route || $route->isFallback) {
            return false;
        }

        foreach ((array) config('splade.navigation.except', []) as $pattern) {
            if ($pattern && (Str::is($pattern, $route->getName() ?? '') || Str::is(trim($pattern, '/'), trim($path, '/')))) {
                return false;
            }
        }

        return true;
    }

    /** Resolve for a request/tab without mutating anything (used for the label). */
    public static function peek(Request $request, ?string $currentUrl = null): ?array
    {
        return static::resolveUp(
            $currentUrl ?? $request->fullUrl(),
            static::tabId($request)
                ? static::seedFromReferer($request, static::stack($request->session(), static::tabId($request)))
                : [],
            [static::class, 'isPage'],
            static::fallbackUrl(),
            (array) config('splade.navigation.up_parents', [])
        );
    }

    /** Resolve AND pop the tab history. Returns the target URL. */
    public static function go(Request $request, string $currentUrl): string
    {
        $tab    = static::tabId($request);
        $result = static::peek($request, $currentUrl);

        if ($result === null) {
            return static::fallbackUrl();
        }

        [$target, $stack] = $result;

        if ($tab) {
            static::putStack($request->session(), $tab, $stack);
        }

        return $target;
    }

    /** Human label for a target: explicit map (path => label) first, then a guess. */
    public static function label(string $url, array $labels = []): string
    {
        $path = static::path($url);

        foreach ($labels as $key => $label) {
            if (static::path((string) $key) === $path) {
                return $label;
            }
        }

        if ($path === static::path(static::fallbackUrl())) {
            return __(config('splade.navigation.up_fallback_label', 'Dashboard'));
        }

        $segments = explode('/', trim($path, '/'));
        $last     = end($segments);

        if (count($segments) > 1 && preg_match('/^[0-9a-f-]+$/i', $last)) {
            return Str::singular(Str::headline($segments[count($segments) - 2]));
        }

        return Str::headline($last);
    }
}
