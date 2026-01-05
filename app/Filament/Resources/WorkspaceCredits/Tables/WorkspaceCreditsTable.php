<?php

namespace App\Filament\Resources\WorkspaceCredits\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WorkspaceCreditsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                // Filter by current workspace if session has workspace_id
                $workspaceId = session('workspace_id');
                $query->where('workspace_id', $workspaceId);
                // Order by most recent first
                $query->orderBy('created_at', 'desc');
            })
            ->columns([
                TextColumn::make('transaction_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'credit' => 'success',
                        'debit' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('credits')
                    ->label('Credits')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn ($state, $record) => $record->transaction_type === 'debit' ? '-' . abs($state) : '+' . abs($state))
                    ->color(fn ($record): string => $record->transaction_type === 'debit' ? 'danger' : 'success'),
                TextColumn::make('transaction_id')
                    ->label('Transaction ID')
                    ->toggleable()
                    ->limit(20),
                TextColumn::make('note')
                    ->label('Note')
                    ->limit(50)
                    ->wrap(),
                TextColumn::make('triggeredBy.name')
                    ->label('Triggered By')
                    ->sortable()
                    ->default('System'),
                TextColumn::make('workspace.name')
                    ->label('Workspace')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->description(fn ($record) => $record->created_at->format('M d, Y h:i A')),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                // No actions - read-only
            ])
            ->bulkActions([
                // No bulk actions - read-only
            ])
            ->defaultSort('created_at', 'desc');
    }
}

