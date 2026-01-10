<?php

namespace App\Filament\Resources\TeamMembers\Schemas;

use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Session;

class TeamMemberForm
{
    public static function configure(Schema $schema): Schema
    {
        return self::createNewMemberForm($schema);
    }

    public static function createNewMemberForm(Schema $schema): Schema
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
                TextInput::make('name')
                    ->label('Name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->maxLength(254),
                Select::make('role')
                    ->label('Role')
                    ->options([
                        'admin' => 'Admin',
                        'member' => 'Member',
                    ])
                    ->required()
                    ->default('member'),
                Checkbox::make('send_welcome_email')
                    ->label('Send welcome email with generated password')
                    ->default(false),
            ]);
    }

    public static function linkExistingMemberForm(Schema $schema): Schema
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
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (callable $set, $state) {
                        // Reset user_ids when team changes
                        $set('user_ids', null);
                    }),
                Select::make('user_ids')
                    ->label('Select Users')
                    ->multiple()
                    ->options(function (callable $get) {
                        $teamId = $get('team_id');
                        $workspaceId = session('workspace_id');
                        
                        if (!$workspaceId) {
                            return [];
                        }

                        // Start with users from the current workspace
                        $query = User::where('workspace_id', $workspaceId);
                        
                        // Exclude users who are already members of the selected team
                        if ($teamId) {
                            $existingMemberIds = TeamMember::where('team_id', $teamId)
                                ->whereNotNull('user_id')
                                ->pluck('user_id')
                                ->toArray();
                            
                            if (!empty($existingMemberIds)) {
                                $query->whereNotIn('id', $existingMemberIds);
                            }
                        }
                        
                        // Get users and format for dropdown
                        $users = $query->orderBy('name')->get();
                        
                        return $users->mapWithKeys(function ($user) {
                            return [$user->id => $user->name . ' (' . $user->email . ')'];
                        })->toArray();
                    })
                    ->searchable()
                    ->preload()
                    ->required()
                    ->disabled(fn (callable $get) => !$get('team_id'))
                    ->helperText('You can select multiple users to add them all to the team at once.'),
                Select::make('role')
                    ->label('Role')
                    ->options([
                        'admin' => 'Admin',
                        'member' => 'Member',
                    ])
                    ->required()
                    ->default('member'),
            ]);
    }
}
