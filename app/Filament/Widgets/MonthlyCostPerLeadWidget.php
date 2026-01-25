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

    protected function showGridLines(): bool
    {
        return true;
    }

    protected function showXAxis(): bool
    {
        return true;
    }

    protected function showYAxis(): bool
    {
        return true;
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
            'maxValue' => !empty($costData) ? max($costData) : 0,
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        $maxValue = $this->getCachedData()['maxValue'] ?? 0;
        $suggestedMax = $maxValue > 0 ? $maxValue + 10 : 10;

        return [
            'responsive' => true,
            'maintainAspectRatio' => false,

            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'suggestedMax' => $suggestedMax,

                    // ✅ SAFE: numbers only
                    'ticks' => [
                        'display' => true,
                        'precision' => 0,
                        'stepSize' => max(1, ceil($suggestedMax / 5)),
                    ],

                    'title' => [
                        'display' => true,
                        'text' => 'Average Cost Per Lead',
                    ],
                ],

                'x' => [
                    'ticks' => [
                        'display' => true,
                    ],
                    'title' => [
                        'display' => true,
                        'text' => 'Month',
                    ],
                ],
            ],

            'plugins' => [
                'legend' => [
                    'display' => true,
                ],

                // ✅ Format currency HERE (safe)
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) {
                        return "$" + context.parsed.y.toFixed(2);
                    }',
                    ],
                ],
            ],
        ];
    }
}
