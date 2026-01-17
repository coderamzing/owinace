@extends('layouts.frontend')

@section('title', 'Email Confirmation')

@section('content')
    <section class="bg-white lg:py-25 md:py-22.5 py-17.5 md:min-h-[50vh] flex items-center">
        <div class="container">
            <div class="lg:w-2/5 md:w-7/10 mx-auto aos-init aos-animate" data-aos="fade-up" data-aos-delay="150" data-aos-duration="500" data-aos-easing="ease-in-out">
                <div class="lg:mb-12.5 md:mb-10 mb-7.5 text-center">
                    <h1 class="lg:text-6xl md:text-5.5xl text-4xl">Email confirmation </h1>
                    <p class="mb-2.5">We sent you a verification link via email. </p>
                </div>

                <div class="bg-body-bg md:p-10 p-5 rounded-2xl">
                    <h2 class="text-3xl text-center">Thank you! Check your Email for a verification link.</h2>
                </div>
            </div>
        </div>
    </section>
@endsection
