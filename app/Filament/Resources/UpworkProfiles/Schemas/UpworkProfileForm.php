<?php

namespace App\Filament\Resources\UpworkProfiles\Schemas;

use App\Models\UpworkProfile;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;

class UpworkProfileForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                TextInput::make('code')
                    ->label('Bot code')
                    ->disabled()
                    ->dehydrated(false)
                    ->helperText('Auto-generated 8-character code. Pass this to the bot with --profile=CODE.')
                    ->visibleOn(['edit'])
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
                Section::make('Proxy')
                    ->description('Required. Each profile must use a proxy with a unique egress IP.')
                    ->schema([
                        TextInput::make('proxy_host')
                            ->label('Host')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('154.16.181.226'),
                        TextInput::make('proxy_port')
                            ->label('Port')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(65535)
                            ->placeholder('12323'),
                        Select::make('proxy_protocol')
                            ->label('Protocol')
                            ->options([
                                'http' => 'HTTP',
                                'socks5' => 'SOCKS5',
                            ])
                            ->default('http')
                            ->required()
                            ->native(false)
                            ->afterStateHydrated(function (Select $component, ?string $state): void {
                                if (blank($state)) {
                                    $component->state('http');
                                }
                            }),
                        TextInput::make('proxy_username')
                            ->label('Username')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('proxy_password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->required(fn (?UpworkProfile $record): bool => $record === null)
                            ->maxLength(255)
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->helperText('Leave blank when editing to keep the current password.'),
                        Placeholder::make('proxy_status')
                            ->label('Last check')
                            ->content(function (?UpworkProfile $record): string {
                                if (! $record?->proxy_validated_at) {
                                    return 'Not validated yet';
                                }

                                $ip = $record->proxy_last_ip ? " (IP: {$record->proxy_last_ip})" : '';

                                return $record->proxy_validated_at->format('M j, Y g:i A').$ip;
                            })
                            ->visibleOn(['edit']),
                        Actions::make([
                            Action::make('testProxyConnection')
                                ->label('Test connection')
                                ->icon('heroicon-o-signal')
                                ->color('gray')
                                ->action('testProxyConnection'),
                        ])
                            ->alignment(Alignment::Start),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
