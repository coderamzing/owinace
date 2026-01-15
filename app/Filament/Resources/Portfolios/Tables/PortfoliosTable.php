<?php

namespace App\Filament\Resources\Portfolios\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Traits\HasPermission;

class PortfoliosTable
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
                TextColumn::make('title')
                    ->searchable()
                    ->color('primary')
                    ->weight('bold')
                    ->sortable(),
                TextColumn::make('keywords')
                    ->limit(50)
                    ->searchable(),
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
                    ->modalHeading('Edit Portfolio')
                    ->modalSubmitActionLabel('Save')
                    ->slideOver()
                    ->visible(fn ($record) => self::hasPermissionTo('portfolio.edit')),
                DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Delete Portfolio')
                    ->modalDescription('This will permanently remove the portfolio.')
                    ->modalSubmitActionLabel('Delete')
                    ->visible(fn ($record) => self::hasPermissionTo('portfolio.delete'))
                    ->color('danger'),
            ])
            ->bulkActions([]);
    }
}

