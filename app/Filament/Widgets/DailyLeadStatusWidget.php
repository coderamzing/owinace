<?php

namespace App\Filament\Widgets;

use App\Models\LeadKanban;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class DailyLeadStatusWidget extends ChartWidget
{
    protected static ?int $sort = 99;

    protected int | string | array $columnSpan = 12;

    public ?string $filter = null;

    protected ?string $maxHeight = '500px';

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

        $selectedPeriod = $this->filter ?? Session::get('analytics_period') ?? Carbon::now()->format('Y-m');
        $start = Carbon::parse($selectedPeriod . '-01')->startOfMonth();
        $end = (clone $start)->endOfMonth();

        // Build list of days in month
        $labels = [];
        $current = $start->copy();
        while ($current->lte($end)) {
            $labels[] = $current->format('Y-m-d');
            $current->addDay();
        }

        // Get NEW / WON / LOST kanban IDs (case-insensitive)
        $newKanbanId = LeadKanban::query()
            ->where('team_id', $teamId)
            ->whereRaw('LOWER(code) = ?', ['new'])
            ->value('id');

        $wonKanbanId = LeadKanban::query()
            ->where('team_id', $teamId)
            ->whereRaw('LOWER(code) = ?', ['won'])
            ->value('id');

        $lostKanbanId = LeadKanban::query()
            ->where('team_id', $teamId)
            ->whereRaw('LOWER(code) = ?', ['lost'])
            ->value('id');

        // NEW: only count the day a lead FIRST became NEW.
        // We do this by taking MIN(date) per lead_id for NEW within the month, then counting per date.
        $newPerDay = [];
        if ($newKanbanId) {
            $firstNewPerLead = DB::table('leads_history')
                ->join('leads', 'leads.id', '=', 'leads_history.lead_id')
                ->where('leads.team_id', $teamId)
                ->where('leads_history.kanban_id', $newKanbanId)
                ->whereBetween('leads_history.created_at', [$start, $end])
                ->selectRaw('leads_history.lead_id as lead_id, MIN(DATE(leads_history.created_at)) as first_date')
                ->groupBy('leads_history.lead_id');

            $newPerDay = DB::query()
                ->fromSub($firstNewPerLead, 't')
                ->selectRaw('t.first_date as date, COUNT(*) as count')
                ->groupBy('t.first_date')
                ->pluck('count', 'date')
                ->toArray();
        }

        // WON / LOST: count distinct leads that entered WON/LOST on that day.
        $wonPerDay = [];
        if ($wonKanbanId) {
            $wonPerDay = DB::table('leads_history')
                ->join('leads', 'leads.id', '=', 'leads_history.lead_id')
                ->where('leads.team_id', $teamId)
                ->where('leads_history.kanban_id', $wonKanbanId)
                ->whereBetween('leads_history.created_at', [$start, $end])
                ->selectRaw('DATE(leads_history.created_at) as date, COUNT(DISTINCT leads_history.lead_id) as count')
                ->groupBy('date')
                ->pluck('count', 'date')
                ->toArray();
        }

        $lostPerDay = [];
        if ($lostKanbanId) {
            $lostPerDay = DB::table('leads_history')
                ->join('leads', 'leads.id', '=', 'leads_history.lead_id')
                ->where('leads.team_id', $teamId)
                ->where('leads_history.kanban_id', $lostKanbanId)
                ->whereBetween('leads_history.created_at', [$start, $end])
                ->selectRaw('DATE(leads_history.created_at) as date, COUNT(DISTINCT leads_history.lead_id) as count')
                ->groupBy('date')
                ->pluck('count', 'date')
                ->toArray();
        }

        $newData = [];
        $wonData = [];
        $lostData = [];

        foreach ($labels as $date) {
            $newData[] = $newPerDay[$date] ?? 0;
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

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
            'maintainAspectRatio' => false,
            'scales' => [
                'x' => [
                    'ticks' => [
                        'maxRotation' => 45,
                        'minRotation' => 45,
                    ],
                ],
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
        ];
    }
}

