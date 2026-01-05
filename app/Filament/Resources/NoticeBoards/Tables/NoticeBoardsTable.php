<?php

namespace App\Filament\Resources\NoticeBoards\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class NoticeBoardsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                TextColumn::make('description')
                    ->label('Description')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->description)
                    ->searchable(),
                TextColumn::make('team.name')
                    ->label('Team')
                    ->default('All Teams')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'warning' => 'draft',
                        'success' => 'published',
                    ])
                    ->searchable()
                    ->sortable(),
                TextColumn::make('published_at')
                    ->label('Published At')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('expire_at')
                    ->label('Expires At')
                    ->dateTime()
                    ->sortable()
                    ->default('Never'),
                IconColumn::make('notify')
                    ->label('Notify')
                    ->boolean()
                    ->sortable(),
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
                EditAction::make()
                    ->modalHeading('Edit Notice')
                    ->modalSubmitActionLabel('Save')
                    ->slideOver()
                    ->mutateFormDataUsing(function (array $data, $record): array {
                        // Preserve workspace_id - don't allow it to be changed
                        $data['workspace_id'] = $record->workspace_id;
                        
                        return $data;
                    }),
            ])
            ->bulkActions([]);
    }
}

