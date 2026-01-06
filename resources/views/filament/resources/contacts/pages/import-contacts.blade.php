<x-filament-panels::page>
    <div class="space-y-6">
        @if($showResults)
            <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6">
                <h3 class="text-lg font-semibold mb-4">Import Results</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                        <div class="flex items-center">
                            <svg class="w-8 h-8 text-green-600 dark:text-green-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Successfully Imported</p>
                                <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $importedCount }}</p>
                            </div>
                        </div>
                    </div>
                    
                    @if($errorCount > 0)
                        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                            <div class="flex items-center">
                                <svg class="w-8 h-8 text-red-600 dark:text-red-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Errors</p>
                                    <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $errorCount }}</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                
                @if(!empty($errors))
                    <div class="mt-4">
                        <h4 class="text-sm font-semibold mb-2 text-gray-700 dark:text-gray-300">Error Details (showing first 10):</h4>
                        <div class="bg-gray-50 dark:bg-gray-900 rounded p-3 max-h-48 overflow-y-auto">
                            <ul class="text-sm space-y-1 text-gray-600 dark:text-gray-400 font-mono">
                                @foreach($errors as $error)
                                    <li class="py-1">• {{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif
                
                <div class="mt-6 flex gap-3">
                    <x-filament::button 
                        wire:click="$set('showResults', false)"
                        color="gray"
                    >
                        Import Another File
                    </x-filament::button>
                    
                    <x-filament::button 
                        :href="App\Filament\Resources\Contacts\ContactResource::getUrl('index')"
                        tag="a"
                    >
                        View All Contacts
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
                    <a href="{{ asset('contact_import_template.csv') }}" 
                       download="contact_import_template.csv"
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

