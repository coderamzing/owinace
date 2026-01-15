@extends('layouts.frontend')

@section('title', 'Support - ' . config('app.name', 'Owinace'))

@section('content')
<section class="w-full lg:py-25 md:py-22.5 py-17.5">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="text-center lg:mb-12.5 mb-7.5">
            <h1 class="lg:text-5.5xl md:text-4.6xl text-3.4xl mb-2.5">Support Center</h1>
            <p class="text-base text-dark">
                We're here to help you succeed
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
                                <a href="mailto:support@owinace.com" class="text-dark underline font-medium">
                                    support@owinace.com
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
                                <p class="text-sm text-dark">Learn how to use {{ config('app.name', 'Owinace') }}</p>
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
                        <h3 class="text-2xl font-bold mb-2">Support Hours</h3>
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

