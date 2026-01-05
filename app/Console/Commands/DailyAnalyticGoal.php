<?php

namespace App\Console\Commands;

use App\Models\Team;
use App\Services\AnalyticsGoalService;
use Illuminate\Console\Command;

class DailyAnalyticGoal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'analytics:daily-goal';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate daily analytics goal data for all active teams';

    /**
     * Execute the console command.
     */
    public function handle(AnalyticsGoalService $service): int
    {
        $this->info('Starting daily analytics goal generation...');

        // Get all teams (Django had is_active filter, but Team model doesn't have it)
        $teams = Team::all();

        $this->info("Found {$teams->count()} teams to process.");

        $progressBar = $this->output->createProgressBar($teams->count());
        $progressBar->start();

        foreach ($teams as $team) {
            try {
                $service->syncAnalyticGoal($team->id);
                $progressBar->advance();
            } catch (\Exception $e) {
                $this->error("\nError processing team {$team->id}: " . $e->getMessage());
                $progressBar->advance();
            }
        }

        $progressBar->finish();
        $this->newLine();
        $this->info('Daily analytics goal generation completed!');

        return Command::SUCCESS;
    }
}
