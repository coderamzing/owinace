<?php

namespace App\Filament\Resources\TeamMembers\Pages;

use App\Filament\Resources\TeamMembers\TeamMemberResource;
use App\Filament\Resources\BaseListRecords;
use App\Jobs\NotificationQueue;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ListTeamMembers extends BaseListRecords
{
    protected static string $resource = TeamMemberResource::class;
    
    protected string $searchPlaceholder = 'Search members by name, email...';

    protected ?string $generatedPassword = null;
    protected bool $sendWelcomeEmail = false;
    protected ?string $welcomeEmail = null;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalHeading('Create Team Member')
                ->modalSubmitActionLabel('Create')
                ->slideOver()
                ->mutateFormDataUsing(function (array $data): array {
                    $this->sendWelcomeEmail = (bool) ($data['send_welcome_email'] ?? false);
                    $this->welcomeEmail = $data['email'] ?? null;

                    // Generate password
                    $this->generatedPassword = $this->sendWelcomeEmail ? Str::random(12) : Str::random(32);

                    // Check if the user with this email already exists
                    $isUserExists = User::where('email', $data['email'])->exists();
                    if ($isUserExists) {
                        Notification::make()
                                ->title('Email already exists')
                                ->body('A user with this email address already exists.')
                                ->danger()
                                ->send();
                        return [];
                    }

                    // Find or create user by email
                    $user = User::create(
                        [
                            'email' => $data['email'],
                            'name' => $data['name'],
                            'password' => Hash::make($this->generatedPassword),
                            'workspace_id' => session('workspace_id'),
                            'email_verified_at' => now(),
                        ]
                    );

                    $user->assignRole($data['role']);

                    // Set user_id for team member
                    $data['user_id'] = $user->id;
                    unset($data['name']);

                    // Set defaults
                    if (!isset($data['status'])) {
                        $data['status'] = 'active';
                    }

                    if ($data['status'] === 'active') {
                        $data['joined_at'] = now();
                    }

                    unset($data['send_welcome_email']);

                    return $data;
                })
                ->after(function ($record) {
                    if (!$this->sendWelcomeEmail || !$this->generatedPassword || !$this->welcomeEmail) {
                        return;
                    }

                    $teamName = $record->team->name ?? 'your team';
                    $loginUrl = route('login');

                    NotificationQueue::dispatch(
                        type: 'member',
                        identifier: $record->user_id,
                        notificationType: 'team.welcome',
                        data: [
                            'subject' => 'Welcome to ' . $teamName,
                            'content' => 'You have been added to ' . $teamName . '.',
                            'url' => $loginUrl,
                            'team_id' => $record->team_id,
                            'user_id' => $record->user_id,
                            'password' => $this->generatedPassword,
                        ]
                    );
                }),
        ];
    }
}