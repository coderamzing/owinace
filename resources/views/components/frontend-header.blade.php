<header class="bg-white sticky transition-all text-white top-0 inset-x-0 w-screen z-20 duration-300">
    <div class="container">
        <div class="flex items-center justify-between py-2.5 lg:py-4.5">
            <div class="text-lg font-bold">
                <a href="{{ route('home') }}">
                    <img src="/images/leadcliq.png" alt="Logo" class="h-8.5 lg:h-9">
                </a>
            </div>

            <div id="navbar" class="lg:flex hidden justify-center gap-5">
                <a href="{{ route('home') }}" class="flex items-center font-medium text-dark rounded-lg text-base py-2.5 hover:underline {{ request()->routeIs('home') ? 'font-bold' : '' }}">
                    Home
                </a>
                <a href="{{ route('how-it-works') }}" class="flex items-center font-medium text-dark rounded-lg text-base py-2.5 hover:underline {{ request()->routeIs('how-it-works') ? 'font-bold' : '' }}">
                    How it Works
                </a>
                <a href="{{ route('pricing') }}" class="flex items-center font-medium text-dark rounded-lg text-base py-2.5 hover:underline {{ request()->routeIs('pricing') ? 'font-bold' : '' }}">
                    Pricing
                </a>
                <a href="{{ route('faq') }}" class="flex items-center font-medium text-dark rounded-lg text-base py-2.5 hover:underline {{ request()->routeIs('faq') ? 'font-bold' : '' }}">
                    FAQ
                </a>
            </div>

            <div class="flex flex-row justify-center items-center md:gap-4 gap-2.5">
                <div class="md:flex hidden gap-3">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="bg-primary text-dark hover:text-primary hover:bg-dark rounded-2xl px-7.5 py-3.5 font-medium transition-all duration-300">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="bg-white text-dark hover:text-primary hover:bg-primary border border-neutral-200 rounded-2xl px-7.5 py-3.5 font-medium transition-all duration-300">
                            Login
                        </a>
                        <a href="{{ route('register') }}" class="bg-primary text-dark hover:text-primary hover:bg-dark rounded-2xl px-7.5 py-3.5 font-medium transition-all duration-300">
                            Get Started
                        </a>
                    @endauth
                </div>

                <div class="flex lg:hidden">
                    <button type="button" @click="$dispatch('open-mobile-menu')" class="bg-dark text-white focus:text-black focus:bg-primary inline-flex justify-center items-center rounded-2xl md:size-13 size-11 p-3.5 font-medium transition-all duration-300" aria-label="Open mobile menu">
                        <span class="iconify tabler--menu-2 text-2xl size-5"></span>
                    </button>
                </div>
        </div>
    </div>
</header>

