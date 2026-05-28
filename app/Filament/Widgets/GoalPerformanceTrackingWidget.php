<?php

namespace App\Filament\Widgets;

use App\Models\AnalyticsGoal;
use Carbon\Carbon;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Session;

class GoalPerformanceTrackingWidget extends Widget
{
    protected string $view = 'filament.widgets.goal-performance-tracking';

    protected int | string | array $columnSpan = 12;

    protected static ?string $heading = 'Goal Performance Tracking';

    protected static ?int $sort = 6;

    /**
     * Get goals grouped by member for display in cards.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getMemberGoals(): array
    {
        $teamId = Session::get('team_id');

        if (! $teamId) {
            return [];
        }

        // Get period from session
        $selectedPeriod = Session::get('analytics_period') ?? Carbon::now()->format('Y-m');
        $month = (int) Carbon::parse($selectedPeriod . '-01')->month;
        $year = (int) Carbon::parse($selectedPeriod . '-01')->year;

        $goals = AnalyticsGoal::query()
            ->where('team_id', $teamId)
            ->where('month', $month)
            ->where('year', $year)
            ->whereNotNull('user_id')
            ->with('user')
            ->get()
            ->groupBy('user_id');

        $result = [];

        foreach ($goals as $userId => $userGoals) {
            $first = $userGoals->first();
            $user = $first?->user;

            $totalTarget = (float) $userGoals->sum('target_value');
            $totalProgress = (float) $userGoals->sum('progress_value');
            $percentage = $totalTarget > 0 ? min(100, ($totalProgress / $totalTarget) * 100) : 0;

            $status = 'behind';
            if ($percentage >= 75) {
                $status = 'on_track';
            }
            if ($percentage >= 100) {
                $status = 'achieved';
            }

            $result[] = [
                'id' => $userId,
                'name' => $first?->fullname ?: ($user?->name ?? 'Unknown'),
                'role' => 'Sales Rep',
                'avatar_url' => $user?->avatar_url ?? asset('/images/avatars/avatar-1.png'),
                'percentage' => round($percentage, 0),
                'progress_value' => $totalProgress,
                'target_value' => $totalTarget,
                'status' => $status,
            ];
        }

        return $result;
    }

    protected function getGoalLabel(string $goalType): string
    {
        return match ($goalType) {
            'lead_generation' => 'Lead Generation',
            'conversion' => 'Conversion',
            'open_leads' => 'Open Leads',
            'general' => 'General Goals',
            default => ucfirst(str_replace('_', ' ', $goalType)),
        };
    }

    protected function getColorForPercentage(float $percentage): string
    {
        if ($percentage >= 100) {
            return 'success';
        }

        if ($percentage >= 75) {
            return 'primary';
        }

        if ($percentage >= 50) {
            return 'warning';
        }

        return 'danger';
    }
}
