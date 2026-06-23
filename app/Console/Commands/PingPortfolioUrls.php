<?php

namespace App\Console\Commands;

use App\Models\Portfolio;
use App\Services\NotificationService;
use App\Services\PortfolioUrlPingService;
use Illuminate\Console\Command;

class PingPortfolioUrls extends Command
{
    protected $signature = 'portfolios:ping-urls
                            {--limit= : Maximum portfolios to check (default: '.PortfolioUrlPingService::DAILY_PING_LIMIT.')}';

    protected $description = 'Ping portfolio URLs that have not been checked in the last 3 days and deactivate unreachable ones';

    public function handle(PortfolioUrlPingService $pingService, NotificationService $notificationService): int
    {
        $limit = (int) ($this->option('limit') ?: PortfolioUrlPingService::DAILY_PING_LIMIT);
        $cutoff = now()->subDays(PortfolioUrlPingService::PING_INTERVAL_DAYS);

        $portfolios = Portfolio::withoutTeam()
            ->whereNotNull('url')
            ->where('url', '!=', '')
            ->where(function ($query) use ($cutoff): void {
                $query->whereNull('pinged_at')
                    ->orWhere('pinged_at', '<', $cutoff);
            })
            ->orderByRaw('CASE WHEN pinged_at IS NULL THEN 0 ELSE 1 END')
            ->orderBy('pinged_at')
            ->limit($limit)
            ->get();

        $this->info("Checking up to {$limit} portfolio URLs (not pinged since {$cutoff->toDateTimeString()}).");
        $this->info("Found {$portfolios->count()} portfolios to check.");

        if ($portfolios->isEmpty()) {
            return Command::SUCCESS;
        }

        $activeCount = 0;
        $inactiveCount = 0;
        $progressBar = $this->output->createProgressBar($portfolios->count());
        $progressBar->start();

        foreach ($portfolios as $portfolio) {
            $result = $pingService->ping((string) $portfolio->url);
            $isReachable = $result['success'];
            $wasActive = $portfolio->is_active;

            $portfolio->forceFill([
                'pinged_at' => now(),
                'is_active' => $isReachable ? $portfolio->is_active : false,
            ])->saveQuietly();

            if ($isReachable) {
                $activeCount++;
            } else {
                $inactiveCount++;
                $this->newLine();
                $this->warn("Portfolio #{$portfolio->id} ({$portfolio->title}): {$result['message']}");

                if ($wasActive && $portfolio->team_id) {
                    $notificationService->notifyAdmin(
                        teamId: $portfolio->team_id,
                        notificationType: 'portfolio.url_unreachable',
                        data: [
                            'title' => 'Portfolio URL unreachable',
                            'subject' => 'Portfolio URL unreachable',
                            'content' => sprintf(
                                'Portfolio "%s" failed the URL health check and was deactivated. %s',
                                $portfolio->title,
                                $result['message']
                            ),
                            'portfolio_id' => $portfolio->id,
                            'portfolio_title' => $portfolio->title,
                            'portfolio_url' => $portfolio->url,
                            'team_id' => $portfolio->team_id,
                            'url' => url('/admin/portfolios'),
                        ],
                        inAppOnly: true
                    );
                }
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();
        $this->info("Completed. Reachable: {$activeCount}, deactivated: {$inactiveCount}.");

        return Command::SUCCESS;
    }
}
