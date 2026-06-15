<?php

namespace App\Jobs;

use App\Models\Portfolio;
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

    public function handle(): void
    {
        try {
            Portfolio::withoutGlobalScope(\App\Models\Scopes\TeamScope::class)
                ->create($this->portfolioData);
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
