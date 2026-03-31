<x-filament-panels::page>
    <div class="w-full max-w-3xl mx-auto">
        <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-lg my-profile-tabs">
            <div class="flex flex-col items-center py-8">
                <div class="w-56 mb-4">
                    {{ $this->avatarForm }}
                </div>
                <style>
                    div[id*="avatar-label"]{
                        display: none !important;
                    }
                </style>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ auth()->user()->name }}
                </h2>
                <div class="text-md text-gray-500 dark:text-gray-400">
                    {{ auth()->user()->email }}
                </div>
            </div>

            <div class="px-4 profile-tabs">
                {{-- Tabs Header --}}
                <x-filament::tabs contained="true">
                    <x-filament::tabs.item
                        wire:click="$set('activeTab', 'profile')"
                        :active="$activeTab === 'profile'"
                        icon="heroicon-o-user"
                    >
                        Profile
                    </x-filament::tabs.item>

                    <x-filament::tabs.item
                        wire:click="$set('activeTab', 'password')"
                        :active="$activeTab === 'password'"
                        icon="heroicon-o-key"
                    >
                        Password
                    </x-filament::tabs.item>

                    <x-filament::tabs.item
                        wire:click="$set('activeTab', 'sessions')"
                        :active="$activeTab === 'sessions'"
                        icon="heroicon-o-computer-desktop"
                    >
                        Sessions
                    </x-filament::tabs.item>
                </x-filament::tabs>
            </div>

            {{-- Tabs Content --}}
            <div class="p-6">

                {{-- Profile Tab --}}
                @if($activeTab === 'profile')
                    <div class="space-y-4">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                            Profile Information
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Update your account's profile information and email address.
                        </p>

                        <form wire:submit="updateProfile">
                            {{ $this->profileForm }}

                            <div class="mt-6 flex profile-submit-button">
                                <x-filament::button
                                    type="submit"
                                    color="primary"
                                    icon="heroicon-o-check"
                                >
                                    Save Profile
                                </x-filament::button>
                            </div>
                        </form>
                    </div>
                @endif

                {{-- Password Tab --}}
                @if($activeTab === 'password')
                    <div class="space-y-4">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                            Update Password
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Ensure your account is using a long, random password to stay secure.
                        </p>

                        <form wire:submit="updatePassword">
                            {{ $this->passwordForm }}

                            <div class="mt-6 flex profile-submit-button">
                                <x-filament::button
                                    type="submit"
                                    color="primary"
                                    icon="heroicon-o-key"
                                >
                                    Update Password
                                </x-filament::button>
                            </div>
                        </form>
                    </div>
                @endif

                {{-- Sessions Tab --}}
                @if($activeTab === 'sessions')
                    <div class="space-y-4">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                                Active Sessions
                            </h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Manage and logout active sessions on your account.
                            </p>
                        </div>

                        @php
                            $sessions = $this->sessions;
                        @endphp

                        @if(empty($sessions))
                            <div class="text-center py-8">
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    No active sessions found.
                                </p>
                            </div>
                        @else
                            <div class="space-y-4">
                                @foreach($sessions as $session)
                                    <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2 mb-2">
                                                <h3 class="font-medium text-gray-900 dark:text-white">
                                                    {{ $session['device'] }}
                                                </h3>
                                                @if($session['is_current_session'])
                                                    <span class="px-2 py-1 text-xs font-medium bg-primary-100 dark:bg-primary-900 text-primary-800 dark:text-primary-200 rounded">
                                                        Current Session
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                                                <div>
                                                    <span class="font-medium">IP Address:</span> {{ $session['ip_address'] ?? 'Unknown' }}
                                                </div>
                                                <div>
                                                    <span class="font-medium">Last Activity:</span> {{ \Carbon\Carbon::createFromTimestamp($session['last_activity'])->diffForHumans() }}
                                                </div>
                                                @if($session['user_agent'])
                                                    <div class="text-xs text-gray-500 dark:text-gray-500 max-w-md word-break-all">
                                                        {{ $session['user_agent'] }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        @if(!$session['is_current_session'])
                                            <div class="ml-4">
                                                <x-filament::button
                                                    wire:click="logoutSession('{{ $session['id'] }}')"
                                                    wire:confirm="Are you sure you want to logout this session?"
                                                    color="danger"
                                                    size="sm"
                                                    icon="heroicon-o-arrow-right-on-rectangle"
                                                >
                                                    Logout
                                                </x-filament::button>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif

            </div>
        </div>
    </div>
</x-filament-panels::page>
