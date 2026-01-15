@extends('layouts.frontend')

@section('title', 'Forgot Password')

@section('content')
    <section class="bg-white lg:py-25 md:py-22.5 py-17.5 md:h-dvh flex items-center">
        <div class="container">
            <div class="lg:w-4/10 md:w-7/10 mx-auto aos-init aos-animate"
                 data-aos="fade-up"
                 data-aos-delay="150"
                 data-aos-duration="500"
                 data-aos-easing="ease-in-out">
                <div class="lg:mb-12.5 md:mb-10 mb-7.5 text-center">
                    <h1 class="lg:text-6xl md:text-5.5xl text-4xl">Reset password</h1>
                    <p class="mb-2.5">Enter your registered email to reset your password.</p>
                </div>

                <div class="bg-body-bg md:p-10 p-5 rounded-2xl">
                    <!-- Session Status -->
                    <x-auth-session-status class="mb-4" :status="session('status')" />

                    <form method="POST" action="{{ route('password.email') }}" class="mb-3.75 space-y-5">
                        @csrf

                        <!-- Email Address -->
                        <div class="mb-5">
                            <x-input-label for="email" :value="__('Work email')" class="mb-1.25 block font-normal" />
                            <x-text-input
                                id="email"
                                class="rounded-2xl py-2.5 px-5 border border-neutral-200 w-full h-14"
                                type="email"
                                name="email"
                                :value="old('email')"
                                required
                                autofocus
                                autocomplete="username"
                                placeholder="Your email"
                            />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <div>
                            <x-primary-button
                                class="md:h-14 w-full py-3.5 lg:px-7.5 px-6.5 text-center bg-dark font-medium rounded-2xl text-white transition-all duration-300 hover:text-primary justify-center"
                            >
                                {{ __('Send reset link') }}
                            </x-primary-button>
                        </div>
                    </form>

                    @if (Route::has('login'))
                        <div class="flex md:justify-between md:gap-5 md:flex-row gap-1.25 flex-col">
                            <p>
                                {{ __('Remember your password?') }}
                                <a href="{{ route('login') }}" class="underline text-dark">
                                    {{ __('Log in') }}
                                </a>
                            </p>
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </section>
@endsection
