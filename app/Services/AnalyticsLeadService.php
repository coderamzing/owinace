<?php

namespace App\Services;

use App\Models\AnalyticsLead;
use App\Models\Lead;
use App\Models\LeadKanban;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsLeadService
{
    /**
     * Generate analytics lead data for a specific month and year
     *
     * @param int $teamId
     * @param int $month
     * @param int $year
     * @param int|null $userId Optional user ID filter
     * @return AnalyticsLead
     */
    public function generateLeadAnalytics(int $teamId, int $month, int $year, ?int $userId = null): AnalyticsLead
    {
        $query = Lead::where('team_id', $teamId)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month);

        if ($userId) {
            $query->where('assigned_member_id', $userId);
        }

        $leads = $query->get();

        // Get won and lost kanban statuses
        $wonKanban = LeadKanban::where('team_id', $teamId)
            ->where(function ($q) {
                $q->where('name', 'like', '%won%')
                  ->orWhere('name', 'like', '%Won%');
            })
            ->first();

        $lostKanban = LeadKanban::where('team_id', $teamId)
            ->where(function ($q) {
                $q->where('name', 'like', '%lost%')
                  ->orWhere('name', 'like', '%Lost%');
            })
            ->first();

        $wonKanbanId = $wonKanban?->id;
        $lostKanbanId = $lostKanban?->id;

        // Calculate metrics
        $totalLead = $leads->count();
        $totalWon = $leads->where('kanban_id', $wonKanbanId)->count();
        $totalLost = $leads->where('kanban_id', $lostKanbanId)->count();
        $totalValue = $leads->sum('actual_value') ?? 0;
        $totalCost = $leads->sum('cost') ?? 0;
        $totalExpectedValue = $leads->sum('expected_value') ?? 0;
        $totalRoi = $totalValue > 0 && $totalCost > 0 ? (($totalValue - $totalCost) / $totalCost) * 100 : 0;
        $avgCostPerLead = $totalLead > 0 ? ($totalCost / $totalLead) : 0;

        // Get user fullname if userId is provided
        $fullname = null;
        if ($userId) {
            $user = User::find($userId);
            $fullname = $user?->name;
        }

        // Create or update analytics lead record
        return AnalyticsLead::updateOrCreate(
            [
                'team_id' => $teamId,
                'month' => $month,
                'year' => $year,
                'user_id' => $userId,
            ],
            [
                'fullname' => $fullname,
                'total_lead' => $totalLead,
                'total_won' => $totalWon,
                'total_lost' => $totalLost,
                'total_value' => $totalValue,
                'total_cost' => $totalCost,
                'total_expected_value' => $totalExpectedValue,
                'total_roi' => $totalRoi,
                'avg_cost_per_lead' => $avgCostPerLead,
            ]
        );
    }

    /**
     * Generate analytics lead data for all users in a team for a specific month and year
     *
     * @param int $teamId
     * @param int $month
     * @param int $year
     * @return array Collection of AnalyticsLead models
     */
    public function generateLeadAnalyticsForAllUsers(int $teamId, int $month, int $year): array
    {
        $userIds = Lead::where('team_id', $teamId)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->distinct()
            ->pluck('assigned_member_id')
            ->filter()
            ->toArray();

        $results = [];

        // Generate analytics for each user
        foreach ($userIds as $userId) {
            $results[] = $this->generateLeadAnalytics($teamId, $month, $year, $userId);
        }

        // Also generate team-wide analytics (no specific user)
        $results[] = $this->generateLeadAnalytics($teamId, $month, $year, null);

        return $results;
    }

    /**
     * Generate analytics lead data for a date range
     *
     * @param int $teamId
     * @param int $startMonth
     * @param int $startYear
     * @param int $endMonth
     * @param int $endYear
     * @param int|null $userId
     * @return array Collection of AnalyticsLead models
     */
    public function generateLeadAnalyticsForRange(
        int $teamId,
        int $startMonth,
        int $startYear,
        int $endMonth,
        int $endYear,
        ?int $userId = null
    ): array {
        $results = [];
        $currentMonth = $startMonth;
        $currentYear = $startYear;

        while (($currentYear < $endYear) || ($currentYear == $endYear && $currentMonth <= $endMonth)) {
            $results[] = $this->generateLeadAnalytics($teamId, $currentMonth, $currentYear, $userId);

            $currentMonth++;
            if ($currentMonth > 12) {
                $currentMonth = 1;
                $currentYear++;
            }
        }

        return $results;
    }

    /**
     * Get analytics lead data for a specific period
     *
     * @param int $teamId
     * @param int $month
     * @param int $year
     * @param int|null $userId
     * @return AnalyticsLead|null
     */
    public function getLeadAnalytics(int $teamId, int $month, int $year, ?int $userId = null): ?AnalyticsLead
    {
        $query = AnalyticsLead::where('team_id', $teamId)
            ->where('month', $month)
            ->where('year', $year);

        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        return $query->first();
    }

    /**
     * Calculate ROI percentage
     *
     * @param float $totalValue
     * @param float $totalCost
     * @return float
     */
    protected function calculateRoi(float $totalValue, float $totalCost): float
    {
        if ($totalCost == 0) {
            return 0;
        }

        return (($totalValue - $totalCost) / $totalCost) * 100;
    }

    /**
     * Sync analytic lead for a specific team (matches Django sync_analytic_lead logic)
     *
     * @param int $teamId
     * @return bool
     */
    public function syncAnalyticLead(int $teamId): bool
    {
        $team = Team::findOrFail($teamId);
        
        // Calculate current month start and end
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();

        // Get team members
        $teamMembers = TeamMember::where('team_id', $teamId)->get();
        
        foreach ($teamMembers as $teamMember) {
            $member = $teamMember->user;
            
            if (!$member) {
                continue;
            }

            // Get OPEN kanban
            $openKanban = LeadKanban::where('team_id', $teamId)
                ->where('code', 'OPEN')
                ->first();

            $openKanbanId = $openKanban?->id;

            // Get member leads excluding OPEN stage
            $memberLeadsQuery = Lead::where('team_id', $teamId)
                ->where('assigned_member_id', $member->id)
                ->where('created_at', '>=', $monthStart)
                ->where('created_at', '<=', $monthEnd);

            if ($openKanbanId) {
                $memberLeadsQuery->where('kanban_id', '!=', $openKanbanId);
            }

            $memberLeads = $memberLeadsQuery->get();

            // Get WON and LOST kanban IDs
            $wonKanban = LeadKanban::where('team_id', $teamId)
                ->where('code', 'WON')
                ->first();
            $lostKanban = LeadKanban::where('team_id', $teamId)
                ->where('code', 'LOST')
                ->first();

            $wonKanbanId = $wonKanban?->id;
            $lostKanbanId = $lostKanban?->id;

            // Calculate metrics
            $totalLeads = $memberLeads->count();
            $totalWon = $memberLeads->where('kanban_id', $wonKanbanId)->count();
            $totalLost = $memberLeads->where('kanban_id', $lostKanbanId)->count();
            $totalValue = $memberLeads->sum('actual_value') ?? 0;

            AnalyticsLead::updateOrCreate(
                [
                    'month' => $monthStart->month,
                    'year' => $monthStart->year,
                    'team_id' => $teamId,
                    'user_id' => $member->id,
                ],
                [
                    'fullname' => $member->name,
                    'total_lead' => $totalLeads,
                    'total_won' => $totalWon,
                    'total_lost' => $totalLost,
                    'total_value' => $totalValue,
                ]
            );
        }

        return true;
    }
}

