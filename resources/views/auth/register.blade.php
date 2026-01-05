@extends('layouts.frontend')

@section('title', 'Register')

@section('content')
    <section class="py-16 sm:py-20 bg-gradient-to-br from-indigo-50 via-white to-blue-50">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 items-center">
                <div class="hidden lg:flex flex-col space-y-4">
                    <p class="text-xs font-semibold tracking-[0.2em] uppercase text-indigo-600">
                        Designed for modern teams
                    </p>
                    <h2 class="text-3xl font-semibold tracking-tight sm:text-4xl text-gray-900">
                        Launch your HR workspace
                    </h2>
                    <p class="text-sm text-gray-600 max-w-md">
                        Create a shared space for employee data, attendance, and approvals – then invite your team in just a few clicks.
                    </p>
                </div>

                <div>
                    <div class="bg-white border border-gray-200 rounded-2xl shadow-xl px-6 py-8 sm:px-8 sm:py-10 max-w-md mx-auto">
                        <div class="space-y-6">
                            <div class="space-y-2 text-center lg:text-left">
                                <p class="text-xs font-semibold tracking-wide text-indigo-600 uppercase">
                                    Get started in minutes
                                </p>
                                <h1 class="text-2xl font-semibold tracking-tight text-gray-900 sm:text-3xl">
                                    Create your HR workspace
                                </h1>
                                <p class="text-sm text-gray-600">
                                    Sign up to create your workspace and start managing your team effectively.
                                </p>
                            </div>

                            <form method="POST" action="{{ route('register') }}" class="space-y-5">
                                @csrf

                                <!-- Name -->
                                <div>
                                    <x-input-label for="name" :value="__('Full name')" />
                                    <x-text-input
                                        id="name"
                                        class="block mt-1 w-full"
                                        type="text"
                                        name="name"
                                        :value="old('name')"
                                        required
                                        autofocus
                                        autocomplete="name"
                                        placeholder="Jane Doe"
                                    />
                                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                                </div>

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
                                        autocomplete="username"
                                        placeholder="you@company.com"
                                    />
                                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                                </div>

                                <!-- Workspace Name -->
                                <div>
                                    <div class="flex items-center justify-between">
                                        <x-input-label for="workspace_name" :value="__('Workspace name')" />
                                        <p class="text-xs text-gray-400">
                                            {{ __('You can change this later from settings.') }}
                                        </p>
                                    </div>
                                    <x-text-input
                                        id="workspace_name"
                                        class="block mt-1 w-full"
                                        type="text"
                                        name="workspace_name"
                                        :value="old('workspace_name')"
                                        required
                                        autocomplete="organization"
                                        placeholder="Acme HR"
                                    />
                                    <x-input-error :messages="$errors->get('workspace_name')" class="mt-2" />
                                </div>

                                <!-- Password -->
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <x-input-label for="password" :value="__('Password')" />

                                        <x-text-input
                                            id="password"
                                            class="block mt-1 w-full"
                                            type="password"
                                            name="password"
                                            required
                                            autocomplete="new-password"
                                            placeholder="••••••••"
                                        />

                                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

                                        <x-text-input
                                            id="password_confirmation"
                                            class="block mt-1 w-full"
                                            type="password"
                                            name="password_confirmation"
                                            required
                                            autocomplete="new-password"
                                            placeholder="••••••••"
                                        />

                                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    <x-primary-button class="w-full justify-center">
                                        {{ __('Create workspace') }}
                                    </x-primary-button>

                                    @if (Route::has('login'))
                                        <p class="text-center text-xs text-gray-500">
                                            {{ __('Already have an account?') }}
                                            <a
                                                href="{{ route('login') }}"
                                                class="font-medium text-indigo-600 hover:text-indigo-700"
                                            >
                                                {{ __('Sign in') }}
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
