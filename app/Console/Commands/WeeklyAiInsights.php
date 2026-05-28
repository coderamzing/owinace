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
    protected $signature = 'ai:weekly-insights {--year=} {--month=} {--team_id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deprecated: kept for compatibility. Generates monthly AI insights (team + member) for the selected month.';

    /**
     * Execute the console command.
     */
    public function handle(AIInsightService $aiInsightService): int
    {
        $year = (int) ($this->option('year') ?: Carbon::now()->year);
        $month = (int) ($this->option('month') ?: Carbon::now()->month);
        $teamId = $this->option('team_id') ? (int) $this->option('team_id') : null;

        $monthStart = Carbon::create($year, $month, 1)->startOfMonth();

        $this->info("Starting AI insights generation for {$monthStart->format('M Y')}...");

        $teamsQuery = Team::query();
        if ($teamId) {
            $teamsQuery->where('id', $teamId);
        }

        $teams = $teamsQuery->get();

        $this->info("Found {$teams->count()} teams to process.");
        $progressBar = $this->output->createProgressBar($teams->count());
        $progressBar->start();

        foreach ($teams as $team) {
            try {
                $aiInsightService->generateMonthlyInsightForTeam($team, $monthStart);
            } catch (\Throwable $e) {
                $this->error("\nError generating AI insights for team {$team->id}: " . $e->getMessage());
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();
        $this->info('AI insights generation completed!');

        return Command::SUCCESS;
    }
}

