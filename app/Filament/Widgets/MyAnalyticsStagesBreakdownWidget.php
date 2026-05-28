<?php

namespace App\Filament\Widgets;

use App\Models\Lead;
use App\Models\LeadKanban;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class MyAnalyticsStagesBreakdownWidget extends ChartWidget
{
    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = 4;

    public ?string $filter = null;

    protected string $view = 'filament.widgets.my-analytics-stages-breakdown';

    public function getHeading(): string
    {
        return 'My Lead Stages Breakdown';
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
            'cutout' => '70%',
        ];
    }

    protected function getData(): array
    {
        $teamId = Session::get('team_id');
        $userId = Auth::id();

        if (! $teamId || ! $userId) {
            return ['datasets' => [], 'labels' => []];
        }

        $selectedPeriod = $this->filter ?? Carbon::now()->format('Y-m');
        $month = (int) Carbon::parse($selectedPeriod . '-01')->month;
        $year = (int) Carbon::parse($selectedPeriod . '-01')->year;

        $kanbans = LeadKanban::query()
            ->where('team_id', $teamId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $labels = [];
        $data = [];
        $colors = [];

        foreach ($kanbans as $kanban) {
            $count = Lead::query()
                ->where('team_id', $teamId)
                ->where('assigned_member_id', $userId)
                ->where('kanban_id', $kanban->id)
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->count();

            if ($count <= 0) {
                continue;
            }

            $labels[] = (string) $kanban->name;
            $data[] = (int) $count;
            $colors[] = (string) ($kanban->color ?? '#6ABE32');
        }

        return [
            'datasets' => [[
                'data' => $data,
                'backgroundColor' => $colors,
                'borderWidth' => 1,
            ]],
            'labels' => $labels,
        ];
    }

    /**
     * @return array<int, array{label: string, count: int, percent: float, color: string}>
     */
    public function getLegendItems(): array
    {
        $data = $this->getData();
        $labels = $data['labels'] ?? [];
        $values = $data['datasets'][0]['data'] ?? [];
        $colors = $data['datasets'][0]['backgroundColor'] ?? [];

        $total = array_sum(array_map('intval', $values));
        $items = [];

        foreach ($labels as $i => $label) {
            $count = (int) ($values[$i] ?? 0);
            $items[] = [
                'label' => (string) $label,
                'count' => $count,
                'percent' => $total > 0 ? round(($count / $total) * 100, 0) : 0.0,
                'color' => (string) ($colors[$i] ?? '#6ABE32'),
            ];
        }

        return $items;
    }

    public function getTotalLeads(): int
    {
        $data = $this->getData();
        $values = $data['datasets'][0]['data'] ?? [];
        return (int) array_sum(array_map('intval', $values));
    }
}

