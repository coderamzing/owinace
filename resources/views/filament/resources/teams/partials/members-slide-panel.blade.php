<div class="space-y-4">
    <div class="flex justify-between items-center">
        <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $record->name }}</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $members->count() }} member(s)</p>
        </div>
    </div>

    <div class="space-y-2">
        @forelse($members as $member)
            <div class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                <div class="flex items-center space-x-3 flex-1">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center">
                            <span class="text-primary-600 dark:text-primary-400 font-semibold">
                                {{ strtoupper(substr($member->user?->name ?? $member->email ?? 'N', 0, 1)) }}
                            </span>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-gray-900 dark:text-white truncate">
                            {{ $member->user?->name ?? $member->email ?? 'N/A' }}
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 truncate">
                            {{ $member->email ?? 'N/A' }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <span class="px-2 py-1 text-xs font-medium rounded {{ $member->role === 'admin' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300' : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300' }}">
                        {{ ucfirst($member->role ?? 'member') }}
                    </span>
                    <span class="px-2 py-1 text-xs font-medium rounded {{ $member->status === 'active' ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300' : ($member->status === 'pending' ? 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300' : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300') }}">
                        {{ ucfirst($member->status ?? 'pending') }}
                    </span>
                    <div class="flex items-center space-x-2">
                        @if($member->status === 'active')
                            <button
                                type="button"
                                wire:click="setMemberInactive({{ $member->id }})"
                                wire:confirm="Are you sure you want to set this member as inactive?"
                                class="px-3 py-1.5 text-xs font-medium text-yellow-700 dark:text-yellow-400 bg-yellow-100 dark:bg-yellow-900/30 border border-yellow-300 dark:border-yellow-700 rounded-lg hover:bg-yellow-200 dark:hover:bg-yellow-900/50 transition-colors"
                            >
                                Set Inactive
                            </button>
                        @else
                            <button
                                type="button"
                                wire:click="setMemberActive({{ $member->id }})"
                                class="px-3 py-1.5 text-xs font-medium text-green-700 dark:text-green-400 bg-green-100 dark:bg-green-900/30 border border-green-300 dark:border-green-700 rounded-lg hover:bg-green-200 dark:hover:bg-green-900/50 transition-colors"
                            >
                                Set Active
                            </button>
                        @endif
                        <button
                            type="button"
                            wire:click="removeMember({{ $member->id }})"
                            wire:confirm="Are you sure you want to remove this member from the team? This action cannot be undone."
                            class="px-3 py-1.5 text-xs font-medium text-red-700 dark:text-red-400 bg-red-100 dark:bg-red-900/30 border border-red-300 dark:border-red-700 rounded-lg hover:bg-red-200 dark:hover:bg-red-900/50 transition-colors"
                        >
                            Remove
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                <p>No members found in this team.</p>
            </div>
        @endforelse
    </div>
</div>

