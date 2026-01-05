<?php

namespace App\Filament\Resources\Proposals\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Traits\HasPermission;

class ProposalsTable
{
    use HasPermission;
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('keywords')
                    ->limit(50)
                    ->searchable(),
                TextColumn::make('sort_order')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->modalHeading('Edit Proposal')
                    ->modalSubmitActionLabel('Save')
                    ->slideOver()
                    ->visible(fn ($record) => self::hasPermissionTo('proposal.edit')),
                DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Delete Proposal')
                    ->modalDescription('This will permanently remove the proposal.')
                    ->modalSubmitActionLabel('Delete')
                    ->visible(fn ($record) => self::hasPermissionTo('proposal.delete'))
                    ->color('danger'),
            ])
            ->bulkActions([]);
    }
}

