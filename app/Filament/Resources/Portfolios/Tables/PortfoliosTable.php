<?php

namespace App\Filament\Resources\Portfolios\Tables;

use App\Filament\Resources\Portfolios\Pages\ListPortfolios;
use App\Models\Portfolio;
use App\Traits\HasPermission;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TagsColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PortfoliosTable
{
    use HasPermission;

    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                ImageColumn::make('avatar')
                    ->label('')
                    ->getStateUsing(fn ($record) => $record->avatar_url)
                    ->circular()
                    ->size(40),
                TextColumn::make('title')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function (Builder $q) use ($search): void {
                            $q->where('title', 'like', "%{$search}%")
                                ->orWhere('keywords', 'like', "%{$search}%")
                                ->orWhere('url', 'like', "%{$search}%");
                        });
                    })
                    ->color('primary')
                    ->weight('bold')
                    ->sortable(),
                TextColumn::make('url')
                    ->label('URL')
                    ->limit(40)
                    ->url(fn (Portfolio $record): ?string => $record->url)
                    ->openUrlInNewTab()
                    ->toggleable(),
                TagsColumn::make('keywords')
                    ->label('Keywords')
                    ->limit(5)
                    ->separator(',')
                    ->badge()
                    ->color(fn (string $state): string => collect([
                        'primary',
                        'success',
                        'warning',
                        'info',
                        'danger',
                        'gray',
                    ])->random()),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('pinged_at')
                    ->label('Last ping')
                    ->dateTime()
                    ->placeholder('Never')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                    ->before(function (array $data, Portfolio $record): void {
                        ListPortfolios::assertUrlOnSave($data, $record);
                    })
                    ->after(function (Portfolio $record): void {
                        ListPortfolios::storeUrlPingTimestamp($record);
                    })
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
