<?php

namespace App\Filament\Widgets;

use App\Models\AnalyticsLead;
use App\Models\Lead;
use App\Models\LeadKanban;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class LeadQualityWidget extends StatsOverviewWidget
{
    public ?string $filter = null;

    protected static ?int $sort = 11;

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

        // Get analytics data for the team
        $analyticsData = AnalyticsLead::where('team_id', $teamId)
            ->where('month', $month)
            ->where('year', $year)
            ->get();

        $totalLeads = $analyticsData->sum('total_lead');
        $wonLeads = $analyticsData->sum('total_won');

        // Calculate high value leads from AnalyticsLead total_expected_value
        $highValueLeads = $analyticsData->filter(function ($analytics) {
            return ($analytics->total_expected_value ?? 0) >= 50000;
        })->count();

        // Calculate qualified leads (those with assigned value > 0)
        $qualifiedLeads = $analyticsData->filter(function ($analytics) {
            return ($analytics->total_expected_value ?? 0) > 0;
        })->count();

        $conversionRate = $totalLeads > 0 ? ($wonLeads / $totalLeads) * 100 : 0;

        // Calculate quality score (simple algorithm)
        $qualityScore = 0;
        if ($totalLeads > 0) {
            $highValueRatio = ($highValueLeads / $totalLeads) * 40; // 40% weight
            $qualifiedRatio = ($qualifiedLeads / $totalLeads) * 30; // 30% weight
            $conversionRatio = min($conversionRate, 50) * 0.3; // 30% weight, capped at 50%

            $qualityScore = $highValueRatio + $qualifiedRatio + $conversionRatio;
        }

        return [
            Stat::make('Lead Quality Score', number_format($qualityScore, 1) . '/100')
                ->description('Overall lead quality metric')
                ->descriptionIcon('heroicon-o-star')
                ->color($qualityScore >= 70 ? 'success' : ($qualityScore >= 50 ? 'warning' : 'danger')),

            Stat::make('High Value Leads', $highValueLeads)
                ->description('Leads with value ≥ $50K')
                ->descriptionIcon('heroicon-o-currency-dollar')
                ->color('success'),

            Stat::make('Qualified Leads', $qualifiedLeads)
                ->description('Leads with assigned value')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('info'),

            Stat::make('Conversion Rate', number_format($conversionRate, 1) . '%')
                ->description('Won leads percentage')
                ->descriptionIcon('heroicon-o-arrow-trending-up')
                ->color($conversionRate >= 25 ? 'success' : ($conversionRate >= 15 ? 'warning' : 'danger')),
        ];
    }
}
