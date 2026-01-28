@extends('layouts.frontend')

@section('title', 'Privacy Policy - ' . config('app.name', 'LeadCliq'))

@push('meta')
<!-- Hreflang -->
<link rel="alternate" hreflang="en" href="{{ url()->current() }}" />
<link rel="alternate" hreflang="x-default" href="{{ url()->current() }}" />

<meta name="description" content="LeadCliq Privacy Policy. Learn how we collect, use, and protect your personal data and information.">
<meta name="keywords" content="privacy policy, data protection, GDPR, personal information, LeadCliq privacy">
<meta name="author" content="LeadCliq">
<meta name="robots" content="index, follow">

<!-- Open Graph / Facebook -->
<meta property="og:type" content="website">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:title" content="Privacy Policy - LeadCliq">
<meta property="og:description" content="LeadCliq Privacy Policy. Learn how we collect, use, and protect your personal data and information.">
<meta property="og:image" content="{{ asset('images/leadcliq-og.png') }}">

<!-- Twitter -->
<meta property="twitter:card" content="summary_large_image">
<meta property="twitter:url" content="{{ url()->current() }}">
<meta property="twitter:title" content="Privacy Policy - LeadCliq">
<meta property="twitter:description" content="LeadCliq Privacy Policy. Learn how we collect, use, and protect your personal data and information.">
<meta property="twitter:image" content="{{ asset('images/leadcliq-og.png') }}">
@endpush

@push('styles')
<style>
    .policy-content {
        max-width: 980px;
        margin: 0 auto;
    }

    .policy-section {
        background: white;
        border-radius: 15px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    .policy-section h2 {
        color: #061d19;
        font-size: 1.75rem;
        font-weight: bold;
        margin-bottom: 1rem;
    }

    .policy-section h3 {
        color: #061d19;
        font-size: 1.25rem;
        font-weight: 600;
        margin-top: 1.5rem;
        margin-bottom: 0.75rem;
    }

    .policy-section p {
        color: #061d19;
        line-height: 1.75;
        margin-bottom: 1rem;
    }

    .policy-section ul {
        list-style: disc;
        padding-left: 1.5rem;
        color: #061d19;
        margin-bottom: 1rem;
    }

    .policy-section li {
        margin-bottom: 0.5rem;
        line-height: 1.75;
    }

    .last-updated {
        background: #f3f3e5;
        border-left: 4px solid #061d19;
        padding: 1rem;
        border-radius: 0.5rem;
        margin-bottom: 2rem;
    }
</style>
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
            "name" => "Privacy Policy",
            "item" => url()->current()
        ]
    ]
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
@endphp
</script>

<section class="w-full lg:py-25 md:py-22.5 py-17.5">
    <div class="max-w-7xl mx-auto px-5">
        <!-- Header -->
        <div class="text-center lg:mb-12.5 mb-7.5 aos-init aos-animate" data-aos="fade-up" data-aos-delay="150" data-aos-duration="500" data-aos-easing="ease-in-out">
            <h1 class="lg:text-6xl md:text-5.5xl text-4xl mb-2.5">Privacy Policy</h1>
            <p class="mb-2.5">Your privacy is important to us</p>
        </div>

        <div class="policy-content">
            <div class="last-updated">
                <p class="text-sm font-semibold text-dark mb-0">
                    <i class="fas fa-calendar-alt mr-2"></i>
                    Last Updated: {{ date('F d, Y') }}
                </p>
            </div>

            <div class="policy-section">
                <h2>1. Introduction</h2>
                <p>
                    Welcome to {{ config('app.name', 'LeadCliq') }}. We respect your privacy and are committed to protecting your personal data. 
                    This privacy policy will inform you about how we look after your personal data when you visit our website and tell you 
                    about your privacy rights and how the law protects you.
                </p>
            </div>

            <div class="policy-section">
                <h2>2. Information We Collect</h2>
                <p>We may collect, use, store and transfer different kinds of personal data about you:</p>
                <ul>
                    <li><strong>Identity Data:</strong> First name, last name, username or similar identifier</li>
                    <li><strong>Contact Data:</strong> Email address, telephone numbers, and billing address</li>
                    <li><strong>Technical Data:</strong> IP address, browser type and version, time zone setting, operating system</li>
                    <li><strong>Usage Data:</strong> Information about how you use our website, products and services</li>
                    <li><strong>Marketing Data:</strong> Your preferences in receiving marketing from us</li>
                </ul>
            </div>

            <div class="policy-section">
                <h2>3. How We Use Your Information</h2>
                <p>We will only use your personal data when the law allows us to. Most commonly, we will use your personal data in the following circumstances:</p>
                <ul>
                    <li>To register you as a new customer</li>
                    <li>To process and deliver your order including managing payments</li>
                    <li>To manage our relationship with you</li>
                    <li>To improve our website, products/services, marketing or customer relationships</li>
                    <li>To recommend products or services which may be of interest to you</li>
                </ul>
            </div>

            <div class="policy-section">
                <h2>4. Data Security</h2>
                <p>
                    We have put in place appropriate security measures to prevent your personal data from being accidentally lost, 
                    used or accessed in an unauthorized way, altered or disclosed. We limit access to your personal data to those 
                    employees, agents, contractors and other third parties who have a business need to know.
                </p>
            </div>

            <div class="policy-section">
                <h2>5. Data Retention</h2>
                <p>
                    We will only retain your personal data for as long as necessary to fulfill the purposes we collected it for, 
                    including for the purposes of satisfying any legal, accounting, or reporting requirements.
                </p>
            </div>

            <div class="policy-section">
                <h2>6. Your Legal Rights</h2>
                <p>Under certain circumstances, you have rights under data protection laws in relation to your personal data:</p>
                <ul>
                    <li>Request access to your personal data</li>
                    <li>Request correction of your personal data</li>
                    <li>Request erasure of your personal data</li>
                    <li>Object to processing of your personal data</li>
                    <li>Request restriction of processing your personal data</li>
                    <li>Request transfer of your personal data</li>
                    <li>Right to withdraw consent</li>
                </ul>
            </div>

            <div class="policy-section">
                <h2>7. Third-Party Links</h2>
                <p>
                    This website may include links to third-party websites, plug-ins and applications. Clicking on those links 
                    or enabling those connections may allow third parties to collect or share data about you. We do not control 
                    these third-party websites and are not responsible for their privacy statements.
                </p>
            </div>

            <div class="policy-section">
                <h2>8. Cookies</h2>
                <p>
                    We use cookies to distinguish you from other users of our website. This helps us to provide you with a good 
                    experience when you browse our website and also allows us to improve our site. You can set your browser to 
                    refuse all or some browser cookies, or to alert you when websites set or access cookies.
                </p>
            </div>

            <div class="policy-section">
                <h2>9. Contact Us</h2>
                <p>
                    If you have any questions about this privacy policy or our privacy practices, please contact us at:
                </p>
                <ul>
                    <li><strong>Email:</strong> support@leadcliq.ai</li>
                    <li><strong>Phone:</strong> +91 7710113366</li>
                    <li><strong>Address:</strong> F-427, 2nd Floor, Sector 91, Mohali, Punjab, India 140307</li>
                </ul>
            </div>

            <div class="policy-section">
                <h2>10. Changes to This Policy</h2>
                <p>
                    We may update this privacy policy from time to time. We will notify you of any changes by posting the new 
                    privacy policy on this page and updating the "Last Updated" date at the top of this policy.
                </p>
            </div>
        </div>
    </div>
</section>
@endsection

