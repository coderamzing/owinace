<x-filament-panels::page>
    @php
        $upcomingFollowUps = $this->getUpcomingFollowUps();
        $todayFollowUps = $this->getTodayFollowUps();
        $thisWeekFollowUps = $this->getThisWeekFollowUps();
    @endphp

    <div class="flex flex-col gap-6">
        <!-- Today's Follow-ups -->
        <div class="bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-700 rounded-lg overflow-hidden">
            <div class="px-6 py-4 bg-white dark:bg-primary-900/30 dark:border-primary-700 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-primary-100 dark:bg-primary-900/50 rounded-lg flex items-center justify-center">
                        <x-filament::icon icon="heroicon-o-calendar" class="w-5 h-5 text-primary-600 dark:text-primary-400" />
                    </div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100">Today's Follow-ups</h2>
                </div>
                <span class="bg-primary-500 text-white px-3 py-1.5 rounded-full text-sm font-bold shadow-sm">
                    {{ $todayFollowUps->count() }}
                </span>
            </div>
            <div class="p-6">
                @if($todayFollowUps->count() > 0)
                    <div class="space-y-3">
                        @foreach($todayFollowUps as $lead)
                            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border-l-4 transition-all duration-200 hover:shadow-lg group" style="border-left-color: {{ $lead->source->color ?? '#3b82f6' }}">
                                <div class="flex items-start justify-between mb-2">
                                    <a 
                                        href="{{ \App\Filament\Resources\Leads\LeadResource::getUrl('view', ['record' => $lead->id]) }}"
                                        class="text-base font-semibold text-gray-900 dark:text-gray-100 flex-1 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors"
                                    >
                                        {{ $lead->title }}
                                    </a>
                                    <div class="flex items-center gap-2 ml-4">
                                        <x-filament::icon icon="heroicon-o-clock" class="w-4 h-4 text-primary-500" />
                                        <span class="text-sm font-bold text-primary-600 dark:text-primary-400 whitespace-nowrap">
                                            {{ $lead->next_follow_up->format('h:i A') }}
                                        </span>
                                    </div>
                                </div>
                                @if($lead->description)
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-3 leading-relaxed">
                                        {{ Str::limit($lead->description, 150) }}
                                    </p>
                                @endif
                                <div class="flex items-center gap-2 flex-wrap">
                                    @if($lead->source)
                                        <span 
                                            class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold shadow-sm" 
                                            style="background-color: {{ $lead->source->color }}20; color: {{ $lead->source->color }}; border: 1px solid {{ $lead->source->color }}40"
                                        >
                                            <x-filament::icon icon="heroicon-o-tag" class="w-3 h-3 mr-1" />
                                            {{ $lead->source->name }}
                                        </span>
                                    @endif
                                    @if($lead->kanban)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600">
                                            <x-filament::icon icon="heroicon-o-squares-2x2" class="w-3 h-3 mr-1" />
                                            {{ $lead->kanban->name }}
                                        </span>
                                    @endif
                                    @if($lead->expected_value)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400 border border-green-200 dark:border-green-700">
                                            <x-filament::icon icon="heroicon-o-currency-dollar" class="w-3 h-3 mr-1" />
                                            ${{ number_format($lead->expected_value, 0) }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-16 px-6">
                        <div class="w-16 h-16 bg-primary-100 dark:bg-primary-900/50 rounded-full flex items-center justify-center mx-auto mb-4">
                            <x-filament::icon icon="heroicon-o-calendar" class="w-8 h-8 text-primary-400 dark:text-primary-500" />
                        </div>
                        <p class="text-gray-600 dark:text-gray-400 font-medium">No follow-ups scheduled for today</p>
                        <p class="text-sm text-gray-500 dark:text-gray-500 mt-1">You're all caught up! 🎉</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Upcoming Follow-ups -->
        <div class="bg-info-50 dark:bg-info-900/20 border border-info-200 dark:border-info-700 rounded-lg overflow-hidden">
            <div class="px-6 py-4 bg-white dark:bg-info-900/30 dark:border-info-700 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-info-100 dark:bg-info-900/50 rounded-lg flex items-center justify-center">
                        <x-filament::icon icon="heroicon-o-calendar-days" class="w-5 h-5 text-info-600 dark:text-info-400" />
                    </div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100">Upcoming Follow-ups</h2>
                </div>
                <span class="bg-info-500 text-white px-3 py-1.5 rounded-full text-sm font-bold shadow-sm">
                    {{ $upcomingFollowUps->flatten()->count() }}
                </span>
            </div>
            <div class="p-6">
                @if($upcomingFollowUps->count() > 0)
                    <div class="space-y-6">
                        @foreach($upcomingFollowUps as $date => $leads)
                            <div class="bg-white dark:bg-gray-800 rounded-lg p-5 border border-gray-200 dark:border-gray-700">
                                <div class="flex items-center gap-3 mb-4 pb-4 border-b-2 border-info-200 dark:border-info-700">
                                    <div class="w-12 h-12 bg-info-100 dark:bg-info-900/50 rounded-lg flex flex-col items-center justify-center">
                                        <span class="text-xs font-semibold text-info-600 dark:text-info-400">
                                            {{ \Carbon\Carbon::parse($date)->format('M') }}
                                        </span>
                                        <span class="text-lg font-bold text-info-600 dark:text-info-400">
                                            {{ \Carbon\Carbon::parse($date)->format('d') }}
                                        </span>
                                    </div>
                                    <div class="flex-1">
                                        <div class="text-base font-bold text-gray-900 dark:text-gray-100">
                                            {{ \Carbon\Carbon::parse($date)->format('l, F j, Y') }}
                                        </div>
                                        <div class="text-sm text-gray-600 dark:text-gray-400 flex items-center gap-1">
                                            <x-filament::icon icon="heroicon-o-bell" class="w-3 h-3" />
                                            {{ $leads->count() }} {{ Str::plural('follow-up', $leads->count()) }}
                                        </div>
                                    </div>
                                </div>
                                <div class="space-y-3">
                                    @foreach($leads as $lead)
                                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 border-l-4 transition-all duration-200 hover:shadow-md group" style="border-left-color: {{ $lead->source->color ?? '#3b82f6' }}">
                                            <div class="flex items-start justify-between mb-2">
                                                <a 
                                                    href="{{ \App\Filament\Resources\Leads\LeadResource::getUrl('view', ['record' => $lead->id]) }}"
                                                    class="text-base font-semibold text-gray-900 dark:text-gray-100 flex-1 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors"
                                                >
                                                    {{ $lead->title }}
                                                </a>
                                                <div class="flex items-center gap-2 ml-4">
                                                    <x-filament::icon icon="heroicon-o-clock" class="w-4 h-4 text-info-500" />
                                                    <span class="text-sm font-bold text-info-600 dark:text-info-400 whitespace-nowrap">
                                                        {{ $lead->next_follow_up->format('h:i A') }}
                                                    </span>
                                                </div>
                                            </div>
                                            @if($lead->description)
                                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-3 leading-relaxed">
                                                    {{ Str::limit($lead->description, 150) }}
                                                </p>
                                            @endif
                                            <div class="flex items-center gap-2 flex-wrap">
                                                @if($lead->source)
                                                    <span 
                                                        class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold shadow-sm" 
                                                        style="background-color: {{ $lead->source->color }}20; color: {{ $lead->source->color }}; border: 1px solid {{ $lead->source->color }}40"
                                                    >
                                                        <x-filament::icon icon="heroicon-o-tag" class="w-3 h-3 mr-1" />
                                                        {{ $lead->source->name }}
                                                    </span>
                                                @endif
                                                @if($lead->kanban)
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600">
                                                        <x-filament::icon icon="heroicon-o-squares-2x2" class="w-3 h-3 mr-1" />
                                                        {{ $lead->kanban->name }}
                                                    </span>
                                                @endif
                                                @if($lead->expected_value)
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400 border border-green-200 dark:border-green-700">
                                                        <x-filament::icon icon="heroicon-o-currency-dollar" class="w-3 h-3 mr-1" />
                                                        ${{ number_format($lead->expected_value, 0) }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-16 px-6">
                        <div class="w-16 h-16 bg-info-100 dark:bg-info-900/50 rounded-full flex items-center justify-center mx-auto mb-4">
                            <x-filament::icon icon="heroicon-o-calendar-days" class="w-8 h-8 text-info-400 dark:text-info-500" />
                        </div>
                        <p class="text-gray-600 dark:text-gray-400 font-medium">No upcoming follow-ups scheduled</p>
                        <p class="text-sm text-gray-500 dark:text-gray-500 mt-1">Schedule follow-ups to stay on top of your leads</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-filament-panels::page>
