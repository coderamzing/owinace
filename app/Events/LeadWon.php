<?php

namespace App\Events;

use App\Models\Lead;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event dispatched when a lead is marked as won
 * 
 * This event is triggered when a lead's kanban status changes to "won".
 * It carries the lead instance along with context about who triggered the change.
 */
class LeadWon
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param Lead $lead The lead that was won
     * @param int|null $convertedById The ID of the user who converted the lead
     */
    public function __construct(
        public Lead $lead,
        public ?int $convertedById = null
    ) {
        //
    }
}

