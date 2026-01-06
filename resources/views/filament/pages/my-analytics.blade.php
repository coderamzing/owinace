<x-filament-panels::page>
    @php
        $team = $this->getTeam();
        $goalsData = $this->getGoalsData();
        $leadData = $this->getLeadData();
        $proposalsCount = $this->getProposalsCount();
        $teamName = $team ? $team->name : 'Unknown Team';
    @endphp

    <div class="space-y-6">
        {{-- Header Section --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6 mb-2">
            <!-- Title Area -->
            <div>
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-gradient-to-br from-primary-600 to-indigo-600 rounded-xl flex items-center justify-center">
                        <x-filament::icon icon="heroicon-o-chart-bar-square" class="w-7 h-7 text-white" />
                    </div>
                    <div>
                        <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white">My Analytics</h1>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-0.5">
                            <span class="font-semibold text-primary-600 dark:text-primary-400">{{ $teamName }}</span>
                            <span class="mx-2">·</span>
                            <span>{{ $this->getCurrentPeriodLabel() }}</span>
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Navigation Controls -->
            <div class="flex items-center gap-3">
                <button
                    type="button"
                    wire:click="goToPreviousMonth"
                    @if(!$this->canGoToPreviousMonth()) disabled @endif
                    class="inline-flex items-center justify-center px-4 py-2.5 text-sm font-semibold text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                    <x-filament::icon icon="heroicon-o-chevron-left" class="w-5 h-5 mr-1.5" />
                    Previous
                </button>
                
                <button
                    type="button"
                    wire:click="goToNextMonth"
                    @if(!$this->canGoToNextMonth()) disabled @endif
                    class="inline-flex items-center justify-center px-4 py-2.5 text-sm font-semibold text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                    Next
                    <x-filament::icon icon="heroicon-o-chevron-right" class="w-5 h-5 ml-1.5" />
                </button>
            </div>
        </div>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Total Goals --}}
            <div class="bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-700 rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Goals</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $goalsData['total_goals'] }}</p>
                    </div>
                    <div class="p-3 bg-primary-100 dark:bg-primary-900/20 rounded-lg">
                        <x-filament::icon icon="heroicon-o-flag" class="w-6 h-6 text-primary-600 dark:text-primary-400" />
                    </div>
                </div>
            </div>

            {{-- Achieved --}}
            <div class="bg-success-50 dark:bg-success-900/20 border border-success-200 dark:border-success-700 rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Achieved</p>
                        <p class="text-3xl font-bold text-success-600 dark:text-success-400 mt-2">{{ $goalsData['achieved'] }}</p>
                    </div>
                    <div class="p-3 bg-success-100 dark:bg-success-900/20 rounded-lg">
                        <x-filament::icon icon="heroicon-o-check-circle" class="w-6 h-6 text-success-600 dark:text-success-400" />
                    </div>
                </div>
            </div>

            {{-- Active --}}
            <div class="bg-info-50 dark:bg-info-900/20 border border-info-200 dark:border-info-700 rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Active</p>
                        <p class="text-3xl font-bold text-primary-600 dark:text-primary-400 mt-2">{{ $goalsData['active'] }}</p>
                    </div>
                    <div class="p-3 bg-info-100 dark:bg-info-900/20 rounded-lg">
                        <x-filament::icon icon="heroicon-o-arrow-trending-up" class="w-6 h-6 text-info-600 dark:text-info-400" />
                    </div>
                </div>
            </div>

            {{-- Success Rate --}}
            <div class="bg-success-50 dark:bg-success-900/20 border border-success-200 dark:border-success-700 rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Success Rate</p>
                        <p class="text-3xl font-bold {{ $goalsData['success_rate'] >= 100 ? 'text-success-600 dark:text-success-400' : 'text-warning-600 dark:text-warning-400' }} mt-2">
                            {{ number_format($goalsData['success_rate'], 1) }}%
                        </p>
                    </div>
                    <div class="p-3 bg-success-100 dark:bg-success-900/20 rounded-lg">
                        <x-filament::icon icon="heroicon-o-chart-bar" class="w-6 h-6 text-success-600 dark:text-success-400" />
                    </div>
                </div>
            </div>
        </div>

        {{-- Leads Summary Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            {{-- Total Leads --}}
            <div class="bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-700 rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Leads</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-2">{{ $leadData['total_lead'] }}</p>
                    </div>
                    <div class="p-3 bg-primary-100 dark:bg-primary-900/20 rounded-lg">
                        <x-filament::icon icon="heroicon-o-user-group" class="w-5 h-5 text-primary-600 dark:text-primary-400" />
                    </div>
                </div>
            </div>

            {{-- Won --}}
            <div class="bg-success-50 dark:bg-success-900/20 border border-success-200 dark:border-success-700 rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Won</p>
                        <p class="text-2xl font-bold text-success-600 dark:text-success-400 mt-2">{{ $leadData['total_won'] }}</p>
                    </div>
                    <div class="p-3 bg-success-100 dark:bg-success-900/20 rounded-lg">
                        <x-filament::icon icon="heroicon-o-trophy" class="w-5 h-5 text-success-600 dark:text-success-400" />
                    </div>
                </div>
            </div>

            {{-- Lost --}}
            <div class="bg-danger-50 dark:bg-danger-900/20 border border-danger-200 dark:border-danger-700 rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Lost</p>
                        <p class="text-2xl font-bold text-danger-600 dark:text-danger-400 mt-2">{{ $leadData['total_lost'] }}</p>
                    </div>
                    <div class="p-3 bg-danger-100 dark:bg-danger-900/20 rounded-lg">
                        <x-filament::icon icon="heroicon-o-x-circle" class="w-5 h-5 text-danger-600 dark:text-danger-400" />
                    </div>
                </div>
            </div>

            {{-- Open --}}
            <div class="bg-info-50 dark:bg-info-900/20 border border-info-200 dark:border-info-700 rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Open</p>
                        <p class="text-2xl font-bold text-info-600 dark:text-info-400 mt-2">{{ $leadData['total_open'] }}</p>
                    </div>
                    <div class="p-3 bg-info-100 dark:bg-info-900/20 rounded-lg">
                        <x-filament::icon icon="heroicon-o-clock" class="w-5 h-5 text-info-600 dark:text-info-400" />
                    </div>
                </div>
            </div>

            {{-- Proposals Created --}}
            <div class="bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-700 rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Proposals</p>
                        <p class="text-2xl font-bold text-primary-600 dark:text-primary-400 mt-2">{{ $proposalsCount }}</p>
                    </div>
                    <div class="p-3 bg-primary-100 dark:bg-primary-900/20 rounded-lg">
                        <x-filament::icon icon="heroicon-o-document-text" class="w-5 h-5 text-primary-600 dark:text-primary-400" />
                    </div>
                </div>
            </div>
        </div>

        {{-- Goals Table --}}
        <div class="bg-gray-50 dark:bg-gray-900/50 border border-gray-300 dark:border-gray-600 rounded-lg overflow-hidden">
            <div class="p-6 border-b-2 border-primary-200 dark:border-primary-700 bg-gradient-to-r from-primary-50 to-info-50 dark:from-primary-900/20 dark:to-info-900/20">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-primary-600 dark:bg-primary-500 rounded-lg flex items-center justify-center">
                        <x-filament::icon icon="heroicon-o-flag" class="w-6 h-6 text-white" />
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                            My Goals for {{ $teamName }}
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $this->getCurrentPeriodLabel() }}</p>
                    </div>
                </div>
            </div>
            
            @if(count($goalsData['goals']) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-100 dark:bg-gray-800/50 border-b-2 border-gray-200 dark:border-gray-700">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                    <x-filament::icon icon="heroicon-o-tag" class="w-4 h-4 inline mr-1" />
                                    Goal Type
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                    <x-filament::icon icon="heroicon-o-chart-bar" class="w-4 h-4 inline mr-1" />
                                    Achievement
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                    <x-filament::icon icon="heroicon-o-signal" class="w-4 h-4 inline mr-1" />
                                    Status
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                    <x-filament::icon icon="heroicon-o-calendar" class="w-4 h-4 inline mr-1" />
                                    Period
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($goalsData['goals'] as $goal)
                                @php
                                    $statusColors = [
                                        'achieved' => 'bg-success-100 text-success-800 dark:bg-success-900/20 dark:text-success-400 border-success-200 dark:border-success-700',
                                        'on_track' => 'bg-info-100 text-info-800 dark:bg-info-900/20 dark:text-info-400 border-info-200 dark:border-info-700',
                                        'behind' => 'bg-danger-100 text-danger-800 dark:bg-danger-900/20 dark:text-danger-400 border-danger-200 dark:border-danger-700',
                                    ];
                                    $statusIcons = [
                                        'achieved' => 'heroicon-o-check-circle',
                                        'on_track' => 'heroicon-o-arrow-trending-up',
                                        'behind' => 'heroicon-o-exclamation-triangle',
                                    ];
                                    $statusLabels = [
                                        'achieved' => 'Achieved',
                                        'on_track' => 'On Track',
                                        'behind' => 'Behind',
                                    ];
                                    $statusColor = $statusColors[$goal['status']] ?? $statusColors['behind'];
                                    $statusIcon = $statusIcons[$goal['status']] ?? $statusIcons['behind'];
                                    $statusLabel = $statusLabels[$goal['status']] ?? 'Behind';
                                    
                                    $progressPercentage = min(100, max(0, $goal['achievement']));
                                    $progressColor = $goal['status'] === 'achieved' ? 'bg-success-600' : ($goal['status'] === 'on_track' ? 'bg-info-600' : 'bg-danger-600');
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-semibold bg-info-100 text-info-800 dark:bg-info-900/20 dark:text-info-400 border border-info-200 dark:border-info-700">
                                            <x-filament::icon icon="heroicon-o-flag" class="w-4 h-4 mr-1.5" />
                                            {{ $goal['goal_type'] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center space-x-3">
                                            <div class="flex-1">
                                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ number_format($goal['achievement'], 1) }}%
                                                </div>
                                                <div class="mt-1 w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                                    <div 
                                                        class="h-2 rounded-full {{ $progressColor }} transition-all duration-300"
                                                        style="width: {{ $progressPercentage }}%"
                                                    ></div>
                                                </div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                    {{ number_format($goal['progress_value'], 0) }} / {{ number_format($goal['target_value'], 0) }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-semibold {{ $statusColor }} border">
                                            <x-filament::icon icon="{{ $statusIcon }}" class="w-4 h-4 mr-1.5" />
                                            {{ $statusLabel }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-semibold bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600">
                                            <x-filament::icon icon="heroicon-o-calendar-days" class="w-4 h-4 mr-1.5" />
                                            {{ $goal['year'] }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-16 text-center">
                    <div class="w-20 h-20 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                        <x-filament::icon icon="heroicon-o-flag" class="w-10 h-10 text-gray-400 dark:text-gray-500" />
                    </div>
                    <p class="text-gray-700 dark:text-gray-300 font-semibold text-lg mb-2">No goals found for this period</p>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Set goals to track your progress and achievements</p>
                </div>
            @endif
        </div>

        {{-- Tips Section --}}
        <div class="bg-gradient-to-br from-success-50 to-primary-50 dark:from-success-900/20 dark:to-primary-900/20 border border-success-200 dark:border-success-700 rounded-lg p-8 overflow-hidden relative">
            <div class="absolute top-0 right-0 w-64 h-64 bg-success-100 dark:bg-success-900/10 rounded-full -mr-32 -mt-32 opacity-30"></div>
            <div class="relative">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 bg-success-600 dark:bg-success-500 rounded-lg flex items-center justify-center">
                        <x-filament::icon icon="heroicon-o-light-bulb" class="w-6 h-6 text-white" />
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">Goal Achievement Tips</h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-primary-200 dark:border-primary-700 hover:shadow-lg transition-all">
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0 p-3 bg-primary-100 dark:bg-primary-900/30 rounded-lg">
                                <x-filament::icon icon="heroicon-o-chart-bar" class="w-6 h-6 text-primary-600 dark:text-primary-400" />
                            </div>
                            <div>
                                <h4 class="text-base font-bold text-gray-900 dark:text-white mb-2">Track Progress</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                                    Monitor your progress regularly and adjust your approach as needed.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-success-200 dark:border-success-700 hover:shadow-lg transition-all">
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0 p-3 bg-success-100 dark:bg-success-900/30 rounded-lg">
                                <x-filament::icon icon="heroicon-o-star" class="w-6 h-6 text-success-600 dark:text-success-400" />
                            </div>
                            <div>
                                <h4 class="text-base font-bold text-gray-900 dark:text-white mb-2">Stay Focused</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                                    Keep your goals in mind and prioritize tasks that help you achieve them.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-info-200 dark:border-info-700 hover:shadow-lg transition-all">
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0 p-3 bg-info-100 dark:bg-info-900/30 rounded-lg">
                                <x-filament::icon icon="heroicon-o-user-group" class="w-6 h-6 text-info-600 dark:text-info-400" />
                            </div>
                            <div>
                                <h4 class="text-base font-bold text-gray-900 dark:text-white mb-2">Team Support</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                                    Collaborate with your team members and ask for help when needed.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>

