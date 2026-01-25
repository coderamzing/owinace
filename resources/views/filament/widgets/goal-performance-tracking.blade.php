@php
    /** @var array<int, array<string, mixed>> $members */
    $members = $this->getMemberGoals();

    $colorBarClasses = [
        'primary' => 'this:primary bg-this dark:bg-this-light',
        'success' => 'this:success bg-this dark:bg-this-light',
        'info' => 'this:info bg-this dark:bg-this-light',
        'warning' => 'this:warning bg-this dark:bg-this-light',
        'danger' => 'this:danger bg-this dark:bg-this-light',
    ];
@endphp

<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex gap-4 overflow-x-auto px-4 sm:px-5 pb-2">
            @forelse ($members as $member)
                <div
                    class="relative break-words card rounded-lg bg-white shadow-soft dark:bg-dark-700 dark:shadow-none w-72 shrink-0 space-y-6 p-4 sm:p-5">
                    <div class="flex justify-between gap-2">
                        <div class="flex items-center gap-3">
                            <div class="avatar relative inline-flex shrink-0"
                                style="height: 2.5rem; width: 2.5rem;">
                                <img class="avatar-image avatar-display relative h-full w-full rounded-full object-cover"
                                    alt="{{ $member['name'] }}"
                                    loading="lazy"
                                    src="{{ $member['avatar_url'] }}">
                            </div>
                            <div>
                                <p class="font-medium text-gray-800 dark:text-dark-100">
                                    {{ $member['name'] }}
                                </p>
                                <p class="text-xs text-gray-400 dark:text-dark-300">
                                    {{ $member['role'] ?? 'Member' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    @if (! empty($member['goals']))
                        <div class="space-y-4">
                            @foreach ($member['goals'] as $goal)
                                @php
                                    $barClasses = $colorBarClasses[$goal['color']] ?? $colorBarClasses['primary'];
                                    if (! empty($goal['is_active'])) {
                                        $barClasses .= ' is-active';
                                    }
                                @endphp

                                <div class="rounded-lg border border-gray-150 p-4 dark:border-dark-600">
                                    <div class="flex justify-between">
                                        <div>
                                            <span
                                                class="text-2xl font-medium text-gray-800 dark:text-dark-100">{{ $goal['value'] }}</span>
                                            <span class="text-xs">{{ $goal['unit'] }}</span>
                                        </div>
                                        <p class="text-xs-plus text-gray-600 dark:text-dark-200">
                                            {{ $goal['label'] }}
                                        </p>
                                    </div>
                                    <div class="progress-rail bg-gray-150 dark:bg-dark-500 mt-3 h-1.5">
                                        <div class="progress relative rounded-full transition-[width] ease-out {{ $barClasses }} flex items-center justify-end leading-none"
                                            style="width: {{ $goal['percentage'] }}%;"></div>
                                    </div>
                                    <div class="mt-2 flex justify-between text-xs text-gray-400 dark:text-dark-300">
                                        <p>Monthly target</p>
                                        <p>{{ $goal['percentage'] }}%</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500 dark:text-dark-300">
                            No goals configured for this member in the selected period.
                        </p>
                    @endif
                </div>
            @empty
                <div class="text-sm text-gray-500 dark:text-dark-300 px-4">
                    No member goals available for this period.
                </div>
            @endforelse
        </div>
    </x-filament::section>
</x-filament-widgets::widget>

