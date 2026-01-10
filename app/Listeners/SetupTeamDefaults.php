<?php

namespace App\Listeners;

use App\Events\TeamCreated;
use App\Models\TeamMember;
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
        $team = $event->team;
        $workspace = $team->workspace;

        // Add workspace owner as admin if they're not already a member
        if ($workspace && $workspace->owner_id) {
            $isAlreadyMember = TeamMember::where('team_id', $team->id)
                ->where('user_id', $workspace->owner_id)
                ->exists();

            if (!$isAlreadyMember) {
                // Load owner to get email
                $owner = $workspace->owner;
                
                TeamMember::create([
                    'team_id' => $team->id,
                    'user_id' => $workspace->owner_id,
                    'role' => 'admin',
                    'status' => 'active',
                    'email' => $owner->email ?? null,
                    'joined_at' => now(),
                ]);
            }
        }

        // Create default kanban stages for the team
        $this->onBoardService->createDefaultKanbanStages($team);

        // Create default lead sources for the team
        $this->onBoardService->createDefaultLeadSources($team);
    }
}

