<?php

namespace App\Filament\Resources\UpworkCampaigns\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class UpworkCampaignJobStatsRelationManager extends RelationManager
{
    protected static string $relationship = 'campaignJobStats';

    protected static ?string $title = 'Campaign job stats';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['job']))
            ->defaultSort('updated_at', 'desc')
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->columns([
                TextColumn::make('job.title')
                    ->label('Job')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('job', function (Builder $q) use ($search): void {
                            $q->where('title', 'like', '%'.$search.'%');
                        });
                    })
                    ->description(fn ($record) => $record->job?->uid)
                    ->url(fn ($record): ?string => $record->job?->url)
                    ->openUrlInNewTab()
                    ->wrap(),
                IconColumn::make('is_matched')
                    ->label('Matched')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                IconColumn::make('is_applied')
                    ->label('Applied')
                    ->boolean()
                    ->trueIcon('heroicon-o-paper-airplane')
                    ->falseIcon('heroicon-o-minus-circle')
                    ->trueColor('info')
                    ->falseColor('gray'),
                TextColumn::make('job.posted_at')
                    ->label('Posted')
                    ->dateTime()
                    ->placeholder('—'),
                TextColumn::make('note')
                    ->placeholder('—')
                    ->limit(40)
                    ->tooltip(fn ($state) => filled($state) ? (string) $state : null)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_matched')
                    ->label('Match status')
                    ->trueLabel('Matched')
                    ->falseLabel('Not matched')
                    ->placeholder('All jobs'),
                TernaryFilter::make('is_applied')
                    ->label('Applied')
                    ->trueLabel('Applied')
                    ->falseLabel('Not applied')
                    ->placeholder('All'),
            ])
            ->headerActions([])
            ->recordActions([])
            ->bulkActions([]);
    }
}
