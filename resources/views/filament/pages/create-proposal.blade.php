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
            <div class="w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg hidden">
                <x-filament::icon icon="heroicon-o-sparkles" class="w-9 h-9 text-white" />
            </div>
            <h1 class="text-4xl font-extrabold text-dark dark:text-white mb-3">
                AI Proposal Generator
            </h1>
            <p class="text-lg text-dark dark:text-gray-400 max-w-2xl mx-auto">
                Create compelling cover letters in seconds using AI. Simply paste the job description and let our AI craft the perfect proposal.
            </p>
        </div>

        <!-- Main Form -->
        <form wire:submit="generate" class="create-proposal-form space-y-6">
            <div class="flex flex-col lg:flex-row gap-6">
                <!-- Left Panel: 80% - Job Description -->
                <div class="w-full lg:w-5/7 space-y-6 flex flex-col justify-between items-stretch">
                    <div class="min-h-[50%]">
                        <div class="lead-generation-form min-h-[90%]">
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
                                        class="w-6 h-6 text-dark"
                                        wire:loading.remove
                                        wire:target="generate"
                                    />
                                    <span wire:loading.remove wire:target="generate" class="text-lg font-bold text-dark">Generate Proposal</span>
                                    <span wire:loading wire:target="generate" class="text-lg font-bold">Generating...</span>
                                </span>
                            </x-filament::button>
                        </div>
                    </div>
                </div>

                <!-- Right Panel: 20% - Word Count & Proposal Type -->
                <div class="w-full flex flex-col justify-between lg:w-2/7 space-y-6 min-w-[300px]">
                    <!-- Word Count -->
                    {{ $this->wordsField }}

                    {{ $this->typeField }}
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
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">
                            Include complete details
                        </p>
                        <p class="text-sm text-gray-600 dark:text-white">
                            Paste entire job description for best results
                        </p>
                    </div>
                </div>

                <div class="group flex gap-3 border border-[#e3e3e3] p-4 !rounded-lg">
                    <x-filament::icon icon="heroicon-o-check-circle" class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" />
                    <div>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">Choose right length</p>
                        <p class="text-sm text-gray-600 dark:text-white">150 quick, 215 standard, 300 detailed</p>
                    </div>
                </div>

                <div class="group flex gap-3 border border-[#e3e3e3] p-4 !rounded-lg">
                    <x-filament::icon icon="heroicon-o-check-circle" class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" />
                    <div>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">Match the tone</p>
                        <p class="text-sm text-gray-600 dark:text-white">PITCH for startups, EXPERIENCE for agencies</p>
                    </div>
                </div>

                <div class="group flex gap-3 border border-[#e3e3e3] p-4 !rounded-lg">
                    <x-filament::icon icon="heroicon-o-check-circle" class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" />
                    <div>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">Review before sending</p>
                        <p class="text-sm text-gray-600 dark:text-white">Always customize for each client</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
