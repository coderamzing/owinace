<?php

namespace App\Console\Commands;

use App\Models\Team;
use App\Models\Lead;
use App\Models\Proposal;
use App\Models\Portfolio;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WeeklySummary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'summary:weekly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send weekly summary to all team admins every Monday';

    /**
     * Execute the console command.
     */
    public function handle(NotificationService $notificationService): int
    {
        $this->info('Starting weekly summary generation...');

        // Get date range for the past week
        $endDate = Carbon::now();
        $startDate = Carbon::now()->subWeek();

        // Get all teams
        $teams = Team::all();

        $this->info("Found {$teams->count()} teams to process.");

        $progressBar = $this->output->createProgressBar($teams->count());
        $progressBar->start();

        foreach ($teams as $team) {
            try {
                // Get statistics for the team
                $stats = $this->getTeamStats($team->id, $startDate, $endDate);

                
                // Only send if there's any activity
                if ($this->hasActivity($stats)) {
                    $notificationService->notifyAdmin(
                        teamId: $team->id,
                        notificationType: 'team.weekly_summary',
                        data: [
                            'title' => 'Weekly Summary - ' . $team->name,
                            'subject' => 'Weekly Summary - ' . $team->name,
                            'content' => 'Your weekly activity summary is ready.',
                            'team_name' => $team->name,
                            'team_id' => $team->id,
                            'start_date' => $startDate->format('M d, Y'),
                            'end_date' => $endDate->format('M d, Y'),
                            'proposals_created' => $stats['proposals_created'],
                            'portfolios_added' => $stats['portfolios_added'],
                            'leads_open' => $stats['leads_open'],
                            'leads_new' => $stats['leads_new'],
                            'leads_won' => $stats['leads_won'],
                            'leads_lost' => $stats['leads_lost'],
                            'url' => url('/admin'),
                        ]
                    );
                }

                $progressBar->advance();
            } catch (\Exception $e) {
                $this->error("\nError processing team {$team->id}: " . $e->getMessage());
                $progressBar->advance();
            }
        }

        $progressBar->finish();
        $this->newLine();
        $this->info('Weekly summary generation completed!');

        return Command::SUCCESS;
    }

    /**
     * Get statistics for a team for the given date range
     */
    protected function getTeamStats(int $teamId, Carbon $startDate, Carbon $endDate): array
    {
        // Get kanban IDs for different statuses
        $openKanbanId = DB::table('lead_kanban')
            ->where('team_id', $teamId)
            ->where('code', 'OPEN')
            ->value('id');

        $wonKanbanId = DB::table('lead_kanban')
            ->where('team_id', $teamId)
            ->where('code', 'WON')
            ->value('id');

        $lostKanbanId = DB::table('lead_kanban')
            ->where('team_id', $teamId)
            ->where('code', 'LOST')
            ->value('id');

        return [
            'proposals_created' => Proposal::where('team_id', $teamId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count(),

            'portfolios_added' => Portfolio::where('team_id', $teamId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count(),

            'leads_open' => $openKanbanId ? Lead::forTeam($teamId)
                ->where('kanban_id', $openKanbanId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count() : 0,

            'leads_new' => Lead::forTeam($teamId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count(),

            'leads_won' => $wonKanbanId ? Lead::forTeam($teamId)
                ->where('kanban_id', $wonKanbanId)
                ->whereBetween('updated_at', [$startDate, $endDate])
                ->count() : 0,

            'leads_lost' => $lostKanbanId ? Lead::forTeam($teamId)
                ->where('kanban_id', $lostKanbanId)
                ->whereBetween('updated_at', [$startDate, $endDate])
                ->count() : 0,
        ];
    }

    /**
     * Check if there's any activity in the stats
     */
    protected function hasActivity(array $stats): bool
    {
        return array_sum($stats) > 0;
    }
}

