<?php

namespace App\Filament\Widgets;

use App\Models\AnalyticsLead;
use App\Models\Lead;
use App\Models\LeadKanban;
use App\Models\Proposal;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class LeadsOverviewWidget extends StatsOverviewWidget
{
    public ?string $filter = null;

    protected static ?int $sort = 2;

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

        // Get analytics lead data
        $analyticsLead = AnalyticsLead::where('team_id', $teamId)
            ->where('user_id', $userId)
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        if ($analyticsLead) {
            $totalLeads = $analyticsLead->total_lead ?? 0;
            $totalWon = $analyticsLead->total_won ?? 0;
            $totalLost = $analyticsLead->total_lost ?? 0;

            // Get OPEN kanban
            $openKanban = LeadKanban::where('team_id', $teamId)
                ->where('code', 'OPEN')
                ->first();

            $totalOpen = 0;
            if ($openKanban) {
                $totalOpen = Lead::where('team_id', $teamId)
                    ->where('assigned_member_id', $userId)
                    ->whereYear('created_at', $year)
                    ->whereMonth('created_at', $month)
                    ->where('kanban_id', $openKanban->id)
                    ->count();
            }
        } else {
            // Calculate from leads directly
            $leadsQuery = Lead::where('team_id', $teamId)
                ->where('assigned_member_id', $userId)
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month);

            $leads = $leadsQuery->get();

            $wonKanban = LeadKanban::where('team_id', $teamId)->where('code', 'WON')->first();
            $lostKanban = LeadKanban::where('team_id', $teamId)->where('code', 'LOST')->first();
            $openKanban = LeadKanban::where('team_id', $teamId)->where('code', 'OPEN')->first();

            $totalLeads = $leads->count();
            $totalWon = $wonKanban ? $leads->where('kanban_id', $wonKanban->id)->count() : 0;
            $totalLost = $lostKanban ? $leads->where('kanban_id', $lostKanban->id)->count() : 0;
            $totalOpen = $openKanban ? $leads->where('kanban_id', $openKanban->id)->count() : 0;
        }

        // Get proposals count
        $proposalsCount = Proposal::where('team_id', $teamId)
            ->where('user_id', $userId)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count();

        return [
            Stat::make('Total Leads', $totalLeads)
                ->description('Leads this period')
                ->descriptionIcon('heroicon-o-user-group')
                ->color('primary'),
            Stat::make('Won', $totalWon)
                ->description('Converted leads')
                ->descriptionIcon('heroicon-o-trophy')
                ->color('success'),
            Stat::make('Open', $totalOpen)
                ->description('Active leads')
                ->descriptionIcon('heroicon-o-clock')
                ->color('info'),
            Stat::make('Proposals', $proposalsCount)
                ->description('Created this period')
                ->descriptionIcon('heroicon-o-document-text')
                ->color('warning'),
        ];
    }
}
