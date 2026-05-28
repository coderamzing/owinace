@php
    $stages = $this->getStages();
    $max = collect($stages)->max('count') ?? 0;
    $conversionRate = $this->getConversionRate();
    $avgSalesCycle = $this->getAvgSalesCycleDays();
@endphp

<x-filament-widgets::widget>
    <x-filament::section heading="Pipeline Funnel">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <div class="md:col-span-8 space-y-2">
                @foreach ($stages as $index => $stage)
                    @php
                        $count = (int) $stage['count'];
                        $widthPct = $max > 0 ? max(12, round(($count / $max) * 100, 0)) : 0;
                        $shade = 600 - ($index * 70);
                        $shade = max(300, $shade);
                    @endphp

                    <div class="flex items-center gap-3">
                        <div class="flex-1">
                            <div class="h-7 rounded-md overflow-hidden bg-gray-100 dark:bg-gray-800">
                                <div
                                    class="h-7 rounded-md"
                                    style="width: {{ (int) $widthPct }}%; background-color: {{ e($stage['color']) }}; opacity: {{ 1 - ($index * 0.08) }};"
                                ></div>
                            </div>
                        </div>
                        <div class="w-28 text-right">
                            <div class="text-xs font-semibold text-gray-700 dark:text-gray-200">{{ $stage['label'] }}</div>
                            <div class="text-[11px] text-gray-500 dark:text-gray-400">{{ number_format($count) }} ({{ (int) $stage['percent'] }}%)</div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="md:col-span-4 grid grid-cols-2 md:grid-cols-1 gap-3">
                <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4">
                    <div class="text-xs text-gray-500 dark:text-gray-400">Conversion Rate</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ (int) $conversionRate }}%</div>
                </div>

                <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4">
                    <div class="text-xs text-gray-500 dark:text-gray-400">Avg Sales Cycle</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $avgSalesCycle }} days</div>
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>

