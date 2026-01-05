<?php

namespace App\Services;

use App\Models\LeadKanban;
use App\Models\LeadSource;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use App\Models\Workspace;
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
                'name' => 'Default Team',
                'description' => 'Default team for ' . $workspace->name,
                'created_by_id' => $user->id,
            ]);

            // Add the workspace owner as the first team member with admin role
            TeamMember::create([
                'team_id' => $team->id,
                'user_id' => $user->id,
                'role' => 'admin',
                'status' => 'active',
                'joined_at' => now(),
            ]);

            // Create default kanban stages for the team
            $this->createDefaultKanbanStages($team);

            // Create default lead sources for the team
            $this->createDefaultLeadSources($team);

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
    protected function createDefaultKanbanStages(Team $team): void
    {
        $defaultStages = [
            ['name' => 'Open', 'code' => 'OPEN', 'color' => '#0d6efd', 'sort_order' => 0, 'is_system' => true],
            ['name' => 'New', 'code' => 'NEW', 'color' => '#6c757d', 'sort_order' => 1, 'is_system' => true],
            ['name' => 'Contacted', 'code' => 'CONTACTED', 'color' => '#17a2b8', 'sort_order' => 2],
            ['name' => 'Proposal Sent', 'code' => 'PROPOSAL_SENT', 'color' => '#ffc107', 'sort_order' => 3],
            ['name' => 'Follow-up', 'code' => 'FOLLOW_UP', 'color' => '#fd7e14', 'sort_order' => 4],
            ['name' => 'Closed Won', 'code' => 'WON', 'color' => '#28a745', 'sort_order' => 5, 'is_system' => true],
            ['name' => 'Closed Lost', 'code' => 'LOST', 'color' => '#dc3545', 'sort_order' => 6, 'is_system' => true],
        ];

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
    protected function createDefaultLeadSources(Team $team): void
    {
        $defaultSources = [
            ['name' => 'Website', 'color' => '#007bff', 'sort_order' => 1, 'description' => 'Leads from company website'],
            ['name' => 'Referral', 'color' => '#28a745', 'sort_order' => 2, 'description' => 'Referrals from existing clients'],
            ['name' => 'Social Media', 'color' => '#17a2b8', 'sort_order' => 3, 'description' => 'Social media platforms'],
            ['name' => 'Email Campaign', 'color' => '#ffc107', 'sort_order' => 4, 'description' => 'Email marketing campaigns'],
            ['name' => 'Cold Outreach', 'color' => '#6c757d', 'sort_order' => 5, 'description' => 'Direct outreach to prospects'],
            ['name' => 'Paid Ads', 'color' => '#fd7e14', 'sort_order' => 6, 'description' => 'Paid advertising campaigns'],
            ['name' => 'Events', 'color' => '#e83e8c', 'sort_order' => 7, 'description' => 'Trade shows and events'],
            ['name' => 'Other', 'color' => '#6f42c1', 'sort_order' => 8, 'description' => 'Other lead sources'],
            ['name' => 'Upwork', 'color' => '#14a800', 'sort_order' => 9, 'description' => 'Upwork'],
        ];

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
