@extends('layouts.frontend')

@section('title', 'Owinace - Manage Leads, Team Goals & Write Proposals with AI')

@section('content')
    <!-- Hero Section -->
    <section class="relative bg-gradient-to-br from-indigo-50 via-white to-blue-50 py-20 sm:py-32">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-4xl font-bold tracking-tight text-gray-900 sm:text-6xl">
                    Manage Leads, Team Goals & Write Proposals with
                    <span class="text-indigo-600">AI</span>
                </h1>
                <p class="mt-6 text-lg leading-8 text-gray-600 max-w-2xl mx-auto">
                    Owinace is your all-in-one CRM solution to manage leads, track team goals, and create professional proposals powered by artificial intelligence.
                </p>
                <div class="mt-10 flex items-center justify-center gap-x-6">
                    @auth
                        <a
                            href="{{ url('/dashboard') }}"
                            class="rounded-md bg-indigo-600 px-6 py-3 text-base font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                        >
                            Go to Dashboard
                        </a>
                    @else
                        <a
                            href="{{ route('register') }}"
                            class="rounded-md bg-indigo-600 px-6 py-3 text-base font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                        >
                            Get started
                        </a>
                        <a
                            href="{{ route('login') }}"
                            class="text-base font-semibold leading-6 text-gray-900 hover:text-indigo-600"
                        >
                            Sign in <span aria-hidden="true">→</span>
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-24 sm:py-32 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-2xl lg:text-center">
                <h2 class="text-base font-semibold leading-7 text-indigo-600">Everything you need</h2>
                <p class="mt-2 text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
                    Powerful features to grow your business
                </p>
                <p class="mt-6 text-lg leading-8 text-gray-600">
                    Owinace provides all the tools you need to manage your sales pipeline, track team performance, and create winning proposals.
                </p>
            </div>
            <div class="mx-auto mt-16 max-w-2xl sm:mt-20 lg:mt-24 lg:max-w-none">
                <dl class="grid max-w-xl grid-cols-1 gap-x-8 gap-y-16 lg:max-w-none lg:grid-cols-3">
                    <!-- Lead Management Feature -->
                    <div class="flex flex-col">
                        <dt class="flex items-center gap-x-3 text-base font-semibold leading-7 text-gray-900">
                            <svg class="h-5 w-5 flex-none text-indigo-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path d="M10 9a3 3 0 100-6 3 3 0 000 6zM6 8a2 2 0 11-4 0 2 2 0 014 0zm.5 9a1.5 1.5 0 01-3 0v-.657c0-.027.102-.208.249-.289A19.8 19.8 0 0110 13c1.163 0 2.281.054 3.251.154.147.08.25.262.25.289v.657a1.5 1.5 0 01-3 0V17a1 1 0 00-1-1H7a1 1 0 00-1 1v.5zM17 7a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1zm-2 4a1 1 0 102 0 1 1 0 00-2 0zm-1 4a1 1 0 112 0 1 1 0 01-2 0z"/>
                            </svg>
                            Lead Management
                        </dt>
                        <dd class="mt-4 flex flex-auto flex-col text-base leading-7 text-gray-600">
                            <p class="flex-auto">
                                Organize and track your leads throughout the entire sales pipeline. Use kanban boards, tags, and custom fields to manage your prospects effectively.
                            </p>
                        </dd>
                    </div>

                    <!-- Team Goals Feature -->
                    <div class="flex flex-col">
                        <dt class="flex items-center gap-x-3 text-base font-semibold leading-7 text-gray-900">
                            <svg class="h-5 w-5 flex-none text-indigo-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-13a.75.75 0 00-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 000-1.5h-3.25V5z" clip-rule="evenodd"/>
                            </svg>
                            Team Goals
                        </dt>
                        <dd class="mt-4 flex flex-auto flex-col text-base leading-7 text-gray-600">
                            <p class="flex-auto">
                                Set, track, and achieve team goals with real-time progress monitoring. Keep your team aligned and motivated with transparent goal tracking.
                            </p>
                        </dd>
                    </div>

                    <!-- AI-Powered Proposals Feature -->
                    <div class="flex flex-col">
                        <dt class="flex items-center gap-x-3 text-base font-semibold leading-7 text-gray-900">
                            <svg class="h-5 w-5 flex-none text-indigo-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-5.5-2.5a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0zM10 12a5.98 5.98 0 00-4.793 2.39A6.483 6.483 0 0010 16.5a6.483 6.483 0 004.793-2.11A5.98 5.98 0 0010 12z" clip-rule="evenodd"/>
                            </svg>
                            AI-Powered Proposals
                        </dt>
                        <dd class="mt-4 flex flex-auto flex-col text-base leading-7 text-gray-600">
                            <p class="flex-auto">
                                Create professional proposals in minutes with AI assistance. Generate compelling content tailored to your clients' needs and increase your win rate.
                            </p>
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="bg-indigo-600">
        <div class="px-4 sm:px-6 lg:px-8 py-24 sm:py-32">
            <div class="mx-auto max-w-2xl text-center">
                <h2 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">
                    Ready to grow your business?
                </h2>
                <p class="mx-auto mt-6 max-w-xl text-lg leading-8 text-indigo-200">
                    Join teams that are already using Owinace to manage their leads, track goals, and win more deals.
                </p>
                <div class="mt-10 flex items-center justify-center gap-x-6">
                    @auth
                        <a
                            href="{{ url('/dashboard') }}"
                            class="rounded-md bg-white px-6 py-3 text-base font-semibold text-indigo-600 shadow-sm hover:bg-indigo-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white"
                        >
                            Go to Dashboard
                        </a>
                    @else
                        <a
                            href="{{ route('register') }}"
                            class="rounded-md bg-white px-6 py-3 text-base font-semibold text-indigo-600 shadow-sm hover:bg-indigo-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white"
                        >
                            Get started for free
                        </a>
                        <a href="{{ route('login') }}" class="text-base font-semibold leading-6 text-white hover:text-indigo-200">
                            Sign in <span aria-hidden="true">→</span>
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </section>
@endsection

