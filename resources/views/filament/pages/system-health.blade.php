<x-filament-panels::page>
    @php
        $healthData = $this->getSystemHealthData();
        $overallPercentage = $this->getOverallHealthPercentage();
        $healthStatus = $this->getHealthStatus();
        $healthStatusColor = $this->getHealthStatusColor();
        $team = $this->getTeam();
    @endphp

    <div class="space-y-6">
        {{-- Overall Health Section --}}
        <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                            System Health Dashboard
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Complete the following requirements to ensure your system is fully operational
                            @if($team)
                                <span class="font-semibold text-gray-700 dark:text-gray-300">({{ $team->name }})</span>
                            @endif
                        </p>
                    </div>
                    <div class="text-right">
                        <div class="text-4xl font-bold {{ 
                            $healthStatusColor === 'success' ? 'text-success-600' : 
                            ($healthStatusColor === 'info' ? 'text-info-600' : 
                            ($healthStatusColor === 'warning' ? 'text-warning-600' : 'text-danger-600'))
                        }}">
                            {{ $overallPercentage }}%
                        </div>
                        <div class="text-sm font-medium text-gray-600 dark:text-gray-400 mt-1">
                            {{ $healthStatus }}
                        </div>
                    </div>
                </div>

                {{-- Progress Bar --}}
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3 overflow-hidden">
                    <div 
                        class="h-3 transition-all duration-500 {{ 
                            $healthStatusColor === 'success' ? 'bg-success-600' : 
                            ($healthStatusColor === 'info' ? 'bg-info-600' : 
                            ($healthStatusColor === 'warning' ? 'bg-warning-600' : 'bg-danger-600'))
                        }}" 
                        style="width: {{ $overallPercentage }}%"
                    ></div>
                </div>
            </div>
        </div>

        {{-- Requirements Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach($healthData as $key => $item)
                <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden hover:shadow-md transition-shadow">
                    <div class="p-6">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-3">
                                    {{-- Status Icon --}}
                                    @if($item['status'] === 'success')
                                        <div class="flex-shrink-0 w-10 h-10 bg-success-100 dark:bg-success-900/20 rounded-full flex items-center justify-center">
                                            <svg class="w-6 h-6 text-success-600 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </div>
                                    @else
                                        <div class="flex-shrink-0 w-10 h-10 bg-danger-100 dark:bg-danger-900/20 rounded-full flex items-center justify-center">
                                            <svg class="w-6 h-6 text-danger-600 dark:text-danger-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </div>
                                    @endif

                                    {{-- Label --}}
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                            {{ $item['label'] }}
                                        </h3>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            @if($item['status'] === 'success')
                                                <span class="text-success-600 dark:text-success-400 font-medium">Completed</span>
                                            @else
                                                <span class="text-danger-600 dark:text-danger-400 font-medium">Action Required</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>

                                {{-- Count Display --}}
                                <div class="mb-4">
                                    <div class="flex items-baseline gap-2">
                                        <span class="text-3xl font-bold {{ 
                                            $item['status'] === 'success' 
                                            ? 'text-success-600 dark:text-success-400' 
                                            : 'text-danger-600 dark:text-danger-400'
                                        }}">
                                            {{ $item['count'] }}
                                        </span>
                                        <span class="text-sm text-gray-500 dark:text-gray-400">
                                            / {{ $item['required'] }} required
                                        </span>
                                    </div>
                                    
                                    {{-- Mini Progress Bar --}}
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 mt-2 overflow-hidden">
                                        <div 
                                            class="h-2 transition-all duration-300 {{ 
                                                $item['status'] === 'success' 
                                                ? 'bg-success-600' 
                                                : 'bg-danger-600'
                                            }}" 
                                            style="width: {{ min(($item['count'] / $item['required']) * 100, 100) }}%"
                                        ></div>
                                    </div>
                                </div>

                                {{-- Action Button --}}
                                @if($item['status'] !== 'success')
                                    <a 
                                        href="{{ $item['link'] }}" 
                                        class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-md transition-colors"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                        Add {{ $item['label'] }}
                                    </a>
                                @else
                                    <a 
                                        href="{{ $item['link'] }}" 
                                        class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-md transition-colors"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        View {{ $item['label'] }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Help Section --}}
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-sm font-semibold text-blue-900 dark:text-blue-100 mb-2">
                        Why is System Health Important?
                    </h3>
                    <p class="text-sm text-blue-800 dark:text-blue-200">
                        Completing these requirements ensures your system has all the necessary components to function properly. 
                        Each requirement plays a crucial role in managing your leads, tracking performance, and achieving your business goals.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>

