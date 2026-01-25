@extends('layouts.frontend')

@section('title', 'FAQ - ' . config('app.name', 'LeadCliq'))

@push('meta')
<!-- Hreflang -->
<link rel="alternate" hreflang="en" href="{{ url()->current() }}" />
<link rel="alternate" hreflang="x-default" href="{{ url()->current() }}" />

<meta name="description" content="Frequently asked questions about LeadCliq. Learn about AI proposals, credits, lead management, team features, and more.">
<meta name="keywords" content="LeadCliq FAQ, frequently asked questions, AI proposals help, lead CRM questions, credits system">
<meta name="author" content="LeadCliq">

<!-- Open Graph / Facebook -->
<meta property="og:type" content="website">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:title" content="Frequently Asked Questions - LeadCliq">
<meta property="og:description" content="Frequently asked questions about LeadCliq. Learn about AI proposals, credits, lead management, team features, and more.">
<meta property="og:image" content="{{ asset('images/leadcliq-og.png') }}">

<!-- Twitter -->
<meta property="twitter:card" content="summary_large_image">
<meta property="twitter:url" content="{{ url()->current() }}">
<meta property="twitter:title" content="Frequently Asked Questions - LeadCliq">
<meta property="twitter:description" content="Frequently asked questions about LeadCliq. Learn about AI proposals, credits, lead management, team features, and more.">
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
            "name" => "FAQ",
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
    "@type" => "FAQPage",
    "mainEntity" => [
        [
            "@type" => "Question",
            "name" => "What is LeadCliq?",
            "acceptedAnswer" => [
                "@type" => "Answer",
                "text" => "LeadCliq is an AI-powered platform that helps freelancers, agencies, and small companies generate personalized proposals, manage leads with Kanban boards, track team performance, analyze costs and ROI, and write cover letters for Upwork using our Chrome extension—all in one workspace."
            ]
        ],
        [
            "@type" => "Question",
            "name" => "How does the credit system work?",
            "acceptedAnswer" => [
                "@type" => "Answer",
                "text" => "Credits are used to generate AI proposals and cover letters. Each generation consumes credits based on complexity. You can purchase credits as needed, and they're shared across your entire workspace. Lead management, Kanban boards, analytics, and team features are completely free."
            ]
        ],
        [
            "@type" => "Question",
            "name" => "Can I work with a team?",
            "acceptedAnswer" => [
                "@type" => "Answer",
                "text" => "Absolutely! LeadCliq supports multiple teams and admins. You can invite team members, assign roles and permissions, set member goals, track individual performance, and collaborate on leads and proposals together."
            ]
        ],
        [
            "@type" => "Question",
            "name" => "Does LeadCliq have a Chrome extension for Upwork?",
            "acceptedAnswer" => [
                "@type" => "Answer",
                "text" => "Yes! Our Chrome extension lets you write AI-powered cover letters directly on Upwork and other freelance platforms. It automatically matches your portfolio to job requirements and generates personalized proposals in seconds."
            ]
        ],
        [
            "@type" => "Question",
            "name" => "How does lead management work?",
            "acceptedAnswer" => [
                "@type" => "Answer",
                "text" => "Our CRM includes Kanban boards for visual pipeline management, automated follow-up reminders, contact management, activity tracking, lead cost analysis, ROI calculations, and comprehensive analytics to help you manage your sales process effectively."
            ]
        ],
        [
            "@type" => "Question",
            "name" => "Is my data secure?",
            "acceptedAnswer" => [
                "@type" => "Answer",
                "text" => "Yes. All data is encrypted in transit and at rest. We follow industry best practices with role-based access controls, audit trails, and secure storage to ensure your information and client data remain confidential."
            ]
        ]
    ]
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
@endphp
</script>

