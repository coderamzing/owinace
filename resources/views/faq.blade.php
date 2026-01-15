@extends('layouts.frontend')

@section('title', 'FAQ - ' . config('app.name', 'Owinace'))

@section('content')
    <section class="bg-body-bg lg:py-25 md:py-12.5 py-7.5">
        <div class="container">
            <div class="text-center aos-init aos-animate" data-aos="fade-up" data-aos-delay="150" data-aos-duration="500" data-aos-easing="ease-in-out">
                <h1 class="lg:text-6xl md:text-5.5xl text-4xl mb-2.5">Frequently asked questions</h1>
                <p class="mb-2.5">Passage its ten led heated removal cordial. Preference any astonished unreserved Mrs.</p>
            </div>
        </div>
    </section>

    <section class="lg:py-25 md:py-22.5 py-17.5">
        <div class="max-w-7xl mx-auto">
            <!-- General Questions -->
            <div class="mb-8" data-aos="fade-up" data-aos-duration="500" data-aos-easing="ease-in-out">
                <h2 class="text-2xl font-bold text-dark mb-6">General Questions</h2>
                
                <div class="space-y-4">
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-neutral-200">
                        <h3 class="text-lg font-semibold text-dark mb-3">What is {{ config('app.name', 'Owinace') }}?</h3>
                        <p class="text-base leading-relaxed">
                            {{ config('app.name', 'Owinace') }} is an AI-powered platform that helps you instantly generate personalized, 
                            high-quality proposals while seamlessly managing leads, tracking team performance with analytics, 
                            monitoring costs via a flexible credit system, and maintaining detailed client records—all in one powerful platform.
                        </p>
                    </div>

                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-neutral-200">
                        <h3 class="text-lg font-semibold text-dark mb-3">How do I get started?</h3>
                        <p class="text-base leading-relaxed">
                            Simply register for an account, complete your profile setup, and you'll be ready to create your first 
                            AI-generated proposal. Our intuitive interface makes it easy to get started in minutes.
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
                            Credits are used to generate proposals and access premium features. Each action consumes a certain 
                            number of credits. You can purchase credits as needed, and they're shared across your workspace.
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
                            Absolutely! You can invite team members to your workspace, assign roles and permissions, and 
                            collaborate on leads, proposals, and analytics together.
                        </p>
                    </div>

                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-neutral-200">
                        <h3 class="text-lg font-semibold text-dark mb-3">How does lead management work?</h3>
                        <p class="text-base leading-relaxed">
                            Our platform includes a comprehensive lead management system with Kanban boards, contact management, 
                            activity tracking, and analytics to help you manage your sales pipeline effectively.
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
                            <a href="mailto:support@owinace.com" class="text-dark underline font-medium hover:text-primary">support@owinace.com</a> 
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
                <h3 class="text-2xl font-bold mb-4">Still Have Questions?</h3>
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

