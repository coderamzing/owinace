<?php

namespace App\Services;

use App\Jobs\EmailQueue;
use App\Jobs\InAppNotificationQueue;
use App\Models\Employee;
use App\Models\NotificationPreference;
use App\Models\User;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Role;
use App\Models\Team;

class NotificationService
{
    /**
     * Notify a single employee
     * Uses employee-specific notification types from config
     */
    public function notifyMember(int $memberId, string $notificationType, array $data): void
    {
        $member = User::find($memberId);
        
        if (!$member) {
            return;
        }

        $user = $member;

        InAppNotificationQueue::dispatch(
            userId: $user->id,
            title: $data['subject'],
            content: $data['content'] ?? '',
            url: $data['url'] ?? null
        );

        $this->sendEmailNotification(
            user: $user,
            notificationType: $notificationType,
            subject: $data['subject'],
            data: $data ?? []
        );
    }

    /**
     * Send email notification
     * Uses EmailTemplateService to get template from config
     */
    protected function sendEmailNotification(
        User $user,
        string $notificationType,
        string $subject,
        array $data
    ): void {
        $templates = [
            'team.welcome' => 'emails.team-welcome',
        ];
        EmailQueue::dispatch(
            to: $user->email,
            subject: $subject,
            message: $data['content'] ?? '',
            template: $templates[$notificationType] ?? '',
            data: $data
        );
    }

    public function enrichData(array $data): array
    {
        if(isset($data['user_id'])) {
            $member = User::findOrFail($data['user_id']);
            $data = array_merge($data, ['user' => $member->toArray()]);
        }
        if(isset($data['team_id'])) {
            $team = Team::findOrFail($data['team_id']);
            $data = array_merge($data, ['team' => $team->toArray()]);
        }
        return $data;
    }


    /**
     * Notify all employees in a workspace who have the required permission and preference enabled
     * Uses employee-specific notification types from config
     */
    public function notifyMembers(int $workspaceId, string $notificationType, array $data): void
    {
        // Get employee notification config
        $notificationConfig = NotificationPreference::getNotificationConfig($notificationType, 'employee');

        if (!$notificationConfig) {
            return;
        }

        $requiredPermission = $notificationConfig['permission'];

        // Get all employees in the workspace
        $members = Employee::where('workspace_id', $workspaceId)
            ->where('status', 'working')
            ->with('user')
            ->get();

        if ($members->isEmpty()) {
            return;
        }

        // Filter employees by notification preferences
        $membersToNotify = $members->filter(function ($member) use ($notificationType, $requiredPermission) {
            if (!$member->user) {
                return false;
            }

            $preference = $this->getPreference($member->user, $notificationType, $requiredPermission);

            // Only notify if at least one channel is enabled
            return $preference->email_enabled || $preference->in_app_enabled;
        });

        // Send notifications to filtered employees
        foreach ($membersToNotify as $member) {
            $preference = $this->getPreference($member->user, $notificationType, $requiredPermission);

            // Send in-app notification if enabled
            if ($preference->in_app_enabled) {
                InAppNotificationQueue::dispatch(
                    userId: $member->user->id,
                    title: $data['title'] ?? $notificationConfig['label'],
                    content: $data['content'] ?? '',
                    url: $data['url'] ?? null
                );
            }

            // Send email if enabled
            if ($preference->email_enabled && $member->user->email) {
                $this->sendEmailNotification(
                    user: $member->user,
                    notificationType: $notificationType,
                    subject: $data['title'] ?? $notificationConfig['label'],
                    data: $data ?? []
                );
            }
        }
    }

    /**
     * Notify all admins in a workspace who have the required permission and preference enabled
     * Uses admin-specific notification types from config
     */
    public function notifyTeam(int $workspaceId, string $notificationType, array $data): void
    {
        // Get admin notification config
        $notificationConfig = NotificationPreference::getNotificationConfig($notificationType, 'admin');

        if (!$notificationConfig) {
            return;
        }

        $requiredPermission = $notificationConfig['permission'];

        // Step 1: Find roles that have the required permission
        $rolesWithPermission = Role::where('workspace_id', $workspaceId)
            ->whereHas('permissions', function ($query) use ($requiredPermission) {
                $query->where('name', $requiredPermission);
            })
            ->get();

        if ($rolesWithPermission->isEmpty()) {
            return;
        }

        // Step 2: Get all admin users in those roles for this workspace
        $users = User::where('workspace_id', $workspaceId)
            ->where('type', 'admin') // Only admin users
            ->whereHas('roles', function ($query) use ($rolesWithPermission) {
                $query->whereIn('id', $rolesWithPermission->pluck('id'));
            })
            ->get();

        if ($users->isEmpty()) {
            return;
        }

        // Step 3: Filter users by notification preferences
        $usersToNotify = $users->filter(function ($user) use ($notificationType, $requiredPermission) {
            $preference = $this->getPreference($user, $notificationType, $requiredPermission);
            
            // Only notify if at least one channel is enabled
            return $preference->email_enabled || $preference->in_app_enabled;
        });

        // Step 4: Send notifications to filtered users
        foreach ($usersToNotify as $user) {
            $preference = $this->getPreference($user, $notificationType, $requiredPermission);

            // Send in-app notification if enabled
            if ($preference->in_app_enabled) {
                InAppNotificationQueue::dispatch(
                    userId: $user->id,
                    title: $data['title'] ?? $notificationConfig['label'],
                    content: $data['content'] ?? '',
                    url: $data['url'] ?? null
                );
            }

            // Send email if enabled
            if ($preference->email_enabled && $user->email) {
                $this->sendEmailNotification(
                    user: $user,
                    notificationType: $notificationType,
                    subject: $data['title'] ?? $notificationConfig['label'],
                    data: $data ?? []
                );
            }
        }
    }

    /**
     * Get user's notification preference, create default if not exists
     */
    protected function getPreference(User $user, string $notificationType, string $requiredPermission): NotificationPreference
    {
        return NotificationPreference::firstOrCreate(
            [
                'user_id' => $user->id,
                'workspace_id' => $user->workspace_id,
                'notification_type' => $notificationType,
            ],
            [
                'required_permission' => $requiredPermission,
                'email_enabled' => true,
                'in_app_enabled' => true,
            ]
        );
    }

    
   
}

