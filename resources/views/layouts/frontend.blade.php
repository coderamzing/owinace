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
        @vite(['resources/css/frontend.css', 'resources/css/app.css',  'resources/js/app.js'])

        @stack('styles')
    </head>
    <body class="font-sans antialiased" data-aos-easing="ease" data-aos-duration="400" data-aos-delay="0">

        <header class="bg-white sticky transition-all text-white top-0 inset-x-0 w-screen z-20 duration-300">
            <div class="container">
                <div class="flex items-center justify-between py-2.5 lg:py-4.5">
                    <div class="text-lg font-bold">
                        <a href="{{ route('home') }}">
                            <img src="/images/owinace.png" alt="Logo" class="h-8.5 lg:h-9">
                        </a>
                    </div>

                    <div id="navbar" class="lg:flex hidden justify-center gap-5">
                        <div class="m-1 hs-dropdown [--trigger:hover] relative inline-flex transition-all duration-300">
                            <button type="button" class="hs-dropdown-toggle cursor-pointer text-dark flex items-center py-2.5 font-medium {{ request()->routeIs('home') ? 'active' : '' }}" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown">
                                Home <i class="iconify tabler--chevron-down ps-5 size-4"></i>
                            </button>

                            <div class="hs-dropdown-menu border border-neutral-200 transition-[opacity,margin] rounded-2xl duration hs-dropdown-open:opacity-100 opacity-0 hidden w-60 bg-white mt-2 after:h-4 after:absolute after:-bottom-4 after:start-0 after:w-full before:h-4 before:absolute before:-top-4 before:start-0 before:w-full" role="menu">
                                <div class="p-5">
                                    <a href="{{ route('home') }}" class="flex items-center font-medium text-dark rounded-lg text-base py-1.25 hover:underline">
                                        Home
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="m-1 hs-dropdown [--trigger:hover] relative inline-flex transition-all duration-300">
                            <button type="button" class="hs-dropdown-toggle cursor-pointer !no-underline text-dark flex items-center py-2.5 font-medium" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown">
                                Product <i class="iconify tabler--chevron-down ps-5 size-4"></i>
                            </button>

                            <div class="hs-dropdown-menu border border-neutral-200 transition-[opacity,margin] rounded-2xl duration hs-dropdown-open:opacity-100 opacity-0 hidden w-[560px] bg-white mt-2 after:h-4 after:absolute after:-bottom-4 after:start-0 after:w-full before:h-4 before:absolute before:-top-4 before:start-0 before:w-full" role="menu">
                                <div class="grid grid-cols-2 p-5 gap-5">
                                    <div>
                                        <img src="/images/15-gb4efDx_.svg" alt="" class="rounded-2xl w-62.5">
                                    </div>

                                    <div class="flex flex-col justify-center gap-2.5 py-5">
                                        <a href="{{ route('home') }}#why-choose" class="flex items-center gap-1.25 p-4 transition-all duration-300 hover:bg-body-bg rounded-2xl">
                                            <div>
                                                <i class="iconify tabler--carambola text-black size-8"></i>
                                            </div>
                                            <div>
                                                <div class="text-black">Seamless onboarding</div>
                                                <p class="text-dark text-sm">Quick, easy setup.</p>
                                            </div>
                                        </a>

                                        <a href="{{ route('home') }}#hero" class="flex items-center gap-1.25 p-4 transition-all duration-300 hover:bg-body-bg rounded-2xl">
                                            <div>
                                                <i class="iconify tabler--settings text-black size-8"></i>
                                            </div>
                                            <div>
                                                <div class="text-black">Responsive design</div>
                                                <p class="text-dark text-sm">Perfect on any device.</p>
                                            </div>
                                        </a>

                                        <a href="{{ route('home') }}#clients" class="flex items-center gap-1.25 p-4 transition-all duration-300 hover:bg-body-bg rounded-2xl">
                                            <div>
                                                <i class="iconify tabler--file-text text-black size-8"></i>
                                            </div>
                                            <div>
                                                <div class="text-black">Integrated analytics</div>
                                                <p class="text-dark text-sm">Real-time insights.</p>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="m-1 hs-dropdown [--trigger:hover] relative inline-flex">
                            <button type="button" class="hs-dropdown-toggle cursor-pointer text-dark flex items-center py-2.5 font-medium" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown">
                                Pages
                                <i class="iconify tabler--chevron-down ps-5"></i>
                            </button>

                            <div class="hs-dropdown-menu border border-neutral-200 transition-[opacity,margin] rounded-2xl duration hs-dropdown-open:opacity-100 opacity-0 hidden w-[560px] bg-white mt-2 after:h-4 after:absolute after:-bottom-4 after:start-0 before:h-4 before:absolute before:-top-4 before:start-0 before:w-full" role="menu">
                                <div class="grid grid-cols-3 p-5 gap-10">
                                    <div>
                                        <a href="{{ route('how-it-works') }}" class="flex items-center font-medium text-dark rounded-lg text-base py-1.25 hover:underline">
                                            How it Works
                                        </a>
                                        <a href="{{ route('support') }}" class="flex items-center font-medium text-dark rounded-lg text-base py-1.25 hover:underline">
                                            Support
                                        </a>
                                        <a href="{{ route('faq') }}" class="flex items-center font-medium text-dark rounded-lg text-base py-1.25 hover:underline">
                                            FAQ
                                        </a>
                                    </div>
                                    <div>
                                        <a href="{{ route('privacy-policy') }}" class="flex items-center font-medium text-dark rounded-lg text-base py-1.25 hover:underline">
                                            Privacy Policy
                                        </a>
                                        <a href="{{ route('terms') }}" class="flex items-center font-medium text-dark rounded-lg text-base py-1.25 hover:underline">
                                            Terms
                                        </a>
                                        <a href="{{ route('refund-policy') }}" class="flex items-center font-medium text-dark rounded-lg text-base py-1.25 hover:underline">
                                            Refund Policy
                                        </a>
                                    </div>
                                    <div>
                                        <a href="{{ route('home') }}#clients" class="flex items-center font-medium text-dark rounded-lg text-base py-1.25 hover:underline">
                                            Statistics
                                        </a>
                                        <a href="{{ route('home') }}#blog" class="flex items-center font-medium text-dark rounded-lg text-base py-1.25 hover:underline">
                                            Features
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="m-1 hs-dropdown [--trigger:hover] relative inline-flex transition-all duration-300">
                            <button type="button" class="hs-dropdown-toggle cursor-pointer text-dark flex items-center py-2.5 font-medium" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown">
                                Account
                                <i class="iconify tabler--chevron-down ps-5 size-4"></i>
                            </button>

                            <div class="hs-dropdown-menu border border-neutral-200 transition-[opacity,margin] rounded-2xl duration hs-dropdown-open:opacity-100 opacity-0 hidden w-[200px] bg-white mt-2 after:h-4 after:absolute after:-bottom-4 after:start-0 before:h-4 before:absolute before:-top-4 before:start-0 before:w-full" role="menu">
                                <div class="p-5">
                                    @auth
                                        <a href="{{ url('/dashboard') }}" class="flex items-center font-medium text-dark rounded-lg text-base py-1.25 hover:underline">
                                            Dashboard
                                        </a>
                                    @else
                                        <a href="{{ route('login') }}" class="flex items-center font-medium text-dark rounded-lg text-base py-1.25 hover:underline">
                                            Log In
                                        </a>
                                        @if (Route::has('register'))
                                            <a href="{{ route('register') }}" class="flex items-center font-medium text-dark rounded-lg text-base py-1.25 hover:underline">
                                                Sign Up
                                            </a>
                                        @endif
                                    @endauth
                                </div>
                            </div>
                        </div>

                        <a href="#workwithus" class="flex items-center font-medium text-dark rounded-lg text-base py-1.25 hover:underline">
                            Contact us
                        </a>
                    </div>

                    <div class="flex flex-row justify-center items-center md:gap-4 gap-2.5">
                        <div class="md:flex hidden">
                            <a href="{{ route('register') }}" class="bg-primary text-dark hover:text-primary hover:bg-dark rounded-2xl px-7.5 py-3.5 font-medium transition-all duration-300">
                                Get Started
                            </a>
                        </div>

                        <div class="flex lg:hidden">
                            <button type="button" class="bg-dark text-white focus:text-black focus:bg-primary inline-flex justify-center items-center rounded-2xl md:size-13 size-11 p-3.5 font-medium transition-all duration-300" aria-haspopup="dialog" aria-expanded="false" aria-controls="mobileMenuOffcanvas" data-hs-overlay="#mobileMenuOffcanvas">
                                <span class="iconify tabler--menu-2 text-2xl size-5"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Mobile Menu (Offcanvas) -->
        <div id="mobileMenuOffcanvas" class="hs-overlay hs-overlay-open:translate-y-0 hidden -translate-y-full fixed top-4 inset-x-4 rounded-lg overflow-hidden transition-all duration-300 transform h-80 z-80 bg-white" role="dialog" tabindex="-1" aria-labelledby="mobileMenuOffcanvas-label">
            <div class="h-16 flex items-center justify-between px-4 border-b border-neutral-200 sticky top-0">
                <a href="{{ route('home') }}">
                    <img src="/image/logo.svg" alt="logo" class="h-8">
                </a>

                <button type="button" class="bg-neutral-600/15 text-neutral-600 size-8 flex justify-center items-center rounded-full" aria-label="Close" data-hs-overlay="#mobileMenuOffcanvas" aria-expanded="false">
                    <span class="sr-only">Close</span>
                    <i class="iconify tabler--x size-4"></i>
                </button>
            </div>

            <div class="flex flex-col p-4 overflow-y-auto h-[calc(100%-64px)]">
                <div class="hs-accordion-group">
                    <div class="hs-accordion">
                        <button class="hs-accordion-toggle text-dark w-full flex items-center justify-between py-2.5 font-medium">
                            <span>Home</span>
                            <i class="iconify tabler--chevron-down size-4 hs-accordion-active:rotate-180 transition-all"></i>
                        </button>

                        <div class="hs-accordion-content hidden w-full overflow-hidden transition-[height]">
                            <a href="{{ route('home') }}" class="flex items-center font-medium text-dark rounded-lg text-base py-1.25 hover:underline">
                                Home
                            </a>
                        </div>
                    </div>

                    <div class="hs-accordion">
                        <button class="hs-accordion-toggle text-dark w-full flex items-center justify-between py-2.5 font-medium">
                            <span>Pages</span>
                            <i class="iconify tabler--chevron-down size-4 hs-accordion-active:rotate-180 transition-all"></i>
                        </button>

                        <div class="hs-accordion-content hidden w-full overflow-hidden transition-[height]">
                            <div class="flex flex-col mt-2">
                                <a href="{{ route('how-it-works') }}" class="flex items-center font-medium text-dark rounded-lg text-base py-1.25 hover:underline">
                                    How it Works
                                </a>
                                <a href="{{ route('support') }}" class="flex items-center font-medium text-dark rounded-lg text-base py-1.25 hover:underline">
                                    Support
                                </a>
                                <a href="{{ route('faq') }}" class="flex items-center font-medium text-dark rounded-lg text-base py-1.25 hover:underline">
                                    FAQ
                                </a>
                                <a href="{{ route('privacy-policy') }}" class="flex items-center font-medium text-dark rounded-lg text-base py-1.25 hover:underline">
                                    Privacy Policy
                                </a>
                            </div>
                        </div>
                    </div>

                    <div>
                        <a href="#workwithus" class="text-dark text-base flex items-center py-2.5 font-medium hover:underline">Contact Us</a>
                    </div>

                    <div class="flex md:hidden mt-1">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="w-full bg-primary text-center text-dark hover:text-primary hover:bg-dark rounded-lg px-7.5 py-3.5 font-medium transition-all duration-300">
                                Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="w-full bg-primary text-center text-dark hover:text-primary hover:bg-dark rounded-lg px-7.5 py-3.5 font-medium transition-all duration-300">
                                Sign in
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>

        @yield('content')

        <!-- Footer -->
        <x-frontend-footer />

        @stack('scripts')
    </body>
</html>
