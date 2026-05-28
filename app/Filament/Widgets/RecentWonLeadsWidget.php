<?php

namespace App\Filament\Widgets;

use App\Models\Lead;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class RecentWonLeadsWidget extends Widget
{
    protected static ?int $sort = 8;

    protected int | string | array $columnSpan = 6;

    protected string $view = 'filament.widgets.recent-won-leads';

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getLeads(): array
    {
        $teamId = Session::get('team_id');

        if (! $teamId) {
            return [];
        }

        $leads = Lead::query()
            ->where('team_id', $teamId)
            ->whereHas('kanban', function ($query) {
                $query->where('code', 'WON');
            })
            ->with(['assignedMember'])
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();

        return $leads->map(function (Lead $lead) {
            $name = $lead->assignedMember?->name ?? '—';
            $initials = Str::of($lead->title)->trim()->substr(0, 1)->upper()->toString();

            return [
                'id' => $lead->id,
                'title' => $lead->title,
                'subtitle' => $name,
                'amount' => (float) ($lead->actual_value ?? 0),
                'date' => optional($lead->created_at)->format('M d, Y'),
                'initials' => $initials,
            ];
        })->toArray();
    }
}

