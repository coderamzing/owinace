<?php

namespace App\Filament\Widgets;

use App\Models\Lead;
use App\Models\LeadKanban;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class LeadConversionFunnelWidget extends ChartWidget
{
    protected int | string | array $columnSpan = 6;

    protected static ?int $sort = 9;

    public function getHeading(): string
    {
        return 'Lead Conversion Funnel';
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

        // Get all active kanban stages ordered by sort_order
        $kanbans = LeadKanban::where('team_id', $teamId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $labels = [];
        $data = [];
        $backgroundColors = [];

        $previousCount = null;

        foreach ($kanbans as $kanban) {
            $count = Lead::where('team_id', $teamId)
                ->where('kanban_id', $kanban->id)
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->count();

            if ($count > 0) {
                $labels[] = $kanban->name;
                $data[] = $count;
                $backgroundColors[] = $kanban->color ?? '#3b82f6';

                // Calculate conversion rate from previous stage
                if ($previousCount !== null && $previousCount > 0) {
                    $conversionRate = ($count / $previousCount) * 100;
                    // Store conversion rate for potential future use
                }

                $previousCount = $count;
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
        return 'bar';
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
            'scales' => [
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
