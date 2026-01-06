<?php

namespace App\Filament\Resources\Teams\Pages;

use App\Filament\Resources\TeamMembers\Schemas\TeamMemberForm;
use App\Filament\Resources\Teams\TeamResource;
use App\Filament\Resources\BaseListRecords;
use App\Models\TeamMember;
use App\Models\User;
use Filament\Actions;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ListTeams extends BaseListRecords
{
    protected static string $resource = TeamResource::class;
    
    protected string $searchPlaceholder = 'Search teams by name...';

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
            Actions\Action::make('createMember')
                ->label('Create New Member')
                ->icon('heroicon-o-user-plus')
                ->color('success')
                ->modalHeading('Create New Member')
                ->modalSubmitActionLabel('Create')
                ->slideOver()
                ->form(function (Schema $schema): Schema {
                    return TeamMemberForm::configure($schema);
                })
                ->action(function (array $data): void {
                    // Find or create user by email
                    $user = User::firstOrCreate(
                        ['email' => $data['email']],
                        [
                            'name' => $data['name'],
                            'password' => Hash::make(Str::random(32)), // Random password, user can reset
                            'workspace_id' => session('workspace_id') ?? auth()->user()?->workspace_id,
                        ]
                    );

                    // Update user name if it changed
                    if ($user->name !== $data['name']) {
                        $user->update(['name' => $data['name']]);
                    }

                    // Update workspace_id if not set
                    $workspaceId = session('workspace_id') ?? auth()->user()?->workspace_id;
                    if (!$user->workspace_id && $workspaceId) {
                        $user->update(['workspace_id' => $workspaceId]);
                    }

                    // Create TeamMember
                    TeamMember::create([
                        'team_id' => $data['team_id'],
                        'user_id' => $user->id,
                        'role' => $data['role'] ?? 'member',
                        'status' => 'active',
                        'email' => $data['email'],
                        'joined_at' => now(),
                    ]);
                })
                ->successNotificationTitle('Member created successfully'),
        ];
    }

    protected function getTableQuery(): Builder
    {
        $workspaceId = session('workspace_id') ?? auth()->user()?->workspace_id;
        
        return parent::getTableQuery()
            ->when($workspaceId, fn ($query) => $query->where('workspace_id', $workspaceId));
    }
}
