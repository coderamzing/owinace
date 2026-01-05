<x-filament-panels::page>
    @php
        $team = $this->getTeam();
        $goalsData = $this->getGoalsData();
        $leadData = $this->getLeadData();
        $proposalsCount = $this->getProposalsCount();
        $teamName = $team ? $team->name : 'Unknown Team';
        $monthName = $this->selectedPeriod ? \Carbon\Carbon::parse($this->selectedPeriod . '-01')->format('F Y') : \Carbon\Carbon::now()->format('F Y');
    @endphp

    <div class="space-y-6">
        {{-- Header --}}
        <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-lg p-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">My Analytics</h2>
            <p class="text-gray-600 dark:text-gray-400">
                Track your goal progress, leads, and proposals for {{ $teamName }} - {{ $monthName }}
            </p>
        </div>

        {{-- Filters --}}
        <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-lg p-4">
            <div class="flex flex-col sm:flex-row gap-4 items-end">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Month & Year
                    </label>
                    <select 
                        wire:model.live="selectedPeriod"
                        class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 focus:border-primary-500 focus:ring-primary-500"
                    >
                        @foreach($this->getPeriodOptions() as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Total Goals --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-lg p-6">
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
            <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-lg p-6">
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
            <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-lg p-6">
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
            <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-lg p-6">
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
            <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-lg p-6">
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
            <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-lg p-6">
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
            <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-lg p-6">
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
            <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-lg p-6">
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
            <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-lg p-6">
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
        <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-lg">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    My Goals for {{ $teamName }} - {{ $monthName }}
                </h3>
            </div>
            
            @if(count($goalsData['goals']) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-900/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Goal Type
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Achievement
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Month/Year
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($goalsData['goals'] as $goal)
                                @php
                                    $statusColors = [
                                        'achieved' => 'bg-success-100 text-success-800 dark:bg-success-900/20 dark:text-success-400',
                                        'on_track' => 'bg-info-100 text-info-800 dark:bg-info-900/20 dark:text-info-400',
                                        'behind' => 'bg-danger-100 text-danger-800 dark:bg-danger-900/20 dark:text-danger-400',
                                    ];
                                    $statusLabels = [
                                        'achieved' => 'Achieved',
                                        'on_track' => 'On Track',
                                        'behind' => 'Behind',
                                    ];
                                    $statusColor = $statusColors[$goal['status']] ?? $statusColors['behind'];
                                    $statusLabel = $statusLabels[$goal['status']] ?? 'Behind';
                                    
                                    $progressPercentage = min(100, max(0, $goal['achievement']));
                                    $progressColor = $goal['status'] === 'achieved' ? 'bg-success-600' : ($goal['status'] === 'on_track' ? 'bg-info-600' : 'bg-danger-600');
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-info-100 text-info-800 dark:bg-info-900/20 dark:text-info-400">
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
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $statusColor }}">
                                            {{ $statusLabel }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                            {{ $goal['year'] }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-12 text-center">
                    <x-filament::icon icon="heroicon-o-flag" class="w-12 h-12 text-gray-400 dark:text-gray-500 mx-auto mb-4" />
                    <p class="text-gray-600 dark:text-gray-400">No goals found for this period.</p>
                </div>
            @endif
        </div>

        {{-- Tips Section --}}
        <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Goal Achievement Tips</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0 p-3 bg-primary-100 dark:bg-primary-900/20 rounded-lg">
                        <x-filament::icon icon="heroicon-o-chart-bar" class="w-6 h-6 text-primary-600 dark:text-primary-400" />
                    </div>
                    <div>
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-1">Track Progress</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Monitor your progress regularly and adjust your approach as needed.
                        </p>
                    </div>
                </div>
                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0 p-3 bg-success-100 dark:bg-success-900/20 rounded-lg">
                        <x-filament::icon icon="heroicon-o-star" class="w-6 h-6 text-success-600 dark:text-success-400" />
                    </div>
                    <div>
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-1">Stay Focused</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Keep your goals in mind and prioritize tasks that help you achieve them.
                        </p>
                    </div>
                </div>
                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0 p-3 bg-info-100 dark:bg-info-900/20 rounded-lg">
                        <x-filament::icon icon="heroicon-o-user-group" class="w-6 h-6 text-info-600 dark:text-info-400" />
                    </div>
                    <div>
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-1">Team Support</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Collaborate with your team members and ask for help when needed.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>

