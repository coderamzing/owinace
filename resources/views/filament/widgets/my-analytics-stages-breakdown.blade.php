@php
    $items = $this->getLegendItems();
    $total = $this->getTotalLeads();
@endphp

<x-filament-widgets::widget class="fi-wi-chart">
    <x-filament::section :heading="$this->getHeading()">
        <div class="grid grid-cols-1 gap-4">
            <div class="relative">
                <div
                    x-load
                    x-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('chart', 'filament/widgets') }}"
                    wire:ignore
                    data-chart-type="{{ $this->getType() }}"
                    x-data="chart({
                                cachedData: @js($this->getCachedData()),
                                maxHeight: @js($maxHeight = $this->getMaxHeight()),
                                options: @js($this->getOptions()),
                                type: @js($this->getType()),
                            })"
                    class="fi-wi-chart-canvas-ctn"
                >
                    <canvas x-ref="canvas" style="max-height: 260px;"></canvas>
                    <span x-ref="backgroundColorElement" class="fi-wi-chart-bg-color"></span>
                    <span x-ref="borderColorElement" class="fi-wi-chart-border-color"></span>
                    <span x-ref="gridColorElement" class="fi-wi-chart-grid-color"></span>
                    <span x-ref="textColorElement" class="fi-wi-chart-text-color"></span>
                </div>

                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($total) }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Total Leads</div>
                </div>
            </div>

            <div class="space-y-2">
                @foreach ($items as $item)
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#6ABE32]"></span>
                            <span class="text-sm text-gray-700 dark:text-gray-200 truncate">{{ $item['label'] }}</span>
                        </div>
                        <div class="flex items-center gap-2 shrink-0 text-sm text-gray-600 dark:text-gray-300">
                            <span class="font-semibold">{{ (int) $item['percent'] }}%</span>
                            <span class="text-gray-400">({{ number_format((int) $item['count']) }})</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>

