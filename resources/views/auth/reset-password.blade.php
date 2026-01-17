@extends('layouts.frontend')

@section('title', 'Reset Password')

@section('content')
    <section class="bg-white lg:py-25 md:py-22.5 py-17.5 md:min-h-[50vh] flex items-center">
        <div class="container">
            <div class="lg:w-4/10 md:w-7/10 mx-auto" data-aos="fade-up" data-aos-delay="150" data-aos-duration="500"
                 data-aos-easing="ease-in-out">
                <div class="lg:mb-12.5 md:mb-10 mb-7.5 text-center">
                    <h1 class="lg:text-6xl md:text-5.5xl text-4xl">Reset password</h1>
                    <p class="mb-2.5">Enter your email and a new password below to securely update your account.</p>
                </div>

                <div class="bg-body-bg md:p-10 p-5 rounded-2xl">
                    <!-- Session Status -->
                    <x-auth-session-status class="mb-4" :status="session('status')" />

                    <form method="POST" action="{{ route('password.store') }}" class="mb-3.75 space-y-5">
                        @csrf

                        <!-- Password Reset Token -->
                        <input type="hidden" name="token" value="{{ $request->route('token') }}">

                        <!-- Email Address -->
                        <div class="mb-5">
                            <x-text-input
                                id="email"
                                class="rounded-2xl py-2.5 px-5 border border-neutral-200 w-full h-14 block mt-1"
                                type="hidden"
                                name="email"
                                :value="old('email', $request->email)"
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
                                class="rounded-2xl py-2.5 px-5 border border-neutral-200 w-full h-14 block mt-1"
                                type="password"
                                name="password"
                                :value="old('password')"
                                required
                                autocomplete="new-password"
                                placeholder="Password"
                            />
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <!-- Confirm Password -->
                        <div class="mb-5">
                            <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="mb-1.25 block font-normal" />
                            <x-text-input
                                id="password_confirmation"
                                class="rounded-2xl py-2.5 px-5 border border-neutral-200 w-full h-14 block mt-1"
                                type="password"
                                name="password_confirmation"
                                :value="old('password_confirmation')"
                                required
                                autocomplete="new-password"
                                placeholder="Confirm password"
                            />
                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                        </div>

                        <div>
                            <x-primary-button
                                class="md:h-14 w-full py-3.5 lg:px-7.5 px-6.5 text-center bg-dark font-medium rounded-2xl text-white transition-all duration-300 hover:text-primary justify-center"
                            >
                                {{ __('Reset Password') }}
                            </x-primary-button>
                        </div>
                    </form>

                    @if (Route::has('login'))
                        <div>
                            <p>
                                {{ __('Remembered your password?') }}
                                <a href="{{ route('login') }}" class="underline text-dark">
                                    {{ __('Sign in instead') }}
                                </a>
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection
