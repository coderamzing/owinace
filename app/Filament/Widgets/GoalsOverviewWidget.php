<?php

namespace App\Filament\Widgets;

use App\Models\AnalyticsGoal;
use App\Models\Team;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class GoalsOverviewWidget extends StatsOverviewWidget
{
    public ?string $filter = null;

    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 12;

    protected function getStats(): array
    {
        $teamId = Session::get('team_id');
        $userId = Auth::id();

        if (!$teamId) {
            return [];
        }

        // Get period from session or filter property
        $selectedPeriod = $this->filter ?? Session::get('analytics_period') ?? Carbon::now()->format('Y-m');
        $month = (int) Carbon::parse($selectedPeriod . '-01')->month;
        $year = (int) Carbon::parse($selectedPeriod . '-01')->year;

        $goals = AnalyticsGoal::where('team_id', $teamId)
            ->where('user_id', $userId)
            ->where('month', $month)
            ->where('year', $year)
            ->get();

        $totalGoals = $goals->count();
        $achieved = $goals->filter(function ($goal) {
            return ($goal->progress_value ?? 0) >= ($goal->target_value ?? 0) && ($goal->target_value ?? 0) > 0;
        })->count();
        
        $totalTarget = $goals->sum('target_value') ?? 0;
        $totalProgress = $goals->sum('progress_value') ?? 0;
        $successRate = $totalTarget > 0 ? ($totalProgress / $totalTarget) * 100 : 0;

        return [
            Stat::make('Total Goals', $totalGoals)
                ->description('Goals for this period')
                ->descriptionIcon('heroicon-o-flag')
                ->color('primary'),
            Stat::make('Achieved', $achieved)
                ->description('Goals completed')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),
            Stat::make('Success Rate', number_format($successRate, 1) . '%')
                ->description('Overall progress')
                ->descriptionIcon('heroicon-o-chart-bar')
                ->color($successRate >= 100 ? 'success' : ($successRate >= 75 ? 'warning' : 'danger')),
        ];
    }
}
