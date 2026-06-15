<?php

namespace App\Filament\Resources\UpworkCampaigns\RelationManagers;

use App\Filament\Resources\UpworkCampaigns\Pages\EditUpworkCampaign;
use App\Filament\Resources\UpworkCampaigns\Pages\ViewUpworkCampaign;
use App\Filament\Tables\PortfolioAttachTable;
use Filament\Actions\Action;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TagsColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class LinkedPortfoliosRelationManager extends RelationManager
{
    protected static string $relationship = 'linkedPortfolios';

    protected static ?string $title = 'Portfolios';

    protected static bool $isLazy = false;

    protected static bool $shouldSkipAuthorization = true;

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return in_array($pageClass, [
            EditUpworkCampaign::class,
            ViewUpworkCampaign::class,
        ], true);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->heading(null)
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10)
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
                    ->limit(120)
                    ->wrap()
                    ->toggleable(),
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('Add portfolio')
                    ->modalHeading('Add portfolios')
                    ->modalSubmitActionLabel('Attach')
                    ->modalWidth(Width::FiveExtraLarge)
                    ->multiple()
                    ->preloadRecordSelect()
                    ->tableSelect(PortfolioAttachTable::class)
                    ->recordSelectSearchColumns(['title', 'description'])
                    ->recordSelectOptionsQuery(function (Builder $query): Builder {
                        $teamId = session('team_id');
                        if ($teamId) {
                            $query->where('team_id', $teamId);
                        }

                        return $query
                            ->where('is_active', true)
                            ->orderBy('sort_order');
                    })
                    ->visible(fn (): bool => $this->pageClass === EditUpworkCampaign::class),
                Action::make('detachAll')
                    ->label('Detach all')
                    ->color('danger')
                    ->icon('heroicon-o-x-mark')
                    ->requiresConfirmation()
                    ->modalHeading('Detach all portfolios?')
                    ->modalDescription('Remove every portfolio linked to this campaign. You can attach them again later.')
                    ->modalSubmitActionLabel('Detach all')
                    ->visible(fn (): bool => $this->pageClass === EditUpworkCampaign::class
                        && $this->getOwnerRecord()->linkedPortfolios()->exists())
                    ->action(function (): void {
                        $this->getOwnerRecord()->linkedPortfolios()->detach();

                        Notification::make()
                            ->title('All portfolios detached')
                            ->success()
                            ->send();
                    }),
            ])
            ->recordActions([
                DetachAction::make()
                    ->label('Remove')
                    ->visible(fn (): bool => $this->pageClass === EditUpworkCampaign::class),
            ])
            ->toolbarActions([]);
    }
}
