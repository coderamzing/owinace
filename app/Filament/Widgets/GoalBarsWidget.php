<?php

namespace App\Filament\Widgets;

use App\Models\AnalyticsGoal;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class GoalBarsWidget extends ChartWidget
{
    protected int | string | array $columnSpan = 12;

    protected static ?int $sort = 7;

    public function getHeading(): string
    {
        return 'Goal Performance by Member';
    }

    public function getDescription(): ?string
    {
        return 'Bar height represents percentage of goal achieved. Full bar (100%) means goal reached.';
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

        // Get all goals for the team and period
        $goals = AnalyticsGoal::where('team_id', $teamId)
            ->where('month', $month)
            ->where('year', $year)
            ->whereNotNull('user_id')
            ->with('user')
            ->get();

        if ($goals->isEmpty()) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        // Get unique users and goal types
        $users = $goals->pluck('user')->unique('id')->filter()->sortBy('name');
        $goalTypes = $goals->pluck('goal_type')->unique()->sort()->values();

        // Build labels (user names)
        $labels = $users->pluck('name')->toArray();

        // Build datasets (one per goal type)
        $datasets = [];
        $colors = [
            '#3b82f6', // blue
            '#10b981', // green
            '#f59e0b', // amber
            '#ef4444', // red
            '#8b5cf6', // purple
            '#06b6d4', // cyan
            '#f97316', // orange
            '#ec4899', // pink
        ];

        foreach ($goalTypes as $index => $goalType) {
            $data = [];
            $backgroundColor = $colors[$index % count($colors)];

            foreach ($users as $user) {
                // Find goal for this user and goal type
                $goal = $goals->firstWhere(function ($g) use ($user, $goalType) {
                    return $g->user_id === $user->id && $g->goal_type === $goalType;
                });

                if ($goal) {
                    $target = $goal->target_value ?? 0;
                    $progress = $goal->progress_value ?? 0;

                    if ($target > 0) {
                        // Calculate percentage (cap at 100%)
                        $percentage = min(100, ($progress / $target) * 100);
                    } else {
                        $percentage = 0;
                    }

                    $data[] = round($percentage, 1);
                } else {
                    // No goal for this user/type combination
                    $data[] = 0;
                }
            }

            $datasets[] = [
                'label' => ucfirst($goalType),
                'data' => $data,
                'backgroundColor' => $backgroundColor,
                'borderColor' => $backgroundColor,
                'borderWidth' => 1,
            ];
        }

        return [
            'datasets' => $datasets,
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
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => "function(context) {
                            return context.dataset.label + ': ' + context.parsed.y.toFixed(1) + '%';
                        }",
                    ],
                ],
            ],
            'animation' => [
                'onComplete' => "function() {
                    const chart = this.chart;
                    const ctx = chart.ctx;
                    chart.data.datasets.forEach((dataset, datasetIndex) => {
                        const meta = chart.getDatasetMeta(datasetIndex);
                        meta.data.forEach((bar, index) => {
                            const value = dataset.data[index];
                            if (value > 0) {
                                const x = bar.x;
                                const y = bar.y;
                                ctx.save();
                                ctx.textAlign = 'center';
                                ctx.textBaseline = 'bottom';
                                ctx.fillStyle = '#374151';
                                ctx.font = 'bold 11px sans-serif';
                                ctx.fillText(dataset.label, x, y - 5);
                                ctx.restore();
                            }
                        });
                    });
                }",
            ],
            'scales' => [
                'x' => [
                    'stacked' => false,
                    'grid' => [
                        'display' => false,
                    ],
                ],
                'y' => [
                    'display' => true,
                    'beginAtZero' => true,
                    'min' => 0,
                    'max' => 100,
                    'ticks' => [
                        'display' => true,
                        'stepSize' => 20,
                        'callback' => "function(value) {
                            return value + '%';
                        }",
                        'color' => '#6b7280',
                        'font' => [
                            'size' => 12,
                        ],
                        'padding' => 8,
                        'autoSkip' => false,
                    ],
                    'grid' => [
                        'display' => true,
                        'color' => 'rgba(0, 0, 0, 0.1)',
                        'drawBorder' => true,
                    ],
                    'title' => [
                        'display' => true,
                        'text' => 'Percentage Achieved (0-100%)',
                        'font' => [
                            'size' => 14,
                            'weight' => 'bold',
                        ],
                        'padding' => [
                            'top' => 10,
                            'bottom' => 10,
                        ],
                    ],
                ],
            ],
        ];
    }
}

