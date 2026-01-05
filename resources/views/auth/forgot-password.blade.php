@extends('layouts.frontend')

@section('title', 'Forgot Password')

@section('content')
    <section class="py-16 sm:py-20 bg-gradient-to-br from-indigo-50 via-white to-blue-50">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 items-center">
                <div class="hidden lg:flex flex-col space-y-4">
                    <p class="text-xs font-semibold tracking-[0.2em] uppercase text-indigo-600">
                        Reset your password
                    </p>
                    <h2 class="text-3xl font-semibold tracking-tight sm:text-4xl text-gray-900">
                        Get back to your workspace
                    </h2>
                    <p class="text-sm text-gray-600 max-w-md">
                        Enter your email address and we'll send you a link to reset your password so you can regain access to your account.
                    </p>
                </div>

                <div>
                    <div class="bg-white border border-gray-200 rounded-2xl shadow-xl px-6 py-8 sm:px-8 sm:py-10 max-w-md mx-auto">
                        <div class="space-y-6">
                            <div class="space-y-2 text-center lg:text-left">
                                <p class="text-xs font-semibold tracking-wide text-indigo-600 uppercase">
                                    Password recovery
                                </p>
                                <h1 class="text-2xl font-semibold tracking-tight text-gray-900 sm:text-3xl">
                                    Reset your password
                                </h1>
                                <p class="text-sm text-gray-600">
                                    Enter your email address and we'll send you a password reset link.
                                </p>
                            </div>

                            <!-- Session Status -->
                            <x-auth-session-status class="mb-4" :status="session('status')" />

                            <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
                                @csrf

                                <!-- Email Address -->
                                <div>
                                    <x-input-label for="email" :value="__('Work email')" />
                                    <x-text-input
                                        id="email"
                                        class="block mt-1 w-full"
                                        type="email"
                                        name="email"
                                        :value="old('email')"
                                        required
                                        autofocus
                                        autocomplete="username"
                                        placeholder="you@company.com"
                                    />
                                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                                </div>

                                <div class="space-y-4">
                                    <x-primary-button class="w-full justify-center">
                                        {{ __('Email Password Reset Link') }}
                                    </x-primary-button>

                                    @if (Route::has('login'))
                                        <p class="text-center text-xs text-gray-500">
                                            {{ __('Remember your password?') }}
                                            <a
                                                href="{{ route('login') }}"
                                                class="font-medium text-indigo-600 hover:text-indigo-700"
                                            >
                                                {{ __('Sign in instead') }}
                                            </a>
                                        </p>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
