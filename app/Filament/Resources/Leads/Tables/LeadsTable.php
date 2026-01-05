<?php

namespace App\Filament\Resources\Leads\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LeadsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('kanban.name')
                    ->label('Status')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('source.name')
                    ->label('Source')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('assignedMember.name')
                    ->label('Assigned To')
                    ->searchable()
                    ->sortable()
                    ->default('Unassigned'),
                TextColumn::make('expected_value')
                    ->label('Expected Value')
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('actual_value')
                    ->label('Actual Value')
                    ->money('USD')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('next_follow_up')
                    ->label('Next Follow Up')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                IconColumn::make('is_archived')
                    ->label('Archived')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('createdBy.name')
                    ->label('Created By')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                \Filament\Actions\ViewAction::make(),
                EditAction::make()
                    ->modalHeading('Edit Lead')
                    ->modalSubmitActionLabel('Save')
                    ->slideOver(),
            ]);
    }
}
