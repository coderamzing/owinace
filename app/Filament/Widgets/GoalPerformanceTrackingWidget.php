<?php

namespace App\Filament\Widgets;

use App\Models\AnalyticsGoal;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class GoalPerformanceTrackingWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 12;

    protected static ?string $heading = 'Goal Performance Tracking';

    protected static ?int $sort = 6;

    public function table(Table $table): Table
    {
        $teamId = Session::get('team_id');
        $userId = Auth::id();

        if (!$teamId) {
            return $table->query(AnalyticsGoal::query()->whereRaw('1 = 0'));
        }

        // Get period from session
        $selectedPeriod = Session::get('analytics_period') ?? Carbon::now()->format('Y-m');
        $month = (int) Carbon::parse($selectedPeriod . '-01')->month;
        $year = (int) Carbon::parse($selectedPeriod . '-01')->year;

        return $table
            ->query(
                AnalyticsGoal::query()
                    ->where('team_id', $teamId)
                    ->where('user_id', $userId)
                    ->where('month', $month)
                    ->where('year', $year)
            )
            ->columns([
                TextColumn::make('goal_type')
                    ->label('Goal Type')
                    ->sortable(),

                TextColumn::make('fullname')
                    ->label('Goal Name')
                    ->sortable(),

                TextColumn::make('target_value')
                    ->label('Target')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('progress_value')
                    ->label('Progress')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('progress_percentage')
                    ->label('Progress %')
                    ->getStateUsing(function (AnalyticsGoal $record) {
                        $target = $record->target_value ?? 0;
                        if ($target == 0) return '0%';

                        $progress = $record->progress_value ?? 0;
                        $percentage = min(100, ($progress / $target) * 100);
                        return number_format($percentage, 1) . '%';
                    })
                    ->badge()
                    ->color(function (AnalyticsGoal $record) {
                        $target = $record->target_value ?? 0;
                        if ($target == 0) return 'gray';

                        $progress = $record->progress_value ?? 0;
                        $percentage = ($progress / $target) * 100;

                        if ($percentage >= 100) return 'success';
                        if ($percentage >= 75) return 'warning';
                        if ($percentage >= 50) return 'danger';
                        return 'danger';
                    }),

                TextColumn::make('status')
                    ->label('Status')
                    ->state(function (AnalyticsGoal $record) {
                        $target = $record->target_value ?? 0;
                        if ($target == 0) return 'No Target';

                        $progress = $record->progress_value ?? 0;
                        $percentage = ($progress / $target) * 100;

                        if ($percentage >= 100) return 'Achieved';
                        if ($percentage >= 75) return 'On Track';
                        if ($percentage >= 50) return 'Behind';
                        return 'Critical';
                    })
                    ->badge()
                    ->color(function (AnalyticsGoal $record) {
                        $target = $record->target_value ?? 0;
                        if ($target == 0) return 'gray';

                        $progress = $record->progress_value ?? 0;
                        $percentage = ($progress / $target) * 100;

                        if ($percentage >= 100) return 'success';
                        if ($percentage >= 75) return 'warning';
                        if ($percentage >= 50) return 'danger';
                        return 'danger';
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated(false);
    }
}
