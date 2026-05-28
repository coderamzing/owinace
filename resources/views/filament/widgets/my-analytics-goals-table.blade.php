@php
    $rows = $this->getRows();
    $toneClasses = [
        'success' => 'bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-300',
        'warning' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/20 dark:text-amber-300',
        'danger' => 'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-300',
    ];
@endphp

<x-filament-widgets::widget>
    <x-filament::section heading="My Goals">
        <x-slot name="afterHeader">
            <div class="inline-flex items-center gap-1 px-2.5 h-8 rounded-lg bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-800 text-xs font-semibold text-gray-800 dark:text-gray-200">
                View All
                <x-filament::icon icon="heroicon-o-chevron-down" class="w-4 h-4 text-gray-500 dark:text-gray-400" />
            </div>
        </x-slot>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-xs text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-800">
                    <tr>
                        <th class="text-left py-3 pr-4 font-semibold">Goal</th>
                        <th class="text-left py-3 pr-4 font-semibold">Target</th>
                        <th class="text-left py-3 pr-4 font-semibold">Achieved</th>
                        <th class="text-left py-3 pr-4 font-semibold">Progress</th>
                        <th class="text-left py-3 font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($rows as $row)
                        @php
                            $tone = $row['status_tone'] ?? 'danger';
                            $toneClass = $toneClasses[$tone] ?? $toneClasses['danger'];
                        @endphp
                        <tr>
                            <td class="py-3 pr-4 text-gray-900 dark:text-white">{{ $row['goal'] }}</td>
                            <td class="py-3 pr-4 text-gray-700 dark:text-gray-200">{{ number_format((float) $row['target'], 0) }}</td>
                            <td class="py-3 pr-4 text-gray-700 dark:text-gray-200">{{ number_format((float) $row['achieved'], 0) }}</td>
                            <td class="py-3 pr-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex-1 h-2 rounded-full bg-gray-100 dark:bg-gray-800 overflow-hidden">
                                        <div style="width: <?php echo (int) ($row['progress_percent'] ?? 0); ?>%; background-color: #6ABE32;" class="h-2 rounded-full"></div>
                                    </div>
                                    <div class="w-10 text-right text-xs text-gray-600 dark:text-gray-300">{{ (int) ($row['progress_percent'] ?? 0) }}%</div>
                                </div>
                            </td>
                            <td class="py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $toneClass }}">
                                    {{ $row['status'] }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-10 text-center text-gray-500 dark:text-gray-400">No goals found for this period.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>

