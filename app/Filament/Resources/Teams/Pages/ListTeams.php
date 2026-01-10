<?php

namespace App\Filament\Resources\Teams\Pages;

use App\Filament\Resources\TeamMembers\Schemas\TeamMemberForm;
use App\Filament\Resources\Teams\TeamResource;
use App\Filament\Resources\BaseListRecords;
use App\Jobs\NotificationQueue;
use App\Models\Scopes\TeamScope;
use App\Models\TeamMember;
use App\Models\User;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ListTeams extends BaseListRecords
{
    protected static string $resource = TeamResource::class;
    
    protected string $searchPlaceholder = 'Search teams by name...';

    protected ?string $generatedPassword = null;
    protected bool $sendWelcomeEmail = false;
    protected ?string $welcomeEmail = null;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->modalHeading('Create Team')
                ->modalSubmitActionLabel('Create')
                ->slideOver()
                ->mutateFormDataUsing(function (array $data): array {
                    $data['created_by_id'] = auth()->id();
                    $data['workspace_id'] = session('workspace_id');
                    
                    if (!$data['workspace_id']) {
                        // Fallback to user's workspace_id if session is not set
                        $data['workspace_id'] = auth()->user()?->workspace_id;
                    }
                    
                    return $data;
                }),
            
            // Action for creating a new member (creates new user)
            CreateAction::make('createNewMember')
                ->label('Create New Member')
                ->modalHeading('Create New Team Member')
                ->modalSubmitActionLabel('Create')
                ->icon('heroicon-o-user-plus')
                ->slideOver()
                ->form(function (Schema $schema) {
                    return TeamMemberForm::createNewMemberForm($schema);
                })
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
                                ->body('A user with this email address already exists. Please use "Link Existing Member" instead.')
                                ->danger()
                                ->send();
                        return [];
                    }

                    // Create new user
                    $userEmail = $data['email'];
                    $user = User::create(
                        [
                            'email' => $userEmail,
                            'name' => $data['name'],
                            'password' => Hash::make($this->generatedPassword),
                            'workspace_id' => session('workspace_id'),
                            'email_verified_at' => now(),
                        ]
                    );

                    $user->assignRole($data['role']);

                    // Set user_id and email for team member
                    $data['user_id'] = $user->id;
                    $data['email'] = $userEmail; // Keep email for TeamMember record
                    unset($data['name']);
                    unset($data['send_welcome_email']);

                    // Set defaults
                    if (!isset($data['status'])) {
                        $data['status'] = 'active';
                    }

                    if ($data['status'] === 'active') {
                        $data['joined_at'] = now();
                    }

                    return $data;
                })
                ->after(function ($record) {
                    // Send welcome email for new users
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
            
            // Action for linking existing member (no user creation) - supports multiple users
            Action::make('linkExistingMember')
                ->label('Link Existing Member')
                ->modalHeading('Link Existing Members to Team')
                ->modalSubmitActionLabel('Link Members')
                ->icon('heroicon-o-link')
                ->color('success')
                ->slideOver()
                ->form(function (Schema $schema) {
                    return TeamMemberForm::linkExistingMemberForm($schema);
                })
                ->action(function (array $data) {
                    $userIds = $data['user_ids'] ?? [];
                    $teamId = $data['team_id'];
                    $role = $data['role'] ?? 'member';
                    
                    if (empty($userIds)) {
                        Notification::make()
                            ->title('No users selected')
                            ->body('Please select at least one user to add to the team.')
                            ->warning()
                            ->send();
                        return;
                    }

                    // Get all selected users
                    $users = User::whereIn('id', $userIds)->get();
                    
                    if ($users->isEmpty()) {
                        Notification::make()
                            ->title('Users not found')
                            ->body('Selected users not found.')
                            ->danger()
                            ->send();
                        return;
                    }

                    // Check which users are already members
                    $existingMemberIds = TeamMember::where('team_id', $teamId)
                        ->whereIn('user_id', $userIds)
                        ->pluck('user_id')
                        ->toArray();

                    $usersToAdd = $users->reject(function ($user) use ($existingMemberIds) {
                        return in_array($user->id, $existingMemberIds);
                    });

                    if ($usersToAdd->isEmpty()) {
                        Notification::make()
                            ->title('All users already in team')
                            ->body('All selected users are already members of this team.')
                            ->warning()
                            ->send();
                        return;
                    }

                    // Create TeamMember records for each user
                    $createdCount = 0;
                    $skippedCount = count($existingMemberIds);

                    foreach ($usersToAdd as $user) {
                        TeamMember::create([
                            'team_id' => $teamId,
                            'user_id' => $user->id,
                            'email' => $user->email,
                            'role' => $role,
                            'status' => 'active',
                            'joined_at' => now(),
                        ]);
                        $createdCount++;
                    }

                    // Show success notification
                    $message = "Successfully added {$createdCount} member(s) to the team.";
                    if ($skippedCount > 0) {
                        $message .= " {$skippedCount} user(s) were already members and were skipped.";
                    }

                    Notification::make()
                        ->title('Members linked successfully')
                        ->body($message)
                        ->success()
                        ->send();
                }),
        ];
    }

    protected function getTableQuery(): Builder
    {
        $workspaceId = session('workspace_id') ?? auth()->user()?->workspace_id;
        
        return parent::getTableQuery()
            ->when($workspaceId, fn ($query) => $query->where('workspace_id', $workspaceId));
    }

    /**
     * Get team members for display in the slide panel
     */
    public function getTeamMembers(int $teamId): Collection
    {
        // Use withoutGlobalScope to bypass TeamScope that filters by session team_id
        return TeamMember::withoutGlobalScope(\App\Models\Scopes\TeamScope::class)
            ->where('team_id', $teamId)
            ->with('user')
            ->orderBy('role', 'desc') // Admins first
            ->orderBy('status', 'desc') // Active first
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Remove a member from the team
     */
    public function removeMember(int $memberId): void
    {
        // Use withoutGlobalScope to bypass TeamScope
        $member = TeamMember::withoutGlobalScope(TeamScope::class)->findOrFail($memberId);
        
        if (!$member) {
            Notification::make()
                ->title('Member not found')
                ->danger()
                ->send();
            return;
        }

        $memberName = $member->user?->name ?? $member->email ?? 'Member';
        $member->delete();

        Notification::make()
            ->title('Member removed')
            ->body("{$memberName} has been removed from the team.")
            ->success()
            ->send();
        
        // Refresh the table
        $this->dispatch('$refresh');
    }

    /**
     * Set a member as inactive
     */
    public function setMemberInactive(int $memberId): void
    {
        // Use withoutGlobalScope to bypass TeamScope
        $member = TeamMember::withoutGlobalScope(TeamScope::class)->findOrFail($memberId);
        
        if (!$member) {
            Notification::make()
                ->title('Member not found')
                ->danger()
                ->send();
            return;
        }

        $member->update([
            'status' => 'inactive',
        ]);

        $memberName = $member->user?->name ?? $member->email ?? 'Member';
        Notification::make()
            ->title('Member set as inactive')
            ->body("{$memberName} has been set as inactive.")
            ->success()
            ->send();
        
        // Refresh the table
        $this->dispatch('$refresh');
    }

    /**
     * Set a member as active
     */
    public function setMemberActive(int $memberId): void
    {
        // Use withoutGlobalScope to bypass TeamScope
        $member = TeamMember::withoutGlobalScope(TeamScope::class)->findOrFail($memberId);
        
        if (!$member) {
            Notification::make()
                ->title('Member not found')
                ->danger()
                ->send();
            return;
        }

        $member->update([
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $memberName = $member->user?->name ?? $member->email ?? 'Member';
        Notification::make()
            ->title('Member set as active')
            ->body("{$memberName} has been set as active.")
            ->success()
            ->send();
        
        // Refresh the table
        $this->dispatch('$refresh');
    }
}
