<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Complete Payment - {{ config('app.name') }}</title>
    <script src="https://checkout.razorpay.com/v1/magic-checkout.js"></script>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 text-center">
            <div>
                <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                    Processing Payment...
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    Please wait while we redirect you to the payment gateway.
                </p>
            </div>
        </div>
    </div>

    <script>
        var options = {
            "key": "{{ $key_id }}",
            "one_click_checkout": true,
            "name": "{{ config('app.name') }}",
            "order_id": "{{ $order->id }}",
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

        setTimeout(function(){
            var rzp1 = new Razorpay(options);
            rzp1.open();
        }, 500);
    </script>
</body>
</html>

