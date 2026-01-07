<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <h1 class="text-3xl font-bold tracking-tight text-gray-950 dark:text-white">
                Complete Payment
            </h1>
        </div>

        <!-- Processing Payment Card -->
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="fi-section-content p-6">
                <div class="p-8 md:p-12">
                    <div class="max-w-md mx-auto text-center space-y-6">
                        <!-- Icon -->
                        <div class="flex justify-center">
                            <div class="rounded-full bg-primary-100 dark:bg-primary-900/20 p-4">
                                <svg class="w-16 h-16 text-primary-600 dark:text-primary-400 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                </svg>
                            </div>
                        </div>

                        <!-- Heading -->
                        <div>
                            <h2 class="text-3xl font-extrabold text-gray-900 dark:text-white">
                                Processing Payment...
                            </h2>
                            <p class="mt-3 text-base text-gray-600 dark:text-gray-400">
                                Please wait while we redirect you to the payment gateway.
                            </p>
                        </div>

                        <!-- Loading Spinner -->
                        <div class="flex justify-center pt-4">
                            <div class="flex space-x-2">
                                <div class="w-3 h-3 bg-primary-600 dark:bg-primary-400 rounded-full animate-bounce" style="animation-delay: 0s"></div>
                                <div class="w-3 h-3 bg-primary-600 dark:bg-primary-400 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                                <div class="w-3 h-3 bg-primary-600 dark:bg-primary-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                            </div>
                        </div>

                        <!-- Security Notice -->
                        <div class="pt-4">
                            <div class="flex items-center justify-center text-sm text-gray-500 dark:text-gray-400">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                                </svg>
                                Secure payment powered by Razorpay
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Details (Optional) -->
        @if($amount)
            <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="fi-section-content p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Order Details
                    </h3>
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600 dark:text-gray-400">Order ID:</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ $orderId ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600 dark:text-gray-400">Amount:</span>
                            <span class="font-medium text-gray-900 dark:text-white">
                                ${{ number_format($amount / 100, 2) }}
                            </span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600 dark:text-gray-400">Currency:</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ strtoupper($currency ?? 'USD') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Razorpay Script -->
    <script src="https://checkout.razorpay.com/v1/magic-checkout.js"></script>
    <script>
        var options = {
            "key": "{{ $keyId }}",
            "one_click_checkout": true,
            "name": "{{ config('app.name') }}",
            "order_id": "{{ $orderId }}",
            "show_coupons": false,
            "handler": function (response) {
                // On success, redirect to success page
                window.location.href = "{{ route('razorpay.success') }}?payment_id=" + response.razorpay_payment_id;
            },
            "prefill": {
                @if($user)
                "name": "{{ $user->name }}",
                "email": "{{ $user->email }}",
                @else
                "name": "",
                "email": "",
                @endif
            },
            "notes": {
                "address": "{{ config('app.name') }}"
            },
            "theme": { "color": "#8165D5" },
            "modal": {
                "ondismiss": function () {
                    window.location.href = "{{ route('razorpay.cancel') }}";
                }
            }
        };

        // Auto-open Razorpay checkout after 500ms
        setTimeout(function(){
            var rzp1 = new Razorpay(options);
            rzp1.open();
        }, 500);
    </script>
</x-filament-panels::page>
