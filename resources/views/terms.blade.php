@extends('layouts.frontend')

@section('title', 'Terms & Conditions - ' . config('app.name', 'Leadcliq'))

@push('styles')
<style>
    .terms-content {
        max-width: 980px;
        margin: 0 auto;
    }

    .terms-section {
        background: white;
        border-radius: 15px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    .terms-section h2 {
        color: #061d19;
        font-size: 1.75rem;
        font-weight: bold;
        margin-bottom: 1rem;
    }

    .terms-section h3 {
        color: #061d19;
        font-size: 1.25rem;
        font-weight: 600;
        margin-top: 1.5rem;
        margin-bottom: 0.75rem;
    }

    .terms-section p {
        color: #061d19;
        line-height: 1.75;
        margin-bottom: 1rem;
    }

    .terms-section ul {
        list-style: disc;
        padding-left: 1.5rem;
        color: #061d19;
        margin-bottom: 1rem;
    }

    .terms-section ol {
        list-style: decimal;
        padding-left: 1.5rem;
        color: #061d19;
        margin-bottom: 1rem;
    }

    .terms-section li {
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

    .important-notice {
        background: #fef3c7;
        border-left: 4px solid #f59e0b;
        padding: 1rem;
        border-radius: 0.5rem;
        margin: 1rem 0;
    }
</style>
@endpush

@section('content')
<section class="w-full lg:py-25 md:py-22.5 py-17.5">
    <div class="max-w-7xl mx-auto px-5">
        <!-- Header -->
        <div class="text-center lg:mb-12.5 mb-7.5 aos-init aos-animate" data-aos="fade-up" data-aos-delay="150" data-aos-duration="500" data-aos-easing="ease-in-out">
            <h1 class="lg:text-6xl md:text-5.5xl text-4xl mb-2.5">Terms & Conditions</h1>
            <p class="mb-2.5">Please read these terms carefully before using our service</p>
        </div>

        <div class="terms-content">
            <div class="last-updated">
                <p class="text-sm font-semibold text-dark mb-0">
                    <i class="fas fa-calendar-alt mr-2"></i>
                    Last Updated: {{ date('F d, Y') }}
                </p>
            </div>

            <div class="important-notice">
                <p class="text-sm font-semibold text-dark mb-0">
                    <i class="fas fa-exclamation-triangle mr-2 text-amber-500"></i>
                    By accessing and using {{ config('app.name', 'Leadcliq') }}, you accept and agree to be bound by the terms and provision of this agreement.
                </p>
            </div>

            <div class="terms-section">
                <h2>1. Acceptance of Terms</h2>
                <p>
                    By accessing and using {{ config('app.name', 'Leadcliq') }} (the "Service"), you accept and agree to be bound by the terms 
                    and provision of this agreement. If you do not agree to these Terms, please do not use the Service.
                </p>
            </div>

            <div class="terms-section">
                <h2>2. Use License</h2>
                <p>Permission is granted to temporarily access and use the Service for personal, non-commercial use subject to these Terms:</p>
                <ul>
                    <li>You must not modify or copy the materials</li>
                    <li>You must not use the materials for any commercial purpose or for any public display</li>
                    <li>You must not attempt to reverse engineer any software contained on the Service</li>
                    <li>You must not remove any copyright or other proprietary notations from the materials</li>
                    <li>You must not transfer the materials to another person or "mirror" the materials on any other server</li>
                </ul>
            </div>

            <div class="terms-section">
                <h2>3. User Accounts</h2>
                <p>When you create an account with us, you must provide accurate, complete, and current information. You are responsible for:</p>
                <ul>
                    <li>Maintaining the confidentiality of your account and password</li>
                    <li>Restricting access to your computer and account</li>
                    <li>All activities that occur under your account or password</li>
                </ul>
                <p>
                    You must notify us immediately of any unauthorized use of your account or any other breach of security.
                </p>
            </div>

            <div class="terms-section">
                <h2>4. Credit System</h2>
                <p>Our Service operates on a credit-based system:</p>
                <ul>
                    <li>Credits are required to generate AI proposals</li>
                    <li>Each proposal generation costs 1 credit</li>
                    <li>New teams receive 20 free credits upon creation</li>
                    <li>Additional credits can be purchased through our platform</li>
                    <li>Credits are non-refundable once purchased</li>
                    <li>Credits do not expire unless otherwise stated</li>
                    <li>We reserve the right to modify credit pricing with prior notice</li>
                </ul>
            </div>

            <div class="terms-section">
                <h2>5. Team Management</h2>
                <p>When using our team features:</p>
                <ul>
                    <li>Team owners are responsible for managing team members and permissions</li>
                    <li>Team owners can add, remove, or modify member roles at any time</li>
                    <li>Credits belong to the team, not individual members</li>
                    <li>Team data is accessible to all members based on their assigned permissions</li>
                </ul>
            </div>

            <div class="terms-section">
                <h2>6. Intellectual Property</h2>
                <p>
                    The Service and its original content, features, and functionality are owned by {{ config('app.name', 'Leadcliq') }} 
                    and are protected by international copyright, trademark, patent, trade secret, and other intellectual property laws.
                </p>
                <h3>User Content</h3>
                <p>
                    You retain all rights to the content you submit, post, or display on or through the Service. By submitting content, 
                    you grant us a worldwide, non-exclusive, royalty-free license to use, copy, reproduce, process, adapt, publish, 
                    transmit, and display such content for the purpose of providing the Service.
                </p>
            </div>

            <div class="terms-section">
                <h2>7. Prohibited Uses</h2>
                <p>You may not use the Service:</p>
                <ul>
                    <li>For any unlawful purpose or to solicit others to perform unlawful acts</li>
                    <li>To violate any international, federal, provincial or state regulations, rules, laws, or local ordinances</li>
                    <li>To infringe upon or violate our intellectual property rights or the intellectual property rights of others</li>
                    <li>To harass, abuse, insult, harm, defame, slander, disparage, intimidate, or discriminate</li>
                    <li>To submit false or misleading information</li>
                    <li>To upload or transmit viruses or any other type of malicious code</li>
                    <li>To spam, phish, pharm, pretext, spider, crawl, or scrape</li>
                    <li>For any obscene or immoral purpose</li>
                </ul>
            </div>

            <div class="terms-section">
                <h2>8. Disclaimer</h2>
                <p>
                    The materials on the Service are provided on an 'as is' basis. {{ config('app.name', 'Leadcliq') }} makes no warranties, 
                    expressed or implied, and hereby disclaims and negates all other warranties including, without limitation, implied 
                    warranties or conditions of merchantability, fitness for a particular purpose, or non-infringement of intellectual 
                    property or other violation of rights.
                </p>
            </div>

            <div class="terms-section">
                <h2>9. Limitations</h2>
                <p>
                    In no event shall {{ config('app.name', 'Leadcliq') }} or its suppliers be liable for any damages (including, without 
                    limitation, damages for loss of data or profit, or due to business interruption) arising out of the use or inability 
                    to use the Service, even if {{ config('app.name', 'Leadcliq') }} or an authorized representative has been notified 
                    orally or in writing of the possibility of such damage.
                </p>
            </div>

            <div class="terms-section">
                <h2>10. Termination</h2>
                <p>
                    We may terminate or suspend your account and bar access to the Service immediately, without prior notice or liability, 
                    under our sole discretion, for any reason whatsoever and without limitation, including but not limited to a breach of 
                    the Terms.
                </p>
                <p>
                    Upon termination, your right to use the Service will immediately cease. If you wish to terminate your account, you 
                    may simply discontinue using the Service.
                </p>
            </div>

            <div class="terms-section">
                <h2>11. Governing Law</h2>
                <p>
                    These Terms shall be governed and construed in accordance with the laws of the jurisdiction in which 
                    {{ config('app.name', 'Leadcliq') }} operates, without regard to its conflict of law provisions.
                </p>
            </div>

            <div class="terms-section">
                <h2>12. Changes to Terms</h2>
                <p>
                    We reserve the right, at our sole discretion, to modify or replace these Terms at any time. If a revision is material, 
                    we will provide at least 30 days' notice prior to any new terms taking effect. What constitutes a material change will 
                    be determined at our sole discretion.
                </p>
                <p>
                    By continuing to access or use our Service after any revisions become effective, you agree to be bound by the revised terms.
                </p>
            </div>

            <div class="terms-section">
                <h2>13. Contact Us</h2>
                <p>
                    If you have any questions about these Terms, please contact us:
                </p>
                <ul>
                    <li><strong>Email:</strong> support@leadcliq.ai</li>
                    <li><strong>Phone:</strong> +91 7710113366</li>
                    <li><strong>Address:</strong> F-427, 2nd Floor, Sector 91, Mohali, Punjab, India 140307</li>
                </ul>
            </div>
        </div>
    </div>
</section>
@endsection

