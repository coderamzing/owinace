<x-filament-panels::page>

    <div class="space-y-6">
        {{-- Settings Cards Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($this->getSettingsCards() as $card)
                @php
                    $colorClasses = [
                        'primary' => 'bg-primary-100 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400',
                        'success' => 'bg-success-100 dark:bg-success-900/20 text-success-600 dark:text-success-400',
                        'warning' => 'bg-warning-100 dark:bg-warning-900/20 text-warning-600 dark:text-warning-400',
                        'danger' => 'bg-danger-100 dark:bg-danger-900/20 text-danger-600 dark:text-danger-400',
                        'info' => 'bg-info-100 dark:bg-info-900/20 text-info-600 dark:text-info-400',
                        'gray' => 'bg-gray-100 dark:bg-gray-900/20 text-gray-600 dark:text-gray-400',
                    ];
                    $bgClasses = [
                        'primary' => 'bg-primary-50 dark:bg-primary-900/20',
                        'success' => 'bg-success-50 dark:bg-success-900/20',
                        'warning' => 'bg-warning-50 dark:bg-warning-900/20',
                        'danger' => 'bg-danger-50 dark:bg-danger-900/20',
                        'info' => 'bg-info-50 dark:bg-info-900/20',
                        'gray' => 'bg-gray-50 dark:bg-gray-900/20',
                    ];
                    $borderClasses = [
                        'primary' => 'border-primary-200 dark:border-primary-700',
                        'success' => 'border-success-200 dark:border-success-700',
                        'warning' => 'border-warning-200 dark:border-warning-700',
                        'danger' => 'border-danger-200 dark:border-danger-700',
                        'info' => 'border-info-200 dark:border-info-700',
                        'gray' => 'border-gray-300 dark:border-gray-600',
                    ];
                    $hoverBorderClasses = [
                        'primary' => 'hover:border-primary-400 dark:hover:border-primary-500',
                        'success' => 'hover:border-success-400 dark:hover:border-success-500',
                        'warning' => 'hover:border-warning-400 dark:hover:border-warning-500',
                        'danger' => 'hover:border-danger-400 dark:hover:border-danger-500',
                        'info' => 'hover:border-info-400 dark:hover:border-info-500',
                        'gray' => 'hover:border-gray-400 dark:hover:border-gray-500',
                    ];
                    $textColorClasses = [
                        'primary' => 'text-primary-600 dark:text-primary-400',
                        'success' => 'text-success-600 dark:text-success-400',
                        'warning' => 'text-warning-600 dark:text-warning-400',
                        'danger' => 'text-danger-600 dark:text-danger-400',
                        'info' => 'text-info-600 dark:text-info-400',
                        'gray' => 'text-gray-600 dark:text-gray-400',
                    ];
                    $iconBgClass = $colorClasses[$card['color']] ?? $colorClasses['primary'];
                    $cardBgClass = $bgClasses[$card['color']] ?? $bgClasses['primary'];
                    $borderClass = $borderClasses[$card['color']] ?? $borderClasses['primary'];
                    $hoverBorderClass = $hoverBorderClasses[$card['color']] ?? $hoverBorderClasses['primary'];
                    $textColorClass = $textColorClasses[$card['color']] ?? $textColorClasses['primary'];
                @endphp
                <a 
                    href="{{ $card['url'] }}" 
                    class="group relative {{ $cardBgClass }} border {{ $borderClass }} {{ $hoverBorderClass }} rounded-lg p-6 transition-all duration-200"
                >
                    {{-- Icon --}}
                    <div class="mb-4">
                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-lg {{ $iconBgClass }} group-hover:scale-110 transition-transform duration-200">
                            <x-filament::icon 
                                icon="{{ $card['icon'] }}" 
                                class="w-6 h-6"
                            />
                        </div>
                    </div>

                    {{-- Title --}}
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
                        {{ $card['title'] }}
                    </h3>

                    {{-- Description --}}
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        {{ $card['description'] }}
                    </p>

                    {{-- Arrow Icon --}}
                    <div class="flex items-center {{ $textColorClass }} text-sm font-medium">
                        <span>Configure</span>
                        <x-filament::icon 
                            icon="heroicon-o-arrow-right" 
                            class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform"
                        />
                    </div>

                    {{-- Hover Effect Overlay --}}
                    <div class="absolute inset-0 rounded-lg bg-primary-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none"></div>
                </a>
            @endforeach
        </div>

        {{-- Quick Stats or Additional Info --}}
        <div class="bg-gray-50 dark:bg-gray-900/50 border border-gray-300 dark:border-gray-600 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Quick Access</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="flex items-center space-x-3">
                    <x-filament::icon icon="heroicon-o-information-circle" class="w-5 h-5 text-primary-600 dark:text-primary-400" />
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">Need Help?</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Check documentation for detailed guides</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <x-filament::icon icon="heroicon-o-shield-check" class="w-5 h-5 text-success-600 dark:text-success-400" />
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">Security</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Manage roles and permissions</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <x-filament::icon icon="heroicon-o-puzzle-piece" class="w-5 h-5 text-warning-600 dark:text-warning-400" />
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">Integrations</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Connect external services</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-filament-panels::page>
