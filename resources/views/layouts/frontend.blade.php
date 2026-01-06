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
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex items-center">
                            <a href="{{ route('home') }}" class="text-xl font-semibold text-gray-900">
                                {{ config('app.name', 'Owinace') }}
                            </a>
                        </div>

                        <div class="flex items-center gap-4">
                            @auth
                                <a
                                    href="{{ url('/dashboard') }}"
                                    class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium"
                                >
                                    Dashboard
                                </a>
                            @else
                                <a
                                    href="{{ route('login') }}"
                                    class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium"
                                >
                                    Log in
                                </a>

                                @if (Route::has('register'))
                                    <a
                                        href="{{ route('register') }}"
                                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium"
                                    >
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
    </body>
</html>
