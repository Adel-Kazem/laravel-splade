<x-splade-link :href="$target" {{ $attributes->merge(['title' => 'Go back']) }}>
    @if(trim($slot) === '')
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
    @else
        {{ $slot }}
    @endif
</x-splade-link>
