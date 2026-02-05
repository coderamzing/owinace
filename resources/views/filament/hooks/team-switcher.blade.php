@php
    $workspaceId = session('workspace_id');
    $currentTeamId = session('team_id');
    $teams = $workspaceId ? \App\Models\Team::where('workspace_id', $workspaceId)->get() : collect([]);
@endphp

<a href="http://127.0.0.1:8000/admin" class="absolute left-0 block lg:hidden">
    <img alt="Laravel logo" src="http://127.0.0.1:8000/images/leadcliq.svg" class="fi-logo sm:min-w-[150px] sm:h-[2rem] min-w-[100px] h-[1.5rem]">
</a>

<div class="fi-topbar-items flex items-center gap-4 w-full">
    <!-- Add Credit Button (left) -->
    <a
        href="{{ \App\Filament\Pages\AddCredit::getUrl() }}"
        class="sm:block hidden fi-topbar-item-button fi-btn fi-btn-size-md fi-btn-color-gray fi-btn-variant-ghost gap-x-2 rounded-lg px-3 py-2 text-sm font-semibold shadow-sm ring-1 transition duration-75 fi-color-gray fi-btn-color-gray hover:bg-gray-50 dark:hover:bg-white/5"
        title="Add Credits"
    >
        <i class="iconify tabler--credit-card size-5"></i>
        <span class="hidden sm:inline">Add Credit</span>
    </a>

    <!-- Global Search (center) -->
    <form
        action="#"
        method="GET"
        class="hidden md:flex flex-1 justify-center"
        role="search"
    >
        <div class="relative w-full max-w-xl">
            <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-gray-400">
                <i class="iconify tabler--search size-4"></i>
            </span>
            <input
                type="search"
                name="q"
                class="w-full rounded-lg border border-gray-300 bg-white/80 pl-9 pr-3 py-1.5 text-sm text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                placeholder="Search leads, contacts, teams..."
                autocomplete="off"
            />
        </div>
    </form>

    <!-- Team Switcher (right of search, before notifications) -->
    @if($teams->count() > 0)
        <div class="fi-topbar-team-switcher flex items-center gap-2 ">
            <form method="POST" action="" id="team-switch-form" class="inline">
                @csrf
                <select 
                    name="team_id"
                    onchange="const teamId = this.value; const form = document.getElementById('team-switch-form'); form.action = '/team/switch/' + teamId; form.submit();"
                    class="text-md rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 focus:border-primary-500 focus:ring-primary-500 outline-none"
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
</div>

