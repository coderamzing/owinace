<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Leads\LeadResource;
use App\Models\Lead;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Session;

class RecentWonLeadsWidget extends BaseWidget
{
    protected static ?string $heading = 'Recent Won Leads';

    protected static ?int $sort = 8;

    protected int | string | array $columnSpan = 6;

    public function table(Table $table): Table
    {
        $teamId = Session::get('team_id');

        if (! $teamId) {
            return $table->query(Lead::query()->whereRaw('1 = 0'));
        }

        return $table
            ->query(
                Lead::query()
                    ->where('team_id', $teamId)
                    ->whereHas('kanban', function ($query) {
                        $query->where('code', 'WON');
                    })
                    ->with(['source', 'kanban', 'assignedMember'])
                    ->orderBy('created_at', 'desc')
                    ->limit(8)
            )
            ->columns([
                TextColumn::make('title')
                    ->label('Title')
                    ->sortable()
                    ->limit(20)
                    ->url(fn (Lead $record) => LeadResource::getUrl('view', ['record' => $record]))
                    ->openUrlInNewTab(),

                TextColumn::make('source.name')
                    ->label('Source')
                    ->badge()
                    ->color('primary'),

                TextColumn::make('assignedMember.name')
                    ->label('Assigned To')
                    ->placeholder('Unassigned'),

                TextColumn::make('actual_value')
                    ->label('Actual Value')
                    ->money('USD')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated(false);
    }
}

