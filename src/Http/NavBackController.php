<?php

namespace ProtoneMedia\Splade\Http;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

/**
 * Steps the user back through the server-side navigation history recorded by
 * SpladeMiddleware. Because the history never contains modal/slideover overlays
 * or partial reloads, "back" always returns to the previous real page.
 *
 * @see \ProtoneMedia\Splade\Http\SpladeMiddleware::recordNavigationHistory()
 */
class NavBackController
{
    public function __invoke(Request $request)
    {
        $key   = config('splade.navigation.session_key', 'splade_nav_history');
        $stack = $request->session()->get($key, []);

        $current = url()->previous();

        // Pop any trailing entries pointing at the current page so we step truly
        // "back" — not to wherever we just came from on a redirect.
        while (! empty($stack) && end($stack) === $current) {
            array_pop($stack);
        }

        $target = array_pop($stack) ?: url(config('splade.navigation.fallback', '/'));

        $request->session()->put($key, $stack);

        return Redirect::to($target);
    }
}
