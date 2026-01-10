<?php

namespace App\Filament\Resources\WorkspaceCredits\Tables;

use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Enums\FiltersResetActionPosition;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
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
                Filter::make('search')
                    ->label('Full Text')
                    ->form([
                        TextInput::make('value')
                            ->placeholder('Search note, transaction ID, triggered by')
                            ->autofocus(),
                    ])
                    ->query(function ($query, array $data) {
                        $value = $data['value'] ?? null;

                        if (!$value) {
                            return $query;
                        }

                        return $query->where(function ($q) use ($value) {
                            $q->where('note', 'like', '%' . $value . '%')
                                ->orWhere('transaction_id', 'like', '%' . $value . '%')
                                ->orWhereHas('triggeredBy', function ($subQuery) use ($value) {
                                    $subQuery->where('name', 'like', '%' . $value . '%')
                                        ->orWhere('email', 'like', '%' . $value . '%');
                                });
                        });
                    })
                    ->indicateUsing(function (array $data): ?string {
                        return filled($data['value'] ?? null)
                            ? 'Search: ' . $data['value']
                            : null;
                    }),
                SelectFilter::make('transaction_type')
                    ->label('')
                    ->placeholder('Type')
                    ->options([
                        'credit' => 'Credit',
                        'debit' => 'Debit',
                    ])
                    ->preload(),
            ])
            ->filtersLayout(FiltersLayout::AboveContent)
            ->persistFiltersInSession()
            ->filtersFormColumns(2)
            ->filtersResetActionPosition(FiltersResetActionPosition::Footer)
            ->recordActions([
                // No actions - read-only
            ])
            ->bulkActions([
                // No bulk actions - read-only
            ])
            ->defaultSort('created_at', 'desc');
    }
}

