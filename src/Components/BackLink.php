<?php

namespace ProtoneMedia\Splade\Components;

use Illuminate\Support\Facades\Route;
use Illuminate\View\Component;

/**
 * Reusable in-app "back" control (<x-splade-back-link>). Routes through the
 * navigation-history stack so it returns to the previous real page, never a
 * modal/slideover overlay. Styling-agnostic: pass classes/attributes and an
 * optional slot for a custom icon/label.
 */
class BackLink extends Component
{
    public function __construct(public ?string $href = null)
    {
    }

    public function render()
    {
        return view('splade::components.back-link', [
            'target' => $this->href
                ?? (Route::has('splade.navBack') ? route('splade.navBack') : '#'),
        ]);
    }
}
