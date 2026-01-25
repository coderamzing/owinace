<?php

namespace App\Filament\Widgets;

use App\Models\Proposal;
use App\Models\Portfolio;
use App\Models\WorkspaceCredit;
use App\Models\Contact;
use App\Models\Team;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class PPAnalyticWidget extends StatsOverviewWidget
{
    public ?string $filter = null;

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 4; // 4 columns for 4 cards
    }

    protected function getStats(): array
    {
        $teamId = Session::get('team_id');

        if (!$teamId) {
            return [];
        }

        // Get team and workspace
        $team = Team::find($teamId);
        if (!$team || !$team->workspace) {
            return [];
        }

        $workspaceId = $team->workspace->id;

        // Get period from session or filter property
        $selectedPeriod = $this->filter ?? Session::get('analytics_period') ?? Carbon::now()->format('Y-m');
        $currentMonth = Carbon::parse($selectedPeriod . '-01')->month;
        $currentYear = Carbon::parse($selectedPeriod . '-01')->year;

        // Previous month
        $previousMonth = Carbon::parse($selectedPeriod . '-01')->subMonth();
        $prevMonth = $previousMonth->month;
        $prevYear = $previousMonth->year;

        // 1. Proposals Created
        $proposalsCreated = Proposal::where('team_id', $teamId)
            ->whereYear('created_at', $currentYear)
            ->whereMonth('created_at', $currentMonth)
            ->count();

        $prevProposalsCreated = Proposal::where('team_id', $teamId)
            ->whereYear('created_at', $prevYear)
            ->whereMonth('created_at', $prevMonth)
            ->count();

        $proposalsChange = $prevProposalsCreated > 0 
            ? (($proposalsCreated - $prevProposalsCreated) / $prevProposalsCreated) * 100 
            : 0;

        // 2. Portfolios Created
        $portfoliosCreated = Portfolio::where('team_id', $teamId)
            ->whereYear('created_at', $currentYear)
            ->whereMonth('created_at', $currentMonth)
            ->count();

        $prevPortfoliosCreated = Portfolio::where('team_id', $teamId)
            ->whereYear('created_at', $prevYear)
            ->whereMonth('created_at', $prevMonth)
            ->count();

        $portfoliosChange = $prevPortfoliosCreated > 0 
            ? (($portfoliosCreated - $prevPortfoliosCreated) / $prevPortfoliosCreated) * 100 
            : 0;

        // 3. Credits Used (negative credits = usage)
        $creditsUsed = abs(WorkspaceCredit::where('workspace_id', $workspaceId)
            ->where('transaction_type', 'DEBIT')
            ->whereYear('created_at', $currentYear)
            ->whereMonth('created_at', $currentMonth)
            ->sum('credits'));

        $prevCreditsUsed = abs(WorkspaceCredit::where('workspace_id', $workspaceId)
            ->where('transaction_type', 'DEBIT')
            ->whereYear('created_at', $prevYear)
            ->whereMonth('created_at', $prevMonth)
            ->sum('credits'));

        $creditsChange = $prevCreditsUsed > 0 
            ? (($creditsUsed - $prevCreditsUsed) / $prevCreditsUsed) * 100 
            : 0;

        // 4. Contacts Created
        $contactsCreated = Contact::where('team_id', $teamId)
            ->whereYear('created_at', $currentYear)
            ->whereMonth('created_at', $currentMonth)
            ->count();

        $prevContactsCreated = Contact::where('team_id', $teamId)
            ->whereYear('created_at', $prevYear)
            ->whereMonth('created_at', $prevMonth)
            ->count();

        $contactsChange = $prevContactsCreated > 0 
            ? (($contactsCreated - $prevContactsCreated) / $prevContactsCreated) * 100 
            : 0;

        return [
            Stat::make('Proposals Created', $proposalsCreated)
                ->description($this->formatChange($proposalsChange))
                ->descriptionIcon($proposalsChange >= 0 ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-trending-down')
                ->icon('heroicon-o-document-text')
                ->color($proposalsChange >= 0 ? 'success' : 'danger'),

            Stat::make('Portfolios Created', $portfoliosCreated)
                ->description($this->formatChange($portfoliosChange))
                ->descriptionIcon($portfoliosChange >= 0 ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-trending-down')
                ->icon('heroicon-o-briefcase')
                ->color($portfoliosChange >= 0 ? 'success' : 'info'),

            Stat::make('Credits Used', $creditsUsed)
                ->description($this->formatChange($creditsChange))
                ->descriptionIcon($creditsChange >= 0 ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-trending-down')
                ->icon('heroicon-o-sparkles')
                ->color($creditsChange <= 0 ? 'success' : 'warning'),

            Stat::make('Contacts', $contactsCreated)
                ->description($this->formatChange($contactsChange))
                ->descriptionIcon($contactsChange >= 0 ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-trending-down')
                ->icon('heroicon-o-users')
                ->color($contactsChange >= 0 ? 'success' : 'info'),
        ];
    }

    private function formatChange(float $change): string
    {
        $prefix = $change >= 0 ? '+' : '';
        return $prefix . number_format($change, 1) . '% from last month';
    }
}
