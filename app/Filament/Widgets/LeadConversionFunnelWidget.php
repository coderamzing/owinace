<?php

namespace App\Filament\Widgets;

use App\Models\Lead;
use App\Models\LeadKanban;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class LeadConversionFunnelWidget extends Widget
{
    protected int | string | array $columnSpan = 6;

    protected static ?int $sort = 9;

    protected string $view = 'filament.widgets.lead-conversion-funnel';

    /**
     * @return array<int, array{label: string, count: int, percent: float, color: string}>
     */
    public function getStages(): array
    {
        $teamId = Session::get('team_id');

        if (!$teamId) {
            return [];
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

        $stages = [];
        $firstCount = null;

        foreach ($kanbans as $kanban) {
            $count = Lead::where('team_id', $teamId)
                ->where('kanban_id', $kanban->id)
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->count();

            if ($count > 0) {
                if ($firstCount === null) {
                    $firstCount = $count;
                }

                $stages[] = [
                    'label' => (string) $kanban->name,
                    'count' => (int) $count,
                    'percent' => $firstCount > 0 ? round(((int) $count / (int) $firstCount) * 100, 0) : 0.0,
                    'color' => (string) ($kanban->color ?? '#10b981'),
                ];
            }
        }

        return $stages;
    }

    public function getConversionRate(): float
    {
        $stages = $this->getStages();
        if (count($stages) < 2) {
            return 0.0;
        }

        $first = (int) ($stages[0]['count'] ?? 0);
        $last = (int) ($stages[array_key_last($stages)]['count'] ?? 0);

        return $first > 0 ? round(($last / $first) * 100, 0) : 0.0;
    }

    public function getAvgSalesCycleDays(): int
    {
        $teamId = Session::get('team_id');
        if (! $teamId) {
            return 0;
        }

        $selectedPeriod = Session::get('analytics_period') ?? Carbon::now()->format('Y-m');
        $month = (int) Carbon::parse($selectedPeriod . '-01')->month;
        $year = (int) Carbon::parse($selectedPeriod . '-01')->year;

        $wonKanban = LeadKanban::where('team_id', $teamId)
            ->where('code', 'WON')
            ->first();

        if (! $wonKanban) {
            return 0;
        }

        $wonLeads = Lead::query()
            ->where('team_id', $teamId)
            ->where('kanban_id', $wonKanban->id)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->whereNotNull('conversion_date')
            ->get(['created_at', 'conversion_date']);

        if ($wonLeads->isEmpty()) {
            return 0;
        }

        $avg = $wonLeads
            ->map(fn ($lead) => Carbon::parse($lead->created_at)->diffInDays(Carbon::parse($lead->conversion_date)))
            ->avg();

        return (int) round((float) $avg);
    }
}
