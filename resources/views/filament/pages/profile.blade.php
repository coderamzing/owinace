<x-filament-panels::page>
    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-lg my-profile-tabs">
        
        {{-- Tabs Header --}}
        <x-filament::tabs>
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

            @if(Filament\Facades\Filament::hasMultiFactorAuthentication())
                <x-filament::tabs.item
                    wire:click="$set('activeTab', 'mfa')"
                    :active="$activeTab === 'mfa'"
                    icon="heroicon-o-shield-check"
                >
                    MFA
                </x-filament::tabs.item>
            @endif
        </x-filament::tabs>

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

            {{-- MFA Tab --}}
            @if($activeTab === 'mfa' && Filament\Facades\Filament::hasMultiFactorAuthentication())
                <div class="space-y-4">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                        Multi-Factor Authentication
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Add an extra layer of security to your account.
                    </p>

                    {{ $this->mfaForm }}
                </div>
            @endif

        </div>
    </div>
</x-filament-panels::page>
