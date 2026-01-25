<x-filament-panels::page>
    <style>
        /* Custom styles for vertical radio buttons */
        .fi-fo-radio .fi-fo-radio-option {
            width: 100% !important;
            margin-bottom: 0.5rem;
        }
        
        .fi-fo-radio .fi-fo-radio-options {
            display: flex !important;
            flex-direction: column !important;
            gap: 0.5rem;
        }

        .fi-fo-radio .fi-fo-radio-option label {
            width: 100%;
            display: flex;
            align-items: center;
        }

        /* Ensure word count buttons are full width */
        [data-field-wrapper="words"] .fi-fo-radio-options {
            width: 100%;
        }

        [data-field-wrapper="words"] .fi-fo-radio-option {
            width: 100% !important;
        }

        /* Ensure proposal type options stack vertically */
        [data-field-wrapper="type"] .fi-fo-radio-options {
            width: 100%;
        }

        [data-field-wrapper="type"] .fi-fo-radio-option {
            width: 100% !important;
        }
    </style>
    
    <div class="max-w-full min-w-full mx-auto">
        <!-- Simple Hero Header -->
        <div class="mb-10 text-center p-5 text-white">
            <div class="w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg">
                <x-filament::icon icon="heroicon-o-sparkles" class="w-9 h-9 text-white" />
            </div>
            <h1 class="text-4xl font-extrabold text-white dark:text-white mb-3">
                AI Proposal Generator
            </h1>
            <p class="text-lg text-white dark:text-gray-400 max-w-2xl mx-auto">
                Create compelling cover letters in seconds using AI. Simply paste the job description and let our AI craft the perfect proposal.
            </p>
        </div>

        <!-- Main Form -->
        <form wire:submit="generate" class="space-y-6">
            <div class="flex flex-col lg:flex-row gap-6">
                <!-- Left Panel: 80% - Job Description -->
                <div class="w-full lg:w-4/5 space-y-6">
                    <div>
                        <label class="block mb-3">
                            <span class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <x-filament::icon icon="heroicon-o-document-text" class="w-5 h-5 text-primary-600" />
                                Job Description
                            </span>
                            <span class="text-sm text-gray-500 dark:text-gray-400 mt-1 block">
                                Paste the complete job description for best results
                            </span>
                        </label>
                        <div class="lead-generation-form">
                            {{ $this->descriptionField }}
                        </div>
                    </div>

                    <!-- Generate Button -->
                    <div class="pt-2">
                        <div class="flex flex-col items-start gap-4">
                            <x-filament::button
                                type="submit"
                                color="primary"
                                size="xl"
                                wire:target="generate"
                                wire:loading.attr="disabled"
                                wire:loading.class="opacity-70 cursor-not-allowed"
                                class="w-full shadow-lg hover:shadow-xl transition-all text-white"
                            >
                                <span class="inline-flex items-center gap-3 px-8">
                                    <x-filament::loading-indicator
                                        wire:loading
                                        wire:target="generate"
                                        class="w-6 h-6"
                                    />
                                    <x-filament::icon
                                        icon="heroicon-o-sparkles"
                                        class="w-6 h-6"
                                        wire:loading.remove
                                        wire:target="generate"
                                    />
                                    <span wire:loading.remove wire:target="generate" class="text-lg font-bold">Generate Proposal</span>
                                    <span wire:loading wire:target="generate" class="text-lg font-bold">Generating...</span>
                                </span>
                            </x-filament::button>
                            
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                <x-filament::icon icon="heroicon-o-clock" class="w-4 h-4 inline" />
                                Takes 5-15 seconds
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Right Panel: 20% - Word Count & Proposal Type -->
                <div class="w-full lg:w-1/5 space-y-6">
                    <!-- Word Count -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm border border-gray-200 dark:border-gray-700">
                        <label class="block mb-3">
                            <span class="text-base font-bold text-gray-900 dark:text-white">
                                Words
                            </span>
                        </label>
                        <div class="space-y-2">
                            {{ $this->wordsField }}
                        </div>
                    </div>

                    <!-- Proposal Type -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm border border-gray-200 dark:border-gray-700">
                        <label class="block mb-3">
                            <span class="text-base font-bold text-gray-900 dark:text-white">
                                Proposal Type*
                            </span>
                        </label>
                        <div class="space-y-2">
                            {{ $this->typeField }}
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <!-- Quick Tips -->
        <div class="mt-12 pt-8 border-t border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-2 mb-6">
                <x-filament::icon icon="heroicon-o-light-bulb" class="w-6 h-6 text-yellow-500" />
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Quick Tips</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
                <div class="group flex gap-3 border border-[#e3e3e3] p-4 !rounded-lg">
                    <x-filament::icon 
                        icon="heroicon-o-check-circle"
                        class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" 
                    />
                    <div>
                        <p class="text-sm font-semibold text-gray-900 group-hover:text-white dark:text-white">
                            Include complete details
                        </p>
                        <p class="text-sm text-gray-600 group-hover:text-white dark:text-white">
                            Paste entire job description for best results
                        </p>
                    </div>
                </div>

                <div class="group flex gap-3 border border-[#e3e3e3] p-4 !rounded-lg">
                    <x-filament::icon icon="heroicon-o-check-circle" class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" />
                    <div>
                        <p class="text-sm font-semibold text-gray-900 group-hover:text-white dark:text-white">Choose right length</p>
                        <p class="text-sm text-gray-600 group-hover:text-white dark:text-white">150 quick, 215 standard, 300 detailed</p>
                    </div>
                </div>

                <div class="group flex gap-3 border border-[#e3e3e3] p-4 !rounded-lg">
                    <x-filament::icon icon="heroicon-o-check-circle" class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" />
                    <div>
                        <p class="text-sm font-semibold text-gray-900 group-hover:text-white dark:text-white">Match the tone</p>
                        <p class="text-sm text-gray-600 group-hover:text-white dark:text-white">PITCH for startups, EXPERIENCE for agencies</p>
                    </div>
                </div>

                <div class="group flex gap-3 border border-[#e3e3e3] p-4 !rounded-lg">
                    <x-filament::icon icon="heroicon-o-check-circle" class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" />
                    <div>
                        <p class="text-sm font-semibold text-gray-900 group-hover:text-white dark:text-white">Review before sending</p>
                        <p class="text-sm text-gray-600 group-hover:text-white dark:text-white">Always customize for each client</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
