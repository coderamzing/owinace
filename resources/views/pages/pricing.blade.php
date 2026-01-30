@extends('layouts.frontend')

@section('title', 'Pricing - ' . config('app.name', 'LeadCliq'))

@push('meta')
<!-- Hreflang -->
<link rel="alternate" hreflang="en" href="{{ url()->current() }}" />
<link rel="alternate" hreflang="x-default" href="{{ url()->current() }}" />

<meta name="description" content="LeadCliq pricing plans. Choose the perfect package for your business. Flexible credit-based pricing for AI proposal generation and lead management.">
<meta name="keywords" content="LeadCliq pricing, CRM pricing, AI proposal costs, lead management plans, subscription plans, credits">
<meta name="author" content="LeadCliq">

<!-- Open Graph / Facebook -->
<meta property="og:type" content="website">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:title" content="Pricing Plans - LeadCliq">
<meta property="og:description" content="LeadCliq pricing plans. Choose the perfect package for your business with flexible credit-based pricing.">
<meta property="og:image" content="{{ asset('images/leadcliq-og.png') }}">

<!-- Twitter -->
<meta property="twitter:card" content="summary_large_image">
<meta property="twitter:url" content="{{ url()->current() }}">
<meta property="twitter:title" content="Pricing Plans - LeadCliq">
<meta property="twitter:description" content="LeadCliq pricing plans. Choose the perfect package for your business with flexible credit-based pricing.">
<meta property="twitter:image" content="{{ asset('images/leadcliq-og.png') }}">
@endpush

