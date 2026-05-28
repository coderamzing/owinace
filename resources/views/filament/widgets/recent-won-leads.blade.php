@php
    $leads = $this->getLeads();
@endphp

<x-filament-widgets::widget>
    <x-filament::section heading="Recent Won Leads">
        <x-slot name="afterHeader">
            <div class="inline-flex items-center gap-1 px-2.5 h-8 rounded-lg bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-800 text-xs font-semibold text-gray-800 dark:text-gray-200">
                View all
                <x-filament::icon icon="heroicon-o-chevron-right" class="w-4 h-4 text-gray-500 dark:text-gray-400" />
            </div>
        </x-slot>

        <div class="space-y-3">
            @foreach ($leads as $lead)
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-9 h-9 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-xs font-bold text-gray-700 dark:text-gray-200 shrink-0">
                            {{ $lead['initials'] }}
                        </div>
                        <div class="min-w-0">
                            <div class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $lead['title'] }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $lead['subtitle'] }}</div>
                        </div>
                    </div>

                    <div class="text-right shrink-0">
                        <div class="text-sm font-semibold text-[#16a34a]">{{ '$' . number_format((float) $lead['amount'], 0) }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $lead['date'] }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>

