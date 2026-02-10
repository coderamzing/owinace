<?php

namespace App\Filament\Widgets;

use App\Models\Lead;
use App\Models\LeadKanban;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use App\Models\AnalyticsCost;

class LeadAnalyticWidget extends StatsOverviewWidget
{
    public ?string $filter = null;

    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 4; // 4 columns for 8 cards (2 rows)
    }

    protected function getStats(): array
    {
        $teamId = Session::get('team_id');

        if (!$teamId) {
            return [];
        }

        // Get period from session or filter property
        $selectedPeriod = $this->filter ?? Session::get('analytics_period') ?? Carbon::now()->format('Y-m');
        $currentMonth = Carbon::parse($selectedPeriod . '-01')->month;
        $currentYear = Carbon::parse($selectedPeriod . '-01')->year;

        // Previous month
        $previousMonth = Carbon::parse($selectedPeriod . '-01')->subMonth();
        $prevMonth = $previousMonth->month;
        $prevYear = $previousMonth->year;

        // Get Kanban statuses
        $wonKanban = LeadKanban::where('team_id', $teamId)->where('code', 'WON')->first();
        $lostKanban = LeadKanban::where('team_id', $teamId)->where('code', 'LOST')->first();
        $openKanban = LeadKanban::where('team_id', $teamId)->where('code', 'OPEN')->first();
        $discussionKanban = LeadKanban::where('team_id', $teamId)->where('code', 'DISCUSSION')->first();

        // Current month leads
        $currentLeads = Lead::where('team_id', $teamId)
            ->whereYear('created_at', $currentYear)
            ->whereMonth('created_at', $currentMonth)
            ->where('kanban_id', '!=', $openKanban->id);

        // Previous month leads
        $previousLeads = Lead::where('team_id', $teamId)
            ->whereYear('created_at', $prevYear)
            ->whereMonth('created_at', $prevMonth)
            ->where('kanban_id', '!=', $openKanban->id);

        // 1. Lead Cost Per Lead / Average Cost
        $analyticsCost = AnalyticsCost::where('team_id', $teamId)
            ->where('month', $currentMonth)
            ->where('year', $currentYear);

        $prevAnalyticsCost = AnalyticsCost::where('team_id', $teamId)
            ->where('month', $prevMonth)
            ->where('year', $prevYear);

        $totalCost = $currentLeads->clone()->sum('cost');
        $totalLeadsCount = $currentLeads->clone()->count();
        $avgCostPerLead = ($analyticsCost->clone()->sum('total_cost') + $totalCost) / max($totalLeadsCount, 1);

        $prevTotalCost = $previousLeads->clone()->sum('cost');
        $prevTotalLeadsCount = $previousLeads->clone()->count();
        $prevAvgCostPerLead = ($prevAnalyticsCost->clone()->sum('total_cost') + $prevTotalCost) / max($prevTotalLeadsCount, 1);

        $avgCostChange = $prevAvgCostPerLead > 0 
            ? (($avgCostPerLead - $prevAvgCostPerLead) / $prevAvgCostPerLead) * 100 
            : 0;

        // 2. Conversion Rate (Open Leads / Won Leads)
        $openLeadsCount = $openKanban ? $currentLeads->count() : 0;
        $wonLeadsCount = $wonKanban ? $currentLeads->clone()->where('kanban_id', $wonKanban->id)->count() : 0;
        $conversionRate = $openLeadsCount > 0 ? ($wonLeadsCount / $openLeadsCount) * 100 : 0;

        $prevOpenLeadsCount = $openKanban ? $previousLeads->count() : 0;
        $prevWonLeadsCount = $wonKanban ? $previousLeads->clone()->where('kanban_id', $wonKanban->id)->count() : 0;
        $prevConversionRate = $prevOpenLeadsCount > 0 ? ($prevWonLeadsCount / $prevOpenLeadsCount) * 100 : 0;

        $conversionRateChange = $prevConversionRate > 0 
            ? $conversionRate - $prevConversionRate 
            : 0;

        // 3. Actual Value of Converted Leads (WON)
        $actualValue = $wonKanban 
            ? $currentLeads->clone()->where('kanban_id', $wonKanban->id)->sum('actual_value') 
            : 0;
        $prevActualValue = $wonKanban 
            ? $previousLeads->clone()->where('kanban_id', $wonKanban->id)->sum('actual_value') 
            : 0;

        $actualValueChange = $prevActualValue > 0 
            ? (($actualValue - $prevActualValue) / $prevActualValue) * 100 
            : 0;

        // 4. ROI (Return on Investment)
        $roi = $totalCost > 0 ? ((($actualValue - $totalCost) / $totalCost) * 100) : 0;
        $prevRoi = $prevTotalCost > 0 ? ((($prevActualValue - $prevTotalCost) / $prevTotalCost) * 100) : 0;
        $roiChange = $roi - $prevRoi;

        // 5. Pipeline Value (Expected value of leads in DISCUSSION)
        $pipelineValue = $discussionKanban 
            ? Lead::where('team_id', $teamId)->where('kanban_id', $discussionKanban->id)->sum('expected_value')
            : 0;
        $prevPipelineValue = $discussionKanban 
            ? Lead::where('team_id', $teamId)
                ->where('kanban_id', $discussionKanban->id)
                ->where('created_at', '<', Carbon::parse($selectedPeriod . '-01'))
                ->sum('expected_value')
            : 0;

        $pipelineValueChange = $prevPipelineValue > 0 
            ? (($pipelineValue - $prevPipelineValue) / $prevPipelineValue) * 100 
            : 0;

        // 6. Total Won
        $totalWon = $wonLeadsCount;
        $prevTotalWon = $prevWonLeadsCount;
        $wonChange = $prevTotalWon > 0 
            ? (($totalWon - $prevTotalWon) / $prevTotalWon) * 100 
            : 0;

        // 7. Total Lost
        $totalLost = $lostKanban ? $currentLeads->clone()->where('kanban_id', $lostKanban->id)->count() : 0;
        $prevTotalLost = $lostKanban ? $previousLeads->clone()->where('kanban_id', $lostKanban->id)->count() : 0;
        $lostChange = $prevTotalLost > 0 
            ? (($totalLost - $prevTotalLost) / $prevTotalLost) * 100 
            : 0;

        // 8. All Leads in Any Status
        $allLeads = $totalLeadsCount;
        $prevAllLeads = $prevTotalLeadsCount;
        $allLeadsChange = $prevAllLeads > 0 
            ? (($allLeads - $prevAllLeads) / $prevAllLeads) * 100 
            : 0;

        return [
            // Row 1
            Stat::make('Avg Cost Per Lead', '$' . number_format($avgCostPerLead, 2))
                ->description($this->formatChange($avgCostChange))
                ->descriptionIcon($avgCostChange >= 0 ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-trending-down')
                ->icon('heroicon-o-currency-dollar')
                ->color($avgCostChange <= 0 ? 'success' : 'danger'),

            Stat::make('Conversion Rate', number_format($conversionRate, 1) . '%')
                ->description($this->formatChange($conversionRateChange, '%', false))
                ->descriptionIcon($conversionRateChange >= 0 ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-trending-down')
                ->icon('heroicon-o-chart-pie')
                ->color($conversionRateChange >= 0 ? 'success' : 'danger'),

            Stat::make('Actual Value (Won)', '$' . number_format($actualValue, 0))
                ->description($this->formatChange($actualValueChange))
                ->descriptionIcon($actualValueChange >= 0 ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-trending-down')
                ->icon('heroicon-o-banknotes')
                ->color($actualValueChange >= 0 ? 'success' : 'danger'),

            Stat::make('ROI', number_format($roi, 1) . '%')
                ->description($this->formatChange($roiChange, '%', false))
                ->descriptionIcon($roiChange >= 0 ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-trending-down')
                ->icon('heroicon-o-chart-bar')
                ->color($roiChange >= 0 ? 'success' : 'danger'),

            // Row 2
            Stat::make('Pipeline Value', '$' . number_format($pipelineValue, 0))
                ->description($this->formatChange($pipelineValueChange))
                ->descriptionIcon($pipelineValueChange >= 0 ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-trending-down')
                ->icon('heroicon-o-funnel')
                ->color($pipelineValueChange >= 0 ? 'success' : 'warning'),

            Stat::make('Total Won', $totalWon)
                ->description($this->formatChange($wonChange))
                ->descriptionIcon($wonChange >= 0 ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-trending-down')
                ->icon('heroicon-o-trophy')
                ->color($wonChange >= 0 ? 'success' : 'danger'),

            Stat::make('Total Lost', $totalLost)
                ->description($this->formatChange($lostChange))
                ->descriptionIcon($lostChange >= 0 ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-trending-down')
                ->icon('heroicon-o-x-circle')
                ->color($lostChange <= 0 ? 'success' : 'danger'),

            Stat::make('All Leads', $allLeads)
                ->description($this->formatChange($allLeadsChange))
                ->descriptionIcon($allLeadsChange >= 0 ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-trending-down')
                ->icon('heroicon-o-user-group')
                ->color($allLeadsChange >= 0 ? 'success' : 'info'),
        ];
    }

    private function formatChange(float $change, string $suffix = '%', bool $includePercent = true): string
    {
        $prefix = $change >= 0 ? '+' : '';
        $formatted = $prefix . number_format(abs($change), 1);
        
        if ($includePercent) {
            $formatted .= $suffix;
        } else {
            $formatted .= $suffix;
        }
        
        return $formatted . ' from last month';
    }
}
