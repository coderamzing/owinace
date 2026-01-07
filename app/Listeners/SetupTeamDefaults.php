<?php

namespace App\Listeners;

use App\Events\TeamCreated;
use App\Services\OnBoardService;

class SetupTeamDefaults
{
    protected OnBoardService $onBoardService;

    /**
     * Create the event listener.
     */
    public function __construct(OnBoardService $onBoardService)
    {
        $this->onBoardService = $onBoardService;
    }

    /**
     * Handle the event.
     */
    public function handle(TeamCreated $event): void
    {
        // Create default kanban stages for the team
        $this->onBoardService->createDefaultKanbanStages($event->team);

        // Create default lead sources for the team
        $this->onBoardService->createDefaultLeadSources($event->team);
    }
}

