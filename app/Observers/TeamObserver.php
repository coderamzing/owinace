<?php

namespace App\Observers;

use App\Events\TeamCreated;
use App\Models\Team;

class TeamObserver
{
    /**
     * Handle the Team "created" event.
     */
    public function created(Team $team): void
    {
        // Dispatch the TeamCreated event
        event(new TeamCreated($team));
    }
}

