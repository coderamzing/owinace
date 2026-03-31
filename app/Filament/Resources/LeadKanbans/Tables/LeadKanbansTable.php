<?php

namespace App\Filament\Resources\LeadKanbans\Tables;

use App\Models\LeadKanban;
use Filament\Actions\ActionGroup;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table as FilamentTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Validation\ValidationException;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class LeadKanbansTable
{
    public static function configure(FilamentTable $table): FilamentTable
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount('leads'))
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->color('primary')
                    ->weight('bold')
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
                DateRangeFilter::make('created_at')
                    ->label('Created Between'),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->modalHeading('Edit Lead Kanban')
                        ->modalSubmitActionLabel('Save')
                        ->slideOver()
                        ->mutateFormDataUsing(function (array $data, LeadKanban $record): array {
                            $data['is_system'] = $record->is_system;

                            return $data;
                        })
                        ->using(function (array $data, HasActions & HasSchemas $livewire, Model $record, ?FilamentTable $table): void {
                            try {
                                $record->update($data);
                            } catch (UniqueConstraintViolationException) {
                                throw ValidationException::withMessages([
                                    'code' => 'A kanban stage with this code already exists for this team. Choose a different code.',
                                ]);
                            }
                        }),
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
