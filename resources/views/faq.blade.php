@extends('layouts.frontend')

@section('title', 'FAQ - ' . config('app.name', 'Owinace'))

@section('content')
<div class="w-full py-12 md:py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl md:text-5xl font-bold mb-4 text-gray-900">Frequently Asked Questions</h1>
            <p class="text-xl text-gray-600">
                Find answers to common questions about {{ config('app.name', 'Owinace') }}
            </p>
        </div>

        <div class="max-w-4xl mx-auto">
            <!-- General Questions -->
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-indigo-600 mb-6 flex items-center">
                    <i class="fas fa-info-circle mr-3"></i>
                    General Questions
                </h2>
                
                <div class="space-y-4">
                    <div class="bg-white rounded-xl p-6 shadow-sm">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-start">
                            <i class="fas fa-question-circle text-indigo-500 mr-3 mt-1"></i>
                            <span>What is {{ config('app.name', 'Owinace') }}?</span>
                        </h3>
                        <p class="text-gray-600 leading-relaxed ml-8">
                            {{ config('app.name', 'Owinace') }} is an AI-powered platform that helps you instantly generate personalized, 
                            high-quality proposals while seamlessly managing leads, tracking team performance with analytics, 
                            monitoring costs via a flexible credit system, and maintaining detailed client records—all in one powerful platform.
                        </p>
                    </div>

                    <div class="bg-white rounded-xl p-6 shadow-sm">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-start">
                            <i class="fas fa-question-circle text-indigo-500 mr-3 mt-1"></i>
                            <span>How do I get started?</span>
                        </h3>
                        <p class="text-gray-600 leading-relaxed ml-8">
                            Simply register for an account, complete your profile setup, and you'll be ready to create your first 
                            AI-generated proposal. Our intuitive interface makes it easy to get started in minutes.
                        </p>
                    </div>

                    <div class="bg-white rounded-xl p-6 shadow-sm">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-start">
                            <i class="fas fa-question-circle text-indigo-500 mr-3 mt-1"></i>
                            <span>Is my data secure?</span>
                        </h3>
                        <p class="text-gray-600 leading-relaxed ml-8">
                            Yes, we take security seriously. All data is encrypted in transit and at rest. We follow industry 
                            best practices to ensure your information and your clients' data remain secure and confidential.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Credits & Billing -->
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-indigo-600 mb-6 flex items-center">
                    <i class="fas fa-credit-card mr-3"></i>
                    Credits & Billing
                </h2>
                
                <div class="space-y-4">
                    <div class="bg-white rounded-xl p-6 shadow-sm">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-start">
                            <i class="fas fa-question-circle text-indigo-500 mr-3 mt-1"></i>
                            <span>How does the credit system work?</span>
                        </h3>
                        <p class="text-gray-600 leading-relaxed ml-8">
                            Credits are used to generate proposals and access premium features. Each action consumes a certain 
                            number of credits. You can purchase credits as needed, and they're shared across your workspace.
                        </p>
                    </div>

                    <div class="bg-white rounded-xl p-6 shadow-sm">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-start">
                            <i class="fas fa-question-circle text-indigo-500 mr-3 mt-1"></i>
                            <span>What payment methods do you accept?</span>
                        </h3>
                        <p class="text-gray-600 leading-relaxed ml-8">
                            We accept all major credit cards, debit cards, and UPI payments through our secure payment gateway powered by Razorpay.
                        </p>
                    </div>

                    <div class="bg-white rounded-xl p-6 shadow-sm">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-start">
                            <i class="fas fa-question-circle text-indigo-500 mr-3 mt-1"></i>
                            <span>Are credits refundable?</span>
                        </h3>
                        <p class="text-gray-600 leading-relaxed ml-8">
                            No, all credit purchases are final and non-refundable. Please review our 
                            <a href="{{ route('refund-policy') }}" class="text-indigo-600 hover:text-indigo-700 font-medium">Refund Policy</a> 
                            for complete details before making a purchase.
                        </p>
                    </div>

                    <div class="bg-white rounded-xl p-6 shadow-sm">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-start">
                            <i class="fas fa-question-circle text-indigo-500 mr-3 mt-1"></i>
                            <span>Do credits expire?</span>
                        </h3>
                        <p class="text-gray-600 leading-relaxed ml-8">
                            Credits may be subject to expiration policies based on inactivity. We recommend using your credits 
                            within a reasonable timeframe and checking your account regularly.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Features & Functionality -->
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-indigo-600 mb-6 flex items-center">
                    <i class="fas fa-cog mr-3"></i>
                    Features & Functionality
                </h2>
                
                <div class="space-y-4">
                    <div class="bg-white rounded-xl p-6 shadow-sm">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-start">
                            <i class="fas fa-question-circle text-indigo-500 mr-3 mt-1"></i>
                            <span>Can I customize the AI-generated proposals?</span>
                        </h3>
                        <p class="text-gray-600 leading-relaxed ml-8">
                            Yes! All AI-generated proposals can be fully customized. You can edit any section, add or remove 
                            content, and adjust the formatting to match your brand and requirements.
                        </p>
                    </div>

                    <div class="bg-white rounded-xl p-6 shadow-sm">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-start">
                            <i class="fas fa-question-circle text-indigo-500 mr-3 mt-1"></i>
                            <span>Can I work with a team?</span>
                        </h3>
                        <p class="text-gray-600 leading-relaxed ml-8">
                            Absolutely! You can invite team members to your workspace, assign roles and permissions, and 
                            collaborate on leads, proposals, and analytics together.
                        </p>
                    </div>

                    <div class="bg-white rounded-xl p-6 shadow-sm">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-start">
                            <i class="fas fa-question-circle text-indigo-500 mr-3 mt-1"></i>
                            <span>How does lead management work?</span>
                        </h3>
                        <p class="text-gray-600 leading-relaxed ml-8">
                            Our platform includes a comprehensive lead management system with Kanban boards, contact management, 
                            activity tracking, and analytics to help you manage your sales pipeline effectively.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Support & Account -->
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-indigo-600 mb-6 flex items-center">
                    <i class="fas fa-user-circle mr-3"></i>
                    Support & Account
                </h2>
                
                <div class="space-y-4">
                    <div class="bg-white rounded-xl p-6 shadow-sm">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-start">
                            <i class="fas fa-question-circle text-indigo-500 mr-3 mt-1"></i>
                            <span>How can I contact support?</span>
                        </h3>
                        <p class="text-gray-600 leading-relaxed ml-8">
                            You can reach our support team via email at 
                            <a href="mailto:support@owinace.com" class="text-indigo-600 hover:text-indigo-700 font-medium">support@owinace.com</a> 
                            or call us at 
                            <a href="tel:+917710113366" class="text-indigo-600 hover:text-indigo-700 font-medium">+91 7710113366</a> 
                            during business hours (Mon-Fri, 9 AM - 6 PM IST).
                        </p>
                    </div>

                    <div class="bg-white rounded-xl p-6 shadow-sm">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-start">
                            <i class="fas fa-question-circle text-indigo-500 mr-3 mt-1"></i>
                            <span>Can I cancel my account?</span>
                        </h3>
                        <p class="text-gray-600 leading-relaxed ml-8">
                            Yes, you can cancel your account at any time from your account settings. Please note that any 
                            unused credits will be forfeited upon account closure and are not refundable.
                        </p>
                    </div>

                    <div class="bg-white rounded-xl p-6 shadow-sm">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-start">
                            <i class="fas fa-question-circle text-indigo-500 mr-3 mt-1"></i>
                            <span>How do I reset my password?</span>
                        </h3>
                        <p class="text-gray-600 leading-relaxed ml-8">
                            Click on "Forgot Password" on the login page, enter your email address, and we'll send you 
                            instructions to reset your password.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Still Have Questions? -->
            <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-2xl p-8 text-white shadow-lg text-center">
                <h3 class="text-2xl font-bold mb-4">Still Have Questions?</h3>
                <p class="text-indigo-100 mb-6">
                    Can't find what you're looking for? Our support team is here to help!
                </p>
                <a href="{{ route('support') }}" class="inline-flex items-center px-6 py-3 bg-white text-indigo-600 rounded-lg font-semibold hover:bg-gray-100 transition-colors">
                    <i class="fas fa-headset mr-2"></i>
                    Contact Support
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

