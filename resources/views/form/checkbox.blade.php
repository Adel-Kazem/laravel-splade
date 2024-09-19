@props([
    'name',
    'value' => 1,
    'falseValue' => 0,
    'label' => '',
    'help' => false,
    'showErrors' => true,
    'afterLabel' => ''
])

<div {{ $attributes->only(['v-if', 'v-show', 'class']) }}>
    <label class="flex items-center">
        <input {{ $attributes->except(['v-if', 'v-show', 'class'])->class('checkbox checkbox-sm')->merge([
            'name' => $name,
            'type' => 'checkbox',
            'v-model' => $vueModel(),
            'data-validation-key' => $validationKey(),
        ]) }} :true-value="{{ $value }}" :false-value="@js($falseValue)" />

        @if(trim($slot))
            <span class="ml-2">{{ $slot }}</span>
        @else
            <span class="ml-2">{{ $label }}</span>
        @endif

        @isset($afterLabel)
            <div>
                {{ $afterLabel }}
            </div>
        @endisset
    </label>

    @includeWhen($help, 'splade::form.help', ['help' => $help])
    @includeWhen($showErrors, 'splade::form.error', ['name' => $validationKey()])
</div>
