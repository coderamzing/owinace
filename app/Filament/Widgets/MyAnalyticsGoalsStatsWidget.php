<?php

namespace App\Filament\Widgets;

use App\Models\AnalyticsGoal;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class MyAnalyticsGoalsStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 'full';

    public ?string $filter = null;

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
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

        $totalGoals = $goals->count();
        $achieved = $goals->filter(fn ($g) => ($g->progress_value ?? 0) >= ($g->target_value ?? 0) && ($g->target_value ?? 0) > 0)->count();
        $active = $goals->filter(fn ($g) => ($g->target_value ?? 0) > 0)->count();

        $totalTarget = (float) ($goals->sum('target_value') ?? 0);
        $totalProgress = (float) ($goals->sum('progress_value') ?? 0);
        $successRate = $totalTarget > 0 ? round(($totalProgress / $totalTarget) * 100, 0) : 0;

        // Lightweight sparklines (stable even if goals don’t change daily).
        $spark = array_fill(0, 12, max(0.0, (float) $successRate));

        return [
            Stat::make('Goals Total', $totalGoals)
                ->description("vs last month")
                ->chart($spark)
                ->icon('heroicon-o-flag')
                ->color('success'),

            Stat::make('Achieved', $achieved)
                ->description("vs last month")
                ->chart($spark)
                ->icon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('Active', $active)
                ->description("vs last month")
                ->chart($spark)
                ->icon('heroicon-o-signal')
                ->color('success'),

            Stat::make('Goal Success Rate', $successRate . '%')
                ->description("vs last month")
                ->chart($spark)
                ->icon('heroicon-o-chart-bar')
                ->color('success'),
        ];
    }
}

