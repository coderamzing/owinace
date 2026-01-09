<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', config('app.name', 'Laravel'))</title>

        @stack('meta')

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        <!-- Styles / Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @stack('styles')
    </head>
    <body class="font-sans antialiased">
        <!-- Navigation -->
        @if (Route::has('login'))
            <nav class="bg-white border-b border-gray-200">
                <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-[24px]">
                    <div class="flex justify-between h-16">
                        <!-- Logo -->
                        <div class="flex items-center">
                            <a href="{{ route('home') }}" class="text-xl font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-rocket text-indigo-600 mr-2"></i>
                                {{ config('app.name', 'Owinace') }}
                            </a>
                        </div>

                        <!-- Desktop Navigation Menu -->
                        <div class="hidden md:flex items-center space-x-1">
                            <a href="{{ route('home') }}" class="text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 px-4 py-2 rounded-md text-sm font-medium transition-colors {{ request()->routeIs('home') ? 'text-indigo-600 bg-indigo-50' : '' }}">
                                <i class="fas fa-home mr-1"></i> Home
                            </a>
                            <a href="{{ route('how-it-works') }}" class="text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 px-4 py-2 rounded-md text-sm font-medium transition-colors {{ request()->routeIs('how-it-works') ? 'text-indigo-600 bg-indigo-50' : '' }}">
                                <i class="fas fa-lightbulb mr-1"></i> How it Works
                            </a>
                            <a href="{{ route('support') }}" class="text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 px-4 py-2 rounded-md text-sm font-medium transition-colors {{ request()->routeIs('support') ? 'text-indigo-600 bg-indigo-50' : '' }}">
                                <i class="fas fa-headset mr-1"></i> Support
                            </a>
                            <a href="{{ route('faq') }}" class="text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 px-4 py-2 rounded-md text-sm font-medium transition-colors {{ request()->routeIs('faq') ? 'text-indigo-600 bg-indigo-50' : '' }}">
                                <i class="fas fa-question-circle mr-1"></i> FAQ
                            </a>
                        </div>

                        <!-- Auth Buttons & Mobile Menu Button -->
                        <div class="flex items-center gap-2">
                            @auth
                                <a
                                    href="{{ url('/dashboard') }}"
                                    class="hidden md:inline-flex text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium"
                                >
                                    <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                                </a>
                            @else
                                <a
                                    href="{{ route('login') }}"
                                    class="hidden md:inline-flex text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium"
                                >
                                    Log in
                                </a>

                                @if (Route::has('register'))
                                    <a
                                        href="{{ route('register') }}"
                                        class="hidden md:inline-flex bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors"
                                    >
                                        Register
                                    </a>
                                @endif
                            @endauth

                            <!-- Mobile menu button -->
                            <button type="button" class="md:hidden inline-flex items-center justify-center p-2 rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500" id="mobile-menu-button">
                                <i class="fas fa-bars text-xl"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Mobile menu -->
                <div class="hidden md:hidden" id="mobile-menu">
                    <div class="px-2 pt-2 pb-3 space-y-1 bg-white border-t border-gray-200">
                        <a href="{{ route('home') }}" class="block text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('home') ? 'text-indigo-600 bg-indigo-50' : '' }}">
                            <i class="fas fa-home mr-2"></i> Home
                        </a>
                        <a href="{{ route('how-it-works') }}" class="block text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('how-it-works') ? 'text-indigo-600 bg-indigo-50' : '' }}">
                            <i class="fas fa-lightbulb mr-2"></i> How it Works
                        </a>
                        <a href="{{ route('support') }}" class="block text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('support') ? 'text-indigo-600 bg-indigo-50' : '' }}">
                            <i class="fas fa-headset mr-2"></i> Support
                        </a>
                        <a href="{{ route('faq') }}" class="block text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('faq') ? 'text-indigo-600 bg-indigo-50' : '' }}">
                            <i class="fas fa-question-circle mr-2"></i> FAQ
                        </a>
                        
                        <div class="border-t border-gray-200 pt-3 mt-3">
                            @auth
                                <a href="{{ url('/dashboard') }}" class="block text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-base font-medium">
                                    <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="block text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-base font-medium">
                                    Log in
                                </a>
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="block bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-2 rounded-md text-base font-medium mt-2">
                                        Register
                                    </a>
                                @endif
                            @endauth
                        </div>
                    </div>
                </div>
            </nav>
        @endif

        @yield('content')

        <!-- Footer -->
        <x-frontend-footer />

        @stack('scripts')

        <!-- Mobile Menu Toggle Script -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const mobileMenuButton = document.getElementById('mobile-menu-button');
                const mobileMenu = document.getElementById('mobile-menu');

                if (mobileMenuButton && mobileMenu) {
                    mobileMenuButton.addEventListener('click', function() {
                        mobileMenu.classList.toggle('hidden');
                        
                        // Toggle icon
                        const icon = this.querySelector('i');
                        if (mobileMenu.classList.contains('hidden')) {
                            icon.classList.remove('fa-times');
                            icon.classList.add('fa-bars');
                        } else {
                            icon.classList.remove('fa-bars');
                            icon.classList.add('fa-times');
                        }
                    });

                    // Close mobile menu when clicking outside
                    document.addEventListener('click', function(event) {
                        const isClickInside = mobileMenuButton.contains(event.target) || mobileMenu.contains(event.target);
                        
                        if (!isClickInside && !mobileMenu.classList.contains('hidden')) {
                            mobileMenu.classList.add('hidden');
                            const icon = mobileMenuButton.querySelector('i');
                            icon.classList.remove('fa-times');
                            icon.classList.add('fa-bars');
                        }
                    });
                }
            });
        </script>
    </body>
</html>
