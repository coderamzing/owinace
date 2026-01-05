@php
    $teams = $this->getTeams();
    $currentTeamId = $this->getCurrentTeamId();
@endphp

@if($teams->count() > 0)
    <div class="flex items-center gap-2 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Team:</label>
        <select 
            wire:change="switchTeam($event.target.value)"
            class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500"
        >
            @foreach($teams as $team)
                <option value="{{ $team->id }}" {{ $currentTeamId == $team->id ? 'selected' : '' }}>
                    {{ $team->name }}
                </option>
            @endforeach
        </select>
    </div>
@endif

