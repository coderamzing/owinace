<x-filament-panels::page>
    <div>
        <div class="space-y-6">
            <!-- Error Messages -->
            @if ($errors->any())
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                            There were errors with your request:
                        </h3>
                        <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                            <ul class="list-disc list-inside space-y-1">
                                @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            @if (session('error'))
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                <p class="text-sm text-red-800 dark:text-red-200">{{ session('error') }}</p>
            </div>
            @endif

            @if (session('success'))
            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                <p class="text-sm text-green-800 dark:text-green-200">{{ session('success') }}</p>
            </div>
            @endif

            <!-- Current Balance -->
            @if($user && $user->workspace)
            <div class="mt-8 p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
                <div class="text-center">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Current Workspace Balance
                    </p>
                    <p class="text-2xl font-bold text-[#b8ff90] mt-1">
                        {{ $user->workspace->totalCredits() }} Credits
                    </p>
                </div>
            </div>
            @endif

            <!-- Header -->
            <div class="text-center">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Choose Your Credit Package
                </h2>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Select the credit package that best fits your needs
                </p>
            </div>
            

            <!-- Credit Packages Grid (Pricing Cards) -->
            <div class="grid lg:grid-cols-4 gap-10 md:items-center md:justify-center mt-8">
                @foreach($packages as $package)
                <div
                    class="md:p-10 p-5 rounded-2xl md:gap-20 flex flex-col lg:justify-between h-full
                        {{ $package['popular'] ? 'bg-primary' : 'bg-white' }}
                         {{ $package['popular'] ? 'dark:bg-primary' : 'dark:bg-gray-800' }}
                         shadow-[2px_1px_10px_#e3e3e3] hover:shadow-[2px_1px_10px_#E5E5E5] dark:shadow-[2px_1px_10px_#706f6fa1] transition-shadow duration-300">
                    <div>
                        <div class="gap-5 flex items-center mb-5">
                            <div class="lg:size-13.5 size-12.5 rounded-full flex items-center justify-center {{ $package['popular'] ? 'bg-dark' : 'bg-primary' }}">
                                <i class="iconify solar--chart-square-linear lg:size-7.5 size-6.5 {{ $package['popular'] ? 'text-primary' : 'text-dark' }}"></i>
                            </div>

                            <h3 class="text-1.5xl {{ $package['popular'] ? 'text-black' : 'text-black' }}
                            {{ $package['popular'] ? 'dark:text-black' : 'dark:text-white' }}">
                                {{ $package['name'] ?? ($package['credits'] . ' Credits') }}
                            </h3>
                        </div>

                        <h4 class="{{ $package['popular'] ? 'text-black' : 'text-black' }} lg:text-4.5xl text-4xl flex items-center
                         {{ $package['popular'] ? 'dark:text-black' : 'dark:text-white' }}">
                            <span>$</span>
                            <span>
                                {{ number_format($package['price'], 2) }}
                            </span>
                            <span class="text-base ml-1">/package</span>
                        </h4>

                        <div class="mt-5">
                            <div class="flex gap-2.5 mb-2.5 items-center">
                                <i class="iconify tabler--circle-check size-6 {{ $package['popular'] ? 'text-black' : 'text-black' }}
                                {{ $package['popular'] ? 'dark:text-black' : 'dark:text-white' }}"></i>
                                <div class="text-base {{ $package['popular'] ? 'dark:text-black' : 'dark:text-white' }}">
                                    {{ $package['description'] }}
                                </div>
                            </div>

                            <div class="flex gap-2.5 mb-2.5 items-center">
                                <i class="iconify tabler--circle-check size-6 {{ $package['popular'] ? 'text-black' : 'text-black' }}
                                {{ $package['popular'] ? 'dark:text-black' : 'dark:text-white' }}"></i>
                                <div class="text-base {{ $package['popular'] ? 'dark:text-black' : 'dark:text-white' }}">
                                    {{ $package['credits'] }} total credits
                                </div>
                            </div>

                            <div class="flex gap-2.5 mb-2.5 items-center">
                                <i class="iconify tabler--circle-check size-6 {{ $package['popular'] ? 'text-black' : 'text-black' }}
                                {{ $package['popular'] ? 'dark:text-black' : 'dark:text-white' }}"></i>
                                <div class="text-base {{ $package['popular'] ? 'dark:text-black' : 'dark:text-white' }}">
                                    ${{ number_format($package['price'] / $package['credits'], 2) }} per credit
                                </div>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('razorpay.create-credit-order') }}" method="POST" class="js-credit-form mt-6">
                        @csrf
                        <input type="hidden" name="credits" value="{{ $package['credits'] }}">
                        <input type="hidden" name="amount" value="{{ $package['price'] }}">

                        <button
                            type="submit"
                            class="py-3.5 lg:px-7.5 px-6.5 w-full text-center bg-dark font-medium rounded-2xl text-white transition-all duration-300 hover:text-primary">
                            Purchase Now
                        </button>
                    </form>
                </div>
                @endforeach
            </div>

            
        </div>


        @push('scripts')
        <script src="https://checkout.razorpay.com/v1/magic-checkout.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const forms = document.querySelectorAll('.js-credit-form');
                const csrfToken = '{{ csrf_token() }}';

                forms.forEach((form) => {
                    form.addEventListener('submit', async function(event) {
                        event.preventDefault();

                        const formData = new FormData(form);

                        try {
                            const response = await fetch(form.action, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': csrfToken,
                                    'Accept': 'application/json',
                                },
                                body: formData,
                            });

                            if (!response.ok) {
                                throw new Error('Failed to create payment order. Please try again.');
                            }

                            const data = await response.json();

                            const options = {
                                key: data.key_id,
                                order_id: data.order_id,
                                name: data.name || '{{ config('
                                app.name ') }}',
                                amount: data.amount,
                                currency: data.currency || 'USD',
                                one_click_checkout: true,
                                show_coupons: false,
                                handler: function(res) {
                                    // Show success modal instead of redirecting
                                    const successModal = document.getElementById('payment-success-modal');
                                    if (successModal) {
                                        const idSpan = successModal.querySelector('[data-payment-id]');
                                        if (idSpan) {
                                            idSpan.textContent = res.razorpay_payment_id || '';
                                        }
                                        successModal.classList.remove('hidden');
                                    } else {
                                        // Fallback to redirect if modal not found
                                        window.location.href = data.success_url + '?payment_id=' + res.razorpay_payment_id;
                                    }
                                },
                                prefill: {
                                    @if(Auth::user())
                                    name: "{{ Auth::user()->name }}",
                                    email: "{{ Auth::user()->email }}",
                                    @else
                                    name: "",
                                    email: "",
                                    @endif
                                },
                                notes: {
                                    address: "{{ config('app.name') }}"
                                },
                                theme: {
                                    color: "#8165D5"
                                },
                                modal: {
                                    ondismiss: function() {
                                        // Show cancellation modal instead of redirecting
                                        const cancelModal = document.getElementById('payment-cancel-modal');
                                        if (cancelModal) {
                                            cancelModal.classList.remove('hidden');
                                        }
                                    }
                                }
                            };

                            const rzp = new Razorpay(options);
                            rzp.open();
                        } catch (error) {
                            console.error(error);
                            alert(error.message || 'Something went wrong. Please try again.');
                        }
                    });
                });
            });

            // Close handlers for the cancel modal
            document.addEventListener('click', function(event) {
                const modal = document.getElementById('payment-cancel-modal');
                if (!modal) return;

                if (event.target.matches('[data-cancel-modal-close]') || event.target === modal) {
                    modal.classList.add('hidden');
                }
            });

            // Close handlers for the success modal
            document.addEventListener('click', function(event) {
                const modal = document.getElementById('payment-success-modal');
                if (!modal) return;

                if (event.target.matches('[data-success-modal-close]') || event.target === modal) {
                    modal.classList.add('hidden');
                    // Reload page to show updated credits
                    window.location.reload();
                }
            });
        </script>
        @endpush

        {{-- Payment Cancelled Modal --}}
        <div
            id="payment-cancel-modal"
            class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-md w-full mx-4 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                    Payment Cancelled
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-300 mb-6">
                    You closed the payment window before completing the transaction. If this was a mistake, you can try again
                    by selecting a package and clicking **Purchase Now**.
                </p>
                <div class="flex justify-end">
                    <button
                        type="button"
                        data-cancel-modal-close
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-dark rounded-lg hover:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-dark">
                        Close
                    </button>
                </div>
            </div>
        </div>

        {{-- Payment Success Modal --}}
        <div
            id="payment-success-modal"
            class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-md w-full mx-4 p-6">
                <div class="flex items-center justify-center mb-4">
                    <div class="w-16 h-16 bg-green-100 dark:bg-green-900/20 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 text-center">
                    Payment Successful
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-300 mb-4 text-center">
                    Thank you! Your credits have been added to your account successfully.
                </p>
                <p class="text-xs text-gray-400 dark:text-gray-500 mb-6 text-center">
                    Payment reference:
                    <span class="font-mono block mt-1" data-payment-id></span>
                </p>
                <div class="flex justify-end gap-3">
                    <button
                        type="button"
                        data-success-modal-close
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>