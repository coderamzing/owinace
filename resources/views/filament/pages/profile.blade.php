<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Profile Information Section --}}
        <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-lg">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                    Profile Information
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Update your account's profile information and email address.
                </p>
            </div>
            <div class="p-6">
                <form wire:submit="updateProfile">
                    {{ $this->profileForm }}
                    
                    <div class="mt-6 flex justify-end gap-3">
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
        </div>

        {{-- Password Update Section --}}
        <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-lg">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                    Update Password
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Ensure your account is using a long, random password to stay secure.
                </p>
            </div>
            <div class="p-6">
                <form wire:submit="updatePassword">
                    {{ $this->passwordForm }}
                    
                    <div class="mt-6 flex justify-end gap-3">
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
        </div>
    </div>
</x-filament-panels::page>

