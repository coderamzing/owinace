@php
    $goals = $this->getGoals();
    
    // Map colors to CSS classes matching the exact format
    $colorClasses = [
        'success' => 'bg-success-500 dark:bg-success-400',
        'warning' => 'bg-warning-500 dark:bg-warning-400',
        'secondary' => 'bg-secondary-500 dark:bg-secondary-400',
        'gray' => 'bg-gray-500 dark:bg-dark-400',
    ];
@endphp

<x-filament-widgets::widget>
    <x-filament::section>
        <div class="grid grid-cols-1 gap-4 px-4 sm:gap-5 sm:px-5 lg:grid-cols-2">
            @forelse($goals as $goal)
                <div class="rounded-lg border border-gray-150 p-4 dark:border-dark-600">
                    <div class="flex justify-between">
                        <div>
                            <span class="text-2xl font-medium text-gray-800 dark:text-dark-100">{{ $goal['value'] }}</span>
                            <span class="text-xs">{{ $goal['unit'] }}</span>
                        </div>
                        <p class="text-xs-plus">{{ $goal['label'] }}</p>
                    </div>
                    <div class="progress-rail bg-gray-150 dark:bg-dark-500 mt-3 h-1.5">
                        <div class="progress relative rounded-full transition-[width] ease-out {{ $colorClasses[$goal['color']] ?? $colorClasses['gray'] }} @if($goal['isActive']) is-active @endif flex items-center justify-end leading-none" style="width: {{ $goal['percentage'] }}%;"></div>
                    </div>
                    <div class="mt-2 flex justify-between text-xs text-gray-400 dark:text-dark-300">
                        <p>Monthly target</p>
                        <p>{{ $goal['percentage'] }}%</p>
                    </div>
                </div>
            @empty
                <div class="col-span-2 rounded-lg border border-gray-150 p-4 dark:border-dark-600 text-center text-gray-500 dark:text-gray-400">
                    <p>No goals available for this period.</p>
                </div>
            @endforelse
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
