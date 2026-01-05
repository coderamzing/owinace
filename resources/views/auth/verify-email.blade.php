@extends('layouts.frontend')

@section('title', 'Verify Email')

@section('content')
    <section class="py-16 sm:py-20 bg-gradient-to-br from-indigo-50 via-white to-blue-50">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 items-center">
                <div class="hidden lg:flex flex-col space-y-4">
                    <p class="text-xs font-semibold tracking-[0.2em] uppercase text-indigo-600">
                        Verify your email
                    </p>
                    <h2 class="text-3xl font-semibold tracking-tight sm:text-4xl text-gray-900">
                        Check your inbox
                    </h2>
                    <p class="text-sm text-gray-600 max-w-md">
                        We've sent you a verification link. Please check your email and click the link to verify your account.
                    </p>
                </div>

                <div>
                    <div class="bg-white border border-gray-200 rounded-2xl shadow-xl px-6 py-8 sm:px-8 sm:py-10 max-w-md mx-auto">
                        <div class="space-y-6">
                            <div class="space-y-2 text-center lg:text-left">
                                <p class="text-xs font-semibold tracking-wide text-indigo-600 uppercase">
                                    Email verification
                                </p>
                                <h1 class="text-2xl font-semibold tracking-tight text-gray-900 sm:text-3xl">
                                    Verify your email
                                </h1>
                            </div>

                            <div class="mb-4 text-sm text-gray-600">
                                {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
                            </div>

                            @if (session('status') == 'verification-link-sent')
                                <div class="mb-4 font-medium text-sm text-green-600">
                                    {{ __('A new verification link has been sent to the email address you provided during registration.') }}
                                </div>
                            @endif

                            <div class="mt-4 flex items-center justify-between">
                                <form method="POST" action="{{ route('verification.send') }}">
                                    @csrf
                                    <x-primary-button>
                                        {{ __('Resend Verification Email') }}
                                    </x-primary-button>
                                </form>

                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        {{ __('Log Out') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
