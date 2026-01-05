<x-filament-panels::page>
    <div class="max-w-4xl mx-auto">
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
                            icon="heroicon-o-sparkles"
                        >
                            Generate
                        </x-filament::button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-filament-panels::page>

