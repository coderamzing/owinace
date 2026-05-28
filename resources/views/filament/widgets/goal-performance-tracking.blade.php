@php
    /** @var array<int, array<string, mixed>> $members */
    $members = $this->getMemberGoals();
@endphp

<x-filament-widgets::widget>
    <x-filament::section heading="Member Goal Progress" class="member-goal-progress-section">
        <div class="grid grid-cols-1 gap-4 px-0 pb-0 sm:px-0 lg:grid-cols-4">
            @forelse ($members as $member)
                <div
                    class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4 space-y-3">
                    <div class="flex items-center gap-3">
                        <img
                            src="{{ $member['avatar_url'] }}"
                            alt="{{ $member['name'] }}"
                            class="w-9 h-9 rounded-full object-cover"
                            loading="lazy"
                        />
                        <div class="min-w-0">
                            <div class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $member['name'] }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $member['role'] ?? 'Sales Rep' }}</div>
                        </div>
                    </div>

                    <div>
                        <div class="text-3xl font-bold text-gray-900 dark:text-white leading-tight">{{ (int) ($member['percentage'] ?? 0) }}%</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">of monthly goal</div>
                    </div>

                    <div class="h-1.5 rounded-full bg-gray-100 dark:bg-gray-800 overflow-hidden">
                        <div class="h-1.5 rounded-full" style="width: <?php echo (int) ($member['percentage'] ?? 0); ?>%; background-color: #6ABE32;"></div>
                    </div>

                    <div class="flex items-center justify-between gap-2 pt-1">
                        <div class="text-xs text-gray-600 dark:text-gray-300">
                            {{ number_format((float) ($member['progress_value'] ?? 0), 0) }} / {{ number_format((float) ($member['target_value'] ?? 0), 0) }}
                        </div>

                        @php
                            $status = $member['status'] ?? 'behind';
                            $statusLabel = match ($status) {
                                'achieved' => 'Achieved',
                                'on_track' => 'On Track',
                                'at_risk' => 'At Risk',
                                default => 'Behind',
                            };
                            $statusClasses = match ($status) {
                                'achieved' => 'bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-300',
                                'on_track' => 'bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-300',
                                'at_risk' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/20 dark:text-amber-300',
                                default => 'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-300',
                            };
                        @endphp

                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $statusClasses }}">
                            {{ $statusLabel }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="text-sm text-gray-500 dark:text-gray-300 lg:col-span-4">
                    No member goals available for this period.
                </div>
            @endforelse
        </div>
    </x-filament::section>
</x-filament-widgets::widget>

