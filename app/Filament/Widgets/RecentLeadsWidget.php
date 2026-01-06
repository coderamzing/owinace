<?php

namespace App\Filament\Widgets;

use App\Models\Lead;
use App\Models\LeadKanban;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class RecentLeadsWidget extends BaseWidget
{
    protected static ?string $heading = 'Recent Leads';

    protected static ?int $sort = 7;

    protected int | string | array $columnSpan = 6;

    public function table(Table $table): Table
    {
        $teamId = Session::get('team_id');

        if (!$teamId) {
            return $table->query(Lead::query()->whereRaw('1 = 0'));
        }

        return $table
            ->query(
                Lead::query()
                    ->where('team_id', $teamId)
                    ->with(['source', 'kanban', 'assignedMember'])
                    ->orderBy('created_at', 'desc')
                    ->limit(8)
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Lead Name')
                    ->sortable(),

                TextColumn::make('source.name')
                    ->label('Source')
                    ->badge()
                    ->color('primary'),

                TextColumn::make('assignedMember.name')
                    ->label('Assigned To')
                    ->placeholder('Unassigned'),

                TextColumn::make('expected_value')
                    ->label('Value')
                    ->money('USD')
                    ->sortable(),

                BadgeColumn::make('kanban.name')
                    ->label('Stage')
                    ->getStateUsing(fn (Lead $record) => $record->kanban?->name ?? 'Unknown')
                    ->color(function (Lead $record) {
                        $code = $record->kanban?->code;
                        return match (strtoupper($code ?? '')) {
                            'WON' => 'success',
                            'LOST' => 'danger',
                            'OPEN' => 'warning',
                            default => 'gray',
                        };
                    }),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M j, Y g:i A')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated(false);
    }
}
