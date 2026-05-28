<?php

namespace App\Jobs;

use App\Services\AnalyticsRefreshManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RefreshTeamAnalytics implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Avoid queue spam when multiple lead updates happen together.
     */
    public int $uniqueFor = 30;

    public function __construct(
        public int $teamId,
    ) {
    }

    public function uniqueId(): string
    {
        return (string) $this->teamId;
    }

    public function handle(AnalyticsRefreshManager $manager): void
    {
        $manager->refreshTeam($this->teamId);
    }
}

