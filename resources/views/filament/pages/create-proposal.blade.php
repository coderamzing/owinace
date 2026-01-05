<x-filament-panels::page>
    <div class="min-w-full mx-auto lg:min-w-5xl">
        <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-lg">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Create New Coverletter
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Find and Generate proposals using Artificial Intelligence.
                </p>
            </div>
            <div class="p-6">
                <form wire:submit="generate">
                    {{ $this->form }}

                    <div class="mt-6 flex justify-center">
                        <x-filament::button
                            type="submit"
                            color="primary"
                            size="lg"
                            wire:target="generate"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-70 cursor-not-allowed"
                        >
                            <span class="inline-flex items-center gap-2">
                                <x-filament::loading-indicator
                                    wire:loading
                                    wire:target="generate"
                                    class="w-5 h-5"
                                />
                                <x-filament::icon
                                    icon="heroicon-o-sparkles"
                                    class="w-5 h-5"
                                    wire:loading.remove
                                    wire:target="generate"
                                />
                                <span wire:loading.remove wire:target="generate">Generate</span>
                                <span wire:loading wire:target="generate">Generating...</span>
                            </span>
                        </x-filament::button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-filament-panels::page>

