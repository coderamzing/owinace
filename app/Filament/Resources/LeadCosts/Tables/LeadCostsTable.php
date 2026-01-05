<?php

namespace App\Filament\Resources\LeadCosts\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LeadCostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('monthly_cost')
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('source.name')
                    ->searchable()
                    ->sortable()
                    ->placeholder('N/A'),
                TextColumn::make('member.name')
                    ->searchable()
                    ->sortable()
                    ->placeholder('N/A'),
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
                ActionGroup::make([
                    EditAction::make()
                        ->modalHeading('Edit Lead Cost')
                        ->modalSubmitActionLabel('Save')
                        ->slideOver(),
                    DeleteAction::make()
                        ->requiresConfirmation(),
                ]),
            ])
            ->bulkActions([]);
    }
}
