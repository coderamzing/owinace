<?php

namespace App\Filament\Resources\UpworkProfiles\Tables;

use App\Filament\Resources\UpworkProfiles\Pages\ListUpworkProfiles;
use App\Models\UpworkProfile;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UpworkProfilesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('code')
                    ->label('Bot code')
                    ->copyable()
                    ->copyMessage('Code copied')
                    ->fontFamily('mono')
                    ->sortable(),
                TextColumn::make('proxy_host')
                    ->label('Proxy host')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('proxy_protocol')
                    ->label('Protocol')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => strtoupper($state ?: 'http'))
                    ->color(fn (?string $state): string => ($state ?: 'http') === 'socks5' ? 'info' : 'gray')
                    ->toggleable(),
                TextColumn::make('proxy_last_ip')
                    ->label('Proxy IP')
                    ->placeholder('—')
                    ->fontFamily('mono')
                    ->toggleable(),
                IconColumn::make('proxy_validated_at')
                    ->label('Proxy OK')
                    ->boolean()
                    ->getStateUsing(fn (UpworkProfile $record): bool => $record->hasProxy() && $record->proxy_validated_at !== null)
                    ->toggleable(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->recordActions([
                EditAction::make()
                    ->modalHeading('Edit Profile')
                    ->modalSubmitActionLabel('Save')
                    ->slideOver()
                    ->before(function (array $data, UpworkProfile $record): void {
                        ListUpworkProfiles::assertProxyOnSave($data, $record);
                    })
                    ->after(function (UpworkProfile $record): void {
                        ListUpworkProfiles::storeProxyValidation($record);
                    }),
                DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Delete Profile')
                    ->modalDescription('Campaigns linked to this profile will have their profile cleared.')
                    ->modalSubmitActionLabel('Delete')
                    ->color('danger'),
            ])
            ->bulkActions([]);
    }
}
