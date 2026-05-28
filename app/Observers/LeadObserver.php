<?php

namespace App\Observers;

use App\Events\LeadWon;
use App\Jobs\RefreshTeamAnalytics;
use App\Models\LeadHistory;
use App\Models\Lead;
use App\Models\LeadKanban;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

/**
 * Observer for Lead model
 * 
 * Monitors changes to leads and dispatches events when significant
 * state changes occur, such as when a lead is won.
 */
class LeadObserver
{
    protected function shouldRefreshAnalytics(Lead $lead): bool
    {
        if ($lead->wasRecentlyCreated) {
            return true;
        }

        return $lead->wasChanged([
            'kanban_id',
            'assigned_member_id',
            'source_id',
            'expected_value',
            'actual_value',
            'cost',
            'conversion_date',
            'is_archived',
        ]);
    }

    protected function dispatchAnalyticsRefreshForTeam(int $teamId): void
    {
        RefreshTeamAnalytics::dispatch($teamId)->delay(now()->addSeconds(3));
    }

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
                        $lead->convertedByIdForEvent = Auth::id();
                        
                        Log::info("Lead status changing to 'won'", [
                            'lead_id' => $lead->id,
                            'old_kanban_id' => $oldKanbanId,
                            'new_kanban_id' => $newKanbanId,
                            'converted_by' => Auth::id(),
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
                $lead,
                $lead->convertedByIdForEvent ?? null
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

        // Log kanban change history if applicable
        if ($lead->wasChanged('kanban_id')) {
            $oldKanbanId = $lead->getOriginal('kanban_id');
            $newKanbanId = $lead->kanban_id;

            $oldKanban = $oldKanbanId
                ? LeadKanban::find($oldKanbanId)
                : null;
            $newKanban = $newKanbanId
                ? LeadKanban::find($newKanbanId)
                : null;

            $oldStage = $oldKanban?->name ?? 'N/A';
            $newStage = $newKanban?->name ?? 'N/A';

            $note = sprintf(
                'Moved to %s from stage %s',
                $newKanban?->code ? strtoupper($newKanban->code) : $newStage,
                $oldStage
            );

            LeadHistory::create([
                'lead_id' => $lead->id,
                'kanban_id' => $newKanbanId,
                'note' => $note,
            ]);
        }

        if ($this->shouldRefreshAnalytics($lead)) {
            $originalTeamId = (int) ($lead->getOriginal('team_id') ?? $lead->team_id);
            $currentTeamId = (int) $lead->team_id;

            $this->dispatchAnalyticsRefreshForTeam($currentTeamId);
            if ($originalTeamId && $originalTeamId !== $currentTeamId) {
                $this->dispatchAnalyticsRefreshForTeam($originalTeamId);
            }
        }
    }

    /**
     * Handle the Lead "created" event.
     *
     * Log the initial stage of the lead for history tracking.
     *
     * @param Lead $lead
     * @return void
     */
    public function created(Lead $lead): void
    {
        $kanban = $lead->kanban_id ? LeadKanban::find($lead->kanban_id) : null;

        LeadHistory::create([
            'lead_id' => $lead->id,
            'kanban_id' => $lead->kanban_id,
            'note' => $kanban
                ? sprintf(
                    'Lead created in stage %s',
                    $kanban->name
                )
                : 'Lead created',
        ]);

        if ($this->shouldRefreshAnalytics($lead)) {
            $this->dispatchAnalyticsRefreshForTeam((int) $lead->team_id);
        }
    }
}

