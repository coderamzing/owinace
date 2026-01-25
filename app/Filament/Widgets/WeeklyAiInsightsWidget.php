<?php

namespace App\Filament\Widgets;

use App\Models\AiInsight;
use App\Models\Team;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class WeeklyAiInsightsWidget extends Widget
{
    protected string $view = 'filament.widgets.weekly-ai-insights-widget';

    protected int | string | array $columnSpan = 12;

    protected static ?int $sort = 1;

    public ?array $insight = null;

    public function mount(): void
    {
        $teamId = Session::get('team_id');

        if (! $teamId) {
            $this->insight = null;
            return;
        }

        $team = Team::find($teamId);

        if (! $team) {
            $this->insight = null;
            return;
        }

        // Show the previous full week (same as the command)
        $weekStart = Carbon::now()->subWeek()->startOfWeek(Carbon::MONDAY);
        $year = (int) $weekStart->year;
        $week = (int) $weekStart->isoWeek;
        $weekKey = sprintf('%d-W%02d', $year, $week);

        $aiInsight = AiInsight::where('team_id', $teamId)
            ->where('week_key', $weekKey)
            ->first();

        if (! $aiInsight) {
            $this->insight = null;
            return;
        }

        $this->insight = [
            'team_name' => $team->name,
            'period_label' => sprintf(
                '%s – %s',
                $weekStart->format('M d, Y'),
                $weekStart->copy()->endOfWeek(Carbon::SUNDAY)->format('M d, Y')
            ),
            'summary' => $aiInsight->summary,
            'highlights' => $aiInsight->highlights ?? [],
            'recommendations' => $aiInsight->recommendations ?? [],
        ];
    }
}

