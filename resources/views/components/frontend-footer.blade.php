<footer class="bg-dark pt-15 pb-10 md:pt-17.5 md:pb-10 lg:pt-25 lg:pb-10 overflow-hidden">
    <div class="container">
        <div class="grid md:grid-cols-2 md:gap-12.5 lg:grid-cols-8 lg:gap-5 gap-10">
            <div class="lg:col-span-3">
                <a href="{{ route('home') }}">
                    <img src="/images/leadcliq.png" alt="LeadCliq" class="h-11 invert">
                </a>
                <p class="mt-2.5 text-white">
                    Trusted solutions for your growing business
                </p>
                <div class="mt-10 flex items-center gap-3.5 md:mt-15">
                    <h2 class="md:text-5.5xl text-4xl text-white">4.8</h2>
                    <div>
                        <div class="mb-1 flex gap-1.5">
                            <i class="iconify tabler--star-filled text-xl text-orange-300"></i>
                            <i class="iconify tabler--star-filled text-xl text-orange-300"></i>
                            <i class="iconify tabler--star-filled text-xl text-orange-300"></i>
                            <i class="iconify tabler--star-filled text-xl text-orange-300"></i>
                            <i class="iconify tabler--star-filled text-xl text-orange-300"></i>
                        </div>
                        <div class="text-white">Best rated company</div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-3">
                <h4 class="text-1.5xl mb-5 text-white">Pages</h4>
                <div class="grid grid-cols-2 md:gap-12.5 lg:gap-5 gap-2.5">
                    <div>
                        <ul class="flex flex-col justify-start gap-2.5 leading-normal">
                            <li>
                                <a href="{{ route('home') }}" class="text-primary">Home</a>
                            </li>
                            <li>
                                <a href="{{ route('how-it-works') }}" class="hover:text-primary text-white transition-all duration-300">How it Works</a>
                            </li>
                            <li>
                                <a href="{{ route('support') }}" class="hover:text-primary text-white transition-all duration-300">Support</a>
                            </li>
                            <li>
                                <a href="{{ route('faq') }}" class="hover:text-primary text-white transition-all duration-300">FAQ</a>
                            </li>
                        </ul>
                    </div>

                    <div>
                        <ul class="flex flex-col justify-start gap-2.5 leading-normal">
                            <li>
                                <a href="{{ route('privacy-policy') }}" class="hover:text-primary text-white transition-all duration-300">Privacy</a>
                            </li>
                            <li>
                                <a href="{{ route('terms') }}" class="hover:text-primary text-white transition-all duration-300">Terms</a>
                            </li>
                            <li>
                                <a href="{{ route('refund-policy') }}" class="hover:text-primary text-white transition-all duration-300">Refund Policy</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2">
                <h4 class="text-1.5xl mb-5 text-white">Contact us</h4>
                <div>
                    <p class="mb-3.75 text-sm text-white md:text-base">
                        F-427, 2nd Floor, Sector 91, Mohali, Punjab, India 140307
                    </p>

                    <p class="hover:text-primary mb-3.75 text-sm text-white transition-all duration-300 md:text-base">
                        <a href="tel:+917710113366">+91 7710113366</a>
                    </p>

                    <p class="hover:text-primary mb-3.75 text-sm text-white underline transition-all duration-300 md:text-base">
                        <a href="mailto:support@leadcliq.com">support@leadcliq.com</a>
                    </p>

                    <div class="mt-7.5 flex items-center gap-2.5 md:mt-12.5">
                        <div class="flex items-center">
                            <p class="text-lg text-white">👋 Follow Us:</p>
                        </div>

                        <div class="flex gap-4">
                            <a href="#" class="flex">
                                <i class="iconify tabler--brand-meta size-5 text-white transform transition duration-300 hover:scale-110"></i>
                            </a>
                            <a href="#" class="flex">
                                <i class="iconify tabler--brand-dribbble size-5 text-white transform transition duration-300 hover:scale-110"></i>
                            </a>
                            <a href="#" class="flex">
                                <i class="iconify tabler--brand-linkedin size-5 text-white transform transition duration-300 hover:scale-110"></i>
                            </a>
                            <a href="#" class="flex">
                                <i class="iconify tabler--brand-x size-5 text-white transform transition duration-300 hover:scale-110"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <hr class="border-top mt-7.5 border-neutral-700 md:mt-15">

        <div class="flex justify-between pt-7.5 md:pt-10">
            <div class="text-sm text-white">
                © {{ date('Y') }}
                <a href="{{ route('home') }}" class="underline">LeadCliq</a>.
            </div>

            <div class="text-sm text-white">
                Crafted with ❤️ By <a href="#" class="underline">Coderthemes</a>
            </div>
        </div>
    </div>
</footer>
