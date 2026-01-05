<?php

namespace App\Filament\Resources\TeamMembers\Pages;

use App\Filament\Resources\TeamMembers\TeamMemberResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateTeamMember extends CreateRecord
{
    protected static string $resource = TeamMemberResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Find or create user by email
        $user = User::firstOrCreate(
            ['email' => $data['email']],
            [
                'name' => $data['name'],
                'password' => Hash::make(Str::random(32)), // Random password, user can reset
                'workspace_id' => session('workspace_id'),
            ]
        );

        // Update user name if it changed
        if ($user->name !== $data['name']) {
            $user->update(['name' => $data['name']]);
        }

        // Update workspace_id if not set
        if (!$user->workspace_id && session('workspace_id')) {
            $user->update(['workspace_id' => session('workspace_id')]);
        }

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

        return $data;
    }
}
