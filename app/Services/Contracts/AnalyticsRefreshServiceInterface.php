<?php

namespace App\Services\Contracts;

interface AnalyticsRefreshServiceInterface
{
    /**
     * Refresh analytics for a team for the current reporting period(s).
     */
    public function refreshForTeam(int $teamId): void;
}

