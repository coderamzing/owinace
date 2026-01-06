<?php

namespace App\Filament\Resources\TeamMembers\Schemas;

use App\Models\Team;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TeamMemberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('team_id')
                    ->label('Team')
                    ->options(
                        Team::where('workspace_id', session('workspace_id'))
                            ->pluck('name', 'id')
                    )
                    ->default(fn () => session('team_id'))
                    ->required(),
                Select::make('role')
                    ->label('Role')
                    ->options([
                        'admin' => 'Admin',
                        'member' => 'Member',
                    ])
                    ->required()
                    ->default('member'),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->maxLength(254),
                TextInput::make('name')
                    ->label('Name')
                    ->required()
                    ->maxLength(255),
                Checkbox::make('send_welcome_email')
                    ->label('Send welcome email with generated password')
                    ->default(false),
            ]);
    }
}
