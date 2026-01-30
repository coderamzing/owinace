<header class="bg-white sticky transition-all text-white top-0 inset-x-0 w-screen z-20 duration-300">
    <div class="container">
        <div class="flex items-center justify-between py-2.5 lg:py-4.5">
            <div class="text-lg font-bold">
                <a href="{{ route('home') }}">
                    <img src="/images/leadcliq.svg" alt="LeadCliq" class="h-8.5 lg:h-9">
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
                        <a href="#" class="bg-dark text-white hover:text-primary hover:bg-dark rounded-2xl px-7.5 py-3.5 font-medium transition-all duration-300">
                            <img src="/images/extension.png" alt="Browser Extension" class="inline h-5 w-5 mr-1.5 -mt-0.5">
                            Download
                        </a>
                        <a href="{{ url('/dashboard') }}" class="bg-primary text-dark hover:text-primary hover:bg-dark rounded-2xl px-7.5 py-3.5 font-medium transition-all duration-300">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="bg-white text-dark hover:bg-primary border border-neutral-200 rounded-2xl px-7.5 py-3.5 font-medium transition-all duration-300">
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
                    <img src="/images/leadcliq.svg" alt="LeadCliq" class="h-8.5">
                </a>

                <button type="button" @click="mobileMenuOpen = false" class="bg-neutral-600/15 text-neutral-600 size-8 flex justify-center items-center rounded-full hover:bg-neutral-600/25 transition-colors" aria-label="Close">
                    <span class="sr-only">Close</span>
                    <i class="iconify tabler--x size-5"></i>
                </button>
            </div>

            <!-- Mobile Menu Content -->
            <div class="flex flex-col p-4 overflow-y-auto flex-1">
                <div class="hs-accordion-group space-y-2">
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