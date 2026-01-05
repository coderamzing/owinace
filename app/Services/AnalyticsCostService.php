<?php

namespace App\Services;

use App\Models\AnalyticsCost;
use App\Models\Lead;
use App\Models\LeadCost;
use App\Models\LeadKanban;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsCostService
{
    /**
     * Generate analytics cost data for a specific month and year
     *
     * @param int $teamId
     * @param int $month
     * @param int $year
     * @param string|null $type Optional type filter (e.g., 'source', 'member')
     * @return AnalyticsCost
     */
    public function generateCostAnalytics(int $teamId, int $month, int $year, ?string $type = null): AnalyticsCost
    {
        // Calculate total cost from LeadCost records for the month
        $totalCost = LeadCost::where('team_id', $teamId)
            ->where('is_active', true)
            ->sum('monthly_cost');

        // Calculate total leads for the month
        $totalLeads = Lead::where('team_id', $teamId)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count();

        // Calculate average cost per lead
        $avgCostPerLead = $totalLeads > 0 ? ($totalCost / $totalLeads) : 0;

        // Determine type if not provided
        if (!$type) {
            $type = 'general';
        }

        // Create or update analytics cost record
        return AnalyticsCost::updateOrCreate(
            [
                'team_id' => $teamId,
                'month' => $month,
                'year' => $year,
                'type' => $type,
            ],
            [
                'total_cost' => $totalCost,
                'avg_cost_per_lead' => $avgCostPerLead,
            ]
        );
    }

    /**
     * Generate analytics cost data by source for a specific month and year
     *
     * @param int $teamId
     * @param int $month
     * @param int $year
     * @param int $sourceId
     * @return AnalyticsCost
     */
    public function generateCostAnalyticsBySource(int $teamId, int $month, int $year, int $sourceId): AnalyticsCost
    {
        // Calculate total cost from LeadCost records for the source
        $totalCost = LeadCost::where('team_id', $teamId)
            ->where('source_id', $sourceId)
            ->where('is_active', true)
            ->sum('monthly_cost');

        // Calculate total leads for the source in the month
        $totalLeads = Lead::where('team_id', $teamId)
            ->where('source_id', $sourceId)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count();

        // Calculate average cost per lead
        $avgCostPerLead = $totalLeads > 0 ? ($totalCost / $totalLeads) : 0;

        // Create or update analytics cost record
        return AnalyticsCost::updateOrCreate(
            [
                'team_id' => $teamId,
                'month' => $month,
                'year' => $year,
                'type' => 'source',
            ],
            [
                'total_cost' => $totalCost,
                'avg_cost_per_lead' => $avgCostPerLead,
            ]
        );
    }

    /**
     * Generate analytics cost data by member for a specific month and year
     *
     * @param int $teamId
     * @param int $month
     * @param int $year
     * @param int $memberId
     * @return AnalyticsCost
     */
    public function generateCostAnalyticsByMember(int $teamId, int $month, int $year, int $memberId): AnalyticsCost
    {
        // Calculate total cost from LeadCost records for the member
        $totalCost = LeadCost::where('team_id', $teamId)
            ->where('member_id', $memberId)
            ->where('is_active', true)
            ->sum('monthly_cost');

        // Calculate total leads assigned to the member in the month
        $totalLeads = Lead::where('team_id', $teamId)
            ->where('assigned_member_id', $memberId)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count();

        // Calculate average cost per lead
        $avgCostPerLead = $totalLeads > 0 ? ($totalCost / $totalLeads) : 0;

        // Create or update analytics cost record
        return AnalyticsCost::updateOrCreate(
            [
                'team_id' => $teamId,
                'month' => $month,
                'year' => $year,
                'type' => 'member',
            ],
            [
                'total_cost' => $totalCost,
                'avg_cost_per_lead' => $avgCostPerLead,
            ]
        );
    }

    /**
     * Generate analytics cost data for a date range
     *
     * @param int $teamId
     * @param int $startMonth
     * @param int $startYear
     * @param int $endMonth
     * @param int $endYear
     * @return array Collection of AnalyticsCost models
     */
    public function generateCostAnalyticsForRange(
        int $teamId,
        int $startMonth,
        int $startYear,
        int $endMonth,
        int $endYear
    ): array {
        $results = [];
        $currentMonth = $startMonth;
        $currentYear = $startYear;

        while (($currentYear < $endYear) || ($currentYear == $endYear && $currentMonth <= $endMonth)) {
            $results[] = $this->generateCostAnalytics($teamId, $currentMonth, $currentYear);

            $currentMonth++;
            if ($currentMonth > 12) {
                $currentMonth = 1;
                $currentYear++;
            }
        }

        return $results;
    }

    /**
     * Get analytics cost data for a specific period
     *
     * @param int $teamId
     * @param int $month
     * @param int $year
     * @param string|null $type
     * @return AnalyticsCost|null
     */
    public function getCostAnalytics(int $teamId, int $month, int $year, ?string $type = null): ?AnalyticsCost
    {
        $query = AnalyticsCost::where('team_id', $teamId)
            ->where('month', $month)
            ->where('year', $year);

        if ($type) {
            $query->where('type', $type);
        }

        return $query->first();
    }

    /**
     * Sync analytic cost for a specific team (matches Django sync_analytic_cost logic)
     *
     * @param int $teamId
     * @return bool
     */
    public function syncAnalyticCost(int $teamId): bool
    {
        $team = Team::findOrFail($teamId);
        
        // Calculate current month start and end
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();

        // Get OPEN kanban code
        $openKanban = LeadKanban::where('team_id', $teamId)
            ->where('code', 'OPEN')
            ->first();

        $openKanbanId = $openKanban?->id;

        // Get total leads excluding OPEN stage
        $leadTotal = Lead::where('team_id', $teamId)
            ->where('created_at', '>=', $monthStart)
            ->where('created_at', '<=', $monthEnd);

        if ($openKanbanId) {
            $leadTotal->where('kanban_id', '!=', $openKanbanId);
        }

        $leadTotal = $leadTotal->count();

        if ($leadTotal == 0) {
            return true;
        }

        // Get total lead cost (from leads)
        $totalLeadCost = Lead::where('team_id', $teamId)
            ->where('created_at', '>=', $monthStart)
            ->where('created_at', '<=', $monthEnd)
            ->sum('cost') ?? 0;

        // Get total tool cost (LeadCost where member_id is null)
        $totalToolCost = LeadCost::where('team_id', $teamId)
            ->whereNull('member_id')
            ->where('is_active', true)
            ->sum('monthly_cost') ?? 0;

        // Get total member cost (LeadCost where member_id is not null)
        $totalMemberCost = LeadCost::where('team_id', $teamId)
            ->whereNotNull('member_id')
            ->where('is_active', true)
            ->sum('monthly_cost') ?? 0;

        // Calculate average cost per lead for tool type
        $avgCostPerLeadTool = ($totalToolCost + $totalLeadCost) / $leadTotal;

        // Update or create tool analytics cost
        AnalyticsCost::updateOrCreate(
            [
                'team_id' => $teamId,
                'month' => $monthStart->month,
                'year' => $monthStart->year,
                'type' => 'tool',
            ],
            [
                'avg_cost_per_lead' => $avgCostPerLeadTool,
                'total_cost' => $totalToolCost,
            ]
        );

        // Calculate average cost per lead for member type
        $avgCostPerLeadMember = $totalMemberCost / $leadTotal;

        // Update or create member analytics cost
        AnalyticsCost::updateOrCreate(
            [
                'team_id' => $teamId,
                'month' => $monthStart->month,
                'year' => $monthStart->year,
                'type' => 'member',
            ],
            [
                'avg_cost_per_lead' => $avgCostPerLeadMember,
                'total_cost' => $totalMemberCost,
            ]
        );

        return true;
    }
}

