<?php

namespace App\Console\Commands;

use App\Models\Team;
use App\Services\AIInsightService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class MonthlyAiInsights extends Command
{
    /**
     * @var string
     */
    protected $signature = 'ai:monthly-insights {--year=} {--month=} {--team_id=}';

    /**
     * @var string
     */
    protected $description = 'Generate monthly AI insights for teams (team + member goals/leads/cost), for admin review';

    public function handle(AIInsightService $aiInsightService): int
    {
        $year = (int) ($this->option('year') ?: Carbon::now()->year);
        $month = (int) ($this->option('month') ?: Carbon::now()->month);
        $teamId = $this->option('team_id') ? (int) $this->option('team_id') : null;

        $monthStart = Carbon::create($year, $month, 1)->startOfMonth();

        $this->info("Starting monthly AI insights generation for {$monthStart->format('M Y')}...");

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
        $this->info('Monthly AI insights generation completed!');

        return Command::SUCCESS;
    }
}

