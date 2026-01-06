<x-filament-panels::page>
    @php
        $record = $this->record;
    @endphp

    <div class="space-y-6">
        {{-- Header Section --}}
        <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-lg p-6">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">{{ $record->title }}</h1>
                    @if($record->description)
                        <p class="text-gray-600 dark:text-gray-400 mt-2">{{ $record->description }}</p>
                    @endif
                </div>
                <div class="flex items-center gap-3">
                    @if($record->kanban)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium" style="background-color: {{ $record->kanban->color }}20; color: {{ $record->kanban->color }}">
                            {{ $record->kanban->name }}
                        </span>
                    @endif
                    @if($record->is_archived)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-warning-100 dark:bg-warning-900/20 text-warning-600 dark:text-warning-400">
                            <x-filament::icon icon="heroicon-o-archive-box" class="w-4 h-4 mr-1" />
                            Archived
                        </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Lead Information Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {{-- Status & Source --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Lead Information</h3>
                <div class="space-y-4">
                    <div>
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</label>
                        @if($record->kanban)
                            <div class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" style="background-color: {{ $record->kanban->color }}20; color: {{ $record->kanban->color }}">
                                    {{ $record->kanban->name }}
                                </span>
                            </div>
                        @else
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">N/A</p>
                        @endif
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Source</label>
                        @if($record->source)
                            <div class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" style="background-color: {{ $record->source->color }}20; color: {{ $record->source->color }}">
                                    {{ $record->source->name }}
                                </span>
                            </div>
                        @else
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">No source</p>
                        @endif
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Assigned To</label>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white flex items-center">
                            <x-filament::icon icon="heroicon-o-user" class="w-4 h-4 mr-2" />
                            {{ $record->assignedMember?->name ?? 'Unassigned' }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Financial Information --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Financial Information</h3>
                <div class="space-y-4">
                    <div>
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Expected Value</label>
                        <p class="mt-1 text-lg font-semibold text-success-600 dark:text-success-400 flex items-center">
                            <x-filament::icon icon="heroicon-o-arrow-trending-up" class="w-5 h-5 mr-2" />
                            ${{ number_format($record->expected_value ?? 0, 2) }}
                        </p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Actual Value</label>
                        <p class="mt-1 text-lg font-semibold text-info-600 dark:text-info-400 flex items-center">
                            <x-filament::icon icon="heroicon-o-currency-dollar" class="w-5 h-5 mr-2" />
                            {{ $record->actual_value ? '$' . number_format($record->actual_value, 2) : 'Not set' }}
                        </p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Cost</label>
                        <p class="mt-1 text-lg font-semibold text-warning-600 dark:text-warning-400 flex items-center">
                            <x-filament::icon icon="heroicon-o-banknotes" class="w-5 h-5 mr-2" />
                            {{ $record->cost ? '$' . number_format($record->cost, 2) : 'Not set' }}
                        </p>
                    </div>
                    @if($record->actual_value && $record->cost)
                        @php
                            $profit = $record->actual_value - $record->cost;
                            $profitColor = $profit >= 0 ? 'success' : 'danger';
                        @endphp
                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Profit</label>
                            <p class="mt-1 text-lg font-semibold text-{{ $profitColor }}-600 dark:text-{{ $profitColor }}-400 flex items-center">
                                <x-filament::icon icon="heroicon-o-chart-bar" class="w-5 h-5 mr-2" />
                                ${{ number_format($profit, 2) }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Timeline --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Timeline</h3>
                <div class="space-y-4">
                    <div>
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Next Follow Up</label>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white flex items-center">
                            <x-filament::icon icon="heroicon-o-calendar" class="w-4 h-4 mr-2 text-warning-600 dark:text-warning-400" />
                            {{ $record->next_follow_up ? $record->next_follow_up->format('M d, Y h:i A') : 'Not scheduled' }}
                        </p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Conversion Date</label>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white flex items-center">
                            <x-filament::icon icon="heroicon-o-check-circle" class="w-4 h-4 mr-2 text-success-600 dark:text-success-400" />
                            {{ $record->conversion_date ? $record->conversion_date->format('M d, Y h:i A') : 'Not converted' }}
                        </p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Created</label>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white flex items-center">
                            <x-filament::icon icon="heroicon-o-clock" class="w-4 h-4 mr-2" />
                            {{ $record->created_at->format('M d, Y h:i A') }}
                        </p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Last Updated</label>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white flex items-center">
                            <x-filament::icon icon="heroicon-o-pencil" class="w-4 h-4 mr-2" />
                            {{ $record->updated_at->format('M d, Y h:i A') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Additional Information --}}
        <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Additional Information</h3>
            <div class="space-y-4">
                @if($record->url)
                    <div>
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400">URL</label>
                        <p class="mt-1">
                            <a href="{{ $record->url }}" target="_blank" class="text-primary-600 dark:text-primary-400 hover:underline flex items-center">
                                <x-filament::icon icon="heroicon-o-link" class="w-4 h-4 mr-2" />
                                {{ $record->url }}
                            </a>
                        </p>
                    </div>
                @endif
                @if($record->notes)
                    <div>
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Notes</label>
                        <div class="mt-1 p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
                            <p class="text-sm text-gray-900 dark:text-white whitespace-pre-wrap">{{ $record->notes }}</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Contacts --}}
        @if($record->contacts->count() > 0)
            <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Contacts</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($record->contacts as $contact)
                        <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4 hover:shadow-md transition-shadow">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center">
                                        <x-filament::icon icon="heroicon-o-user" class="w-5 h-5 text-primary-600 dark:text-primary-400" />
                                    </div>
                                </div>
                                <div class="ml-3 flex-1">
                                    @if($contact->first_name || $contact->last_name)
                                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white">
                                            {{ trim($contact->first_name . ' ' . $contact->last_name) }}
                                        </h4>
                                    @endif
                                    @if($contact->job_title)
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $contact->job_title }}</p>
                                    @endif
                                    @if($contact->company)
                                        <p class="text-xs text-gray-600 dark:text-gray-300 mt-1 flex items-center">
                                            <x-filament::icon icon="heroicon-o-building-office" class="w-3 h-3 mr-1" />
                                            {{ $contact->company }}
                                        </p>
                                    @endif
                                    @if($contact->email)
                                        <p class="text-xs text-primary-600 dark:text-primary-400 mt-1 flex items-center">
                                            <x-filament::icon icon="heroicon-o-envelope" class="w-3 h-3 mr-1" />
                                            <a href="mailto:{{ $contact->email }}" class="hover:underline truncate">{{ $contact->email }}</a>
                                        </p>
                                    @endif
                                    @if($contact->phone_number)
                                        <p class="text-xs text-gray-600 dark:text-gray-300 mt-1 flex items-center">
                                            <x-filament::icon icon="heroicon-o-phone" class="w-3 h-3 mr-1" />
                                            <a href="tel:{{ $contact->phone_number }}" class="hover:underline">{{ $contact->phone_number }}</a>
                                        </p>
                                    @endif
                                    @if($contact->website)
                                        <p class="text-xs text-primary-600 dark:text-primary-400 mt-1 flex items-center">
                                            <x-filament::icon icon="heroicon-o-globe-alt" class="w-3 h-3 mr-1" />
                                            <a href="{{ $contact->website }}" target="_blank" class="hover:underline truncate">{{ $contact->website }}</a>
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Tags --}}
        @if($record->tags->count() > 0)
            <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Tags</h3>
                <div class="flex flex-wrap gap-2">
                    @foreach($record->tags as $tag)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium" style="background-color: {{ $tag->color }}20; color: {{ $tag->color }}">
                            {{ $tag->name }}
                        </span>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Team & Ownership --}}
        <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Team & Ownership</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Team</label>
                    <p class="mt-1 text-sm text-gray-900 dark:text-white flex items-center">
                        <x-filament::icon icon="heroicon-o-user-group" class="w-4 h-4 mr-2" />
                        {{ $record->team?->name ?? 'No team assigned' }}
                    </p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Created By</label>
                    <p class="mt-1 text-sm text-gray-900 dark:text-white flex items-center">
                        <x-filament::icon icon="heroicon-o-user-plus" class="w-4 h-4 mr-2" />
                        {{ $record->createdBy?->name ?? 'Unknown' }}
                    </p>
                </div>
                @if($record->conversionBy)
                    <div>
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Converted By</label>
                        <p class="mt-1 text-sm text-success-600 dark:text-success-400 flex items-center">
                            <x-filament::icon icon="heroicon-o-trophy" class="w-4 h-4 mr-2" />
                            {{ $record->conversionBy->name }}
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-filament-panels::page>

