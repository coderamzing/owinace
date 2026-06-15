<?php

namespace App\Filament\Resources\UpworkCampaigns\Tables;

use App\Filament\Resources\UpworkCampaigns\UpworkCampaignResource;
use App\Models\UpworkCampaign;
use App\Models\UpworkProfile;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UpworkCampaignsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                ViewColumn::make('is_active')
                    ->label('Active')
                    ->view('filament.resources.upwork-campaigns.table.active-toggle')
                    ->action(self::toggleActiveAction()),
                TextColumn::make('search_url')
                    ->label('Search URL')
                    ->limit(40)
                    ->tooltip(fn ($state) => $state)
                    ->toggleable(),
                IconColumn::make('auto_bidding')
                    ->label('Auto bid')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('profile.title')
                    ->label('Profile')
                    ->placeholder('—')
                    ->toggleable(),
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
                SelectFilter::make('profile_id')
                    ->label('Profile')
                    ->placeholder('All profiles')
                    ->options(function (): array {
                        $teamId = session('team_id');
                        if (! $teamId || $teamId < 1) {
                            return [];
                        }

                        return UpworkProfile::query()
                            ->where('team_id', $teamId)
                            ->orderBy('title')
                            ->pluck('title', 'id')
                            ->all();
                    })
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    self::cloneAction(),
                    DeleteAction::make(),
                ]),
            ])
            ->bulkActions([]);
    }

    protected static function cloneAction(): Action
    {
        return Action::make('clone')
            ->label('Clone')
            ->icon('heroicon-o-document-duplicate')
            ->requiresConfirmation()
            ->modalHeading('Clone campaign?')
            ->modalDescription(fn (UpworkCampaign $record): string => "Create a copy of \"{$record->title}\"? The clone will start inactive.")
            ->modalSubmitActionLabel('Clone')
            ->action(function (UpworkCampaign $record): void {
                $clone = $record->cloneAsCopy();

                Notification::make()
                    ->title('Campaign cloned')
                    ->body("\"{$clone->title}\" was created.")
                    ->success()
                    ->send();

                redirect(UpworkCampaignResource::getUrl('edit', ['record' => $clone]));
            });
    }

    protected static function toggleActiveAction(): Action
    {
        return Action::make('toggleCampaignActive')
            ->requiresConfirmation()
            ->modalIcon(fn (UpworkCampaign $record): string => $record->is_active
                ? 'heroicon-o-pause-circle'
                : 'heroicon-o-play-circle')
            ->modalHeading(fn (UpworkCampaign $record): string => $record->is_active
                ? 'Disable campaign?'
                : 'Enable campaign?')
            ->modalDescription(fn (UpworkCampaign $record): string => $record->is_active
                ? "The bot will stop searching and bidding for \"{$record->title}\"."
                : "The bot will start searching and bidding for \"{$record->title}\".")
            ->modalSubmitActionLabel(fn (UpworkCampaign $record): string => $record->is_active
                ? 'Disable'
                : 'Enable')
            ->color(fn (UpworkCampaign $record): string => $record->is_active ? 'danger' : 'success')
            ->action(function (UpworkCampaign $record): void {
                $wasActive = $record->is_active;

                $record->update([
                    'is_active' => ! $wasActive,
                ]);

                Notification::make()
                    ->title($wasActive ? 'Campaign disabled' : 'Campaign enabled')
                    ->body($wasActive
                        ? "\"{$record->title}\" is no longer active."
                        : "\"{$record->title}\" is now active.")
                    ->success()
                    ->send();
            });
    }
}
