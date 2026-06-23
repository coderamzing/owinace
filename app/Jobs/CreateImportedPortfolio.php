<?php

namespace App\Jobs;

use App\Models\Portfolio;
use App\Services\PortfolioUrlPingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CreateImportedPortfolio implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(
        public array $portfolioData,
        public int $rowNumber,
    ) {}

    public function handle(PortfolioUrlPingService $pingService): void
    {
        try {
            $url = trim((string) ($this->portfolioData['url'] ?? ''));

            if ($url === '') {
                throw new \RuntimeException('URL is required.');
            }

            $pingService->assertReachable($url);

            Portfolio::withoutGlobalScope(\App\Models\Scopes\TeamScope::class)
                ->create(array_merge($this->portfolioData, [
                    'pinged_at' => now(),
                ]));
        } catch (\Throwable $exception) {
            Log::error('Portfolio import failed', [
                'row' => $this->rowNumber,
                'title' => $this->portfolioData['title'] ?? null,
                'message' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}
