<?php

namespace ProtoneMedia\Splade\Http;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use ProtoneMedia\Splade\Navigation\UpNavigator;

/**
 * Header "Up" arrow endpoint (route splade.navUp). Resolves the smart Up target
 * for the page given in ?from= (falls back to the Referer) and pops this tab's
 * history so the user can never bounce between two pages.
 *
 * Device/browser back keeps using NavBackController (plain history).
 *
 * @see \ProtoneMedia\Splade\Navigation\UpNavigator
 */
class NavUpController
{
    public function __invoke(Request $request)
    {
        $from = (string) $request->query('from', '');

        // Only same-host "from" values are trusted; anything else -> Referer.
        if ($from === '' || (parse_url($from, PHP_URL_HOST) && parse_url($from, PHP_URL_HOST) !== $request->getHost())) {
            $from = url()->previous();
        }

        return Redirect::to(UpNavigator::go($request, $from));
    }
}
