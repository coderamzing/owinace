<?php

namespace App\Services;

use App\Jobs\EmailQueue;
use App\Jobs\InAppNotificationQueue;
use App\Models\Employee;
use App\Models\NotificationPreference;
use App\Models\User;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Role;

class NotificationService
{
    /**
     * Notify a single employee
     * Uses employee-specific notification types from config
     */
    public function notifyMember(int $employeeId, string $notificationType, array $data): void
    {
        $employee = Employee::find($employeeId);
        
        if (!$employee || !$employee->user) {
            return;
        }

        $user = $employee->user;
        
        // Get employee notification config
        $notificationConfig = NotificationPreference::getNotificationConfig($notificationType, 'employee');

        if (!$notificationConfig) {
            return;
        }

        // // Check if user has the required permission
        // if (!$user->hasPermissionTo($notificationConfig['permission'], 'web')) {
        //     return;
        // }

        // Get user's notification preference
        $preference = $this->getPreference($user, $notificationType, $notificationConfig['permission']);

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
        $employees = Employee::where('workspace_id', $workspaceId)
            ->where('status', 'working')
            ->with('user')
            ->get();

        if ($employees->isEmpty()) {
            return;
        }

        // Filter employees by notification preferences
        $employeesToNotify = $employees->filter(function ($employee) use ($notificationType, $requiredPermission) {
            if (!$employee->user) {
                return false;
            }

            $preference = $this->getPreference($employee->user, $notificationType, $requiredPermission);

            // Only notify if at least one channel is enabled
            return $preference->email_enabled || $preference->in_app_enabled;
        });

        // Send notifications to filtered employees
        foreach ($employeesToNotify as $employee) {
            $preference = $this->getPreference($employee->user, $notificationType, $requiredPermission);

            // Send in-app notification if enabled
            if ($preference->in_app_enabled) {
                InAppNotificationQueue::dispatch(
                    userId: $employee->user->id,
                    title: $data['title'] ?? $notificationConfig['label'],
                    content: $data['content'] ?? '',
                    url: $data['url'] ?? null
                );
            }

            // Send email if enabled
            if ($preference->email_enabled && $employee->user->email) {
                $this->sendEmailNotification(
                    user: $employee->user,
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
        // Get template from config using EmailTemplateService
        $template = EmailTemplateService::getTemplate($notificationType);

        EmailQueue::dispatch(
            to: $user->email,
            subject: $subject,
            template: $template,
            data: array_merge($data, [
                'user_name' => $user->name,
            ])
        );
    }

    public function enrichData(array $data): array
    {
        if(isset($data['employee_id'])) {
            $employee = Employee::findOrFail($data['employee_id']);
            $data = array_merge($data, ['employee' => $employee->toArray()]);
        }
        return $data;
    }
}

