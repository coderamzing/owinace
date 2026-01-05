<x-filament-panels::page>
    @php
        $upcomingFollowUps = $this->getUpcomingFollowUps();
        $todayFollowUps = $this->getTodayFollowUps();
        $thisWeekFollowUps = $this->getThisWeekFollowUps();
    @endphp

    <style>
        .calendar-container {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .calendar-section {
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
        }

        .dark .calendar-section {
            background: #1f2937;
            border-color: #374151;
        }

        .calendar-section-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .dark .calendar-section-header {
            border-color: #374151;
        }

        .calendar-section-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: #111827;
        }

        .dark .calendar-section-title {
            color: #f9fafb;
        }

        .calendar-section-count {
            background: #3b82f6;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .calendar-day-group {
            padding: 1.5rem;
        }

        .calendar-day-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #e5e7eb;
        }

        .dark .calendar-day-header {
            border-color: #374151;
        }

        .calendar-day-title {
            font-size: 1rem;
            font-weight: 600;
            color: #111827;
        }

        .dark .calendar-day-title {
            color: #f9fafb;
        }

        .calendar-day-date {
            font-size: 0.875rem;
            color: #6b7280;
        }

        .dark .calendar-day-date {
            color: #9ca3af;
        }

        .calendar-lead-card {
            background: #f9fafb;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 0.75rem;
            border-left: 4px solid #3b82f6;
            transition: all 0.2s;
        }

        .dark .calendar-lead-card {
            background: #374151;
            border-left-color: #60a5fa;
        }

        .calendar-lead-card:hover {
            background: #f3f4f6;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .dark .calendar-lead-card:hover {
            background: #4b5563;
        }

        .calendar-lead-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }

        .calendar-lead-title {
            font-size: 1rem;
            font-weight: 600;
            color: #111827;
            flex: 1;
        }

        .dark .calendar-lead-title {
            color: #f9fafb;
        }

        .calendar-lead-time {
            font-size: 0.875rem;
            font-weight: 500;
            color: #3b82f6;
            white-space: nowrap;
            margin-left: 1rem;
        }

        .dark .calendar-lead-time {
            color: #60a5fa;
        }

        .calendar-lead-meta {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
            margin-top: 0.5rem;
        }

        .calendar-lead-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.5rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .calendar-lead-value {
            font-size: 0.875rem;
            font-weight: 600;
            color: #059669;
        }

        .dark .calendar-lead-value {
            color: #10b981;
        }

        .calendar-empty-state {
            text-align: center;
            padding: 3rem 1.5rem;
            color: #6b7280;
        }

        .dark .calendar-empty-state {
            color: #9ca3af;
        }

        .calendar-empty-icon {
            width: 3rem;
            height: 3rem;
            margin: 0 auto 1rem;
            opacity: 0.5;
        }
    </style>

    <div class="calendar-container">
        <!-- Today's Follow-ups -->
        <div class="calendar-section">
            <div class="calendar-section-header">
                <h2 class="calendar-section-title">Today's Follow-ups</h2>
                <span class="calendar-section-count">{{ $todayFollowUps->count() }}</span>
            </div>
            <div class="calendar-day-group">
                @if($todayFollowUps->count() > 0)
                    @foreach($todayFollowUps as $lead)
                        <div class="calendar-lead-card">
                            <div class="calendar-lead-header">
                                <a 
                                    href="{{ \App\Filament\Resources\Leads\LeadResource::getUrl('view', ['record' => $lead->id]) }}"
                                    class="calendar-lead-title hover:text-primary-600 dark:hover:text-primary-400"
                                >
                                    {{ $lead->title }}
                                </a>
                                <span class="calendar-lead-time">
                                    {{ $lead->next_follow_up->format('h:i A') }}
                                </span>
                            </div>
                            @if($lead->description)
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                    {{ Str::limit($lead->description, 150) }}
                                </p>
                            @endif
                            <div class="calendar-lead-meta">
                                @if($lead->source)
                                    <span 
                                        class="calendar-lead-badge" 
                                        style="background-color: {{ $lead->source->color }}20; color: {{ $lead->source->color }}"
                                    >
                                        {{ $lead->source->name }}
                                    </span>
                                @endif
                                @if($lead->kanban)
                                    <span class="calendar-lead-badge bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                        {{ $lead->kanban->name }}
                                    </span>
                                @endif
                                @if($lead->expected_value)
                                    <span class="calendar-lead-value">
                                        ${{ number_format($lead->expected_value, 0) }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="calendar-empty-state">
                        <svg class="calendar-empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <p>No follow-ups scheduled for today</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Upcoming Follow-ups -->
        <div class="calendar-section">
            <div class="calendar-section-header">
                <h2 class="calendar-section-title">Upcoming Follow-ups</h2>
                <span class="calendar-section-count">{{ $upcomingFollowUps->flatten()->count() }}</span>
            </div>
            <div class="calendar-day-group">
                @if($upcomingFollowUps->count() > 0)
                    @foreach($upcomingFollowUps as $date => $leads)
                        <div class="mb-6">
                            <div class="calendar-day-header">
                                <div>
                                    <div class="calendar-day-title">
                                        {{ \Carbon\Carbon::parse($date)->format('l, F j, Y') }}
                                    </div>
                                    <div class="calendar-day-date">
                                        {{ $leads->count() }} {{ Str::plural('follow-up', $leads->count()) }}
                                    </div>
                                </div>
                            </div>
                            <div>
                                @foreach($leads as $lead)
                                    <div class="calendar-lead-card">
                                        <div class="calendar-lead-header">
                                            <a 
                                                href="{{ \App\Filament\Resources\Leads\LeadResource::getUrl('view', ['record' => $lead->id]) }}"
                                                class="calendar-lead-title hover:text-primary-600 dark:hover:text-primary-400"
                                            >
                                                {{ $lead->title }}
                                            </a>
                                            <span class="calendar-lead-time">
                                                {{ $lead->next_follow_up->format('h:i A') }}
                                            </span>
                                        </div>
                                        @if($lead->description)
                                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                                {{ Str::limit($lead->description, 150) }}
                                            </p>
                                        @endif
                                        <div class="calendar-lead-meta">
                                            @if($lead->source)
                                                <span 
                                                    class="calendar-lead-badge" 
                                                    style="background-color: {{ $lead->source->color }}20; color: {{ $lead->source->color }}"
                                                >
                                                    {{ $lead->source->name }}
                                                </span>
                                            @endif
                                            @if($lead->kanban)
                                                <span class="calendar-lead-badge bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                                    {{ $lead->kanban->name }}
                                                </span>
                                            @endif
                                            @if($lead->expected_value)
                                                <span class="calendar-lead-value">
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
                    <div class="calendar-empty-state">
                        <svg class="calendar-empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <p>No upcoming follow-ups scheduled</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-filament-panels::page>

