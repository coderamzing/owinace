<?php

namespace App\Filament\Resources\LeadGoals\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LeadGoalsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('goal_type')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'lead_generation' => 'Lead Generation Goal',
                        'conversion' => 'Conversion Goal',
                        'open_leads' => 'Open Leads Goal',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'lead_generation' => 'info',
                        'conversion' => 'success',
                        'open_leads' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('period')
                    ->searchable()
                    ->sortable()
                    ->badge(),
                TextColumn::make('target_value')
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('current_value')
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('progress')
                    ->label('Progress')
                    ->getStateUsing(function ($record) {
                        if ($record->target_value == 0) {
                            return '0%';
                        }
                        $percentage = ($record->current_value / $record->target_value) * 100;
                        return number_format($percentage, 1) . '%';
                    })
                    ->badge()
                    ->color(function ($state, $record) {
                        if ($record->target_value == 0) {
                            return 'gray';
                        }
                        $percentage = ($record->current_value / $record->target_value) * 100;
                        return match (true) {
                            $percentage >= 100 => 'success',
                            $percentage >= 75 => 'warning',
                            default => 'danger',
                        };
                    }),
                TextColumn::make('member.name')
                    ->searchable()
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
                    ->modalHeading('Edit Lead Goal')
                    ->modalSubmitActionLabel('Save')
                    ->slideOver(),
            ])
            ->bulkActions([]);
    }
}
