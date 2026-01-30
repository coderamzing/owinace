<?php

namespace App\Listeners;

use App\Events\TeamCreated;
use App\Models\TeamMember;
use App\Services\OnBoardService;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;

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
        // Use firstOrCreate with try-catch to handle race conditions and duplicate entries
        if ($workspace && $workspace->owner_id) {
            try {
                // Load owner to get email
                $owner = $workspace->owner;
                
                TeamMember::firstOrCreate(
                    [
                        'team_id' => $team->id,
                        'user_id' => $workspace->owner_id,
                    ],
                    [
                        'role' => 'admin',
                        'status' => 'active',
                        'email' => $owner->email ?? null,
                        'joined_at' => now(),
                    ]
                );
            } catch (UniqueConstraintViolationException $e) {
                // If it's a duplicate key error, ignore it - member already exists
                // This can happen in race conditions where firstOrCreate is called simultaneously
                // The unique constraint will prevent the duplicate, so we can safely ignore this
                // Just continue - we still need to create kanban stages and lead sources
            } catch (QueryException $e) {
                // Also catch QueryException in case it's thrown before conversion to UniqueConstraintViolationException
                // Check if it's a duplicate entry error (SQLSTATE 23000)
                if ($e->getCode() === '23000' || (is_int($e->getCode()) && $e->getCode() === 23000)) {
                    // Member already exists, which is fine - just continue
                    // Just continue - we still need to create kanban stages and lead sources
                } else {
                    // Re-throw if it's a different error
                    throw $e;
                }
            }
        }

        // Create default kanban stages for the team
        $this->onBoardService->createDefaultKanbanStages($team);

        // Create default lead sources for the team
        $this->onBoardService->createDefaultLeadSources($team);
    }
}

