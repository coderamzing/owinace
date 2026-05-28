<?php

namespace App\Filament\Resources\Contacts\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Traits\HasPermission;

class ContactsTable
{
    use HasPermission;
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('avatar')
                    ->label('')
                    ->getStateUsing(fn ($record) => $record->avatar_url)
                    ->circular()
                    ->size(40),

                TextColumn::make('name')
                    ->label('Name')
                    ->getStateUsing(function ($record) {
                        $name = trim(($record->first_name ?? '') . ' ' . ($record->last_name ?? ''));
                        return $name ?: 'N/A';
                    })
                    ->color('primary')
                    ->weight('bold')
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
                
                TextColumn::make('website')
                    ->label('Website')
                    ->searchable()
                    ->url(fn ($record) => $record->website ? (str_starts_with($record->website, 'http') ? $record->website : 'https://' . $record->website) : null)
                    ->openUrlInNewTab()
                    ->placeholder('N/A')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('linkedin_url')
                    ->label('LinkedIn')
                    ->searchable()
                    ->url(fn ($record) => $record->linkedin_url ? (str_starts_with($record->linkedin_url, 'http') ? $record->linkedin_url : 'https://' . $record->linkedin_url) : null)
                    ->openUrlInNewTab()
                    ->placeholder('N/A')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('personalized_message')
                    ->label('Personalized Message')
                    ->searchable()
                    ->limit(40)
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
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->modalHeading('Edit Contact')
                    ->modalSubmitActionLabel('Save')
                    ->slideOver()
                    ->visible(fn ($record) => self::hasPermissionTo('contact.edit'))
                    ->mutateFormDataUsing(function (array $data, $record): array {
                        $teamId = session('team_id');

                        if ($teamId) {
                            $data['team_id'] = $teamId;
                        } elseif ($record?->team_id) {
                            $data['team_id'] = $record->team_id;
                        }

                        return $data;
                    }),
                DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Delete Contact')
                    ->modalDescription('This will permanently remove the contact.')
                    ->modalSubmitActionLabel('Delete')
                    ->visible(fn ($record) => self::hasPermissionTo('contact.delete'))
                    ->color('danger'),
            ]);
    }
}
