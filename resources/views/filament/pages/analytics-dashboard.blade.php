<x-filament-panels::page>
    @php
        $team = $this->getTeam();
        $teamName = $team ? $team->name : 'Unknown Team';
        $currentPeriodLabel = $this->getCurrentPeriodLabel();
        $selectedPeriod = $this->selectedPeriod;
    @endphp

    <div class="space-y-6">
        {{-- Month Navigation Header --}}
        <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-lg p-4">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex-1">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Analytics Dashboard</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $teamName }} - {{ $currentPeriodLabel }}</p>
                </div>
                
                <div class="flex items-center gap-3">
                    {{-- Previous Month Button --}}
                    <button
                        type="button"
                        wire:click="goToPreviousMonth"
                        wire:loading.attr="disabled"
                        @if(!$this->canGoToPreviousMonth()) disabled @endif
                        class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                        <x-filament::icon icon="heroicon-o-chevron-left" class="w-5 h-5 mr-1" />
                        Previous
                    </button>

                    {{-- Current Period Display --}}
                    <div class="px-4 py-2 text-sm font-medium text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg min-w-[140px] text-center">
                        {{ $currentPeriodLabel }}
                    </div>

                    {{-- Next Month Button --}}
                    <button
                        type="button"
                        wire:click="goToNextMonth"
                        wire:loading.attr="disabled"
                        @if(!$this->canGoToNextMonth()) disabled @endif
                        class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                        Next
                        <x-filament::icon icon="heroicon-o-chevron-right" class="w-5 h-5 ml-1" />
                    </button>
                </div>
            </div>
        </div>

        {{-- Widgets --}}
        @if(method_exists($this, 'getWidgets'))
            <div class="space-y-6">
                @foreach($this->getWidgets() as $widget)
                    @php
                        $widgetClass = is_string($widget) ? $widget : get_class($widget);
                        $widgetKey = str_replace('\\', '-', $widgetClass) . '-' . $selectedPeriod;
                    @endphp
                    @livewire($widgetClass, ['filter' => $selectedPeriod], key($widgetKey))
                @endforeach
            </div>
        @endif
    </div>
</x-filament-panels::page>
