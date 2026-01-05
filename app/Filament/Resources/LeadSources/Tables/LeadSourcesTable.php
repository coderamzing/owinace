<?php

namespace App\Filament\Resources\LeadSources\Tables;

use App\Models\LeadSource;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LeadSourcesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount('leads'))
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('color')
                    ->searchable(),
                TextColumn::make('leads_count')
                    ->label('Leads')
                    ->counts('leads')
                    ->badge()
                    ->sortable(),
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
                ActionGroup::make([
                    EditAction::make()
                        ->modalHeading('Edit Lead Source')
                        ->modalSubmitActionLabel('Save')
                        ->slideOver(),
                    DeleteAction::make()
                        ->requiresConfirmation()
                        ->action(function (LeadSource $record) {
                            if ($record->leads()->exists()) {
                                Notification::make()
                                    ->title('Cannot delete lead source')
                                    ->body('This source is assigned to one or more leads.')
                                    ->danger()
                                    ->send();

                                return;
                            }

                            $record->delete();

                            Notification::make()
                                ->title('Lead source deleted')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->bulkActions([]);
    }
}
