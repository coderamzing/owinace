<?php

namespace App\Filament\Widgets;

use App\Models\Lead;
use App\Models\LeadKanban;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class DailyLeadStatusWidget extends ChartWidget
{
    protected static ?int $sort = 99;

    protected int | string | array $columnSpan = 12;

    public function getHeading(): string
    {
        return 'Daily Leads (New / Won / Lost)';
    }

    protected function getData(): array
    {
        $teamId = Session::get('team_id');

        if (!$teamId) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        $selectedPeriod = Session::get('analytics_period') ?? Carbon::now()->format('Y-m');
        $start = Carbon::parse($selectedPeriod . '-01')->startOfMonth();
        $end = (clone $start)->endOfMonth();

        // Build list of days in month
        $labels = [];
        $current = $start->copy();
        while ($current->lte($end)) {
            $labels[] = $current->format('Y-m-d');
            $current->addDay();
        }

        // New leads per day (based on created_at)
        $newLeads = Lead::where('team_id', $teamId)
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();

        // Get WON and LOST kanban IDs
        $wonKanban = LeadKanban::where('team_id', $teamId)->where('code', 'WON')->first();
        $lostKanban = LeadKanban::where('team_id', $teamId)->where('code', 'LOST')->first();

        $wonPerDay = [];
        $lostPerDay = [];

        if ($wonKanban) {
            $wonPerDay = DB::table('leads_history')
                ->join('leads', 'leads.id', '=', 'leads_history.lead_id')
                ->join('lead_kanban', 'lead_kanban.id', '=', 'leads_history.kanban_id')
                ->where('leads.team_id', $teamId)
                ->whereBetween('leads_history.created_at', [$start, $end])
                ->where('lead_kanban.id', $wonKanban->id)
                ->selectRaw('DATE(leads_history.created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->pluck('count', 'date')
                ->toArray();
        }

        if ($lostKanban) {
            $lostPerDay = DB::table('leads_history')
                ->join('leads', 'leads.id', '=', 'leads_history.lead_id')
                ->join('lead_kanban', 'lead_kanban.id', '=', 'leads_history.kanban_id')
                ->where('leads.team_id', $teamId)
                ->whereBetween('leads_history.created_at', [$start, $end])
                ->where('lead_kanban.id', $lostKanban->id)
                ->selectRaw('DATE(leads_history.created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->pluck('count', 'date')
                ->toArray();
        }

        $newData = [];
        $wonData = [];
        $lostData = [];

        foreach ($labels as $date) {
            $newData[] = $newLeads[$date] ?? 0;
            $wonData[] = $wonPerDay[$date] ?? 0;
            $lostData[] = $lostPerDay[$date] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'New',
                    'data' => $newData,
                    'backgroundColor' => '#3b82f6',
                ],
                [
                    'label' => 'Won',
                    'data' => $wonData,
                    'backgroundColor' => '#10b981',
                ],
                [
                    'label' => 'Lost',
                    'data' => $lostData,
                    'backgroundColor' => '#ef4444',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}

