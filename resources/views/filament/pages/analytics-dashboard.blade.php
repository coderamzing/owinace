<x-filament-panels::page>
    @php
        $team = $this->getTeam();
        $teamName = $team ? $team->name : 'Unknown Team';
        $currentPeriodLabel = $this->getCurrentPeriodLabel();
        $selectedPeriod = $this->selectedPeriod;
    @endphp

    <div class="space-y-6">
        {{-- Header Section --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6 mb-2">
            <!-- Title Area -->
            <div>
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-gradient-to-br from-primary-600 to-indigo-600 rounded-xl flex items-center justify-center">
                        <x-filament::icon icon="heroicon-o-chart-bar-square" class="w-7 h-7 text-white" />
                    </div>
                    <div>
                        <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white">Analytics Dashboard</h1>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-0.5">
                            <span class="font-semibold text-primary-600 dark:text-primary-400">{{ $teamName }}</span>
                            <span class="mx-2">·</span>
                            <span>{{ $currentPeriodLabel }}</span>
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Navigation Controls -->
            <div class="flex items-center gap-3">
                <button
                    type="button"
                    wire:click="goToPreviousMonth"
                    @if(!$this->canGoToPreviousMonth()) disabled @endif
                    class="inline-flex items-center justify-center px-4 py-2.5 text-sm font-semibold text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                    <x-filament::icon icon="heroicon-o-chevron-left" class="w-5 h-5 mr-1.5" />
                    Previous
                </button>
                
                <button
                    type="button"
                    wire:click="goToNextMonth"
                    @if(!$this->canGoToNextMonth()) disabled @endif
                    class="inline-flex items-center justify-center px-4 py-2.5 text-sm font-semibold text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                    Next
                    <x-filament::icon icon="heroicon-o-chevron-right" class="w-5 h-5 ml-1.5" />
                </button>
            </div>
        </div>

        {{-- Widgets --}}
        @if(method_exists($this, 'getWidgets'))
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 analytics-dashboard">
                @foreach($this->getWidgets() as $widget)
                    @php
                        $widgetClass = is_string($widget) ? $widget : get_class($widget);
                        $widgetKey = str_replace('\\', '-', $widgetClass) . '-' . $selectedPeriod;
                        
                        // Get the widget's column span
                        $widgetInstance = app($widgetClass);
                        $columnSpan = 'full';
                        
                        if (property_exists($widgetInstance, 'columnSpan')) {
                            $reflection = new ReflectionClass($widgetInstance);
                            $property = $reflection->getProperty('columnSpan');
                            $property->setAccessible(true);
                            $columnSpan = $property->getValue($widgetInstance);
                        }
                        
                        // Convert column span to Tailwind classes
                        $colSpanClass = match($columnSpan) {
                            'full' => 'lg:col-span-12',
                            12 => 'lg:col-span-12',
                            6 => 'lg:col-span-6 border border-[#e3e3e3] analytics-border',
                            4 => 'lg:col-span-4',
                            3 => 'lg:col-span-3',
                            2 => 'lg:col-span-2',
                            1 => 'lg:col-span-1',
                            default => 'lg:col-span-12',
                        };
                    @endphp
                    <div class="{{ $colSpanClass }}">
                        @livewire($widgetClass, ['filter' => $selectedPeriod], key($widgetKey))
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-filament-panels::page>
