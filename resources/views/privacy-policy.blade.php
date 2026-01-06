@extends('layouts.frontend')

@section('title', 'Privacy Policy - ' . config('app.name', 'Owinace'))

@push('styles')
<style>
    .policy-content {
        max-width: 900px;
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
        color: #667eea;
        font-size: 1.75rem;
        font-weight: bold;
        margin-bottom: 1rem;
    }

    .policy-section h3 {
        color: #374151;
        font-size: 1.25rem;
        font-weight: 600;
        margin-top: 1.5rem;
        margin-bottom: 0.75rem;
    }

    .policy-section p {
        color: #6b7280;
        line-height: 1.75;
        margin-bottom: 1rem;
    }

    .policy-section ul {
        list-style: disc;
        padding-left: 1.5rem;
        color: #6b7280;
        margin-bottom: 1rem;
    }

    .policy-section li {
        margin-bottom: 0.5rem;
        line-height: 1.75;
    }

    .last-updated {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
        border-left: 4px solid #667eea;
        padding: 1rem;
        border-radius: 0.5rem;
        margin-bottom: 2rem;
    }
</style>
@endpush

@section('content')
<div class="w-full py-12 md:py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl md:text-5xl font-bold mb-4 text-gray-900">Privacy Policy</h1>
            <p class="text-xl text-gray-600">
                Your privacy is important to us
            </p>
        </div>

        <div class="policy-content">
            <div class="last-updated">
                <p class="text-sm font-semibold text-gray-700 mb-0">
                    <i class="fas fa-calendar-alt mr-2"></i>
                    Last Updated: {{ date('F d, Y') }}
                </p>
            </div>

            <div class="policy-section">
                <h2>1. Introduction</h2>
                <p>
                    Welcome to {{ config('app.name', 'Owinace') }}. We respect your privacy and are committed to protecting your personal data. 
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
                    <li><strong>Email:</strong> privacy@owinace.com</li>
                    <li><strong>Address:</strong> 123 Business St, Suite 100, City, State 12345</li>
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
</div>
@endsection

