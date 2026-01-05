@php
    $workspaceId = session('workspace_id');
    $currentTeamId = session('team_id');
    $teams = $workspaceId ? \App\Models\Team::where('workspace_id', $workspaceId)->get() : collect([]);
@endphp

@if($teams->count() > 0)
    <div class="fi-topbar-team-switcher flex items-center gap-2 px-4">
        <label class="text-sm font-medium text-gray-700 dark:text-gray-300 whitespace-nowrap">Team:</label>
        <form method="POST" action="" id="team-switch-form" class="inline">
            @csrf
            <select 
                name="team_id"
                onchange="const teamId = this.value; const form = document.getElementById('team-switch-form'); form.action = '/team/switch/' + teamId; form.submit();"
                class="text-sm rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 focus:border-primary-500 focus:ring-primary-500"
            >
                @foreach($teams as $team)
                    <option value="{{ $team->id }}" {{ $currentTeamId == $team->id ? 'selected' : '' }}>
                        {{ $team->name }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>
@endif

