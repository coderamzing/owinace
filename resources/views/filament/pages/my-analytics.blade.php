<x-filament-panels::page>
    @php
        $team = $this->getTeam();
        $teamName = $team ? $team->name : 'Unknown Team';
        $selectedPeriod = $this->selectedPeriod;
    @endphp

    <div class="space-y-6">
        {{-- Header (match screenshot) --}}
        <div class="flex items-center justify-between gap-4">
            <div class="min-w-0">
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white truncate">My Analytics</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 truncate">Track your performance and goals</p>
            </div>

            <div class="flex items-center gap-2">
                <button
                    type="button"
                    wire:click="goToPreviousMonth"
                    @if(!$this->canGoToPreviousMonth()) disabled @endif
                    class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed transition"
                    aria-label="Previous month"
                >
                    <x-filament::icon icon="heroicon-o-chevron-left" class="w-5 h-5 text-gray-600 dark:text-gray-300" />
                </button>

                <div class="inline-flex items-center gap-2 px-3 h-9 rounded-lg bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800">
                    <x-filament::icon icon="heroicon-o-calendar-days" class="w-5 h-5 text-gray-600 dark:text-gray-300" />
                    <span class="text-sm font-semibold text-gray-900 dark:text-white whitespace-nowrap">
                        {{ $this->getCurrentPeriodLabel() }}
                    </span>
                </div>

                <button
                    type="button"
                    wire:click="goToNextMonth"
                    @if(!$this->canGoToNextMonth()) disabled @endif
                    class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed transition"
                    aria-label="Next month"
                >
                    <x-filament::icon icon="heroicon-o-chevron-right" class="w-5 h-5 text-gray-600 dark:text-gray-300" />
                </button>
            </div>
        </div>

        {{-- Widgets --}}
        @if(method_exists($this, 'getWidgets'))
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                @foreach($this->getWidgets() as $widget)
                    @php
                        $widgetClass = is_string($widget) ? $widget : get_class($widget);
                        $widgetKey = str_replace('\\', '-', $widgetClass) . '-' . ($selectedPeriod ?? 'now');

                        $widgetInstance = app($widgetClass);
                        $columnSpan = 'full';

                        if (property_exists($widgetInstance, 'columnSpan')) {
                            $reflection = new ReflectionClass($widgetInstance);
                            $property = $reflection->getProperty('columnSpan');
                            $property->setAccessible(true);
                            $columnSpan = $property->getValue($widgetInstance);
                        }

                        $colSpanClass = match($columnSpan) {
                            'full' => 'lg:col-span-12',
                            12 => 'lg:col-span-12',
                            8 => 'lg:col-span-8',
                            6 => 'lg:col-span-6',
                            4 => 'lg:col-span-4',
                            3 => 'lg:col-span-3',
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

