<div class="space-y-4">
    <div class="flex justify-between items-center">
        <div>
            <h3 class="text-lg font-semibold">{{ $team->name }}</h3>
            <p class="text-sm text-gray-500">{{ $members->count() }} member(s)</p>
        </div>
    </div>

    <div class="space-y-2">
        @forelse($members as $member)
            <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                <div class="flex items-center space-x-3">
                    <div>
                        <p class="font-medium">
                            {{ $member->user ? $member->user->name : ($member->email ?? 'N/A') }}
                        </p>
                        <p class="text-sm text-gray-500">{{ $member->email ?? 'N/A' }}</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="px-2 py-1 text-xs font-medium rounded {{ $member->role === 'admin' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                        {{ ucfirst($member->role ?? 'member') }}
                    </span>
                    <span class="px-2 py-1 text-xs font-medium rounded {{ $member->status === 'active' ? 'bg-green-100 text-green-800' : ($member->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                        {{ ucfirst($member->status ?? 'pending') }}
                    </span>
                    @if($member->user_id)
                        <a 
                            href="{{ \Filament\Facades\Filament::getUrl() . '/team-members/' . $member->id . '/edit' }}"
                            class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                        >
                            Manage
                        </a>
                    @endif
                </div>
            </div>
        @empty
            <p class="text-gray-500 text-center py-4">No members in this team yet.</p>
        @endforelse
    </div>

    <div class="pt-4 border-t">
        <a 
            href="{{ \Filament\Facades\Filament::getUrl() . '/team-members?tableFilters[team_id][value]=' . $team->id }}"
            class="text-blue-600 hover:text-blue-800 text-sm font-medium"
        >
            View all members →
        </a>
    </div>
</div>
