@extends('layouts.frontend')

@section('title', 'Register')

@section('content')
    <section class="bg-white lg:py-25 md:py-22.5 py-17.5 md:min-h-[50vh] flex items-center">
        <div class="container">
            <div class="lg:w-4/10 md:w-7/10 mx-auto" data-aos="fade-up" data-aos-delay="150" data-aos-duration="500"
                 data-aos-easing="ease-in-out">
                <div class="lg:mb-12.5 md:mb-10 mb-7.5 text-center">
                    <h1 class="lg:text-6xl md:text-5.5xl text-4xl">Sign up</h1>
                    <p class="mb-2.5">Create an account and start using LeadCliq.</p>
                </div>

                <div class="bg-body-bg md:p-10 p-5 rounded-2xl">
                    <form method="POST" action="{{ route('register') }}" class="mb-3.75 space-y-5">
                        @csrf

                        <!-- General Error Messages -->
                        @if($errors->any())
                            <div class="mb-5 p-4 bg-red-50 border border-red-200 rounded-2xl">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3 flex-1">
                                        <h3 class="text-sm font-medium text-red-800">
                                            {{ __('There were errors with your submission:') }}
                                        </h3>
                                        <div class="mt-2 text-sm text-red-700">
                                            <ul class="list-disc list-inside space-y-1">
                                                @foreach($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Name -->
                        <div class="mb-5">
                            <x-input-label for="name" :value="__('Full name')" class="mb-1.25 block font-normal" />
                            <x-text-input
                                id="name"
                                class="rounded-2xl py-2.5 px-5 border border-neutral-200 w-full h-14 block mt-1"
                                type="text"
                                name="name"
                                :value="old('name')"
                                required
                                autofocus
                                autocomplete="name"
                                placeholder="Your name"
                            />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <!-- Email Address -->
                        <div class="mb-5">
                            <x-input-label for="email" :value="__('Work email')" class="mb-1.25 block font-normal" />
                            <x-text-input
                                id="email"
                                class="rounded-2xl py-2.5 px-5 border border-neutral-200 w-full h-14 block mt-1"
                                type="email"
                                name="email"
                                :value="old('email')"
                                required
                                autocomplete="username"
                                placeholder="Your email"
                            />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <!-- Phone Number -->
                        <div class="mb-5">
                            <x-input-label for="phone_number" :value="__('Phone number')" class="mb-1.25 block font-normal" />
                            <x-text-input
                                id="phone_number"
                                class="rounded-2xl py-2.5 px-5 border border-neutral-200 w-full h-14 block mt-1"
                                type="tel"
                                name="phone_number"
                                :value="old('phone_number')"
                                required
                                autocomplete="tel"
                                placeholder="+1 (555) 000-0000"
                            />
                            <x-input-error :messages="$errors->get('phone_number')" class="mt-2" />
                        </div>

                        <!-- Workspace Name -->
                        <div class="mb-5">
                            <div class="flex items-center justify-between">
                                <x-input-label for="workspace_name" :value="__('Workspace name')" class="mb-1.25 block font-normal" />
                                <p class="text-xs text-gray-400">
                                    {{ __('You can change this later from settings.') }}
                                </p>
                            </div>
                            <x-text-input
                                id="workspace_name"
                                class="rounded-2xl py-2.5 px-5 border border-neutral-200 w-full h-14 block mt-1"
                                type="text"
                                name="workspace_name"
                                :value="old('workspace_name')"
                                required
                                autocomplete="organization"
                                placeholder="Workspace name"
                            />
                            <x-input-error :messages="$errors->get('workspace_name')" class="mt-2" />
                        </div>

                        <!-- Password -->
                        <div class="grid gap-4 sm:grid-cols-1">
                            <div class="mb-5 sm:mb-0">
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

                            <div class="mb-5 sm:mb-0">
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
                        </div>

                        <div>
                            <x-primary-button
                                class="md:h-14 w-full py-3.5 lg:px-7.5 px-6.5 text-center bg-dark font-medium rounded-2xl text-white transition-all duration-300 hover:text-primary justify-center"
                            >
                                {{ __('Create workspace') }}
                            </x-primary-button>
                        </div>
                    </form>

                    @if (Route::has('login'))
                        <div>
                            <p>
                                {{ __('Already have an account?') }}
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
