<?php

namespace App\Filament\Resources\TeamMembers\Pages;

use App\Filament\Resources\TeamMembers\TeamMemberResource;
use App\Filament\Resources\TeamMembers\Schemas\TeamMemberForm;
use App\Jobs\NotificationQueue;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateTeamMember extends CreateRecord
{
    protected static string $resource = TeamMemberResource::class;

    protected ?string $generatedPassword = null;
    protected bool $sendWelcomeEmail = false;
    protected ?string $welcomeEmail = null;

    public function form(Schema $schema): Schema
    {
        return TeamMemberForm::createNewMemberForm($schema);
    }

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
                'email' => 'A user with this email address already exists. Please use "Link Existing Member" action instead.',
            ]);
        }

        // Create new user
        $userEmail = $data['email'];
        $user = User::create(
            [
                'email' => $userEmail,
                'name' => $data['name'],
                'password' => Hash::make($this->generatedPassword),
                'workspace_id' => session('workspace_id'),
            ]
        );

        $user->assignRole($data['role']);

        // Set user_id and email for TeamMember creation
        $data['user_id'] = $user->id;
        $data['email'] = $userEmail; // Keep email for TeamMember record

        unset($data['name']);
        unset($data['send_welcome_email']);

        // Set default status if not provided
        if (!isset($data['status'])) {
            $data['status'] = 'active';
        }

        // Set joined_at if status is active
        if ($data['status'] === 'active') {
            $data['joined_at'] = now();
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        // Send welcome email for new users
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
