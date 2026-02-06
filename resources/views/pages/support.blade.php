@extends('layouts.frontend')

@section('title', 'Support - ' . config('app.name', 'LeadCliq'))

@push('meta')
<!-- Hreflang -->
<link rel="alternate" hreflang="en" href="{{ url()->current() }}" />
<link rel="alternate" hreflang="x-default" href="{{ url()->current() }}" />

<meta name="description" content="LeadCliq Support Center. Get help with AI proposals, lead management, analytics, and more. Access documentation and contact our support team.">
<meta name="keywords" content="LeadCliq support, help center, documentation, customer service, AI proposals help, CRM support">
<meta name="author" content="LeadCliq">

<!-- Open Graph / Facebook -->
<meta property="og:type" content="website">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:title" content="Support Center - LeadCliq">
<meta property="og:description" content="LeadCliq Support Center. Get help with AI proposals, lead management, analytics, and more.">
<meta property="og:image" content="{{ asset('images/leadcliq-og.png') }}">

<!-- Twitter -->
<meta property="twitter:card" content="summary_large_image">
<meta property="twitter:url" content="{{ url()->current() }}">
<meta property="twitter:title" content="Support Center - LeadCliq">
<meta property="twitter:description" content="LeadCliq Support Center. Get help with AI proposals, lead management, analytics, and more.">
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
                "name" => "Support",
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
        "@type" => "WebPage",
        "name" => "Support Center - LeadCliq",
        "description" => "LeadCliq Support Center and Documentation",
        "url" => url()->current()
    ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
@endphp
</script>

<section class="w-full lg:py-25 md:py-22.5 py-17.5">
    <div class="max-w-7xl mx-auto px-5">
        <!-- Header -->
        <div class="text-center lg:mb-12.5 mb-7.5 aos-init aos-animate" data-aos="fade-up" data-aos-duration="500" data-aos-easing="ease-in-out">
            <h1 class="lg:text-6xl md:text-5.5xl text-4xl mb-2.5">Support Center</h1>
            <p class="text-base text-dark">    
                Get help with LeadCliq's AI proposals, lead CRM, Chrome extension, team management, and billing
            </p>
        </div>

        <div class="max-w-4xl mx-auto">
            <!-- Contact Support Section -->
            <div class="bg-white rounded-2xl p-8 mb-8 shadow-sm">
                <div class="text-center mb-8">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-body-bg rounded-full mb-4">
                        <i class="fas fa-headset text-dark text-2xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold mb-2 text-black">Get in Touch</h2>
                    <p class="text-dark">Our support team is ready to assist you</p>
                </div>

                <div class="grid md:grid-cols-2 gap-6">
                    <div class="bg-body-bg rounded-xl p-6">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center w-12 h-12 bg-white rounded-lg shadow-sm">
                                    <i class="fas fa-envelope text-dark text-xl"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-black mb-2">Email Support</h3>
                                <p class="text-dark text-sm mb-2">Get help via email</p>
                                <a href="mailto:support@leadcliq.ai" class="text-dark underline font-medium">
                                    support@leadcliq.ai
                                </a>
                                <p class="text-xs text-neutral-600 mt-2">Response time: 24-48 hours</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-body-bg rounded-xl p-6">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center w-12 h-12 bg-white rounded-lg shadow-sm">
                                    <i class="fas fa-phone text-dark text-xl"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-black mb-2">Phone Support</h3>
                                <p class="text-dark text-sm mb-2">Call us directly</p>
                                <a href="tel:+917710113366" class="text-dark underline font-medium">
                                    +91 7710113366
                                </a>
                                <p class="text-xs text-neutral-600 mt-2">Mon-Fri, 9:00 AM - 6:00 PM IST</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Help Topics -->
            <div class="bg-white rounded-2xl p-8 mb-8 shadow-sm">
                <h2 class="text-2xl font-bold mb-6 text-black">Quick Help Topics</h2>
                <div class="space-y-4">
                    <a href="{{ route('faq') }}" class="block p-4 bg-body-bg hover:bg-white rounded-lg transition-colors">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <i class="fas fa-question-circle text-dark text-xl mr-4"></i>
                                <div>
                                    <h3 class="font-semibold text-black">Frequently Asked Questions</h3>
                                    <p class="text-sm text-dark">Find answers to common questions</p>
                                </div>
                            </div>
                            <i class="fas fa-chevron-right text-neutral-500"></i>
                        </div>
                    </a>

                    <div class="block p-4 bg-body-bg rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-book text-dark text-xl mr-4"></i>
                            <div>
                                <h3 class="font-semibold text-black">Getting Started Guide</h3>
                                <p class="text-sm text-dark">Learn how to use {{ config('app.name', 'LeadCliq') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="block p-4 bg-body-bg rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-credit-card text-dark text-xl mr-4"></i>
                            <div>
                                <h3 class="font-semibold text-black">Billing & Credits</h3>
                                <p class="text-sm text-dark">Understand our credit system and pricing</p>
                            </div>
                        </div>
                    </div>

                    <div class="block p-4 bg-body-bg rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-shield-alt text-dark text-xl mr-4"></i>
                            <div>
                                <h3 class="font-semibold text-black">Account & Security</h3>
                                <p class="text-sm text-dark">Manage your account settings and security</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Support Hours -->
            <div class="bg-dark rounded-2xl p-8 text-white shadow-lg">
                <div class="flex items-center justify-between flex-wrap gap-4">
                    <div>
                        <h3 class="text-2xl font-bold mb-2 text-white/80!">Support Hours</h3>
                        <p class="text-white/80">Monday - Friday: 9:00 AM - 6:00 PM (IST)</p>
                        <p class="text-white/80">Saturday - Sunday: Closed</p>
                    </div>
                    <div class="text-center">
                        <div class="inline-flex items-center justify-center w-20 h-20 bg-white/20 rounded-full backdrop-blur-sm">
                            <i class="fas fa-clock text-4xl"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

