<?php

namespace App\Filament\Widgets;

use App\Models\Lead;
use App\Models\LeadSource;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class LeadsBySourceWidget extends ChartWidget
{
    protected static ?int $sort = 4;
    
    protected int | string | array $columnSpan = 6;

    protected string $view = 'filament.widgets.leads-by-source';

    public function getHeading(): string
    {
        return 'Leads by Source';
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

        // Get period from session
        $selectedPeriod = Session::get('analytics_period') ?? Carbon::now()->format('Y-m');
        $month = (int) Carbon::parse($selectedPeriod . '-01')->month;
        $year = (int) Carbon::parse($selectedPeriod . '-01')->year;

        // Get all active lead sources for the team
        $sources = LeadSource::where('team_id', $teamId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $labels = [];
        $data = [];
        $backgroundColors = [];

        foreach ($sources as $source) {
            $count = Lead::where('team_id', $teamId)
                ->where('source_id', $source->id)
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->count();

            if ($count > 0) {
                $labels[] = $source->name;
                $data[] = $count;
                $backgroundColors[] = $source->color ?? '#10b981';
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Leads',
                    'data' => $data,
                    'backgroundColor' => $backgroundColors,
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $labels,
        ];
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
            'cutout' => '68%',
        ];
    }

    /**
     * @return array<int, array{label: string, count: int, percent: float, color: string}>
     */
    public function getLegendItems(): array
    {
        $teamId = Session::get('team_id');
        if (! $teamId) {
            return [];
        }

        $selectedPeriod = Session::get('analytics_period') ?? Carbon::now()->format('Y-m');
        $month = (int) Carbon::parse($selectedPeriod . '-01')->month;
        $year = (int) Carbon::parse($selectedPeriod . '-01')->year;

        $sources = LeadSource::where('team_id', $teamId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $items = [];
        $total = 0;

        foreach ($sources as $source) {
            $count = Lead::where('team_id', $teamId)
                ->where('source_id', $source->id)
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->count();

            if ($count <= 0) {
                continue;
            }

            $total += $count;
            $items[] = [
                'label' => (string) $source->name,
                'count' => (int) $count,
                'percent' => 0.0,
                'color' => (string) ($source->color ?? '#10b981'),
            ];
        }

        foreach ($items as $idx => $item) {
            $items[$idx]['percent'] = $total > 0 ? round(($item['count'] / $total) * 100, 0) : 0.0;
        }

        return $items;
    }
}
