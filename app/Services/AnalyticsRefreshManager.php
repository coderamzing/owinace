<?php

namespace App\Services;

use App\Services\Contracts\AnalyticsRefreshServiceInterface;

class AnalyticsRefreshManager
{
    /**
     * @param iterable<AnalyticsRefreshServiceInterface> $refreshers
     */
    public function __construct(
        protected iterable $refreshers,
    ) {
    }

    public function refreshTeam(int $teamId): void
    {
        foreach ($this->refreshers as $refresher) {
            $refresher->refreshForTeam($teamId);
        }
    }
}

