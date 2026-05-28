<?php

namespace App\Services;

use App\Models\AiInsight;
use App\Models\AnalyticsGoal;
use App\Models\AnalyticsLead;
use App\Models\Lead;
use App\Models\LeadKanban;
use App\Models\LeadSource;
use App\Models\LeadCost;
use App\Models\Team;
use App\Models\TeamMember;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;

class AIInsightService
{
    /**
     * Generate and store AI insights for a specific team and month.
     *
     * @param Team $team
     * @param Carbon $monthStart Any date within the target month
     * @return AiInsight|null
     */
    public function generateMonthlyInsightForTeam(Team $team, Carbon $monthStart): ?AiInsight
    {
        $monthStart = $monthStart->copy()->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();

        $year = (int) $monthStart->year;
        $month = (int) $monthStart->month;

        $previousMonthStart = $monthStart->copy()->subMonthNoOverflow()->startOfMonth();
        $previousMonthEnd = $previousMonthStart->copy()->endOfMonth();

        $periodLabel = $monthStart->format('M Y');

        $teamMetrics = $this->computeTeamLeadMetrics($team->id, $monthStart, $monthEnd);
        $previousTeamMetrics = $this->computeTeamLeadMetrics($team->id, $previousMonthStart, $previousMonthEnd);

        $teamCosts = $this->computeTeamCosts($team->id, $monthStart);
        $previousTeamCosts = $this->computeTeamCosts($team->id, $previousMonthStart);

        $members = TeamMember::withoutTeam()
            ->where('team_id', $team->id)
            ->with('user')
            ->get()
            ->map(function (TeamMember $memberRow) use ($team, $monthStart, $monthEnd, $previousMonthStart, $previousMonthEnd) {
                $member = $memberRow->user;

                if (! $member) {
                    return null;
                }

                $leadMetrics = $this->computeMemberLeadMetrics($team->id, $member->id, $monthStart, $monthEnd);
                $previousLeadMetrics = $this->computeMemberLeadMetrics($team->id, $member->id, $previousMonthStart, $previousMonthEnd);

                $memberGoals = AnalyticsGoal::withoutTeam()
                    ->where('team_id', $team->id)
                    ->where('year', $monthStart->year)
                    ->where('month', $monthStart->month)
                    ->where('user_id', $member->id)
                    ->get()
                    ->map(fn (AnalyticsGoal $goal) => [
                        'goal_type' => (string) $goal->goal_type,
                        'target_value' => (float) ($goal->target_value ?? 0),
                        'progress_value' => (float) ($goal->progress_value ?? 0),
                        'achieved_percentage' => (float) ($goal->acheived ?? 0),
                    ])
                    ->values()
                    ->all();

                $memberCost = $this->computeMemberCosts($team->id, $member->id, $monthStart);

                return [
                    'user_id' => $member->id,
                    'name' => $member->name,
                    'leads' => $leadMetrics,
                    'leads_previous_month' => $previousLeadMetrics,
                    'goals' => $memberGoals,
                    'costs' => $memberCost,
                ];
            })
            ->filter()
            ->values()
            ->all();

        $payload = [
            'team_name' => $team->name,
            'period' => $periodLabel,
            'year' => $year,
            'month' => $month,
            'team' => [
                'leads' => $teamMetrics,
                'leads_previous_month' => $previousTeamMetrics,
                'costs' => $teamCosts,
                'costs_previous_month' => $previousTeamCosts,
            ],
            'members' => $members,
        ];

        try {
            $response = OpenAI::chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a B2B sales and revenue operations analyst. '
                            . 'Given structured monthly team+member lead/goals/cost data (with previous-month comparisons), '
                            . 'write an admin-facing monthly summary: what improved, what regressed, who is trending up/down, '
                            . 'and specific actions to improve lead quality, conversion, and cost efficiency. '
                            . 'Reply ONLY in JSON with keys: summary (string), highlights (string[]), recommendations (string[]). '
                            . 'Recommendations should be actionable and may include member-specific items prefixed with the member name.',
                    ],
                    [
                        'role' => 'user',
                        'content' => 'Here is the monthly team performance data (JSON): ' . json_encode($payload),
                    ],
                ],
                'temperature' => 0.4,
                'response_format' => [
                    'type' => 'json_object',
                ],
            ]);

            $content = $response['choices'][0]['message']['content'] ?? null;

            if (! $content) {
                throw new \RuntimeException('Empty response from OpenAI for AI insights');
            }

            $decoded = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException('Invalid JSON returned by OpenAI for AI insights');
            }

            $summary = isset($decoded['summary']) ? (string) $decoded['summary'] : '';
            $highlights = isset($decoded['highlights']) && is_array($decoded['highlights'])
                ? $decoded['highlights']
                : [];
            $recommendations = isset($decoded['recommendations']) && is_array($decoded['recommendations'])
                ? $decoded['recommendations']
                : [];
        } catch (\Throwable $e) {
            Log::error('AIInsightService error: ' . $e->getMessage(), [
                'team_id' => $team->id,
                'year' => $year,
                'month' => $month,
            ]);

            // Fallback: store metrics without AI text
            $summary = 'AI insights could not be generated for this month due to an error.';
            $highlights = [];
            $recommendations = [];
        }

        return AiInsight::updateOrCreate(
            [
                'team_id' => $team->id,
                'year' => $year,
                'month' => $month,
            ],
            [
                'year' => $year,
                'month' => $month,
                'summary' => $summary,
                'highlights' => $highlights,
                'recommendations' => $recommendations,
                'raw_payload' => $payload,
            ]
        );
    }

    protected function computeTeamLeadMetrics(int $teamId, Carbon $start, Carbon $end): array
    {
        $leads = Lead::withoutTeam()
            ->where('team_id', $teamId)
            ->whereBetween('created_at', [$start, $end])
            ->get();

        $wonKanbanId = LeadKanban::withoutTeam()->where('team_id', $teamId)->where('code', 'WON')->value('id');
        $lostKanbanId = LeadKanban::withoutTeam()->where('team_id', $teamId)->where('code', 'LOST')->value('id');
        $openKanbanId = LeadKanban::withoutTeam()->where('team_id', $teamId)->where('code', 'OPEN')->value('id');

        $totalLeads = $leads->count();
        $wonCount = $wonKanbanId ? $leads->where('kanban_id', $wonKanbanId)->count() : 0;
        $lostCount = $lostKanbanId ? $leads->where('kanban_id', $lostKanbanId)->count() : 0;
        $openCount = $openKanbanId ? $leads->where('kanban_id', $openKanbanId)->count() : 0;

        $totalValue = (float) ($leads->sum('actual_value') ?? 0);
        $totalExpected = (float) ($leads->sum('expected_value') ?? 0);
        $totalLeadCost = (float) ($leads->sum('cost') ?? 0);

        $conversionRate = $totalLeads > 0 ? ($wonCount / $totalLeads) * 100 : 0;

        return [
            'total_leads' => $totalLeads,
            'won_leads' => $wonCount,
            'lost_leads' => $lostCount,
            'open_leads' => $openCount,
            'total_value' => $totalValue,
            'total_expected_value' => $totalExpected,
            'lead_cost' => $totalLeadCost,
            'conversion_rate' => $conversionRate,
        ];
    }

    protected function computeMemberLeadMetrics(int $teamId, int $userId, Carbon $start, Carbon $end): array
    {
        $leads = Lead::withoutTeam()
            ->where('team_id', $teamId)
            ->where('assigned_member_id', $userId)
            ->whereBetween('created_at', [$start, $end])
            ->get();

        $wonKanbanId = LeadKanban::withoutTeam()->where('team_id', $teamId)->where('code', 'WON')->value('id');
        $lostKanbanId = LeadKanban::withoutTeam()->where('team_id', $teamId)->where('code', 'LOST')->value('id');
        $openKanbanId = LeadKanban::withoutTeam()->where('team_id', $teamId)->where('code', 'OPEN')->value('id');

        $totalLeads = $leads->count();
        $wonCount = $wonKanbanId ? $leads->where('kanban_id', $wonKanbanId)->count() : 0;
        $lostCount = $lostKanbanId ? $leads->where('kanban_id', $lostKanbanId)->count() : 0;
        $openCount = $openKanbanId ? $leads->where('kanban_id', $openKanbanId)->count() : 0;

        $totalValue = (float) ($leads->sum('actual_value') ?? 0);
        $totalExpected = (float) ($leads->sum('expected_value') ?? 0);
        $totalLeadCost = (float) ($leads->sum('cost') ?? 0);

        $conversionRate = $totalLeads > 0 ? ($wonCount / $totalLeads) * 100 : 0;

        return [
            'total_leads' => $totalLeads,
            'won_leads' => $wonCount,
            'lost_leads' => $lostCount,
            'open_leads' => $openCount,
            'total_value' => $totalValue,
            'total_expected_value' => $totalExpected,
            'lead_cost' => $totalLeadCost,
            'conversion_rate' => $conversionRate,
        ];
    }

    protected function computeTeamCosts(int $teamId, Carbon $monthStart): array
    {
        $monthStart = $monthStart->copy()->startOfMonth();
        $daysInMonth = $monthStart->daysInMonth;
        $daysElapsed = Carbon::now()->isSameMonth($monthStart) ? Carbon::now()->day : $daysInMonth;

        $toolMonthly = (float) (LeadCost::withoutTeam()
            ->where('team_id', $teamId)
            ->whereNull('member_id')
            ->where('is_active', true)
            ->sum('monthly_cost') ?? 0);

        $memberMonthly = (float) (LeadCost::withoutTeam()
            ->where('team_id', $teamId)
            ->whereNotNull('member_id')
            ->where('is_active', true)
            ->sum('monthly_cost') ?? 0);

        $toolProrated = $toolMonthly / $daysInMonth * $daysElapsed;
        $memberProrated = $memberMonthly / $daysInMonth * $daysElapsed;

        return [
            'tool_cost_prorated' => $toolProrated,
            'member_cost_prorated' => $memberProrated,
            'total_cost_prorated' => $toolProrated + $memberProrated,
        ];
    }

    protected function computeMemberCosts(int $teamId, int $userId, Carbon $monthStart): array
    {
        $monthStart = $monthStart->copy()->startOfMonth();
        $daysInMonth = $monthStart->daysInMonth;
        $daysElapsed = Carbon::now()->isSameMonth($monthStart) ? Carbon::now()->day : $daysInMonth;

        $monthly = (float) (LeadCost::withoutTeam()
            ->where('team_id', $teamId)
            ->where('member_id', $userId)
            ->where('is_active', true)
            ->sum('monthly_cost') ?? 0);

        $prorated = $monthly / $daysInMonth * $daysElapsed;

        return [
            'member_cost_prorated' => $prorated,
        ];
    }
}

