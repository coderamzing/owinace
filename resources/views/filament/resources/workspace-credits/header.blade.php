<div class="mb-6">
    <div class="relative overflow-hidden rounded-xl bg-gradient-to-r from-primary-600 to-primary-500 dark:from-primary-700 dark:to-primary-600 shadow-lg">
        <div class="absolute inset-0 bg-grid-white/10 [mask-image:linear-gradient(0deg,white,rgba(255,255,255,0.6))]"></div>
        
        <div class="relative px-6 py-8 sm:px-8">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-6">
                <!-- Left Side - Credits Info -->
                <div class="flex items-center gap-6">
                    <div class="flex-shrink-0">
                        <div class="w-16 h-16 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    
                    <div>
                        <h3 class="text-sm font-medium text-white/80 uppercase tracking-wider">
                            Your Workspace Balance
                        </h3>
                        <p class="mt-1 text-4xl font-bold text-white">
                            {{ number_format($totalCredits) }}
                            <span class="text-xl font-normal text-white/90">Credits</span>
                        </p>
                        <p class="mt-1 text-sm text-white/70">
                            Available for workspace operations
                        </p>
                    </div>
                </div>

                <!-- Right Side - Action Button -->
                <div class="flex-shrink-0">
                    <a href="/admin/buy-credits" class="inline-flex items-center gap-2 px-6 py-3 bg-white text-primary-600 font-semibold rounded-lg shadow-lg hover:bg-gray-50 hover:shadow-xl transition-all duration-200 transform hover:scale-105">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        <span>Buy More Credits</span>
                    </a>
                </div>
            </div>

            <!-- Bottom Stats -->
            <div class="mt-6 pt-6 border-t border-white/20">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="text-center sm:text-left">
                        <p class="text-xs text-white/70 uppercase tracking-wider">Status</p>
                        <p class="mt-1 text-sm font-semibold text-white">
                            @if($totalCredits > 100)
                                <span class="inline-flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    Excellent Balance
                                </span>
                            @elseif($totalCredits > 50)
                                <span class="inline-flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                    </svg>
                                    Good Balance
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                    Low Balance
                                </span>
                            @endif
                        </p>
                    </div>
                    
                    <div class="text-center">
                        <p class="text-xs text-white/70 uppercase tracking-wider">Quick Action</p>
                        <p class="mt-1 text-sm font-semibold text-white">View Transaction History Below</p>
                    </div>
                    
                    <div class="text-center sm:text-right">
                        <p class="text-xs text-white/70 uppercase tracking-wider">Need Help?</p>
                        <p class="mt-1 text-sm font-semibold text-white">Contact Support</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

