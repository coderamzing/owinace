<?php

namespace App\Filament\Pages;

use App\Models\AnalyticsGoal;
use App\Models\AnalyticsLead;
use App\Models\Lead;
use App\Models\LeadKanban;
use App\Models\Proposal;
use App\Models\Team;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use UnitEnum;

class MyAnalytics extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?string $navigationLabel = 'My Analytics';

    protected static ?int $navigationSort = 5;

    protected static ?string $title = 'My Analytics';

    protected static ?string $slug = 'my-analytic';

    protected string $view = 'filament.pages.my-analytics';

    public ?string $selectedPeriod = null;
    public ?string $search = '';

    public function mount(): void
    {
        $currentMonth = Carbon::now()->format('Y-m');
        $this->selectedPeriod = $currentMonth;
    }

    public function getTeamId(): ?int
    {
        return Session::get('team_id');
    }

    public function getUserId(): int
    {
        return Auth::id();
    }

    public function getTeam(): ?Team
    {
        $teamId = $this->getTeamId();
        return $teamId ? Team::find($teamId) : null;
    }

    protected function getSelectedMonth(): int
    {
        if (!$this->selectedPeriod) {
            return Carbon::now()->month;
        }
        return (int) Carbon::parse($this->selectedPeriod . '-01')->month;
    }

    protected function getSelectedYear(): int
    {
        if (!$this->selectedPeriod) {
            return Carbon::now()->year;
        }
        return (int) Carbon::parse($this->selectedPeriod . '-01')->year;
    }

    public function getGoalsData(): array
    {
        $teamId = $this->getTeamId();
        $userId = $this->getUserId();

        if (!$teamId) {
            return [
                'total_goals' => 0,
                'achieved' => 0,
                'active' => 0,
                'success_rate' => 0,
                'goals' => [],
            ];
        }

        $month = $this->getSelectedMonth();
        $year = $this->getSelectedYear();

        $goals = AnalyticsGoal::where('team_id', $teamId)
            ->where('user_id', $userId)
            ->where('month', $month)
            ->where('year', $year)
            ->get();

        $totalGoals = $goals->count();
        $achieved = $goals->filter(function ($goal) {
            return ($goal->progress_value ?? 0) >= ($goal->target_value ?? 0) && ($goal->target_value ?? 0) > 0;
        })->count();
        $active = $goals->filter(function ($goal) {
            return ($goal->target_value ?? 0) > 0;
        })->count();

        $totalTarget = $goals->sum('target_value') ?? 0;
        $totalProgress = $goals->sum('progress_value') ?? 0;
        $successRate = $totalTarget > 0 ? ($totalProgress / $totalTarget) * 100 : 0;

        return [
            'total_goals' => $totalGoals,
            'achieved' => $achieved,
            'active' => $active,
            'success_rate' => round($successRate, 1),
            'goals' => $goals->map(function ($goal) use ($totalTarget) {
                $progressPercentage = $goal->target_value > 0 
                    ? ($goal->progress_value / $goal->target_value) * 100 
                    : 0;
                
                $status = 'behind';
                if ($progressPercentage >= 100) {
                    $status = 'achieved';
                } elseif ($progressPercentage >= 75) {
                    $status = 'on_track';
                }

                return [
                    'id' => $goal->id,
                    'goal_type' => $this->formatGoalType($goal->goal_type),
                    'achievement' => round($progressPercentage, 1),
                    'progress_value' => $goal->progress_value ?? 0,
                    'target_value' => $goal->target_value ?? 0,
                    'status' => $status,
                    'month' => $goal->month,
                    'year' => $goal->year,
                ];
            }),
        ];
    }

    public function getLeadData(): array
    {
        $teamId = $this->getTeamId();
        $userId = $this->getUserId();

        if (!$teamId) {
            return [
                'total_lead' => 0,
                'total_won' => 0,
                'total_lost' => 0,
                'total_open' => 0,
                'total_value' => 0,
            ];
        }

        $month = $this->getSelectedMonth();
        $year = $this->getSelectedYear();

        // Get analytics lead data
        $analyticsLead = AnalyticsLead::where('team_id', $teamId)
            ->where('user_id', $userId)
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        if ($analyticsLead) {
            // Get OPEN kanban to count open leads
            $openKanban = LeadKanban::where('team_id', $teamId)
                ->where('code', 'OPEN')
                ->first();

            $openLeads = 0;
            if ($openKanban) {
                $openLeads = Lead::where('team_id', $teamId)
                    ->where('assigned_member_id', $userId)
                    ->whereYear('created_at', $year)
                    ->whereMonth('created_at', $month)
                    ->where('kanban_id', $openKanban->id)
                    ->count();
            }

            return [
                'total_lead' => $analyticsLead->total_lead ?? 0,
                'total_won' => $analyticsLead->total_won ?? 0,
                'total_lost' => $analyticsLead->total_lost ?? 0,
                'total_open' => $openLeads,
                'total_value' => $analyticsLead->total_value ?? 0,
            ];
        }

        // If no analytics data, calculate from leads directly
        $leadsQuery = Lead::where('team_id', $teamId)
            ->where('assigned_member_id', $userId)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month);

        $leads = $leadsQuery->get();

        // Get kanban statuses
        $wonKanban = LeadKanban::where('team_id', $teamId)
            ->where('code', 'WON')
            ->first();
        $lostKanban = LeadKanban::where('team_id', $teamId)
            ->where('code', 'LOST')
            ->first();
        $openKanban = LeadKanban::where('team_id', $teamId)
            ->where('code', 'OPEN')
            ->first();

        $wonKanbanId = $wonKanban?->id;
        $lostKanbanId = $lostKanban?->id;
        $openKanbanId = $openKanban?->id;

        $totalWon = $wonKanbanId ? $leads->where('kanban_id', $wonKanbanId)->count() : 0;
        $totalLost = $lostKanbanId ? $leads->where('kanban_id', $lostKanbanId)->count() : 0;
        $totalOpen = $openKanbanId ? $leads->where('kanban_id', $openKanbanId)->count() : 0;
        $totalValue = $leads->sum('actual_value') ?? 0;

        return [
            'total_lead' => $leads->count(),
            'total_won' => $totalWon,
            'total_lost' => $totalLost,
            'total_open' => $totalOpen,
            'total_value' => $totalValue,
        ];
    }

    public function getProposalsCount(): int
    {
        $teamId = $this->getTeamId();
        $userId = $this->getUserId();

        if (!$teamId) {
            return 0;
        }

        $month = $this->getSelectedMonth();
        $year = $this->getSelectedYear();

        return Proposal::where('team_id', $teamId)
            ->where('user_id', $userId)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count();
    }

    protected function formatGoalType(?string $type): string
    {
        return match($type) {
            'lead_generation' => 'Lead Generation Goal',
            'conversion' => 'Conversion Goal',
            'open_leads' => 'Open Leads Goal',
            default => ucfirst($type ?? 'Goal'),
        };
    }

    public function updatedSelectedPeriod(): void
    {
        // Refresh data when period changes
    }

    public function getPeriodOptions(): array
    {
        $team = $this->getTeam();
        if (!$team || !$team->created_at) {
            // Fallback to current month/year if no team
            $startDate = Carbon::now()->startOfMonth();
        } else {
            // Start from team creation date
            $startDate = Carbon::parse($team->created_at)->startOfMonth();
        }

        $endDate = Carbon::now()->endOfMonth();
        $options = [];

        $current = $startDate->copy();
        while ($current->lte($endDate)) {
            $value = $current->format('Y-m');
            $label = $current->format('F Y');
            $options[$value] = $label;
            $current->addMonth();
        }

        // Reverse to show most recent first
        return array_reverse($options, true);
    }
}

