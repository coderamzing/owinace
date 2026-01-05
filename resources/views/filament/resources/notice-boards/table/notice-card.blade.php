@php
    $statusColors = [
        'draft' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-200',
        'published' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-200',
    ];

    $statusLabel = ucfirst($record->status ?? 'draft');
    $publishedAt = $record->published_at?->format('M d, Y h:i A');
    $expiresAt = $record->expire_at?->format('M d, Y h:i A');
@endphp

<div class="flex flex-col gap-2 border-b border-gray-200 py-3 last:border-b-0 dark:border-gray-800">
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0 space-y-1">
            <h3 class="truncate text-base font-semibold text-gray-900 dark:text-white">
                {{ $record->title }}
            </h3>
            @if ($record->description)
                <div class="prose prose-sm max-w-none text-gray-700 dark:prose-invert dark:text-gray-200 line-clamp-2">
                    {!! $record->description !!}
                </div>
            @endif
        </div>
        <span class="whitespace-nowrap rounded-full px-3 py-1 text-xs font-semibold {{ $statusColors[$record->status] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-200' }}">
            {{ $statusLabel }}
        </span>
    </div>

    <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600 dark:text-gray-300">
        <span class="inline-flex items-center gap-1">
            <x-filament::icon icon="heroicon-o-user-group" class="h-4 w-4 text-primary-500" />
            {{ $record->team?->name ?? 'All Teams' }}
        </span>
        <span class="inline-flex items-center gap-1">
            <x-filament::icon icon="heroicon-o-bell" class="h-4 w-4 text-primary-500" />
            {{ $record->notify ? 'Notifications on' : 'Notifications off' }}
        </span>
        <span class="inline-flex items-center gap-1">
            <x-filament::icon icon="heroicon-o-calendar-days" class="h-4 w-4 text-primary-500" />
            {{ $publishedAt ?? 'Not scheduled' }}
        </span>
        <span class="inline-flex items-center gap-1">
            <x-filament::icon icon="heroicon-o-clock" class="h-4 w-4 text-primary-500" />
            {{ $expiresAt ?? 'No expiry' }}
        </span>
    </div>
</div>
