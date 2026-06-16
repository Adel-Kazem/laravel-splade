
<SpladeToast
        v-bind:auto-dismiss="@json($autoDismiss)"
        #default="toast"
>
    <x-splade-component
            is="transition"
            appear
            show="toast.show"
    >
        <x-splade-component
                is="transition"
                child
                after-leave="toast.emitDismiss"
        >
            @php
                $uniqueId = 'countdown-border-' . uniqid();
                $uniqueTimestamp = microtime(true) * 1000; // Current timestamp in milliseconds
                $autoDismissMs = isset($autoDismiss) ? $autoDismiss * 1000 : null; // Convert to milliseconds if set, otherwise null
            @endphp

            {{-- Content-sized toast. The only non-standard utility is the mobile width cap
                 max-w-[calc(100vw-2rem)] (viewport minus the wrapper's 1rem gutter each side)
                 which guarantees it can never exceed the screen on a phone. On >=sm it caps at
                 max-w-sm. Everything else is standard Tailwind. --}}
            <div
                    @class([
            'relative w-auto max-w-[calc(100vw-2rem)] sm:max-w-sm p-4 rounded-2xl border shadow-2xl bg-white dark:bg-stone-900 pointer-events-auto',
            'border-indigo-500' => $isSuccess,
            'border-yellow-500' => $isWarning,
            'border-stone-200 dark:border-stone-800' => $isInfo,
            'border-red-600' => $isDanger,
            ])
            >
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0">
                        @if($isSuccess)
                            <div class="w-8 h-8 rounded-full flex items-center justify-center bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        @elseif($isWarning)
                            <div class="w-8 h-8 rounded-full flex items-center justify-center bg-yellow-50 text-yellow-600 dark:bg-yellow-500/10 dark:text-yellow-400">
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        @elseif($isDanger)
                            <div class="w-8 h-8 rounded-full flex items-center justify-center bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400">
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        @elseif($isInfo)
                            <div class="w-8 h-8 rounded-full flex items-center justify-center bg-stone-100 text-stone-600 dark:bg-stone-800 dark:text-stone-400">
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        @endif
                    </div>

                    <div class="flex-1 min-w-0 break-words">
                        <h3 class="text-sm font-semibold text-stone-900 dark:text-stone-100">
                            {!! nl2br(e($title ?: $message)) !!}
                        </h3>

                        @if($title && $message)
                            <p class="mt-0.5 text-xs text-stone-500 dark:text-stone-400">
                                {!! nl2br(e($message)) !!}
                            </p>
                        @endif
                    </div>

                    <button
                            id="close-button-{{ $uniqueId }}"
                            type="button"
                            @click.prevent="toast.setShow(false)"
                            class="flex-shrink-0 p-1 text-stone-400 hover:text-stone-600 dark:hover:text-stone-200"
                    >
                        <span class="sr-only">Dismiss Toast</span>
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>

                {{-- Progress bar clipped to the card's rounded bottom corners (overflow-hidden +
                     rounded-b-2xl), so the square countdown bar never pokes past the box. --}}
                <div class="absolute bottom-0 left-0 right-0 px-4 h-0.5 overflow-hidden rounded-b-2xl pointer-events-none">
                    <div id="{{ $uniqueId }}" class="h-full opacity-60"
                         :class="{
                            'bg-indigo-600': @json($isSuccess),
                            'bg-yellow-500': @json($isWarning),
                            'bg-red-600': @json($isDanger),
                            'bg-stone-400': @json($isInfo),
                        }"
                    ></div>
                </div>
            </div>

            <x-splade-script>
                (function() {
                    const startTime = {{ $uniqueTimestamp }};
                    const duration = {{ $autoDismissMs ?? 'null' }}; // Duration in milliseconds, or null if not set

                    const borderElement = document.querySelector('#{{ $uniqueId }}');
                    const closeButton = document.querySelector('#close-button-{{ $uniqueId }}');

                    if (duration !== null) {
                        const endTime = startTime + duration;

                        function updateCountdown() {
                            const now = Date.now();
                            const timeLeft = Math.max(endTime - now, 0);
                            const percentageLeft = (timeLeft / duration) * 100;

                            if (borderElement) {
                                borderElement.style.width = percentageLeft + "%";
                            }

                            if (timeLeft > 0) {
                                requestAnimationFrame(updateCountdown);
                            } else if (closeButton) {
                                closeButton.click();
                            }
                        }

                        updateCountdown();
                    } else if (borderElement) {
                        // No auto-dismiss: hide the countdown bar
                        borderElement.style.display = 'none';
                    }
                })();
            </x-splade-script>


        </x-splade-component>
    </x-splade-component>
</SpladeToast>
