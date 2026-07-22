<?php

namespace ProtoneMedia\Splade\Components;

use Illuminate\View\Component;
use ProtoneMedia\Splade\SpladeCore;

class Rehydrate extends Component
{
    use PassesVueVariablesThrough;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        public SpladeCore $splade,
        public array|string $on = '',
        public array|string $passthrough = '',
        public ?string $name = null
    ) {
        if (is_string($on)) {
            $this->on = Form::splitByComma($on);
        }

        $this->passthrough = implode(',', Form::splitByComma($passthrough));
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        // A custom name makes the key stable and addressable (per-item surgical
        // rehydrate); unnamed components keep the positional auto-counter, which
        // named siblings must NOT consume — otherwise adding a named item would
        // shift every auto key on the page.
        $key = $this->name ?? $this->splade->newRehydrateComponentKey();

        // Only the TARGETED component collapses into extraction markers. Every
        // other Rehydrate component renders normally, so a component nested
        // inside the extracted slice survives as a live component after the
        // rehydrate (previously ALL components collapsed, which silently killed
        // nested Rehydrate components on the first refresh of their parent).
        $isTarget = $this->splade->isRehydrateRequest()
            && $this->splade->getRehydrateComponentKey() === (string) $key;

        return $isTarget
            ? implode([
                '<!--START-SPLADE-REHYDRATE-' . $key . '-->',
                '{{ $slot }}',
                '<!--END-SPLADE-REHYDRATE-' . $key . '-->',
            ]) : view('splade::functional.rehydrate', [
                'name' => $key,
                'on'   => $this->on,
            ]);
    }
}
