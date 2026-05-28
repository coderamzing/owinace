<?php

namespace App\Filament\Widgets;

use App\Models\AiInsight;
use App\Models\Team;
use Carbon\Carbon;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Session;

class MonthlyAiInsightsWidget extends Widget
{
    protected string $view = 'filament.widgets.monthly-ai-insights-widget';

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

        $monthStart = Carbon::now()->startOfMonth();

        $aiInsight = AiInsight::where('team_id', $teamId)
            ->where('year', (int) $monthStart->year)
            ->where('month', (int) $monthStart->month)
            ->first();

        if (! $aiInsight) {
            $this->insight = null;
            return;
        }

        $this->insight = [
            'team_name' => $team->name,
            'period_label' => $monthStart->format('M Y'),
            'summary' => $aiInsight->summary,
            'highlights' => $aiInsight->highlights ?? [],
            'recommendations' => $aiInsight->recommendations ?? [],
        ];
    }
}

