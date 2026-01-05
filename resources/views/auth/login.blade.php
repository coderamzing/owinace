@extends('layouts.frontend')

@section('title', 'Login')

@section('content')
    <section class="py-16 sm:py-20 bg-gradient-to-br from-indigo-50 via-white to-blue-50">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 items-center">
                <div class="hidden lg:flex flex-col space-y-4">
                    <p class="text-xs font-semibold tracking-[0.2em] uppercase text-indigo-600">
                        Secure employee access
                    </p>
                    <h2 class="text-3xl font-semibold tracking-tight sm:text-4xl text-gray-900">
                        Log in to manage your people
                    </h2>
                    <p class="text-sm text-gray-600 max-w-md">
                        View attendance, approve leave, and keep your HR operations running smoothly from a single dashboard.
                    </p>
                </div>

                <div>
                    <div class="bg-white border border-gray-200 rounded-2xl shadow-xl px-6 py-8 sm:px-8 sm:py-10 max-w-md mx-auto">
                        <div class="space-y-6">
                            <div class="space-y-2 text-center lg:text-left">
                                <p class="text-xs font-semibold tracking-wide text-indigo-600 uppercase">
                                    Welcome back
                                </p>
                                <h1 class="text-2xl font-semibold tracking-tight text-gray-900 sm:text-3xl">
                                    Sign in to your workspace
                                </h1>
                                <p class="text-sm text-gray-600">
                                    Access attendance, leave requests, and everything your team needs in one place.
                                </p>
                            </div>

                            <!-- Session Status -->
                            <x-auth-session-status class="mb-4" :status="session('status')" />

                            <!-- Resend Verification Email Notice -->
                            @if($errors->has('email') && str_contains($errors->first('email'), 'verify') && session('unverified_email'))
                                <div class="rounded-lg bg-yellow-50 border border-yellow-200 p-4">
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

                            <form method="POST" action="{{ route('login') }}" class="space-y-5">
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

                                <!-- Password -->
                                <div>
                                    <div class="flex items-center justify-between">
                                        <x-input-label for="password" :value="__('Password')" />

                                        @if (Route::has('password.request'))
                                            <a
                                                class="text-xs font-medium text-indigo-600 hover:text-indigo-700 focus:outline-none focus:underline"
                                                href="{{ route('password.request') }}"
                                            >
                                                {{ __('Forgot password?') }}
                                            </a>
                                        @endif
                                    </div>

                                    <x-text-input
                                        id="password"
                                        class="block mt-1 w-full"
                                        type="password"
                                        name="password"
                                        required
                                        autocomplete="current-password"
                                        placeholder="••••••••"
                                    />

                                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                                </div>

                                <!-- Remember Me -->
                                <div class="flex items-center justify-between">
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

                                <div class="space-y-4">
                                    <x-primary-button class="w-full justify-center">
                                        {{ __('Sign in') }}
                                    </x-primary-button>

                                    @if (Route::has('register'))
                                        <p class="text-center text-xs text-gray-500">
                                            {{ __("Don't have an account?") }}
                                            <a
                                                href="{{ route('register') }}"
                                                class="font-medium text-indigo-600 hover:text-indigo-700"
                                            >
                                                {{ __('Create a workspace') }}
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
