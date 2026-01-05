<?php

namespace App\Services;

use App\Models\AnalyticsGoal;
use App\Models\Lead;
use App\Models\LeadGoal;
use App\Models\LeadKanban;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsGoalService
{
    /**
     * Generate analytics goal data for a specific month and year
     *
     * @param int $teamId
     * @param int $month
     * @param int $year
     * @param int|null $userId Optional user ID filter
     * @return AnalyticsGoal
     */
    public function generateGoalAnalytics(int $teamId, int $month, int $year, ?int $userId = null): AnalyticsGoal
    {
        $query = LeadGoal::where('team_id', $teamId)
            ->where('is_active', true);

        if ($userId) {
            $query->where('member_id', $userId);
        }

        $goals = $query->get();

        $totalTarget = 0;
        $totalAcheived = 0;
        $fullname = null;

        foreach ($goals as $goal) {
            // Calculate progress based on period
            $progress = $this->calculateGoalProgress($goal, $month, $year);
            
            $totalTarget += $goal->target_value ?? 0;
            $totalAcheived += $progress;

            if ($userId && !$fullname) {
                $user = User::find($userId);
                $fullname = $user?->name;
            }
        }

        // Determine goal type (could be aggregated from multiple goal types)
        $goalType = $goals->isNotEmpty() ? $goals->first()->goal_type : 'general';

        // Create or update analytics goal record
        return AnalyticsGoal::updateOrCreate(
            [
                'team_id' => $teamId,
                'month' => $month,
                'year' => $year,
                'goal_type' => $goalType,
                'user_id' => $userId,
            ],
            [
                'fullname' => $fullname,
                'target_value' => $totalTarget,
                'progress_value' => $totalAcheived,
                'acheived' => $totalAcheived,
            ]
        );
    }

    /**
     * Calculate goal progress for a specific month and year
     *
     * @param LeadGoal $goal
     * @param int $month
     * @param int $year
     * @return float
     */
    protected function calculateGoalProgress(LeadGoal $goal, int $month, int $year): float
    {
        // Extract period information from goal
        $period = $goal->period ?? 'monthly';
        
        // If goal is for this specific period, return current_value
        // Otherwise, calculate proportion based on period type
        if ($period === 'monthly') {
            // For monthly goals, return the current value if it matches the period
            return $goal->current_value ?? 0;
        } elseif ($period === 'yearly') {
            // For yearly goals, divide by 12 to get monthly portion
            return ($goal->current_value ?? 0) / 12;
        } elseif ($period === 'quarterly') {
            // For quarterly goals, divide by 3 to get monthly portion
            return ($goal->current_value ?? 0) / 3;
        }

        return $goal->current_value ?? 0;
    }

    /**
     * Generate analytics goal data for all users in a team for a specific month and year
     *
     * @param int $teamId
     * @param int $month
     * @param int $year
     * @return array Collection of AnalyticsGoal models
     */
    public function generateGoalAnalyticsForAllUsers(int $teamId, int $month, int $year): array
    {
        $userIds = LeadGoal::where('team_id', $teamId)
            ->where('is_active', true)
            ->distinct()
            ->pluck('member_id')
            ->filter()
            ->toArray();

        $results = [];

        // Generate analytics for each user
        foreach ($userIds as $userId) {
            $results[] = $this->generateGoalAnalytics($teamId, $month, $year, $userId);
        }

        // Also generate team-wide analytics (no specific user)
        $results[] = $this->generateGoalAnalytics($teamId, $month, $year, null);

        return $results;
    }

    /**
     * Generate analytics goal data for a date range
     *
     * @param int $teamId
     * @param int $startMonth
     * @param int $startYear
     * @param int $endMonth
     * @param int $endYear
     * @param int|null $userId
     * @return array Collection of AnalyticsGoal models
     */
    public function generateGoalAnalyticsForRange(
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
            $results[] = $this->generateGoalAnalytics($teamId, $currentMonth, $currentYear, $userId);

            $currentMonth++;
            if ($currentMonth > 12) {
                $currentMonth = 1;
                $currentYear++;
            }
        }

        return $results;
    }

    /**
     * Get analytics goal data for a specific period
     *
     * @param int $teamId
     * @param int $month
     * @param int $year
     * @param string|null $goalType
     * @param int|null $userId
     * @return AnalyticsGoal|null
     */
    public function getGoalAnalytics(
        int $teamId,
        int $month,
        int $year,
        ?string $goalType = null,
        ?int $userId = null
    ): ?AnalyticsGoal {
        $query = AnalyticsGoal::where('team_id', $teamId)
            ->where('month', $month)
            ->where('year', $year);

        if ($goalType) {
            $query->where('goal_type', $goalType);
        }

        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        return $query->first();
    }

    /**
     * Sync analytic goal for a specific team (matches Django sync_analytic_goal logic)
     *
     * @param int $teamId
     * @return bool
     */
    public function syncAnalyticGoal(int $teamId): bool
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

            // Get goals for this member
            $goals = LeadGoal::where('team_id', $teamId)
                ->where('member_id', $member->id)
                ->where('period', 'monthly')
                ->where('is_active', true)
                ->get();

            foreach ($goals as $goal) {
                $achieved = 0;

                if ($goal->goal_type == 'lead_generation') {
                    // Get OPEN kanban
                    $openKanban = LeadKanban::where('team_id', $teamId)
                        ->where('code', 'OPEN')
                        ->first();

                    $openKanbanId = $openKanban?->id;

                    $query = Lead::where('team_id', $teamId)
                        ->where('assigned_member_id', $member->id)
                        ->where('created_at', '>=', $monthStart)
                        ->where('created_at', '<=', $monthEnd);

                    if ($openKanbanId) {
                        $query->where('kanban_id', '!=', $openKanbanId);
                    }

                    $totalLeads = $query->count();
                    $achieved = $totalLeads;
                } elseif ($goal->goal_type == 'conversion') {
                    // Get WON kanban
                    $wonKanban = LeadKanban::where('team_id', $teamId)
                        ->where('code', 'WON')
                        ->first();

                    $wonKanbanId = $wonKanban?->id;

                    $totalWon = Lead::where('team_id', $teamId)
                        ->where('assigned_member_id', $member->id)
                        ->where('created_at', '>=', $monthStart)
                        ->where('created_at', '<=', $monthEnd)
                        ->where('kanban_id', $wonKanbanId)
                        ->sum('actual_value') ?? 0;

                    $achieved = $totalWon;
                } elseif ($goal->goal_type == 'open_leads') {
                    $totalOpen = Lead::where('team_id', $teamId)
                        ->where('assigned_member_id', $member->id)
                        ->where('created_at', '>=', $monthStart)
                        ->where('created_at', '<=', $monthEnd)
                        ->count();

                    $achieved = $totalOpen;
                }

                // Calculate percentage achieved
                $achievedPercentage = $goal->target_value > 0 
                    ? ($achieved / $goal->target_value) * 100 
                    : 0;

                AnalyticsGoal::updateOrCreate(
                    [
                        'user_id' => $member->id,
                        'team_id' => $teamId,
                        'month' => $monthStart->month,
                        'year' => $monthStart->year,
                        'goal_type' => $goal->goal_type,
                    ],
                    [
                        'fullname' => $member->name,
                        'acheived' => $achievedPercentage,
                        'target_value' => $goal->target_value,
                        'progress_value' => $achieved,
                    ]
                );
            }
        }

        return true;
    }
}

