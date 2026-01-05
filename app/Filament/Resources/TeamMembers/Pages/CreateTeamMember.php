<?php

namespace App\Filament\Resources\TeamMembers\Pages;

use App\Filament\Resources\TeamMembers\TeamMemberResource;
use App\Jobs\NotificationQueue;
use App\Models\User;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateTeamMember extends CreateRecord
{
    protected static string $resource = TeamMemberResource::class;

    protected ?string $generatedPassword = null;
    protected bool $sendWelcomeEmail = false;
    protected ?string $welcomeEmail = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->sendWelcomeEmail = (bool) ($data['send_welcome_email'] ?? false);
        $this->welcomeEmail = $data['email'] ?? null;

        // Generate a password only if we need to send it; otherwise use random hash
        $this->generatedPassword = $this->sendWelcomeEmail ? Str::random(12) : Str::random(32);

        // Check if the user with this email already exists
        $isUserExists = User::where('email', $data['email'])->exists();
        if ($isUserExists) {
            throw new \Illuminate\Validation\ValidationException([
                'email' => 'A user with this email address already exists.',
            ]);
        }

        // Find or create user by email
        $user = User::create(
            [
                'email' => $data['email'],
                'name' => $data['name'],
                'password' => Hash::make($this->generatedPassword),
                'workspace_id' => session('workspace_id'),
            ]
        );

        // Replace name and email with user_id for TeamMember creation
        $data['user_id'] = $user->id;

        unset($data['name']);
        unset($data['email']);

        // Set default status if not provided
        if (!isset($data['status'])) {
            $data['status'] = 'active';
        }

        // Set joined_at if status is active
        if ($data['status'] === 'active') {
            $data['joined_at'] = now();
        }

        // Remove transient field
        unset($data['send_welcome_email']);

        return $data;
    }

    protected function afterCreate(): void
    {
        if (!$this->sendWelcomeEmail || !$this->generatedPassword || !$this->welcomeEmail) {
            return;
        }

        $teamName = $this->record?->team?->name ?? 'your team';
        $loginUrl = route('login');

        NotificationQueue::dispatch(
            type: 'member',
            identifier: $this->record?->user_id,
            notificationType: 'team.welcome',
            data: [
                'subject' => 'Welcome to ' . $teamName,
                'content' => 'You have been added to ' . $teamName . '.',
                'url' => $loginUrl,
                'team_id' => $this->record?->team_id,
                'user_id' => $this->record?->user_id,
                'password' => $this->generatedPassword,
            ]
        );
    }
}
