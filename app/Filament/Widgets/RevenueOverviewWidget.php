<?php

namespace App\Filament\Widgets;

use App\Models\AnalyticsLead;
use App\Models\Lead;
use App\Models\LeadKanban;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class RevenueOverviewWidget extends StatsOverviewWidget
{
    public ?string $filter = null;

    protected static ?int $sort = 8;

    protected function getStats(): array
    {
        $teamId = Session::get('team_id');

        if (!$teamId) {
            return [];
        }

        // Get period from session or filter property
        $selectedPeriod = $this->filter ?? Session::get('analytics_period') ?? Carbon::now()->format('Y-m');
        $month = (int) Carbon::parse($selectedPeriod . '-01')->month;
        $year = (int) Carbon::parse($selectedPeriod . '-01')->year;

        // Get WON kanban
        $wonKanban = LeadKanban::where('team_id', $teamId)->where('code', 'WON')->first();

        $currentMonthRevenue = 0;
        $previousMonthRevenue = 0;
        $totalPipelineValue = 0;
        $conversionRate = 0;

        if ($wonKanban) {
            // Current month revenue
            $currentMonthRevenue = Lead::where('team_id', $teamId)
                ->where('kanban_id', $wonKanban->id)
                ->whereYear('updated_at', $year)
                ->whereMonth('updated_at', $month)
                ->sum('expected_value');

            // Previous month revenue
            $previousMonth = Carbon::parse($selectedPeriod . '-01')->subMonth();
            $previousMonthRevenue = Lead::where('team_id', $teamId)
                ->where('kanban_id', $wonKanban->id)
                ->whereYear('updated_at', $previousMonth->year)
                ->whereMonth('updated_at', $previousMonth->month)
                ->sum('expected_value');

            // Total pipeline value (all open leads)
            $openKanban = LeadKanban::where('team_id', $teamId)->where('code', 'OPEN')->first();
            if ($openKanban) {
                $totalPipelineValue = Lead::where('team_id', $teamId)
                    ->where('kanban_id', $openKanban->id)
                    ->sum('expected_value');
            }

            // Conversion rate calculation using AnalyticsLead data
            $analyticsData = AnalyticsLead::where('team_id', $teamId)
                ->where('month', $month)
                ->where('year', $year)
                ->get();

            $totalLeads = $analyticsData->sum('total_lead');
            $wonLeads = $analyticsData->sum('total_won');
            $conversionRate = $totalLeads > 0 ? ($wonLeads / $totalLeads) * 100 : 0;
        }

        // Calculate growth
        $growth = 0;
        if ($previousMonthRevenue > 0) {
            $growth = (($currentMonthRevenue - $previousMonthRevenue) / $previousMonthRevenue) * 100;
        }

        return [
            Stat::make('Monthly Revenue', '$' . number_format($currentMonthRevenue, 0))
                ->description($growth >= 0 ? '+' . number_format($growth, 1) . '% from last month' : number_format($growth, 1) . '% from last month')
                ->descriptionIcon($growth >= 0 ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-trending-down')
                ->color($growth >= 0 ? 'success' : 'danger'),

            Stat::make('Pipeline Value', '$' . number_format($totalPipelineValue, 0))
                ->description('Total value of open leads')
                ->descriptionIcon('heroicon-o-currency-dollar')
                ->color('warning'),

            Stat::make('Conversion Rate', number_format($conversionRate, 1) . '%')
                ->description('Leads converted to revenue')
                ->descriptionIcon('heroicon-o-chart-pie')
                ->color($conversionRate >= 20 ? 'success' : ($conversionRate >= 10 ? 'warning' : 'danger')),
        ];
    }
}
