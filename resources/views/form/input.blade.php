@php $flatpickrOptions = $flatpickrOptions() @endphp

<SpladeInput
        {{ $attributes->only(['v-if', 'v-show', 'v-for', 'class'])->class(['hidden' => $isHidden()]) }}
        :flatpickr="@js($flatpickrOptions)"
        :js-flatpickr-options="{!! $jsFlatpickrOptions !!}"
        v-model="{{ $vueModel() }}"
        #default="inputScope"
>
    <label class="block">
        <div class="flex items-center">
            @includeWhen($label, 'splade::form.label', ['label' => $label])
            @isset($afterLabel)
                <div>
                    {{ $afterLabel }}
                </div>
            @endisset
        </div>

        <div class="flex rounded-md border border-gray-300 shadow-sm">
            @if($prepend)
                <span :class="{ 'opacity-50': inputScope.disabled && @json(!$alwaysEnablePrepend) }" class="inline-flex items-center px-3 rounded-l-md border border-t-0 border-b-0 border-l-0 border-gray-300 bg-gray-50 text-gray-500">
                    {!! $prepend !!}
                </span>
            @endif

            <input {{ $attributes->except(['v-if', 'v-show', 'v-for', 'class'])->class([
                'input input-sm block border-gray-400 w-full',
                'rounded-md' => !$append && !$prepend,
                'min-w-0 flex-1 rounded-none' => $append || $prepend,
                'rounded-l-md' => $append && !$prepend,
                'rounded-r-md' => !$append && $prepend,
            ])->merge([
                'name' => $name,
                'type' => $type,
                'data-validation-key' => $validationKey(),
            ])->when(!$flatpickrOptions, fn($attributes) => $attributes->merge([
                'v-model' => $vueModel(),
            ])) }}
            />

            @if($append)
                <span :class="{ 'opacity-50': inputScope.disabled && @json(!$alwaysEnableAppend) }" class="inline-flex items-center px-3 rounded-r-md border border-t-0 border-b-0 border-r-0 border-gray-300 bg-gray-50 text-gray-500">
                    {!! $append !!}
                </span>
            @endif
        </div>
    </label>

    @include('splade::form.help', ['help' => $help])
    @includeWhen($showErrors, 'splade::form.error', ['name' => $validationKey()])
</SpladeInput>
