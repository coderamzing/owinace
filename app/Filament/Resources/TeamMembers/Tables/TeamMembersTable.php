<?php

namespace App\Filament\Resources\TeamMembers\Tables;

use App\Mail\WelcomeTeamMemberMail;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class TeamMembersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('team.name')
                    ->label('Team')
                    ->searchable()
                    ->sortable()
                    ->url(fn ($record) => \App\Filament\Resources\Teams\TeamResource::getUrl('index') . '?tableFilters[team_id][value]=' . $record->team_id)
                    ->openUrlInNewTab(false),
                TextColumn::make('user.name')
                    ->label('Member')
                    ->searchable()
                    ->default('Not registered'),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('role')
                    ->badge()
                    ->colors([
                        'primary' => 'admin',
                        'success' => 'member',
                    ])
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'success' => 'active',
                        'warning' => 'pending',
                        'danger' => 'invited',
                    ])
                    ->searchable(),
                TextColumn::make('joined_at')
                    ->label('Joined')
                    ->dateTime()
                    ->sortable()
                    ->default('Not joined yet'),
                TextColumn::make('created_at')
                    ->label('Invited')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('resetPassword')
                        ->label('Reset Password')
                        ->icon('heroicon-o-key')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Reset Password')
                        ->modalDescription('Enter a new password for this team member.')
                        ->modalSubmitActionLabel('Reset Password')
                        ->form([
                            TextInput::make('password')
                                ->label('New Password')
                                ->password()
                                ->required()
                                ->minLength(8)
                                ->confirmed(),
                            TextInput::make('password_confirmation')
                                ->label('Confirm New Password')
                                ->password()
                                ->required(),
                        ])
                        ->action(function (array $data, $record): void {
                            if ($record->user) {
                                $record->user->update([
                                    'password' => Hash::make($data['password']),
                                ]);

                                Notification::make()
                                    ->title('Password reset successfully')
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('User not found')
                                    ->danger()
                                    ->send();
                            }
                        })
                        ->visible(fn ($record) => $record->user !== null),
                    DeleteAction::make()
                        ->label('Delete')
                        ->modalHeading('Delete Team Member')
                        ->modalDescription('Are you sure you want to delete this team member? This action cannot be undone.')
                        ->modalSubmitActionLabel('Delete')
                        ->successNotificationTitle('Team member deleted successfully')
                        ->visible(fn ($record) => $record->user_id !== Auth::id())
                        ->after(function ($record) {
                            // Delete the associated user if exists
                            if ($record->user) {
                                $record->user->delete();
                            }
                        }),
                ]),
            ]);
    }
}
