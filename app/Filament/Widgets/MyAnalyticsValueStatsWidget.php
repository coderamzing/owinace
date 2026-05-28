<?php

namespace App\Filament\Widgets;

use App\Models\Lead;
use App\Models\LeadKanban;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class MyAnalyticsValueStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 3;

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

        $wonKanban = LeadKanban::query()->where('team_id', $teamId)->where('code', 'WON')->first();

        $base = Lead::query()
            ->where('team_id', $teamId)
            ->where('assigned_member_id', $userId)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month);

        $totalCost = (float) (clone $base)->sum('cost');
        $totalLeads = (int) (clone $base)->count();
        $avgCostPerLead = $totalLeads > 0 ? ($totalCost / $totalLeads) : 0.0;

        $wonValue = $wonKanban ? (float) (clone $base)->where('kanban_id', $wonKanban->id)->sum('actual_value') : 0.0;
        $roi = $totalCost > 0 ? ((($wonValue - $totalCost) / $totalCost) * 100) : 0.0;

        $spark = $this->buildSparkline($base, $year, $month, (int) ($wonKanban?->id ?? 0));

        return [
            Stat::make('Total Cost', '$' . number_format($totalCost, 0))
                ->description('vs last month')
                ->chart($spark['cost'])
                ->icon('heroicon-o-currency-dollar')
                ->color('success'),

            Stat::make('Avg Cost/Lead', '$' . number_format($avgCostPerLead, 2))
                ->description('vs last month')
                ->chart($spark['avg_cost'])
                ->icon('heroicon-o-calculator')
                ->color('success'),

            Stat::make('Won Value', '$' . number_format($wonValue, 0))
                ->description('vs last month')
                ->chart($spark['won_value'])
                ->icon('heroicon-o-trophy')
                ->color('success'),

            Stat::make('ROI', number_format($roi, 0) . '%')
                ->description('vs last month')
                ->chart($spark['roi'])
                ->icon('heroicon-o-chart-bar')
                ->color('success'),
        ];
    }

    /**
     * @return array<string, array<int, float>>
     */
    protected function buildSparkline($baseQuery, int $year, int $month, int $wonKanbanId): array
    {
        $days = Carbon::createFromDate($year, $month, 1)->daysInMonth;

        $cost = [];
        $avg_cost = [];
        $won_value = [];
        $roi = [];

        for ($d = 1; $d <= $days; $d++) {
            $q = (clone $baseQuery)->whereDay('created_at', $d);
            $leads = (float) $q->count();
            $c = (float) $q->sum('cost');
            $w = $wonKanbanId ? (float) (clone $q)->where('kanban_id', $wonKanbanId)->sum('actual_value') : 0.0;

            $cost[] = $c;
            $avg_cost[] = $leads > 0 ? ($c / $leads) : 0.0;
            $won_value[] = $w;
            $roi[] = $c > 0 ? ((($w - $c) / $c) * 100) : 0.0;
        }

        return compact('cost', 'avg_cost', 'won_value', 'roi');
    }
}

