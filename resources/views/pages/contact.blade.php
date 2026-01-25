@extends('layouts.frontend')

@section('title', 'Contact Us - ' . config('app.name', 'LeadCliq'))

@push('meta')
<!-- Hreflang -->
<link rel="alternate" hreflang="en" href="{{ url()->current() }}" />
<link rel="alternate" hreflang="x-default" href="{{ url()->current() }}" />

<meta name="description" content="Get in touch with LeadCliq support team. Contact us for inquiries about AI proposals, lead management, or technical support.">
<meta name="keywords" content="contact LeadCliq, support, customer service, AI proposals help, lead CRM support">
<meta name="author" content="LeadCliq">

<!-- Open Graph / Facebook -->
<meta property="og:type" content="website">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:title" content="Contact Us - LeadCliq">
<meta property="og:description" content="Get in touch with LeadCliq support team. Contact us for inquiries about AI proposals, lead management, or technical support.">
<meta property="og:image" content="{{ asset('images/leadcliq-og.png') }}">

<!-- Twitter -->
<meta property="twitter:card" content="summary_large_image">
<meta property="twitter:url" content="{{ url()->current() }}">
<meta property="twitter:title" content="Contact Us - LeadCliq">
<meta property="twitter:description" content="Get in touch with LeadCliq support team. Contact us for inquiries about AI proposals, lead management, or technical support.">
<meta property="twitter:image" content="{{ asset('images/leadcliq-og.png') }}">
@endpush

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
            "name" => "Contact",
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
    "@type" => "ContactPage",
    "name" => "Contact Us - LeadCliq",
    "description" => "Get in touch with LeadCliq support team",
    "url" => url()->current()
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
@endphp
</script>

<section class="bg-body-bg lg:py-25 md:py-22.5 py-17.5">
        <div class="container-small">

            <div class="text-center md:mb-12.5 mb-7.5 aos-init aos-animate"
                 data-aos="fade-up"
                 data-aos-delay="150"
                 data-aos-duration="500"
                 data-aos-easing="ease-in-out">
                <h2 class="mb-2.5 lg:text-6xl md:text-4.6xl text-4xl">We're ready to assist you</h2>
                <p>Have questions? We’re ready to help!</p>
            </div>

            <div class="grid lg:grid-cols-2 lg:gap-12.5 md:gap-5 gap-7.5 aos-init aos-animate"
                 data-aos="fade-up"
                 data-aos-delay="150"
                 data-aos-duration="500"
                 data-aos-easing="ease-in-out">

                <div class="bg-primary lg:p-10 p-5 rounded-2xl h-full flex justify-between gap-12.5 flex-col">
                    <div>
                        <h2 class="text-2.5xl">
                            For any inquiries or feedback, our team is here to assist you.
                        </h2>
                    </div>

                    <div class="flex gap-2.5 flex-col">
                        <a href="mailto:support@leadcliq.ai" class="underline text-dark">support@leadcliq.ai</a>
                        <a href="tel:+917710113366" class="underline text-dark">+91 7710113366</a>
                    </div>
                </div>

                <form action="#" method="POST">
                    @csrf
                    <!-- Name  -->
                    <div class="mb-5">
                        <label for="name" class="mb-1.25 block font-normal">Name</label>
                        <input
                            class="rounded-2xl py-2.5 px-5 border border-neutral-200 w-full h-14"
                            maxlength="256"
                            name="name"
                            id="name"
                            placeholder="John Deo"
                            type="text">
                    </div>

                    <!-- Email Address  -->
                    <div class="mb-5">
                        <label for="email" class="mb-1.25 block font-normal">Email address</label>
                        <input
                            class="rounded-2xl py-2.5 px-5 border border-neutral-200 w-full h-14"
                            maxlength="256"
                            name="email"
                            id="email"
                            placeholder="hello@example.com"
                            type="email">
                    </div>

                    <div class="mb-5 flex gap-5 lg:flex-row flex-col">
                        <!-- Phone  -->
                        <div class="w-full">
                            <label for="phone" class="mb-1.25 block font-normal">Phone</label>
                            <input
                                class="rounded-2xl py-2.5 px-5 border border-neutral-200 w-full h-14"
                                name="phone"
                                id="phone"
                                placeholder="+2 123 456 66"
                                type="text">
                        </div>
                        <!-- Subject  -->
                        <div class="w-full">
                            <label for="subject" class="mb-1.25 block font-normal">Subject</label>
                            <input
                                class="rounded-2xl py-2.5 px-5 border border-neutral-200 w-full h-14"
                                name="subject"
                                id="subject"
                                placeholder="Subject"
                                type="text">
                        </div>
                    </div>

                    <!-- Message  -->
                    <div class="mb-5">
                        <label for="message" class="mb-1.25 block font-normal">Message</label>
                        <textarea
                            class="rounded-2xl py-2.5 px-5 border border-neutral-200 w-full h-50"
                            name="message"
                            id="message"
                            maxlength="5000"
                            placeholder="Comment"></textarea>
                    </div>

                    <div>
                        <button
                            type="submit"
                            class="md:h-14 w-full py-3.5 lg:px-7.5 px-6.5 text-center bg-dark font-medium rounded-2xl text-white transition-all duration-300 hover:text-primary">
                            Submit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection

