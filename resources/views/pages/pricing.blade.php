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
                            <a href="{{ route('register') }}" class="py-3.5 lg:px-7.5 px-6.5 inline-flex text-center bg-primary font-medium rounded-2xl text-black transition-all duration-300 hover:text-primary hover:bg-black">
                                Get Started
                            </a>
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
@endsection
