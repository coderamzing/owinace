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

            $memberGoals = [];

            foreach ($userGoals as $goal) {
                $target = (float) ($goal->target_value ?? 0);
                $progress = (float) ($goal->progress_value ?? 0);
                $percentage = $target > 0 ? min(100, ($progress / $target) * 100) : 0;

                // Format value & unit similar to other goals widgets
                $value = $progress;
                $unit = '';

                if ($value >= 1000) {
                    $value = $value / 1000;
                    $unit = 'Gb';
                } else {
                    $unit = 'Mb';
                }

                if ($value >= 100) {
                    $formattedValue = number_format($value, 0);
                } else {
                    $formattedValue = number_format($value, 1);
                }

                $memberGoals[] = [
                    'type' => $goal->goal_type,
                    'label' => $this->getGoalLabel((string) $goal->goal_type),
                    'target' => $target,
                    'progress' => $progress,
                    'value' => $formattedValue,
                    'unit' => $unit,
                    'percentage' => round($percentage, 1),
                    'color' => $this->getColorForPercentage($percentage),
                    'is_active' => $percentage >= 75,
                ];
            }

            $result[] = [
                'id' => $userId,
                'name' => $first?->fullname ?: ($user?->name ?? 'Unknown'),
                'role' => 'Member',
                'avatar_url' => $user?->avatar_url ?? asset('/images/avatars/avatar-1.png'),
                'goals' => $memberGoals,
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
