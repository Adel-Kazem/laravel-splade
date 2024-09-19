@props([
    'label' => 'Submit',
    'type' => 'primary',
    'size' => 'default',
    'fullWidth' => false,
    'id' => null,
    'name' => null,
    'value' => null,
    'spinner' => true,
])

@php
    $buttonClasses = '';
    $sizeClasses = '';

    // Determine button size classes
    switch ($size) {
        case 'large':
            $sizeClasses = 'px-4 py-2 text-sm';
            break;
        default:
            $sizeClasses = 'px-3 py-1.5 text-xs';
            break;
    }

    // Determine button type classes
    switch ($type) {
        case 'primary':
            $buttonClasses = 'primary-button buttonPressable primaryButtonShadow text-white rounded-lg font-semibold';
            break;
        case 'icon':
            $buttonClasses = 'bg-white buttonPressable hover:bg-neutral-50 active:bg-[#f7f7f7] boxShadowNormal text-[#303030] rounded-lg font-semibold flex items-center gap-2';
            break;
        case 'white':
            $buttonClasses = 'bg-white buttonPressable hover:bg-neutral-50 active:bg-[#f7f7f7] boxShadowNormal text-[#303030] rounded-lg font-semibold';
            break;
        case 'danger':
            $buttonClasses = 'bg-red-600 hover:bg-red-700 active:bg-red-800 buttonPressable text-white rounded-lg font-semibold';
            break;
        default:
            $buttonClasses = 'primary-button buttonPressable primaryButtonShadow text-white rounded-lg font-semibold';
            break;
    }

    // Combine type and size classes
    $buttonClasses .= ' ' . $sizeClasses;

    // Add full width class if needed
    if ($fullWidth) {
        $buttonClasses .= ' w-full';
    }
@endphp

<div @class($wrapperClass)>
    <button id="{{ $id }}" class="{{ $buttonClasses }}" {{ $attributes->merge([
    ])->when($name, fn($attr) => $attr->merge(['name' => $name, 'value' => $value])) }}>
        @if(trim($slot))
            {{ $slot }}
        @else
            <div class="flex flex-row items-center justify-center">
                <svg
                        v-if="@js($spinner) && form.processing"
                        class="animate-spin mr-3 h-4 @if($type === 'white') text-gray-700 @else text-white @endif" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                >
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                </svg>

                <span :class="{ 'opacity-50': form.processing || form.$uploading }">
                    {{ $label }}
                </span>
            </div>
        @endif
    </button>
</div>
