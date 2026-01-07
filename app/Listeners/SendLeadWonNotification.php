<?php

namespace App\Listeners;

use App\Events\LeadWon;
use App\Jobs\NotificationQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Listener for LeadWon event
 * 
 * This listener is triggered when a lead is won and queues notifications
 * to all team members who have permission to view leads.
 */
class SendLeadWonNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param LeadWon $event
     * @return void
     */
    public function handle(LeadWon $event): void
    {
        $lead = $event->lead;
        
        // Load necessary relationships
        $lead->load(['team', 'source', 'assignedMember', 'conversionBy']);

        // Prepare notification data
        $leadTitle = $lead->title ?? 'Untitled Lead';
        $leadUrl = route('filament.admin.resources.leads.view', ['record' => $lead->id]);
        $teamName = $lead->team?->name ?? 'Unknown Team';
        $assignedMemberName = $lead->assignedMember?->name ?? 'Unassigned';
        $convertedByName = $lead->conversionBy?->name ?? $lead->assignedMember?->name ?? 'Team Member';
        $actualValue = $lead->actual_value ? number_format($lead->actual_value, 2) : 'N/A';

        $notificationData = [
            'subject' => '🎉 Lead Won: ' . $leadTitle,
            'title' => '🎉 Lead Won: ' . $leadTitle,
            'content' => "Great news! The lead '{$leadTitle}' has been won by {$convertedByName} with a value of \${$actualValue}.",
            'url' => $leadUrl,
            'lead_title' => $leadTitle,
            'lead_url' => $leadUrl,
            'team_name' => $teamName,
            'assigned_member' => $assignedMemberName,
            'converted_by' => $convertedByName,
            'actual_value' => $actualValue,
            'conversion_date' => $lead->conversion_date?->format('F j, Y') ?? now()->format('F j, Y'),
            'lead_description' => $lead->description ?? 'No description provided.',
            'team_id' => $lead->team_id,
        ];

        // Queue notification to all team members
        // The NotificationQueue will handle filtering by permissions and preferences
        NotificationQueue::dispatch(
            type: 'members',
            identifier: $lead->team->workspace_id,
            notificationType: 'lead.won',
            data: $notificationData
        );

        Log::info("LeadWon event processed for lead #{$lead->id}", [
            'lead_id' => $lead->id,
            'lead_title' => $leadTitle,
            'team_id' => $lead->team_id,
            'workspace_id' => $lead->team->workspace_id,
        ]);
    }

    /**
     * Handle a job failure.
     *
     * @param LeadWon $event
     * @param \Throwable $exception
     * @return void
     */
    public function failed(LeadWon $event, \Throwable $exception): void
    {
        Log::error("Failed to process LeadWon notification", [
            'lead_id' => $event->lead->id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}

