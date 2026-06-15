<x-filament-panels::page>
    <div class="space-y-6">
        @if($showQueued)
            <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6">
                <h3 class="text-lg font-semibold mb-4">Import Queued</h3>

                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-4">
                    <div class="flex items-center">
                        <svg class="w-8 h-8 text-blue-600 dark:text-blue-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Portfolios Queued</p>
                            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $queuedCount }}</p>
                        </div>
                    </div>
                </div>

                <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                    Your import is running in the background. Each portfolio will be created with an AI-generated embedding.
                    Make sure your queue worker is running to process the import.
                </p>

                <div class="flex gap-3">
                    <x-filament::button
                        wire:click="$set('showQueued', false)"
                        color="gray"
                    >
                        Import Another File
                    </x-filament::button>

                    <x-filament::button
                        :href="App\Filament\Resources\Portfolios\PortfolioResource::getUrl('index')"
                        tag="a"
                    >
                        View All Portfolios
                    </x-filament::button>
                </div>
            </div>
        @else
            <form wire:submit="import">
                {{ $this->form }}

                <div class="flex justify-end gap-3 mt-6">
                    @foreach($this->getFormActions() as $action)
                        {{ $action }}
                    @endforeach
                </div>
            </form>
        @endif

        <div class="rounded-lg border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20 p-4">
            <div class="flex">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="text-sm text-blue-800 dark:text-blue-200">
                    <p class="font-semibold mb-1">Need a sample CSV file?</p>
                    <p class="mb-2">Download a sample template to see the correct format:</p>
                    <a href="{{ asset('portfolio_import_template.csv') }}"
                       download="portfolio_import_template.csv"
                       class="inline-flex items-center px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Download Sample CSV
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
