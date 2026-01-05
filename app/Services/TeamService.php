<?php

namespace App\Services;

use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\TeamMember;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;

class TeamService
{
    /**
     * Create a new team in a workspace
     *
     * @param Workspace $workspace
     * @param User $user The user creating the team
     * @param array $teamData Team data (name, description)
     * @return Team
     * @throws \Exception
     */
    public function createTeam(Workspace $workspace, User $user, array $teamData): Team
    {
        // Check if user is workspace owner or has permission
        if ($workspace->owner_id !== $user->id) {
            throw new \Exception('Only workspace owner can create teams');
        }

        return DB::transaction(function () use ($workspace, $user, $teamData) {
            $team = Team::create([
                'workspace_id' => $workspace->id,
                'name' => $teamData['name'],
                'description' => $teamData['description'] ?? null,
                'created_by_id' => $user->id,
            ]);

            // Add the creator as the first team member with admin role
            TeamMember::create([
                'team_id' => $team->id,
                'user_id' => $user->id,
                'role' => 'admin',
                'status' => 'active',
                'joined_at' => now(),
            ]);

            return $team;
        });
    }

    /**
     * Invite a user to a team by email
     * Creates team_members entry immediately and sends invitation email
     *
     * @param Team $team
     * @param User $inviter The user sending the invitation
     * @param string $email Email address to invite
     * @param string|null $role Optional role for the invitation
     * @return array Returns ['invitation' => TeamInvitation, 'member' => TeamMember, 'warning' => string|null]
     * @throws \Exception
     */
    public function inviteByEmail(Team $team, User $inviter, string $email, ?string $role = null): array
    {
        // Check if inviter has permission (workspace owner or team admin)
        $workspace = $team->workspace;
        if ($workspace->owner_id !== $inviter->id) {
            $member = TeamMember::where('team_id', $team->id)
                ->where('user_id', $inviter->id)
                ->where('role', 'admin')
                ->first();
            
            if (!$member) {
                throw new \Exception('You do not have permission to invite members to this team');
            }
        }

        $existingUser = User::where('email', $email)->first();
        $warning = null;

        // Check if user exists and is in another workspace
        if ($existingUser && $existingUser->workspace_id && $existingUser->workspace_id !== $workspace->id) {
            $warning = "User is already in another workspace. They will need to accept this invitation to join this workspace.";
        }

        // Check if user is already a member
        if ($existingUser) {
            $existingMember = TeamMember::where('team_id', $team->id)
                ->where('user_id', $existingUser->id)
                ->first();
            
            if ($existingMember) {
                throw new \Exception('User is already a member of this team');
            }
        }

        // Check if there's a pending invitation
        $existingInvitation = TeamInvitation::where('team_id', $team->id)
            ->where('email', $email)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->first();

        if ($existingInvitation) {
            throw new \Exception('An invitation has already been sent to this email');
        }

        return DB::transaction(function () use ($team, $inviter, $email, $role, $existingUser, $warning) {
            // Create invitation
            $invitation = TeamInvitation::create([
                'team_id' => $team->id,
                'workspace_id' => $team->workspace_id,
                'email' => $email,
                'invited_by_id' => $inviter->id,
                'status' => 'pending',
                'expires_at' => now()->addDays(7),
            ]);

            // Create team_members entry immediately (with user_id if user exists, otherwise null)
            $teamMember = TeamMember::create([
                'team_id' => $team->id,
                'user_id' => $existingUser?->id,
                'role' => $role ?? 'member',
                'status' => $existingUser ? 'pending' : 'invited',
                'email' => $email,
                'joined_at' => $existingUser ? now() : null,
            ]);

            // Send invitation email
            Mail::to($email)->send(new \App\Mail\TeamInvitationMail($invitation));

            return [
                'invitation' => $invitation,
                'member' => $teamMember,
                'warning' => $warning,
            ];
        });
    }

    /**
     * Accept a team invitation
     *
     * @param string $token Invitation token
     * @param User|null $user User accepting the invitation (null if not logged in)
     * @return TeamMember
     * @throws \Exception
     */
    public function acceptInvitation(string $token, ?User $user = null): TeamMember
    {
        $invitation = TeamInvitation::where('token', $token)
            ->where('status', 'pending')
            ->first();

        if (!$invitation) {
            throw new \Exception('Invitation not found or already used');
        }

        if ($invitation->isExpired()) {
            $invitation->update(['status' => 'expired']);
            throw new \Exception('Invitation has expired');
        }

        // Find or create user by email
        if (!$user) {
            $user = User::where('email', $invitation->email)->first();
        } else {
            // Verify email matches
            if ($invitation->email !== $user->email) {
                throw new \Exception('This invitation is for a different email address');
            }
        }

        if (!$user) {
            throw new \Exception('User account not found. Please register first.');
        }

        // Check if user is already in another workspace
        $workspace = $invitation->workspace;
        if ($user->workspace_id && $user->workspace_id !== $workspace->id) {
            // User can only be in one workspace - update to new workspace
            $user->update(['workspace_id' => $workspace->id]);
        } else {
            // Assign workspace if not set
            if (!$user->workspace_id) {
                $user->update(['workspace_id' => $workspace->id]);
            }
        }

        return DB::transaction(function () use ($invitation, $user) {
            // Find existing team member entry (created on invite)
            $member = TeamMember::where('team_id', $invitation->team_id)
                ->where('email', $invitation->email)
                ->first();

            if ($member) {
                // Update existing member entry
                $member->update([
                    'user_id' => $user->id,
                    'status' => 'active',
                    'joined_at' => now(),
                ]);
            } else {
                // Create team member if not exists
                $member = TeamMember::create([
                    'team_id' => $invitation->team_id,
                    'user_id' => $user->id,
                    'role' => 'member',
                    'status' => 'active',
                    'joined_at' => now(),
                    'email' => $user->email,
                ]);
            }

            // Update invitation
            $invitation->update([
                'status' => 'accepted',
                'accepted_at' => now(),
            ]);

            // Assign member role if user doesn't have a role yet
            if (!$user->hasAnyRole(['owner', 'admin', 'member'])) {
                $memberRole = Role::where('name', 'member')
                    ->where('guard_name', 'web')
                    ->first();
                
                if ($memberRole) {
                    $user->assignRole($memberRole);
                }
            }

            return $member;
        });
    }

    /**
     * Remove a member from a team
     *
     * @param Team $team
     * @param User $remover User removing the member
     * @param int $memberId ID of the member to remove
     * @return bool
     * @throws \Exception
     */
    public function removeMember(Team $team, User $remover, int $memberId): bool
    {
        // Check permissions
        $workspace = $team->workspace;
        if ($workspace->owner_id !== $remover->id) {
            $removerMember = TeamMember::where('team_id', $team->id)
                ->where('user_id', $remover->id)
                ->where('role', 'admin')
                ->first();
            
            if (!$removerMember) {
                throw new \Exception('You do not have permission to remove members from this team');
            }
        }

        $member = TeamMember::where('team_id', $team->id)
            ->where('id', $memberId)
            ->first();

        if (!$member) {
            throw new \Exception('Member not found');
        }

        return $member->delete();
    }
}

