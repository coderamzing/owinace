@extends('layouts.frontend')

@section('title', 'Login')

@section('content')
    <section class="bg-white lg:py-25 md:py-22.5 py-17.5 md:h-dvh flex items-center">
        <div class="container">
            <div class="lg:w-4/10 md:w-7/10 mx-auto aos-init aos-animate" data-aos="fade-up" data-aos-delay="150" data-aos-duration="500" data-aos-easing="ease-in-out">
                <div class="lg:mb-12.5 md:mb-10 mb-7.5 text-center">
                    <h1 class="lg:text-6xl md:text-5.5xl text-4xl">log-in</h1>
                    <p class="mb-2.5">Please fill your email and password to login.</p>
                </div>

                <div class="bg-body-bg md:p-10 p-5 rounded-2xl">
                    <!-- Session Status -->
                    <x-auth-session-status class="mb-4" :status="session('status')" />

                    <!-- Resend Verification Email Notice -->
                    @if($errors->has('email') && str_contains($errors->first('email'), 'verify') && session('unverified_email'))
                        <div class="rounded-lg bg-yellow-50 border border-yellow-200 p-4 mb-4">
                            <div class="flex flex-col space-y-3">
                                <div class="flex items-start">
                                    <svg class="h-5 w-5 text-yellow-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    <div class="flex-1">
                                        <h3 class="text-sm font-medium text-yellow-800">
                                            Email Verification Required
                                        </h3>
                                        <p class="mt-1 text-sm text-yellow-700">
                                            Your email address hasn't been verified yet. Please check your inbox for the verification link or click the button below to resend it.
                                        </p>
                                    </div>
                                </div>
                                <form method="POST" action="{{ route('verification.resend') }}" class="mt-2">
                                    @csrf
                                    <input type="hidden" name="email" value="{{ session('unverified_email') }}">
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 focus:bg-yellow-700 active:bg-yellow-800 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                        Resend Verification Email
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" class="mb-3.75 space-y-5">
                        @csrf

                        <!-- Email Address -->
                        <div class="mb-5">
                            <x-input-label for="email" :value="__('Email')" class="mb-1.25 block font-normal" />
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

                        <!-- Password -->
                        <div class="mb-5">
                            <x-input-label for="password" :value="__('Password')" class="mb-1.25 block font-normal" />
                            <x-text-input
                                id="password"
                                class="rounded-2xl py-2.5 px-5 border border-neutral-200 w-full h-14"
                                type="password"
                                name="password"
                                required
                                autocomplete="current-password"
                                placeholder="Password"
                            />
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <!-- Remember Me -->
                        <div class="mb-5 flex items-center justify-between">
                            <label for="remember_me" class="inline-flex items-center gap-2">
                                <input
                                    id="remember_me"
                                    type="checkbox"
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                    name="remember"
                                >
                                <span class="text-sm text-gray-600">{{ __('Remember this device') }}</span>
                            </label>
                        </div>

                        <div>
                            <button type="submit" class="md:h-14 w-full py-3.5 lg:px-7.5 px-6.5 text-center bg-dark font-medium rounded-2xl text-white transition-all duration-300 hover:text-primary">
                                {{ __('Submit') }}
                            </button>
                        </div>
                    </form>

                    <div class="flex md:justify-between md:gap-5 md:flex-row gap-1.25 flex-col">
                        @if (Route::has('register'))
                            <p>
                                {{ __("Don't have an account?") }}
                                <a href="{{ route('register') }}" class="underline text-dark">
                                    {{ __('Sign up') }}
                                </a>
                            </p>
                        @endif

                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="underline text-dark">
                                {{ __('Forgot password?') }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
