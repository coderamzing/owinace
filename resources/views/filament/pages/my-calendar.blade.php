<x-filament-panels::page>
    @php
        $upcomingFollowUps = $this->getUpcomingFollowUps();
        $todayFollowUps = $this->getTodayFollowUps();
        $thisWeekFollowUps = $this->getThisWeekFollowUps();
    @endphp

    <div class="flex flex-col gap-6">
        <!-- Today's Follow-ups -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Today's Follow-ups</h2>
                <span class="bg-blue-500 text-white px-3 py-1 rounded-full text-sm font-medium">
                    {{ $todayFollowUps->count() }}
                </span>
            </div>
            <div class="p-6">
                @if($todayFollowUps->count() > 0)
                    @foreach($todayFollowUps as $lead)
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 mb-3 border-l-4 border-blue-500 transition-all duration-200 hover:bg-gray-100 dark:hover:bg-gray-600 hover:shadow-md">
                            <div class="flex items-start justify-between mb-2">
                                <a 
                                    href="{{ \App\Filament\Resources\Leads\LeadResource::getUrl('view', ['record' => $lead->id]) }}"
                                    class="text-base font-semibold text-gray-900 dark:text-gray-100 flex-1 hover:text-primary-600 dark:hover:text-primary-400"
                                >
                                    {{ $lead->title }}
                                </a>
                                <span class="text-sm font-medium text-blue-500 dark:text-blue-400 whitespace-nowrap ml-4">
                                    {{ $lead->next_follow_up->format('h:i A') }}
                                </span>
                            </div>
                            @if($lead->description)
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                    {{ Str::limit($lead->description, 150) }}
                                </p>
                            @endif
                            <div class="flex items-center gap-3 flex-wrap mt-2">
                                @if($lead->source)
                                    <span 
                                        class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium" 
                                        style="background-color: {{ $lead->source->color }}20; color: {{ $lead->source->color }}"
                                    >
                                        {{ $lead->source->name }}
                                    </span>
                                @endif
                                @if($lead->kanban)
                                    <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300">
                                        {{ $lead->kanban->name }}
                                    </span>
                                @endif
                                @if($lead->expected_value)
                                    <span class="text-sm font-semibold text-green-600 dark:text-green-400">
                                        ${{ number_format($lead->expected_value, 0) }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-12 px-6 text-gray-500 dark:text-gray-400">
                        <svg class="w-12 h-12 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <p>No follow-ups scheduled for today</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Upcoming Follow-ups -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Upcoming Follow-ups</h2>
                <span class="bg-blue-500 text-white px-3 py-1 rounded-full text-sm font-medium">
                    {{ $upcomingFollowUps->flatten()->count() }}
                </span>
            </div>
            <div class="p-6">
                @if($upcomingFollowUps->count() > 0)
                    @foreach($upcomingFollowUps as $date => $leads)
                        <div class="mb-6">
                            <div class="flex items-center justify-between mb-4 pb-3 border-b-2 border-gray-200 dark:border-gray-700">
                                <div>
                                    <div class="text-base font-semibold text-gray-900 dark:text-gray-100">
                                        {{ \Carbon\Carbon::parse($date)->format('l, F j, Y') }}
                                    </div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ $leads->count() }} {{ Str::plural('follow-up', $leads->count()) }}
                                    </div>
                                </div>
                            </div>
                            <div>
                                @foreach($leads as $lead)
                                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 mb-3 border-l-4 border-blue-500 transition-all duration-200 hover:bg-gray-100 dark:hover:bg-gray-600 hover:shadow-md">
                                        <div class="flex items-start justify-between mb-2">
                                            <a 
                                                href="{{ \App\Filament\Resources\Leads\LeadResource::getUrl('view', ['record' => $lead->id]) }}"
                                                class="text-base font-semibold text-gray-900 dark:text-gray-100 flex-1 hover:text-primary-600 dark:hover:text-primary-400"
                                            >
                                                {{ $lead->title }}
                                            </a>
                                            <span class="text-sm font-medium text-blue-500 dark:text-blue-400 whitespace-nowrap ml-4">
                                                {{ $lead->next_follow_up->format('h:i A') }}
                                            </span>
                                        </div>
                                        @if($lead->description)
                                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                                {{ Str::limit($lead->description, 150) }}
                                            </p>
                                        @endif
                                        <div class="flex items-center gap-3 flex-wrap mt-2">
                                            @if($lead->source)
                                                <span 
                                                    class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium" 
                                                    style="background-color: {{ $lead->source->color }}20; color: {{ $lead->source->color }}"
                                                >
                                                    {{ $lead->source->name }}
                                                </span>
                                            @endif
                                            @if($lead->kanban)
                                                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300">
                                                    {{ $lead->kanban->name }}
                                                </span>
                                            @endif
                                            @if($lead->expected_value)
                                                <span class="text-sm font-semibold text-green-600 dark:text-green-400">
                                                    ${{ number_format($lead->expected_value, 0) }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-12 px-6 text-gray-500 dark:text-gray-400">
                        <svg class="w-12 h-12 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <p>No upcoming follow-ups scheduled</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-filament-panels::page>
