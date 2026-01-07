<?php

namespace App\Console\Commands;

use App\Models\Lead;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DailyFollowUpReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminder:daily-followup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily reminder to members for follow-ups scheduled in the next 24 hours';

    /**
     * Execute the console command.
     */
    public function handle(NotificationService $notificationService): int
    {
        $this->info('Starting daily follow-up reminder generation...');

        // Get date range for next 24 hours
        $now = Carbon::now('UTC');
        $next24Hours = Carbon::now('UTC')->addDay(2);

        $this->info("Checking for follow-ups between {$now->toDateTimeString()} and {$next24Hours->toDateTimeString()} UTC");

        // Get all leads with follow-ups in the next 24 hours that have assigned members
        // Use withoutTeam() to bypass TeamScope since we're in a console command (no session)
        $leads = Lead::withoutTeam()
            ->whereBetween('next_follow_up', [$now, $next24Hours])
            ->whereNotNull('assigned_member_id')
            ->where('is_archived', false)
            ->with(['assignedMember', 'kanban', 'source', 'contacts', 'team'])
            ->get();

        $this->info("Found {$leads->count()} leads with upcoming follow-ups.");

        if ($leads->isEmpty()) {
            $this->info('No follow-ups scheduled for the next 24 hours.');
            return Command::SUCCESS;
        }

        // Group leads by assigned member
        $leadsByMember = $leads->groupBy('assigned_member_id');

        $this->info("Processing reminders for {$leadsByMember->count()} members.");

        $progressBar = $this->output->createProgressBar($leadsByMember->count());
        $progressBar->start();

        foreach ($leadsByMember as $memberId => $memberLeads) {
            try {
                $member = $memberLeads->first()->assignedMember;

                if (!$member || !$member->email) {
                    $this->warn("\nSkipping member {$memberId} - no valid email found.");
                    $progressBar->advance();
                    continue;
                }

                // Prepare leads data for email
                $leadsData = $memberLeads->map(function ($lead) use ($now) {
                    $followUpTime = Carbon::parse($lead->next_follow_up);
                    $hoursUntil = $now->diffInHours($followUpTime, false);
                    
                    return [
                        'id' => $lead->id,
                        'title' => $lead->title,
                        'description' => $lead->description,
                        'expected_value' => $lead->expected_value,
                        'kanban_name' => $lead->kanban?->name ?? 'N/A',
                        'source_name' => $lead->source?->name ?? 'N/A',
                        'next_follow_up' => $followUpTime->format('M d, Y h:i A'),
                        'hours_until' => round($hoursUntil, 1),
                        'is_urgent' => $hoursUntil <= 2, // Mark as urgent if within 2 hours
                        'url' => url("/admin/leads/{$lead->id}"),
                        'team_name' => $lead->team?->name ?? 'N/A',
                        'contacts' => $lead->contacts->map(function ($contact) {
                            return [
                                'name' => $contact->name,
                                'email' => $contact->email,
                                'phone' => $contact->phone,
                            ];
                        })->toArray(),
                        'notes' => $lead->notes,
                    ];
                })->toArray();

                // Send notification to the member
                $notificationService->notifyMember(
                    memberId: $member->id,
                    notificationType: 'lead.followup_reminder',
                    data: [
                        'subject' => 'Daily Follow-up Reminder - ' . count($leadsData) . ' Lead' . (count($leadsData) > 1 ? 's' : ''),
                        'content' => 'You have ' . count($leadsData) . ' lead' . (count($leadsData) > 1 ? 's' : '') . ' requiring follow-up in the next 24 hours.',
                        'member_name' => $member->name,
                        'leads' => $leadsData,
                        'total_leads' => count($leadsData),
                        'date' => $now->format('M d, Y'),
                        'url' => url('/admin/leads'),
                    ]
                );

                $progressBar->advance();
            } catch (\Exception $e) {
                $this->error("\nError processing member {$memberId}: " . $e->getMessage());
                $progressBar->advance();
            }
        }

        $progressBar->finish();
        $this->newLine();
        $this->info('Daily follow-up reminder generation completed!');

        return Command::SUCCESS;
    }
}

