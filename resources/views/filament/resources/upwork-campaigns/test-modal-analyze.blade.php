@php
    $matched = $result['is_matched'] ?? false;
    $reason = $result['reason'] ?? '';
@endphp
<div class="space-y-4 text-sm">
    <div class="flex flex-wrap items-center gap-2">
        <span class="text-gray-600 dark:text-gray-400">Match:</span>
        @if ($matched)
            <span
                class="inline-flex items-center rounded-md bg-success-50 px-2.5 py-1 text-xs font-medium text-success-700 ring-1 ring-inset ring-success-600/20 dark:bg-success-400/10 dark:text-success-400 dark:ring-success-400/20">
                Yes — good fit
            </span>
        @else
            <span
                class="inline-flex items-center rounded-md bg-danger-50 px-2.5 py-1 text-xs font-medium text-danger-700 ring-1 ring-inset ring-danger-600/20 dark:bg-danger-400/10 dark:text-danger-400 dark:ring-danger-400/20">
                No — weak or poor fit
            </span>
        @endif
    </div>
    <div>
        <h3 class="text-base font-semibold text-gray-950 dark:text-white">Reason</h3>
        <p class="mt-2 leading-relaxed text-gray-700 dark:text-gray-200">{{ $reason ?: '—' }}</p>
    </div>
</div>