<section class="bg-body-bg lg:py-25 md:py-12.5 py-7.5">
        <div class="container">
            <div class="text-center aos-init aos-animate" data-aos="fade-up" data-aos-delay="150" data-aos-duration="500" data-aos-easing="ease-in-out">
                <h1 class="lg:text-6xl md:text-5.5xl text-4xl mb-2.5">Frequently asked questions</h1>
                <p class="mb-2.5">Everything you need to know about LeadCliq's AI proposals, lead CRM, team management, pricing, and Chrome extension for Upwork.</p>
            </div>
        </div>
        <div class="max-w-7xl mx-auto">
            <!-- General Questions -->
            <div class="mb-8" data-aos="fade-up" data-aos-duration="500" data-aos-easing="ease-in-out">
                <h2 class="text-2xl font-bold text-dark mb-6">General Questions</h2>
                
                <div class="space-y-4">
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-neutral-200">
                        <h3 class="text-lg font-semibold text-dark mb-3">What is {{ config('app.name', 'LeadCliq') }}?</h3>
                        <p class="text-base leading-relaxed">
                            {{ config('app.name', 'LeadCliq') }} is an AI-powered platform built for freelancers, agencies, and small companies. 
                            Generate personalized proposals instantly, manage leads with Kanban boards, track team performance, analyze costs and ROI, 
                            and write cover letters for Upwork using our Chrome extension—all in one powerful workspace.
                        </p>
                    </div>

                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-neutral-200">
                        <h3 class="text-lg font-semibold text-dark mb-3">Does LeadCliq have a Chrome extension for Upwork?</h3>
                        <p class="text-base leading-relaxed">
                            Yes! Our Chrome extension lets you write AI-powered cover letters directly on Upwork and other freelance platforms. 
                            It automatically matches your portfolio to job requirements and generates personalized proposals in seconds, 
                            helping you win more clients faster.
                        </p>
                    </div>

                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-neutral-200">
                        <h3 class="text-lg font-semibold text-dark mb-3">Is my data secure?</h3>
                        <p class="text-base leading-relaxed">
                            Yes, we take security seriously. All data is encrypted in transit and at rest. We follow industry 
                            best practices to ensure your information and your clients' data remain secure and confidential.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Credits & Billing -->
            <div class="mb-8" data-aos="fade-up" data-aos-duration="500" data-aos-easing="ease-in-out">
                <h2 class="text-2xl font-bold text-dark mb-6">Credits & Billing</h2>
                
                <div class="space-y-4">
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-neutral-200">
                        <h3 class="text-lg font-semibold text-dark mb-3">How does the credit system work?</h3>
                        <p class="text-base leading-relaxed">
                            Credits are used to generate AI proposals and cover letters. Each generation consumes credits based on complexity. 
                            You can purchase credits as needed, and they're shared across your entire workspace. Lead management, Kanban boards, 
                            analytics, and team features are completely free.
                        </p>
                    </div>

                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-neutral-200">
                        <h3 class="text-lg font-semibold text-dark mb-3">What payment methods do you accept?</h3>
                        <p class="text-base leading-relaxed">
                            We accept all major credit cards, debit cards, and UPI payments through our secure payment gateway powered by Razorpay.
                        </p>
                    </div>

                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-neutral-200">
                        <h3 class="text-lg font-semibold text-dark mb-3">Are credits refundable?</h3>
                        <p class="text-base leading-relaxed">
                            No, all credit purchases are final and non-refundable. Please review our 
                            <a href="{{ route('refund-policy') }}" class="text-dark underline font-medium hover:text-primary">Refund Policy</a> 
                            for complete details before making a purchase.
                        </p>
                    </div>

                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-neutral-200">
                        <h3 class="text-lg font-semibold text-dark mb-3">Do credits expire?</h3>
                        <p class="text-base leading-relaxed">
                            Credits may be subject to expiration policies based on inactivity. We recommend using your credits 
                            within a reasonable timeframe and checking your account regularly.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Features & Functionality -->
            <div class="mb-8" data-aos="fade-up" data-aos-duration="500" data-aos-easing="ease-in-out">
                <h2 class="text-2xl font-bold text-dark mb-6">Features & Functionality</h2>
                
                <div class="space-y-4">
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-neutral-200">
                        <h3 class="text-lg font-semibold text-dark mb-3">Can I customize the AI-generated proposals?</h3>
                        <p class="text-base leading-relaxed">
                            Yes! All AI-generated proposals can be fully customized. You can edit any section, add or remove 
                            content, and adjust the formatting to match your brand and requirements.
                        </p>
                    </div>

                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-neutral-200">
                        <h3 class="text-lg font-semibold text-dark mb-3">Can I work with a team?</h3>
                        <p class="text-base leading-relaxed">
                            Absolutely! LeadCliq supports multiple teams and admins. You can invite team members, assign roles and permissions, 
                            set member goals, track individual performance, and collaborate on leads and proposals together. Perfect for agencies 
                            and growing companies.
                        </p>
                    </div>

                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-neutral-200">
                        <h3 class="text-lg font-semibold text-dark mb-3">How does lead management work?</h3>
                        <p class="text-base leading-relaxed">
                            Our CRM includes Kanban boards for visual pipeline management, automated follow-up reminders, contact management, 
                            activity tracking, lead cost analysis, ROI calculations, and comprehensive analytics to help you manage your sales 
                            process effectively.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Support & Account -->
            <div class="mb-8" data-aos="fade-up" data-aos-duration="500" data-aos-easing="ease-in-out">
                <h2 class="text-2xl font-bold text-dark mb-6">Support & Account</h2>
                
                <div class="space-y-4">
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-neutral-200">
                        <h3 class="text-lg font-semibold text-dark mb-3">How can I contact support?</h3>
                        <p class="text-base leading-relaxed">
                            You can reach our support team via email at 
                            <a href="mailto:support@leadcliq.ai" class="text-dark underline font-medium hover:text-primary">support@leadcliq.ai</a> 
                            or call us at 
                            <a href="tel:+917710113366" class="text-dark underline font-medium hover:text-primary">+91 7710113366</a> 
                            during business hours (Mon-Fri, 9 AM - 6 PM IST).
                        </p>
                    </div>

                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-neutral-200">
                        <h3 class="text-lg font-semibold text-dark mb-3">Can I cancel my account?</h3>
                        <p class="text-base leading-relaxed">
                            Yes, you can cancel your account at any time from your account settings. Please note that any 
                            unused credits will be forfeited upon account closure and are not refundable.
                        </p>
                    </div>

                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-neutral-200">
                        <h3 class="text-lg font-semibold text-dark mb-3">How do I reset my password?</h3>
                        <p class="text-base leading-relaxed">
                            Click on "Forgot Password" on the login page, enter your email address, and we'll send you 
                            instructions to reset your password.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Still Have Questions? -->
            <div class="bg-dark rounded-2xl lg:p-10 p-5 text-white shadow-lg text-center" data-aos="fade-up" data-aos-duration="500" data-aos-easing="ease-in-out">
                <h3 class="text-2xl font-bold mb-4 text-white/80!">Still Have Questions?</h3>
                <p class="mb-6">
                    Can't find what you're looking for? Our support team is here to help!
                </p>
                <a href="{{ route('contact') }}" class="inline-flex items-center px-6 py-3 bg-primary text-dark rounded-2xl font-medium hover:bg-primary/90 transition-all duration-300">
                    Contact Support
                </a>
            </div>
        </div>
    </section>
@endsection

