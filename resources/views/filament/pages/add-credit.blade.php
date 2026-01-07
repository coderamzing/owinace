<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Error Messages -->
        @if ($errors->any())
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                            There were errors with your request:
                        </h3>
                        <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                            <ul class="list-disc list-inside space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                <p class="text-sm text-red-800 dark:text-red-200">{{ session('error') }}</p>
            </div>
        @endif

        @if (session('success'))
            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                <p class="text-sm text-green-800 dark:text-green-200">{{ session('success') }}</p>
            </div>
        @endif

        <!-- Header -->
        <div class="text-center">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                Choose Your Credit Package
            </h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                Select the credit package that best fits your needs
            </p>
        </div>

        <!-- Credit Packages Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mt-8">
            @foreach($packages as $package)
                <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300 overflow-hidden {{ $package['popular'] ? 'ring-2 ring-primary-500' : '' }}">
                    @if($package['popular'])
                        <div class="absolute top-0 right-0 bg-primary-500 text-white px-3 py-1 text-xs font-semibold rounded-bl-lg">
                            Popular
                        </div>
                    @endif
                    
                    <div class="p-6">
                        <!-- Credits -->
                        <div class="text-center mb-4">
                            <div class="text-4xl font-bold text-primary-600 dark:text-primary-400">
                                {{ $package['credits'] }}
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                Credits
                            </div>
                        </div>

                        <!-- Price -->
                        <div class="text-center mb-4">
                            <div class="text-3xl font-bold text-gray-900 dark:text-white">
                                ${{ number_format($package['price'], 2) }}
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                ${{ number_format($package['price'] / $package['credits'], 2) }} per credit
                            </div>
                        </div>

                        <!-- Description -->
                        <p class="text-center text-sm text-gray-600 dark:text-gray-400 mb-6">
                            {{ $package['description'] }}
                        </p>

                        <!-- Purchase Button -->
                        <form action="{{ route('razorpay.create-credit-order') }}" method="POST">
                            @csrf
                            <input type="hidden" name="credits" value="{{ $package['credits'] }}">
                            <input type="hidden" name="amount" value="{{ $package['price'] }}">
                            
                            <button type="submit" class="w-full px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-lg transition-colors duration-200">
                                Purchase Now
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Current Balance -->
        @if($user && $user->workspace)
            <div class="mt-8 p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
                <div class="text-center">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Current Workspace Balance
                    </p>
                    <p class="text-2xl font-bold text-primary-600 dark:text-primary-400 mt-1">
                        {{ $user->workspace->totalCredits() }} Credits
                    </p>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
