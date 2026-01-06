@extends('layouts.frontend')

@section('title', 'Contact Us - ' . config('app.name', 'Owinace'))

@push('styles')
<style>
    .contact-card {
        background: white;
        border-radius: 20px;
        padding: 2rem;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
        border: 1px solid rgba(0,0,0,0.05);
    }

    .contact-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.15);
    }

    .contact-icon {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #667eea, #764ba2);
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
        margin-bottom: 1rem;
    }

    .form-input {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        transition: all 0.3s ease;
    }

    .form-input:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        padding: 0.75rem 2rem;
        border-radius: 0.5rem;
        font-weight: 600;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
    }
</style>
@endpush

@section('content')
<div class="w-full py-12 md:py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl md:text-5xl font-bold mb-4 text-gray-900">Contact Us</h1>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                Have questions? We'd love to hear from you. Send us a message and we'll respond as soon as possible.
            </p>
        </div>

        <!-- Contact Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
            <div class="contact-card text-center">
                <div class="contact-icon mx-auto">
                    <i class="fas fa-envelope"></i>
                </div>
                <h3 class="text-xl font-bold mb-2">Email Us</h3>
                <p class="text-gray-600 mb-3">Our team is here to help</p>
                <a href="mailto:support@owinace.com" class="text-purple-600 hover:text-purple-700 font-semibold">
                    support@owinace.com
                </a>
            </div>

            <div class="contact-card text-center">
                <div class="contact-icon mx-auto">
                    <i class="fas fa-phone"></i>
                </div>
                <h3 class="text-xl font-bold mb-2">Call Us</h3>
                <p class="text-gray-600 mb-3">Mon-Fri from 9am to 6pm</p>
                <a href="tel:+1234567890" class="text-purple-600 hover:text-purple-700 font-semibold">
                    +1 (234) 567-890
                </a>
            </div>

            <div class="contact-card text-center">
                <div class="contact-icon mx-auto">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <h3 class="text-xl font-bold mb-2">Visit Us</h3>
                <p class="text-gray-600 mb-3">Come say hello</p>
                <p class="text-purple-600 font-semibold">
                    123 Business St, Suite 100<br>
                    City, State 12345
                </p>
            </div>
        </div>

        <!-- Contact Form -->
        <div class="max-w-3xl mx-auto">
            <div class="contact-card">
                <h2 class="text-2xl font-bold mb-6">Send us a Message</h2>
                <form action="#" method="POST" class="space-y-6">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Name *</label>
                            <input type="text" id="name" name="name" required class="form-input" placeholder="Your name">
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email *</label>
                            <input type="email" id="email" name="email" required class="form-input" placeholder="your@email.com">
                        </div>
                    </div>

                    <div>
                        <label for="subject" class="block text-sm font-semibold text-gray-700 mb-2">Subject *</label>
                        <input type="text" id="subject" name="subject" required class="form-input" placeholder="How can we help?">
                    </div>

                    <div>
                        <label for="message" class="block text-sm font-semibold text-gray-700 mb-2">Message *</label>
                        <textarea id="message" name="message" rows="6" required class="form-input" placeholder="Tell us more about your inquiry..."></textarea>
                    </div>

                    <div>
                        <button type="submit" class="btn-primary w-full md:w-auto">
                            <i class="fas fa-paper-plane mr-2"></i>
                            Send Message
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- FAQ Section -->
        <div class="mt-16 text-center">
            <h2 class="text-3xl font-bold mb-4">Frequently Asked Questions</h2>
            <p class="text-gray-600 mb-6">
                Find answers to common questions in our 
                <a href="{{ route('how-it-works') }}" class="text-purple-600 hover:text-purple-700 font-semibold">FAQ section</a>
            </p>
        </div>
    </div>
</div>
@endsection

