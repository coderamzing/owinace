<?php

namespace App\Observers;

use App\Events\LeadWon;
use App\Models\Lead;
use App\Models\LeadKanban;
use Illuminate\Support\Facades\Log;

/**
 * Observer for Lead model
 * 
 * Monitors changes to leads and dispatches events when significant
 * state changes occur, such as when a lead is won.
 */
class LeadObserver
{
    /**
     * Handle the Lead "updating" event.
     * 
     * This runs before the model is saved, allowing us to compare
     * the old and new kanban_id values.
     *
     * @param Lead $lead
     * @return void
     */
    public function updating(Lead $lead): void
    {
        // Check if kanban_id is being changed
        if ($lead->isDirty('kanban_id')) {
            $oldKanbanId = $lead->getOriginal('kanban_id');
            $newKanbanId = $lead->kanban_id;

            // Load the new kanban to check if it's a "won" status
            if ($newKanbanId) {
                $newKanban = LeadKanban::find($newKanbanId);
                
                // Check if the new kanban has code 'won'
                if ($newKanban && $newKanban->code === 'won') {
                    // Only trigger if this is a status change (not creating a new lead as won)
                    if ($oldKanbanId && $oldKanbanId !== $newKanbanId) {
                        // Store a flag to dispatch the event after save
                        $lead->shouldDispatchWonEvent = true;
                        $lead->convertedByIdForEvent = auth()->id();
                        
                        Log::info("Lead status changing to 'won'", [
                            'lead_id' => $lead->id,
                            'old_kanban_id' => $oldKanbanId,
                            'new_kanban_id' => $newKanbanId,
                            'converted_by' => auth()->id(),
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Handle the Lead "updated" event.
     * 
     * This runs after the model is saved. We dispatch the LeadWon
     * event here to ensure the lead is in a consistent state.
     *
     * @param Lead $lead
     * @return void
     */
    public function updated(Lead $lead): void
    {
        // Check if we should dispatch the won event
        if (isset($lead->shouldDispatchWonEvent) && $lead->shouldDispatchWonEvent) {
            // Dispatch the LeadWon event
            LeadWon::dispatch(
                lead: $lead,
                convertedById: $lead->convertedByIdForEvent ?? null
            );

            Log::info("LeadWon event dispatched", [
                'lead_id' => $lead->id,
                'lead_title' => $lead->title,
                'team_id' => $lead->team_id,
            ]);

            // Clean up the temporary properties
            unset($lead->shouldDispatchWonEvent);
            unset($lead->convertedByIdForEvent);
        }
    }
}