@section('content')
<script type="application/ld+json">
@php
echo json_encode([
    "@context" => "https://schema.org",
    "@type" => "BreadcrumbList",
    "itemListElement" => [
        [
            "@type" => "ListItem",
            "position" => 1,
            "name" => "Home",
            "item" => url('/')
        ],
        [
            "@type" => "ListItem",
            "position" => 2,
            "name" => "Pricing",
            "item" => url()->current()
        ]
    ]
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
@endphp
</script>

<script type="application/ld+json">
@php
echo json_encode([
    "@context" => "https://schema.org",
    "@type" => "Product",
    "name" => "LeadCliq",
    "description" => "AI-powered proposal generation and lead management platform",
    "brand" => [
        "@type" => "Brand",
        "name" => "LeadCliq"
    ],
    "offers" => [
        "@type" => "AggregateOffer",
        "priceCurrency" => "USD",
        "offerCount" => count($tiers ?? [])
    ]
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
@endphp
</script>

<section class="bg-body-bg lg:py-25 md:py-22.5 py-17.5">
    <div class="container-medium">
        @if(session('error'))
            <div class="mb-5 p-4 bg-red-100 border border-red-400 text-red-700 rounded-2xl">
                {{ session('error') }}
            </div>
        @endif

        <div class="lg:mb-12.5 text-center mb-7.5 aos-init aos-animate" data-aos="fade-up" data-aos-duration="500" data-aos-easing="ease-in-out">
            <h2 class="mb-2.5 lg:text-6xl md:text-[52px] text-4xl">Simple, Transparent Pricing</h2>
            <p>All plans include unlimited lead CRM, Kanban boards, team management, analytics, and Chrome extension. Pay only for AI proposal credits.</p>
        </div>

        <div class="flex lg:gap-12.5 gap-7.5 flex-col aos-init" data-aos="fade-up" data-aos-duration="500" data-aos-easing="ease-in-out">
            @foreach($tiers as $tier)
            <div class="bg-white rounded-2xl lg:p-12.5 md:p-7.5 py-7.5 px-3.75">
                <div class="grid md:grid-cols-2 md:gap-12.5 gap-5">
                    <!-- Pricing Details -->
                    <div>
                        <h2 class="mb-2.5 text-1.5xl">{{ $tier->title }}</h2>
                        <h4 class="lg:text-4.4xl md:text-4xl text-3xl flex items-center">
                            $ {{ number_format($tier->special_price ?? $tier->price, 2) }} USD 
                            <div class="text-base neutral-700">/month</div>
                            @if($tier->special_price && $tier->special_price < $tier->price)
                                <div class="text-base line-through text-neutral-500 ml-2">$ {{ number_format($tier->price, 2) }}</div>
                            @endif
                        </h4>
                        <p class="lg:mt-12.5 lg:mb-2.5 my-5">{{ $tier->description ?? 'Essential features for your needs' }}</p>

                        @if($tier->max_members)
                            <p class="text-sm text-neutral-600 mb-5">Up to {{ $tier->max_members }} team members</p>
                        @endif

                        @if($tier->max_storage)
                            <p class="text-sm text-neutral-600 mb-5">{{ number_format($tier->max_storage / 1024, 1) }} GB storage</p>
                        @endif

                        <div>
                            @php
                                $tierPrice = $tier->special_price ?? $tier->price;
                                $isCurrentPlan = false;
                                $isUpgrade = false;
                                $isDowngrade = false;
                                
                                if (Auth::check() && $currentTier) {
                                    $currentTierPrice = $currentTier->special_price ?? $currentTier->price;
                                    if ($tier->id === $currentTier->id) {
                                        $isCurrentPlan = true;
                                    } elseif ($tierPrice > $currentTierPrice) {
                                        $isUpgrade = true;
                                    } elseif ($tierPrice < $currentTierPrice) {
                                        $isDowngrade = true;
                                    }
                                }
                            @endphp
                            
                            @if($isCurrentPlan)
                                <button type="button" 
                                        class="py-3.5 lg:px-7.5 px-6.5 inline-flex text-center bg-gray-300 font-medium rounded-2xl text-gray-600 cursor-not-allowed"
                                        disabled>
                                    Current Plan
                                </button>
                            @elseif($isDowngrade)
                                <button type="button" 
                                        class="py-3.5 lg:px-7.5 px-6.5 inline-flex text-center bg-gray-300 font-medium rounded-2xl text-gray-600 cursor-not-allowed"
                                        disabled>
                                    Downgrade Not Available
                                </button>
                            @else
                                <button type="button" 
                                        class="tier-select-btn py-3.5 lg:px-7.5 px-6.5 inline-flex text-center bg-primary font-medium rounded-2xl text-black transition-all duration-300 hover:text-primary hover:bg-black"
                                        data-tier-id="{{ $tier->id }}"
                                        data-tier-title="{{ $tier->title }}"
                                        data-tier-price="{{ number_format($tierPrice, 2) }}"
                                        data-tier-members="{{ $tier->max_members ?? '' }}"
                                        data-tier-description="{{ $tier->description ?? 'Essential features for your needs' }}"
                                        @if($isUpgrade)
                                        data-is-upgrade="true"
                                        data-current-tier-price="{{ number_format($currentTierPrice, 2) }}"
                                        @endif>
                                    {{ $isUpgrade ? 'Upgrade Plan' : 'Get Started' }}
                                </button>
                            @endif
                        </div>
                    </div>

                    <!-- What's Included -->
                    <div>
                        <h4 class="mb-2.5 text-1.5xl">What's included</h4>
                        <div class="flex gap-3.75 flex-col">
                            @if($tier->max_members)
                            <div class="flex gap-2.5">
                                <i class="iconify tabler--circle-check size-5 mt-0.5"></i>
                                <p class="text-base">Up to {{ $tier->max_members }} team members</p>
                            </div>
                            @endif

                            @if($tier->max_storage)
                            <div class="flex gap-2.5">
                                <i class="iconify tabler--circle-check size-5 mt-0.5"></i>
                                <p class="text-base">{{ number_format($tier->max_storage / 1024, 1) }} GB storage space</p>
                            </div>
                            @endif

                            <div class="flex gap-2.5">
                                <i class="iconify tabler--circle-check size-5 mt-0.5"></i>
                                <p class="text-base">Full access to all features</p>
                            </div>

                            <div class="flex gap-2.5">
                                <i class="iconify tabler--circle-check size-5 mt-0.5"></i>
                                <p class="text-base">Priority customer support</p>
                            </div>

                            <div class="flex gap-2.5">
                                <i class="iconify tabler--circle-check size-5 mt-0.5"></i>
                                <p class="text-base">Regular updates and new features</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Premium Checkout Modal -->
<div id="checkout-modal" class="hidden fixed inset-0 bg-opacity-10 overflow-y-auto h-full w-full z-50 backdrop-blur-sm">
    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-5xl w-full overflow-hidden flex flex-col lg:flex-row" style="min-height: 600px;">
            <!-- Left Side - Order Summary (Dark Background) -->
            <div class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-white p-8 lg:p-12 flex flex-col justify-between lg:w-2/5">
                <div>
                    <button type="button" onclick="closeCheckoutModal()" class="text-gray-400 hover:text-white mb-6 transition-colors" aria-label="Close modal">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                    
                    <!-- Product/Plan Display -->
                    <div class="mb-8">
                        <div class="flex items-center justify-center mb-6">
                            <div class="w-20 h-20 bg-primary rounded-xl flex items-center justify-center shadow-lg">
                                <svg class="w-12 h-12 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-2xl font-bold mb-2 text-center" id="modal-tier-title">Premium Plan</h3>
                        <p class="text-gray-300 text-center" id="modal-tier-description">Complete Solution</p>
                    </div>
                    
                    <!-- Quantity and Price -->
                    <div class="mb-6">
                        <div class="flex justify-between items-center mb-4">
                            <span class="text-gray-400">Quantity</span>
                            <span class="text-white font-semibold">1 x $<span id="modal-tier-price">0.00</span></span>
                        </div>
                        <div class="border-t border-gray-700 pt-4">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-300 text-lg">To pay</span>
                                <span class="text-4xl font-bold">$<span id="modal-total-price">0.00</span></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Cancel Button -->
                <button type="button" onclick="closeCheckoutModal()" class="flex items-center justify-center space-x-2 text-gray-400 hover:text-white transition-colors py-3 border border-gray-700 rounded-lg hover:border-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    <span>Cancel your payment</span>
                </button>
            </div>
            
            <!-- Right Side - Payment Form (Light Background) -->
            <div class="bg-white p-8 lg:p-12 flex flex-col justify-between lg:w-3/5">
                <div>
                    <div class="flex justify-between items-center mb-8">
                        <div>
                            <h2 class="text-3xl font-bold text-gray-900 mb-2">Payments</h2>
                            <p class="text-gray-600">Pay with credit card</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="bg-blue-600 text-white px-3 py-1 rounded text-sm font-semibold">VISA</div>
                        </div>
                    </div>
                    
                    <!-- Logged In User Confirmation -->
                    @if(Auth::check())
                        <div id="logged-in-confirmation" class="space-y-6">
                            <form id="logged-in-checkout-form" method="POST" action="{{ route('razorpay.create-order') }}" class="space-y-5">
                                @csrf
                                <input type="hidden" name="tier_id" id="modal-tier-id-logged">
                                
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Confirm Your Details</h3>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="logged-first_name" class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                                        <input type="text" 
                                               name="first_name" 
                                               id="logged-first_name" 
                                               value="{{ Auth::user()->name ? explode(' ', Auth::user()->name)[0] : '' }}" 
                                               required 
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-all">
                                    </div>
                                    <div>
                                        <label for="logged-last_name" class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                                        <input type="text" 
                                               name="last_name" 
                                               id="logged-last_name" 
                                               value="{{ Auth::user()->name && count(explode(' ', Auth::user()->name)) > 1 ? explode(' ', Auth::user()->name)[1] : '' }}" 
                                               required 
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-all">
                                    </div>
                                </div>
                                
                                <div>
                                    <label for="logged-email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                    <input type="email" 
                                           name="email" 
                                           id="logged-email" 
                                           value="{{ Auth::user()->email }}" 
                                           required 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-all">
                                </div>
                                
                                <div>
                                    <label for="logged-phone" class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                                    <input type="tel" 
                                           name="phone" 
                                           id="logged-phone" 
                                           value="{{ Auth::user()->phone_number ?? '' }}" 
                                           required 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-all">
                                </div>
                                
                                <div>
                                    <label for="logged-workspace_name" class="block text-sm font-medium text-gray-700 mb-2">Workspace Name</label>
                                    <input type="text" 
                                           name="workspace_name" 
                                           id="logged-workspace_name" 
                                           value="{{ Auth::user()->workspace->name ?? '' }}" 
                                           required 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-all">
                                </div>
                                
                                <button type="submit" class="w-full bg-primary hover:bg-black text-black hover:text-primary py-4 rounded-2xl font-semibold text-lg transition-all duration-300 shadow-lg">
                                    Pay Now
                                </button>
                            </form>
                        </div>
                    @endif
                    
                    <!-- Guest User Form -->
                    <div id="guest-form" class="{{ Auth::check() ? 'hidden' : '' }} space-y-6">
                        <form id="guest-checkout-form" method="POST" action="{{ route('razorpay.create-order') }}" class="space-y-5">
                            @csrf
                            <input type="hidden" name="tier_id" id="modal-tier-id">
                            <input type="hidden" name="guest" value="true">
                            
                            <div>
                                <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                                <input type="text" name="first_name" id="first_name" placeholder="First Name" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-all">
                            </div>
                            
                            <div>
                                <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                                <input type="text" name="last_name" id="last_name" placeholder="Last Name" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-all">
                            </div>
                            
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                <input type="email" name="email" id="email" placeholder="your@email.com" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-all">
                            </div>
                            
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                                <input type="tel" name="phone" id="phone" placeholder="+1 (555) 000-0000" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-all">
                            </div>
                            
                            <div>
                                <label for="workspace_name" class="block text-sm font-medium text-gray-700 mb-2">Workspace Name</label>
                                <input type="text" name="workspace_name" id="workspace_name" placeholder="Your Workspace" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-all">
                            </div>
                            
                            <button type="submit" class="w-full bg-primary hover:bg-black text-black hover:text-primary py-4 rounded-2xl font-semibold text-lg transition-all duration-300 shadow-lg">
                                Pay Now
                            </button>
                        </form>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Payment Cancelled Modal --}}
<div
    id="payment-cancel-modal"
    class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="bg-white rounded-2xl shadow-xl max-w-md w-full mx-4 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-2">
            Payment Cancelled
        </h3>
        <p class="text-sm text-gray-600 mb-6">
            You closed the payment window before completing the transaction. If this was a mistake, you can try again
            by selecting a plan and clicking <strong>Get Started</strong>.
        </p>
        <div class="flex justify-end">
            <button
                type="button"
                data-cancel-modal-close
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-primary rounded-lg hover:bg-black focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                Close
            </button>
        </div>
    </div>
</div>

{{-- Payment Success Modal --}}
<div
    id="payment-success-modal"
    class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="bg-white rounded-2xl shadow-xl max-w-md w-full mx-4 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-2">
            Payment Successful
        </h3>
        <p class="text-sm text-gray-600 mb-4">
            Thank you! Your payment was completed successfully.
        </p>
        <p class="text-xs text-gray-400 mb-6">
            Payment reference:
            <span class="font-mono" data-payment-id></span>
        </p>
        <div class="flex justify-end gap-3">
            <button
                type="button"
                data-success-modal-close
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300">
                Close
            </button>
            <a
                href="{{ route('dashboard') }}"
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-primary rounded-lg hover:bg-black focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                Go to Dashboard
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://checkout.razorpay.com/v1/magic-checkout.js"></script>
<script>
    // Handle tier selection buttons
    document.addEventListener('DOMContentLoaded', function() {
        const tierButtons = document.querySelectorAll('.tier-select-btn');
        tierButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                const tierId = this.getAttribute('data-tier-id');
                const title = this.getAttribute('data-tier-title');
                const price = parseFloat(this.getAttribute('data-tier-price'));
                const maxMembers = this.getAttribute('data-tier-members');
                const description = this.getAttribute('data-tier-description');
                const isUpgrade = this.getAttribute('data-is-upgrade') === 'true';
                const currentTierPrice = this.getAttribute('data-current-tier-price') ? parseFloat(this.getAttribute('data-current-tier-price')) : null;
                
                selectTier(tierId, title, price, maxMembers, description, isUpgrade, currentTierPrice);
            });
        });

        // Handle form submissions with AJAX
        const loggedInForm = document.getElementById('logged-in-checkout-form');
        const guestForm = document.getElementById('guest-checkout-form');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

        if (loggedInForm) {
            loggedInForm.addEventListener('submit', async function(event) {
                event.preventDefault();
                await handleFormSubmission(this, csrfToken);
            });
        }

        if (guestForm) {
            guestForm.addEventListener('submit', async function(event) {
                event.preventDefault();
                await handleFormSubmission(this, csrfToken);
            });
        }
    });

    async function handleFormSubmission(form, csrfToken) {
        const formData = new FormData(form);
        const submitButton = form.querySelector('button[type="submit"]');
        const originalText = submitButton.textContent;
        
        // Disable button and show loading state
        submitButton.disabled = true;
        submitButton.textContent = 'Processing...';

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData,
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.error || 'Failed to create payment order. Please try again.');
            }

            const data = await response.json();

            // Close the checkout modal
            closeCheckoutModal();

            // Open Razorpay checkout
            const options = {
                key: data.key_id,
                order_id: data.order_id,
                name: data.name || "{{ config('app.name') }}",
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
                    name: "{{ addslashes(Auth::check() ? Auth::user()->name : '') }}",
                    email: "{{ addslashes(Auth::check() ? Auth::user()->email : '') }}",
                },
                notes: {
                    address: "{{ config('app.name') }}"
                },
                theme: {
                    color: "#8165D5"
                },
                modal: {
                    ondismiss: function() {
                        // Show cancellation message
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
        } finally {
            // Re-enable button
            submitButton.disabled = false;
            submitButton.textContent = originalText;
        }
    }

    function selectTier(tierId, title, price, maxMembers, description, isUpgrade = false, currentTierPrice = null) {
        // Update modal with tier information
        document.getElementById('modal-tier-title').textContent = title;
        if (description) {
            document.getElementById('modal-tier-description').textContent = description;
        }
        
        // Calculate display price (upgrade amount or full price)
        let displayPrice = price;
        const priceContainer = document.getElementById('modal-tier-price').parentElement;
        
        if (isUpgrade && currentTierPrice !== null) {
            displayPrice = price - currentTierPrice;
            // Show upgrade info
            priceContainer.innerHTML = `
                <span class="text-white font-semibold">1 x $<span id="modal-tier-price">${displayPrice.toFixed(2)}</span></span>
                <div class="text-xs text-gray-400 mt-1">
                    Upgrade: $${price.toFixed(2)} - $${currentTierPrice.toFixed(2)} = $${displayPrice.toFixed(2)}
                </div>
            `;
        } else {
            document.getElementById('modal-tier-price').textContent = price.toFixed(2);
        }
        
        document.getElementById('modal-total-price').textContent = displayPrice.toFixed(2);
        
        // Set tier ID in forms
        document.getElementById('modal-tier-id').value = tierId;
        const loggedInForm = document.getElementById('modal-tier-id-logged');
        if (loggedInForm) {
            loggedInForm.value = tierId;
        }
        
        // Show modal
        const modal = document.getElementById('checkout-modal');
        if (modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden'; // Prevent background scrolling
        }
    }

    function closeCheckoutModal() {
        const modal = document.getElementById('checkout-modal');
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = ''; // Restore scrolling
        }
    }

    // Close modal when clicking outside
    document.getElementById('checkout-modal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeCheckoutModal();
        }
    });

    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeCheckoutModal();
        }
    });

    // Close handlers for the cancel & success modals
    document.addEventListener('click', function(event) {
        const cancelModal = document.getElementById('payment-cancel-modal');
        const successModal = document.getElementById('payment-success-modal');

        if (cancelModal && (event.target.matches('[data-cancel-modal-close]') || event.target === cancelModal)) {
            cancelModal.classList.add('hidden');
        }

        if (successModal && (event.target.matches('[data-success-modal-close]') || event.target === successModal)) {
            successModal.classList.add('hidden');
        }
    });
</script>
@endpush
@endsection
