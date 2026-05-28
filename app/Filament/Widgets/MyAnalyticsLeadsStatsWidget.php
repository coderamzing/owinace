<?php

namespace App\Filament\Widgets;

use App\Models\Lead;
use App\Models\LeadKanban;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class MyAnalyticsLeadsStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    public ?string $filter = null;

    protected function getColumns(): int
    {
        return 6;
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
        $lostKanban = LeadKanban::query()->where('team_id', $teamId)->where('code', 'LOST')->first();
        $openKanban = LeadKanban::query()->where('team_id', $teamId)->where('code', 'OPEN')->first();

        $base = Lead::query()
            ->where('team_id', $teamId)
            ->where('assigned_member_id', $userId)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month);

        $totalLeads = (clone $base)->count();
        $won = $wonKanban ? (clone $base)->where('kanban_id', $wonKanban->id)->count() : 0;
        $lost = $lostKanban ? (clone $base)->where('kanban_id', $lostKanban->id)->count() : 0;
        $open = $openKanban ? (clone $base)->where('kanban_id', $openKanban->id)->count() : 0;

        $proposals = \App\Models\Proposal::query()
            ->where('team_id', $teamId)
            ->where('user_id', $userId)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count();

        $conversion = $totalLeads > 0 ? round(($won / $totalLeads) * 100, 1) : 0.0;

        $spark = $this->buildSparkline($base, $year, $month);

        return [
            Stat::make('Total Leads', number_format($totalLeads))
                ->description('vs last month')
                ->chart($spark['all'])
                ->icon('heroicon-o-user-group')
                ->color('success'),

            Stat::make('Won', number_format($won))
                ->description('vs last month')
                ->chart($spark['won'])
                ->icon('heroicon-o-trophy')
                ->color('success'),

            Stat::make('Lost', number_format($lost))
                ->description('vs last month')
                ->chart($spark['lost'])
                ->icon('heroicon-o-x-circle')
                ->color('danger'),

            Stat::make('Open', number_format($open))
                ->description('vs last month')
                ->chart($spark['open'])
                ->icon('heroicon-o-clock')
                ->color('success'),

            Stat::make('Proposals', number_format($proposals))
                ->description('vs last month')
                ->chart($spark['proposals'])
                ->icon('heroicon-o-document-text')
                ->color('success'),

            Stat::make('Conversion Rate', $conversion . '%')
                ->description('vs last month')
                ->chart($spark['conversion'])
                ->icon('heroicon-o-arrow-path')
                ->color('success'),
        ];
    }

    /**
     * @return array<string, array<int, float>>
     */
    protected function buildSparkline($baseQuery, int $year, int $month): array
    {
        $days = Carbon::createFromDate($year, $month, 1)->daysInMonth;

        $all = [];
        $won = [];
        $lost = [];
        $open = [];
        $proposals = [];
        $conversion = [];

        for ($d = 1; $d <= $days; $d++) {
            $q = (clone $baseQuery)->whereDay('created_at', $d);
            $count = (float) $q->count();

            $all[] = $count;
            $won[] = (float) (clone $q)->whereHas('kanban', fn ($k) => $k->where('code', 'WON'))->count();
            $lost[] = (float) (clone $q)->whereHas('kanban', fn ($k) => $k->where('code', 'LOST'))->count();
            $open[] = (float) (clone $q)->whereHas('kanban', fn ($k) => $k->where('code', 'OPEN'))->count();

            $proposals[] = 0.0;
            $conversion[] = $count > 0 ? (($won[array_key_last($won)] / $count) * 100) : 0.0;
        }

        // Keep proposals sparkline stable; can be enhanced later with per-day proposals.
        $proposals = array_fill(0, $days, 0.0);

        return compact('all', 'won', 'lost', 'open', 'proposals', 'conversion');
    }
}

