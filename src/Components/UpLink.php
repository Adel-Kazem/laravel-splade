<?php

namespace ProtoneMedia\Splade\Components;

use Illuminate\Support\Facades\Route;
use Illuminate\View\Component;
use ProtoneMedia\Splade\Navigation\UpNavigator;

/**
 * Header "Up" arrow (<x-splade-up-link>). Goes one level up on deep pages and to
 * the last different place on main pages, popping the tab history (no ping-pong).
 * Renders NOTHING on the home/fallback page. Exposes $label ("Library") to the
 * view; pass :labels="[url-or-path => label]" to name targets (e.g. your nav items),
 * :show-label="false" for an icon-only arrow (the label stays in the title).
 */
class UpLink extends Component
{
    public ?array $resolved = null;

    public function __construct(public array $labels = [], public bool $showLabel = true)
    {
        if (Route::has('splade.navUp')) {
            $this->resolved = UpNavigator::peek(request());
        }
    }

    public function shouldRender(): bool
    {
        return $this->resolved !== null;
    }

    public function render()
    {
        return view('splade::components.up-link', [
            'href'  => route('splade.navUp', ['from' => request()->fullUrl()]),
            'label' => UpNavigator::label($this->resolved[0] ?? '/', $this->labels),
        ]);
    }
}
