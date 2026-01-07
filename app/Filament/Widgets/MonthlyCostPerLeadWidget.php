<?php

namespace App\Filament\Widgets;

use App\Models\AnalyticsCost;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class MonthlyCostPerLeadWidget extends ChartWidget
{
    protected static ?int $sort = 9;
    
    protected int | string | array $columnSpan = 'full';

    public function getHeading(): string
    {
        return 'Average Cost Per Lead - Last 12 Months';
    }

    public function getDescription(): ?string
    {
        return 'Total average cost per lead (sum of tool and member costs)';
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

        // Get data for the last 12 months
        $labels = [];
        $costData = [];
        $now = Carbon::now();

        for ($i = 11; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            $month = $date->month;
            $year = $date->year;

            // Format label as "MMM YYYY" (e.g., "Jan 2024")
            $labels[] = $date->format('M Y');

            // Get all analytics cost data for this month (all types) and SUM the avg_cost_per_lead
            $totalAvgCost = AnalyticsCost::where('team_id', $teamId)
                ->where('month', $month)
                ->where('year', $year)
                ->sum('avg_cost_per_lead');

            // Add the summed cost per lead for this month
            $costData[] = round((float) $totalAvgCost, 2);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Avg Cost Per Lead ($)',
                    'data' => $costData,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.5)', // Blue color
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 2,
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
            'maintainAspectRatio' => false,
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => 'function(value) { return "$" + value.toFixed(2); }',
                    ],
                    'title' => [
                        'display' => true,
                        'text' => 'Average Cost Per Lead ($)',
                        'font' => [
                            'size' => 14,
                            'weight' => 'bold',
                        ],
                    ],
                ],
                'x' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Month',
                        'font' => [
                            'size' => 14,
                            'weight' => 'bold',
                        ],
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                    'labels' => [
                        'font' => [
                            'size' => 14,
                        ],
                    ],
                ],
                'tooltip' => [
                    'enabled' => true,
                    'callbacks' => [
                        'label' => 'function(context) { return context.dataset.label + ": $" + context.parsed.y.toFixed(2); }',
                        'title' => 'function(context) { return context[0].label; }',
                    ],
                ],
            ],
        ];
    }
}

