<?php

namespace App\Services;

use App\Models\LeadKanban;
use App\Models\LeadSource;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceCredit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class OnBoardService
{
    /**
     * Create a new workspace with an admin user
     *
     * @param array $userData User registration data
     * @param array $workspaceData Workspace creation data
     * @param bool $withSampleData Whether to create sample data (currently no-op)
     * @return array Returns ['user' => User, 'workspace' => Workspace]
     * @throws \Exception
     */
    public function createWorkspaceWithAdmin(array $userData, array $workspaceData, bool $withSampleData = false): array
    {
        return DB::transaction(function () use ($userData, $workspaceData, $withSampleData) {
            // Create the user
            $user = User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => Hash::make($userData['password']),
                'type' => 'admin',
            ]);

            // Generate slug if not provided
            $slug = $workspaceData['slug'] ?? Str::slug($workspaceData['name']);
            
            // Ensure slug is unique
            $originalSlug = $slug;
            $counter = 1;
            while (Workspace::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }

            // Create the workspace
            $workspace = Workspace::create([
                'name' => $workspaceData['name'],
                'owner_id' => $user->id,
                'description' => $workspaceData['description'] ?? null,
                'slug' => $slug,
                'trial_end' => now()->addDays(14),
                'expire_at' => now()->addDays(30),
                'start_at' => now(),
                'tier_id' => 1,
            ]);

        
            // Set the user's workspace
            $user->update([
                'workspace_id' => $workspace->id,
            ]);

            // Assign Owner role to the user (workspace owner gets owner role)
            $ownerRole = Role::where('name', 'owner')
                ->where('guard_name', 'web')
                ->first();

            if ($ownerRole) {
                $user->assignRole($ownerRole);
            }

            // Create default team for the workspace
            $team = Team::create([
                'workspace_id' => $workspace->id,
                'name' => $workspace->name,
                'description' => 'Default team for ' . $workspace->name,
                'created_by_id' => $user->id,
            ]);

            // Add the workspace owner as the first team member with admin role
            TeamMember::create([
                'team_id' => $team->id,
                'user_id' => $user->id,
                'role' => 'admin',
                'status' => 'active',
                'email' => $user->email,
                'joined_at' => now(),
            ]);

            // Add welcome credits to the workspace
            WorkspaceCredit::create([
                'workspace_id' => $workspace->id,
                'transaction_type' => 'welcome_bonus',
                'credits' => config('credit.credit_welcome', 50),
                'note' => 'Welcome bonus credits',
                'triggered_by_id' => $user->id,
                'transaction_id' => null,
            ]);

            // Note: Default kanban stages and lead sources are automatically created
            // via the TeamObserver when the team is created

            // Create sample data if requested
            if ($withSampleData) {
                $this->createSampleData($workspace);
            }

            return ['user' => $user, 'workspace' => $workspace, 'team' => $team];
        });
    }

    /**
     * Create default kanban stages for a team
     *
     * @param Team $team
     * @return void
     */
    public function createDefaultKanbanStages(Team $team): void
    {
        $defaultStages = config('defaults.kanban_stages', []);

        foreach ($defaultStages as $stageData) {
            LeadKanban::create([
                'team_id' => $team->id,
                'name' => $stageData['name'],
                'code' => $stageData['code'],
                'color' => $stageData['color'],
                'sort_order' => $stageData['sort_order'],
                'is_system' => $stageData['is_system'] ?? false,
                'is_active' => true,
            ]);
        }
    }

    /**
     * Create default lead sources for a team
     *
     * @param Team $team
     * @return void
     */
    public function createDefaultLeadSources(Team $team): void
    {
        $defaultSources = config('defaults.lead_sources', []);

        foreach ($defaultSources as $sourceData) {
            LeadSource::create([
                'team_id' => $team->id,
                'name' => $sourceData['name'],
                'description' => $sourceData['description'],
                'color' => $sourceData['color'],
                'sort_order' => $sourceData['sort_order'],
                'is_active' => true,
            ]);
        }
    }

    /**
     * Create sample data for a workspace (currently empty - no sample data)
     *
     * @param Workspace $workspace
     * @return void
     */
    public function createSampleData(Workspace $workspace): void
    {
        // No sample data to create
    }
}
