<?php

namespace App\Filament\Tables;

use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TagsColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PortfolioAttachTable
{
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
                    ->weight('bold')
                    ->wrap(),
                TagsColumn::make('keywords')
                    ->label('Keywords')
                    ->limit(5)
                    ->separator(',')
                    ->badge(),
                TextColumn::make('description')
                    ->label('Description')
                    ->limit(100)
                    ->wrap()
                    ->searchable(),
            ]);
    }
}
