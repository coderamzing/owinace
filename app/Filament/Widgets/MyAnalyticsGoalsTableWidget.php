<?php

namespace App\Filament\Widgets;

use App\Models\AnalyticsGoal;
use Carbon\Carbon;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class MyAnalyticsGoalsTableWidget extends Widget
{
    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 8;

    public ?string $filter = null;

    protected string $view = 'filament.widgets.my-analytics-goals-table';

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getRows(): array
    {
        $teamId = Session::get('team_id');
        $userId = Auth::id();

        if (! $teamId || ! $userId) {
            return [];
        }

        $selectedPeriod = $this->filter ?? Carbon::now()->format('Y-m');
        $month = (int) Carbon::parse($selectedPeriod . '-01')->month;
        $year = (int) Carbon::parse($selectedPeriod . '-01')->year;

        $goals = AnalyticsGoal::query()
            ->where('team_id', $teamId)
            ->where('user_id', $userId)
            ->where('month', $month)
            ->where('year', $year)
            ->get();

        return $goals->map(function (AnalyticsGoal $goal) {
            $target = (float) ($goal->target_value ?? 0);
            $progress = (float) ($goal->progress_value ?? 0);
            $percent = $target > 0 ? round(($progress / $target) * 100, 0) : 0;

            $status = 'Behind';
            $statusTone = 'danger';
            if ($percent >= 100) {
                $status = 'Achieved';
                $statusTone = 'success';
            } elseif ($percent >= 75) {
                $status = 'On Track';
                $statusTone = 'success';
            } elseif ($percent >= 50) {
                $status = 'At Risk';
                $statusTone = 'warning';
            }

            return [
                'goal' => $goal->goal_type ? ucfirst(str_replace('_', ' ', (string) $goal->goal_type)) : 'Goal',
                'target' => $target,
                'achieved' => $progress,
                'progress_percent' => (int) min(100, max(0, $percent)),
                'status' => $status,
                'status_tone' => $statusTone,
            ];
        })->toArray();
    }
}

