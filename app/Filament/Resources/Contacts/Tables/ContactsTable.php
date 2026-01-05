<?php

namespace App\Filament\Resources\Contacts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ContactsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->getStateUsing(function ($record) {
                        $name = trim(($record->first_name ?? '') . ' ' . ($record->last_name ?? ''));
                        return $name ?: 'N/A';
                    })
                    ->searchable(query: function ($query, $search) {
                        $query->where(function ($q) use ($search) {
                            $q->where('first_name', 'like', "%{$search}%")
                              ->orWhere('last_name', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(),
                
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->placeholder('N/A'),
                
                TextColumn::make('phone_number')
                    ->label('Phone')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->placeholder('N/A'),
                
                TextColumn::make('company')
                    ->label('Company')
                    ->searchable()
                    ->sortable()
                    ->placeholder('N/A'),
                
                TextColumn::make('job_title')
                    ->label('Job Title')
                    ->searchable()
                    ->sortable()
                    ->placeholder('N/A'),
                
                TextColumn::make('website')
                    ->label('Website')
                    ->searchable()
                    ->url(fn ($record) => $record->website ? (str_starts_with($record->website, 'http') ? $record->website : 'https://' . $record->website) : null)
                    ->openUrlInNewTab()
                    ->placeholder('N/A')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->modalHeading('Edit Contact')
                    ->modalSubmitActionLabel('Save')
                    ->slideOver()
                    ->mutateFormDataUsing(function (array $data, $record): array {
                        $teamId = session('team_id');

                        if ($teamId) {
                            $data['team_id'] = $teamId;
                        } elseif ($record?->team_id) {
                            $data['team_id'] = $record->team_id;
                        }

                        return $data;
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
