<x-filament::section>
    <x-slot name="heading">
        Monthly AI Insights
    </x-slot>

    @if (! $insight)
        <p class="text-sm text-gray-500">
            No AI insights available for this month yet. They will appear here after the monthly job runs.
        </p>
    @else
        <div class="space-y-3">
            <p class="text-xs text-gray-500">
                {{ $insight['team_name'] ?? '' }} &middot; {{ $insight['period_label'] ?? '' }}
            </p>

            @if (!empty($insight['summary']))
                <p class="text-sm text-gray-800">
                    {{ $insight['summary'] }}
                </p>
            @endif

            @if (!empty($insight['highlights']))
                <div class="space-y-1">
                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide">
                        Highlights
                    </h4>
                    <ul class="list-disc list-inside text-sm text-gray-800 space-y-1">
                        @foreach ($insight['highlights'] as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (!empty($insight['recommendations']))
                <div class="space-y-1">
                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide">
                        Recommendations
                    </h4>
                    <ul class="list-disc list-inside text-sm text-gray-800 space-y-1">
                        @foreach ($insight['recommendations'] as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    @endif
</x-filament::section>

