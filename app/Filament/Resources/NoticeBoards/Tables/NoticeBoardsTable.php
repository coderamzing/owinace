<?php

namespace App\Filament\Resources\NoticeBoards\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class NoticeBoardsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // Custom row/card view for each notice
                ViewColumn::make('notice_card')
                    ->label('')
                    ->view('filament.resources.notice-boards.table.notice-card'),
                // Hidden helper columns keep search/sort working
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('description')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('team.name')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('published_at')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('expire_at')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('notify')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('View')
                    ->modalHeading(fn ($record) => $record->title)
                    ->modalContent(fn ($record) => view('filament.resources.notice-boards.table.view-notice', [
                        'record' => $record,
                    ]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),
                EditAction::make()
                    ->modalHeading('Edit Notice')
                    ->modalSubmitActionLabel('Save')
                    ->slideOver()
                    ->mutateFormDataUsing(function (array $data, $record): array {
                        // Preserve workspace_id - don't allow it to be changed
                        $data['workspace_id'] = $record->workspace_id;
                        
                        return $data;
                    }),
                DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Delete Notice')
                    ->modalDescription('This action will permanently remove this notice.')
                    ->modalSubmitActionLabel('Delete')
                    ->color('danger'),
            ])
            ->bulkActions([]);
    }
}

