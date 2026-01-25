<?php

namespace App\Console\Commands;

use App\Models\Team;
use App\Services\AIInsightService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class WeeklyAiInsights extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ai:weekly-insights';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate weekly AI insights for all teams based on the previous week lead activity';

    /**
     * Execute the console command.
     */
    public function handle(AIInsightService $aiInsightService): int
    {
        $this->info('Starting weekly AI insights generation...');

        // Use previous full week (Monday to Sunday)
        $weekStart = Carbon::now()->subWeek()->startOfWeek(Carbon::MONDAY);

        $teams = Team::all();

        $this->info("Found {$teams->count()} teams to process.");
        $progressBar = $this->output->createProgressBar($teams->count());
        $progressBar->start();

        foreach ($teams as $team) {
            try {
                $aiInsightService->generateWeeklyInsightForTeam($team, $weekStart);
            } catch (\Throwable $e) {
                $this->error("\nError generating AI insights for team {$team->id}: " . $e->getMessage());
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();
        $this->info('Weekly AI insights generation completed!');

        return Command::SUCCESS;
    }
}

