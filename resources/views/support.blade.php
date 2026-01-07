@extends('layouts.frontend')

@section('title', 'Support - ' . config('app.name', 'Owinace'))

@section('content')
<div class="w-full py-12 md:py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl md:text-5xl font-bold mb-4 text-gray-900">Support Center</h1>
            <p class="text-xl text-gray-600">
                We're here to help you succeed
            </p>
        </div>

        <div class="max-w-4xl mx-auto">
            <!-- Contact Support Section -->
            <div class="bg-white rounded-2xl p-8 mb-8 shadow-sm">
                <div class="text-center mb-8">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-indigo-100 rounded-full mb-4">
                        <i class="fas fa-headset text-indigo-600 text-2xl"></i>
                    </div>
                    <h2 class="text-indigo-500 text-3xl font-bold mb-2">Get in Touch</h2>
                    <p class="text-gray-600">Our support team is ready to assist you</p>
                </div>

                <div class="grid md:grid-cols-2 gap-6">
                    <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-xl p-6">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center w-12 h-12 bg-white rounded-lg shadow-sm">
                                    <i class="fas fa-envelope text-indigo-600 text-xl"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">Email Support</h3>
                                <p class="text-gray-600 text-sm mb-2">Get help via email</p>
                                <a href="mailto:support@owinace.com" class="text-indigo-600 hover:text-indigo-700 font-medium">
                                    support@owinace.com
                                </a>
                                <p class="text-xs text-gray-500 mt-2">Response time: 24-48 hours</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl p-6">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center w-12 h-12 bg-white rounded-lg shadow-sm">
                                    <i class="fas fa-phone text-green-600 text-xl"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">Phone Support</h3>
                                <p class="text-gray-600 text-sm mb-2">Call us directly</p>
                                <a href="tel:+917710113366" class="text-green-600 hover:text-green-700 font-medium">
                                    +91 7710113366
                                </a>
                                <p class="text-xs text-gray-500 mt-2">Mon-Fri, 9:00 AM - 6:00 PM IST</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Help Topics -->
            <div class="bg-white rounded-2xl p-8 mb-8 shadow-sm">
                <h2 class="text-indigo-500 text-3xl font-bold mb-6">Quick Help Topics</h2>
                <div class="space-y-4">
                    <a href="{{ route('faq') }}" class="block p-4 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <i class="fas fa-question-circle text-indigo-500 text-xl mr-4"></i>
                                <div>
                                    <h3 class="font-semibold text-gray-900">Frequently Asked Questions</h3>
                                    <p class="text-sm text-gray-600">Find answers to common questions</p>
                                </div>
                            </div>
                            <i class="fas fa-chevron-right text-gray-400"></i>
                        </div>
                    </a>

                    <div class="block p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-book text-indigo-500 text-xl mr-4"></i>
                            <div>
                                <h3 class="font-semibold text-gray-900">Getting Started Guide</h3>
                                <p class="text-sm text-gray-600">Learn how to use {{ config('app.name', 'Owinace') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="block p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-credit-card text-indigo-500 text-xl mr-4"></i>
                            <div>
                                <h3 class="font-semibold text-gray-900">Billing & Credits</h3>
                                <p class="text-sm text-gray-600">Understand our credit system and pricing</p>
                            </div>
                        </div>
                    </div>

                    <div class="block p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-shield-alt text-indigo-500 text-xl mr-4"></i>
                            <div>
                                <h3 class="font-semibold text-gray-900">Account & Security</h3>
                                <p class="text-sm text-gray-600">Manage your account settings and security</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Support Hours -->
            <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-2xl p-8 text-white shadow-lg">
                <div class="flex items-center justify-between flex-wrap gap-4">
                    <div>
                        <h3 class="text-2xl font-bold mb-2">Support Hours</h3>
                        <p class="text-indigo-100">Monday - Friday: 9:00 AM - 6:00 PM (IST)</p>
                        <p class="text-indigo-100">Saturday - Sunday: Closed</p>
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
</div>
@endsection

