<?php

namespace App\Filament\Resources\LeadKanbans\Tables;

use App\Models\LeadKanban;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LeadKanbansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount('leads'))
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
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
                IconColumn::make('is_system')
                    ->label('System')
                    ->boolean()
                    ->sortable(),
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
                        ->modalHeading('Edit Lead Kanban')
                        ->modalSubmitActionLabel('Save')
                        ->slideOver(),
                    DeleteAction::make()
                        ->requiresConfirmation()
                        ->visible(fn (LeadKanban $record) => !$record->is_system)
                        ->action(function (LeadKanban $record) {
                            if ($record->is_system) {
                                Notification::make()
                                    ->title('Cannot delete system stage')
                                    ->body('System kanban stages cannot be removed.')
                                    ->danger()
                                    ->send();

                                return;
                            }

                            if ($record->leads()->exists()) {
                                Notification::make()
                                    ->title('Cannot delete kanban stage')
                                    ->body('This stage is assigned to one or more leads.')
                                    ->danger()
                                    ->send();

                                return;
                            }

                            $record->delete();

                            Notification::make()
                                ->title('Kanban stage deleted')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->bulkActions([]);
    }
}
