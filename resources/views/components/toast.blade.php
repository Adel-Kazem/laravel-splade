
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


            <div
                    @class([
            'p-3 pointer-events-auto border-l-4 shadow-md min-w-[240px] relative border rounded-xl',
            'bg-green-500 border-green-500' => $isSuccess,
            'bg-yellow-400 border-yellow-400' => $isWarning,
            'bg-blue-300 border-blue-300' => $isInfo,
            'bg-red-600 border-red-600' => $isDanger,
            ])
            >
            <div class="flex">
                <div class="flex-shrink-0">
                    @if($isSuccess)
                        <svg class="h-5 w-5 text-black" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    @elseif($isWarning)
                        <svg class="h-5 w-5 fill-black" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    @elseif($isDanger)
                        <svg class="h-5 w-5 fill-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    @elseif($isInfo)
                        <svg class="h-5 w-5 fill-black" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    @endif
                </div>
                <div class="ml-3 break-words">
                    <h3
                            @class([
                    'text-sm font-medium',
                    'text-black' => $isSuccess || $isWarning || $isInfo,
                    'text-white' => $isDanger,
                    ])
                    >
                    {!! nl2br(e($title ?: $message)) !!}
                    </h3>

                    @if($title && $message)
                        <div
                                @class([
                        'mt-2 text-sm',
                        'text-black' => $isSuccess || $isWarning || $isInfo,
                        'text-white' => $isDanger,
                        ])
                        >
                        <p>{!! nl2br(e($message)) !!}</p>
                </div>
                @endif
            </div>

            <div class="ml-auto pl-3">
                <div class="-mx-1.5 -my-1.5">
                    <button
                            id="close-button-{{ $uniqueId }}"
                            type="button"
                            @click.prevent="toast.setShow(false)"
                            @class([
                    'inline-flex rounded-md p-1.5 focus:outline-none focus:ring-2 focus:ring-offset-2',
                    'bg-green-500 text-black hover:bg-green-600 focus:ring-offset-green-500 focus:ring-green-600' => $isSuccess,
                    'bg-yellow-400 text-black hover:bg-yellow-500 focus:ring-offset-yellow-400 focus:ring-yellow-500' => $isWarning,
                    'bg-red-600 text-white hover:bg-red-700 focus:ring-offset-red-600 focus:ring-red-700' => $isDanger,
                    'bg-blue-300 text-black hover:bg-blue-400 focus:ring-offset-blue-300 focus:ring-blue-400' => $isInfo,
                    ])
                    >
                    <span class="sr-only">Dismiss Toast</span>
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                    </button>
                </div>
            </div>
            </div>
            <div id="{{ $uniqueId }}" class="absolute bottom-0 left-0 right-0 h-1 transition-all duration-1000 ease-linear"
                 :class="{
                        'bg-green-600': @json($isSuccess),
                        'bg-yellow-500': @json($isWarning),
                        'bg-red-700': @json($isDanger),
                        'bg-blue-400': @json($isInfo),
                    }"
                 style="width: 100%;"
            ></div>
            </div>

            <x-splade-script>
                (function() {
                    const toastId = '{{ $uniqueId }}';
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
                            } else {
                                if (closeButton) {
                                    closeButton.click();
                                }
                            }
                        }

                        // Start the countdown immediately
                        updateCountdown();
                    } else {
                        // If no auto-dismiss, hide the countdown border
                        if (borderElement) {
                            borderElement.style.display = 'none';
                        }
                    }
                })();
            </x-splade-script>


        </x-splade-component>
    </x-splade-component>
</SpladeToast>
