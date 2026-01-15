<?php

namespace App\Filament\Widgets;

use App\Models\AnalyticsGoal;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class GoalsDisplayWidget extends Widget
{
    protected string $view = 'filament.widgets.goals-display';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 2;

    public ?string $filter = null;

    public function getGoals(): array
    {
        $teamId = Session::get('team_id');

        if (!$teamId) {
            return [];
        }

        // Get period from session or filter property
        $selectedPeriod = $this->filter ?? Session::get('analytics_period') ?? Carbon::now()->format('Y-m');
        $month = (int) Carbon::parse($selectedPeriod . '-01')->month;
        $year = (int) Carbon::parse($selectedPeriod . '-01')->year;

        // Get all goals for the team and period
        $goals = AnalyticsGoal::where('team_id', $teamId)
            ->where('month', $month)
            ->where('year', $year)
            ->whereNotNull('user_id')
            ->with('user')
            ->get();

        // Format goals for display
        $formattedGoals = [];
        $colors = ['success', 'warning', 'secondary', 'gray'];
        $colorIndex = 0;

        // Get unique goal types or limit to 4
        $goalTypes = $goals->pluck('goal_type')->unique()->take(4);

        foreach ($goalTypes as $goalType) {
            // Get the first goal of this type (or aggregate if multiple)
            $goal = $goals->where('goal_type', $goalType)->first();
            
            if ($goal) {
                $target = $goal->target_value ?? 0;
                $progress = $goal->progress_value ?? 0;
                $percentage = $target > 0 ? min(100, ($progress / $target) * 100) : 0;

                // Format value and unit
                $value = $progress;
                $unit = '';
                
                // Determine unit based on value size
                if ($value >= 1000) {
                    $value = $value / 1000;
                    $unit = 'Gb';
                } else {
                    $unit = 'Mb';
                }

                // Format value to show appropriate decimals
                if ($value >= 100) {
                    $formattedValue = number_format($value, 0);
                } else {
                    $formattedValue = number_format($value, 1);
                }

                // Map goal type to display label
                $label = $this->getGoalLabel($goalType);

                $formattedGoals[] = [
                    'value' => $formattedValue,
                    'unit' => $unit,
                    'label' => $label,
                    'percentage' => round($percentage, 0),
                    'color' => $colors[$colorIndex % count($colors)],
                    'isActive' => $percentage >= 75, // Active if >= 75%
                ];

                $colorIndex++;
            }
        }

        // If we have less than 4 goals, fill with empty placeholders or use team-level aggregated goals
        while (count($formattedGoals) < 4) {
            // Try to get team-level goals (without user_id)
            $teamGoals = AnalyticsGoal::where('team_id', $teamId)
                ->where('month', $month)
                ->where('year', $year)
                ->whereNull('user_id')
                ->get();

            if ($teamGoals->isNotEmpty() && count($formattedGoals) < 4) {
                $remainingTypes = $teamGoals->pluck('goal_type')->unique()->take(4 - count($formattedGoals));
                
                foreach ($remainingTypes as $goalType) {
                    if (count($formattedGoals) >= 4) break;
                    
                    $goal = $teamGoals->where('goal_type', $goalType)->first();
                    if ($goal) {
                        $target = $goal->target_value ?? 0;
                        $progress = $goal->progress_value ?? 0;
                        $percentage = $target > 0 ? min(100, ($progress / $target) * 100) : 0;

                        $value = $progress;
                        $unit = '';
                        
                        if ($value >= 1000) {
                            $value = $value / 1000;
                            $unit = 'Gb';
                        } else {
                            $unit = 'Mb';
                        }

                        if ($value >= 100) {
                            $formattedValue = number_format($value, 0);
                        } else {
                            $formattedValue = number_format($value, 1);
                        }

                        $formattedGoals[] = [
                            'value' => $formattedValue,
                            'unit' => $unit,
                            'label' => $this->getGoalLabel($goalType),
                            'percentage' => round($percentage, 0),
                            'color' => $colors[$colorIndex % count($colors)],
                            'isActive' => $percentage >= 75,
                        ];

                        $colorIndex++;
                    }
                }
            }

            // If still not enough, break to avoid infinite loop
            if (count($formattedGoals) < 4 && $teamGoals->isEmpty()) {
                break;
            }
        }

        return $formattedGoals;
    }

    protected function getGoalLabel(string $goalType): string
    {
        return match($goalType) {
            'lead_generation' => 'Lead Generation',
            'conversion' => 'Conversion',
            'open_leads' => 'Open Leads',
            'general' => 'General Goals',
            default => ucfirst(str_replace('_', ' ', $goalType)),
        };
    }
}