<!-- Mobile Menu (Slide Panel) -->
<div @open-mobile-menu.window="mobileMenuOpen = true"
     @keydown.escape.window="mobileMenuOpen = false"
     class="fixed inset-0 z-50 lg:hidden" 
     style="display: none;"
     x-show="mobileMenuOpen"
     x-cloak
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">
    <!-- Backdrop -->
    <div @click="mobileMenuOpen = false" class="fixed inset-0 bg-black/50"></div>
    
    <!-- Slide Panel -->
    <div class="fixed top-0 right-0 h-full w-full max-w-sm bg-white shadow-xl transform transition-transform duration-300 ease-in-out"
         :class="mobileMenuOpen ? 'translate-x-0' : 'translate-x-full'"
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="translate-x-full">
        <div class="flex flex-col h-full">
            <!-- Mobile Menu Header -->
            <div class="h-16 flex items-center justify-between px-4 border-b border-neutral-200 bg-white sticky top-0 z-10">
                <a href="{{ route('home') }}">
                    <img src="/images/owinace.png" alt="Logo" class="h-8.5">
                </a>

                <button type="button" @click="mobileMenuOpen = false" class="bg-neutral-600/15 text-neutral-600 size-8 flex justify-center items-center rounded-full hover:bg-neutral-600/25 transition-colors" aria-label="Close">
                    <span class="sr-only">Close</span>
                    <i class="iconify tabler--x size-5"></i>
                </button>
            </div>

            <!-- Mobile Menu Content -->
            <div class="flex flex-col p-4 overflow-y-auto flex-1">
                <div class="hs-accordion-group space-y-2">
                    <!-- Home Accordion -->
                    <div class="hs-accordion">
                        <button class="hs-accordion-toggle text-dark w-full flex items-center justify-between py-3 font-medium text-base hover:bg-neutral-50 rounded-lg px-3 transition-colors">
                            <span>Home</span>
                            <i class="iconify tabler--chevron-down size-4 hs-accordion-active:rotate-180 transition-transform"></i>
                        </button>

                        <div class="hs-accordion-content hidden w-full overflow-hidden transition-[height]">
                            <div class="flex flex-col mt-2 pl-3">
                                <a href="{{ route('home') }}" class="flex items-center font-medium text-dark rounded-lg text-base py-2 hover:bg-neutral-50 px-3 transition-colors">
                                    Home
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Product Accordion -->
                    <div class="hs-accordion">
                        <button class="hs-accordion-toggle text-dark w-full flex items-center justify-between py-3 font-medium text-base hover:bg-neutral-50 rounded-lg px-3 transition-colors">
                            <span>Product</span>
                            <i class="iconify tabler--chevron-down size-4 hs-accordion-active:rotate-180 transition-transform"></i>
                        </button>

                        <div class="hs-accordion-content hidden w-full overflow-hidden transition-[height]">
                            <div class="flex flex-col mt-2 pl-3 space-y-2">
                                <a href="{{ route('home') }}#why-choose" class="flex items-start gap-3 py-2 hover:bg-neutral-50 rounded-lg px-3 transition-colors">
                                    <i class="iconify tabler--carambola text-black size-6 mt-0.5"></i>
                                    <div>
                                        <div class="text-black font-medium">Seamless onboarding</div>
                                        <p class="text-dark text-sm">Quick, easy setup.</p>
                                    </div>
                                </a>
                                <a href="{{ route('home') }}#hero" class="flex items-start gap-3 py-2 hover:bg-neutral-50 rounded-lg px-3 transition-colors">
                                    <i class="iconify tabler--settings text-black size-6 mt-0.5"></i>
                                    <div>
                                        <div class="text-black font-medium">Responsive design</div>
                                        <p class="text-dark text-sm">Perfect on any device.</p>
                                    </div>
                                </a>
                                <a href="{{ route('home') }}#clients" class="flex items-start gap-3 py-2 hover:bg-neutral-50 rounded-lg px-3 transition-colors">
                                    <i class="iconify tabler--file-text text-black size-6 mt-0.5"></i>
                                    <div>
                                        <div class="text-black font-medium">Integrated analytics</div>
                                        <p class="text-dark text-sm">Real-time insights.</p>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Pages Accordion -->
                    <div class="hs-accordion">
                        <button class="hs-accordion-toggle text-dark w-full flex items-center justify-between py-3 font-medium text-base hover:bg-neutral-50 rounded-lg px-3 transition-colors">
                            <span>Pages</span>
                            <i class="iconify tabler--chevron-down size-4 hs-accordion-active:rotate-180 transition-transform"></i>
                        </button>

                        <div class="hs-accordion-content hidden w-full overflow-hidden transition-[height]">
                            <div class="flex flex-col mt-2 pl-3 space-y-2">
                                <a href="{{ route('how-it-works') }}" class="flex items-center font-medium text-dark rounded-lg text-base py-2 hover:bg-neutral-50 px-3 transition-colors">
                                    How it Works
                                </a>
                                <a href="{{ route('support') }}" class="flex items-center font-medium text-dark rounded-lg text-base py-2 hover:bg-neutral-50 px-3 transition-colors">
                                    Support
                                </a>
                                <a href="{{ route('faq') }}" class="flex items-center font-medium text-dark rounded-lg text-base py-2 hover:bg-neutral-50 px-3 transition-colors">
                                    FAQ
                                </a>
                                <a href="{{ route('privacy-policy') }}" class="flex items-center font-medium text-dark rounded-lg text-base py-2 hover:bg-neutral-50 px-3 transition-colors">
                                    Privacy Policy
                                </a>
                                <a href="{{ route('terms') }}" class="flex items-center font-medium text-dark rounded-lg text-base py-2 hover:bg-neutral-50 px-3 transition-colors">
                                    Terms
                                </a>
                                <a href="{{ route('refund-policy') }}" class="flex items-center font-medium text-dark rounded-lg text-base py-2 hover:bg-neutral-50 px-3 transition-colors">
                                    Refund Policy
                                </a>
                                <a href="{{ route('home') }}#clients" class="flex items-center font-medium text-dark rounded-lg text-base py-2 hover:bg-neutral-50 px-3 transition-colors">
                                    Statistics
                                </a>
                                <a href="{{ route('home') }}#blog" class="flex items-center font-medium text-dark rounded-lg text-base py-2 hover:bg-neutral-50 px-3 transition-colors">
                                    Features
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Account Accordion -->
                    <div class="hs-accordion">
                        <button class="hs-accordion-toggle text-dark w-full flex items-center justify-between py-3 font-medium text-base hover:bg-neutral-50 rounded-lg px-3 transition-colors">
                            <span>Account</span>
                            <i class="iconify tabler--chevron-down size-4 hs-accordion-active:rotate-180 transition-transform"></i>
                        </button>

                        <div class="hs-accordion-content hidden w-full overflow-hidden transition-[height]">
                            <div class="flex flex-col mt-2 pl-3 space-y-2">
                                @auth
                                    <a href="{{ url('/dashboard') }}" class="flex items-center font-medium text-dark rounded-lg text-base py-2 hover:bg-neutral-50 px-3 transition-colors">
                                        Dashboard
                                    </a>
                                @else
                                    <a href="{{ route('login') }}" class="flex items-center font-medium text-dark rounded-lg text-base py-2 hover:bg-neutral-50 px-3 transition-colors">
                                        Log In
                                    </a>
                                    @if (Route::has('register'))
                                        <a href="{{ route('register') }}" class="flex items-center font-medium text-dark rounded-lg text-base py-2 hover:bg-neutral-50 px-3 transition-colors">
                                            Sign Up
                                        </a>
                                    @endif
                                @endauth
                            </div>
                        </div>
                    </div>

                    <!-- Contact Link -->
                    <div>
                        <a href="#workwithus" class="text-dark text-base flex items-center py-3 font-medium hover:bg-neutral-50 rounded-lg px-3 transition-colors">Contact Us</a>
                    </div>
                </div>

                <!-- Mobile Menu Footer Actions -->
                <div class="mt-6 pt-6 border-t border-neutral-200 space-y-3">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="w-full bg-primary text-center text-dark hover:text-primary hover:bg-dark rounded-2xl px-7.5 py-3.5 font-medium transition-all duration-300 block">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="w-full bg-primary text-center text-dark hover:text-primary hover:bg-dark rounded-2xl px-7.5 py-3.5 font-medium transition-all duration-300 block">
                            Sign in
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="w-full bg-dark text-white hover:text-primary hover:bg-primary rounded-2xl px-7.5 py-3.5 font-medium transition-all duration-300 block text-center">
                                Get Started
                            </a>
                        @endif
                    @endauth
                </div>
            </div>
        </div>
    </div>
</div>