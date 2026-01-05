<?php

namespace App\Jobs;

use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Notification Queue Job
 *
 * Queue notifications to be created in the background.
 * This job delegates to NotificationService which handles:
 * - Permission checking
 * - Notification preference filtering
 * - Email and in-app notification sending
 *
 * Usage Examples:
 *
 * // Notify a single employee
 * NotificationQueue::dispatch(
 *     type: 'employee',
 *     identifier: $memberId, // employee ID
 *     notificationType: 'leave.approved',
 *     data: [
 *         'title' => 'Leave Approved',
 *         'content' => 'Your leave request has been approved.',
 *         'url' => '/employee/leave-requests',
 *         'email' => [
 *             'employee_name' => 'John Doe',
 *             'start_date' => '2025-01-01',
 *             'end_date' => '2025-01-05',
 *         ]
 *     ]
 * );
 *
 * // Notify workspace admins
 * NotificationQueue::dispatch(
 *     type: 'workspace',
 *     identifier: $workspaceId, // workspace ID
 *     notificationType: 'leave.request',
 *     data: [
 *         'title' => 'New Leave Request',
 *         'content' => 'New leave request received.',
 *         'url' => '/dashboard/employee-leaves/123',
 *         'email' => [
 *             'employee_name' => 'John Doe',
 *             'leave_url' => url('/dashboard/employee-leaves/123'),
 *         ]
 *     ]
 * );
 *
 * // Notify all employees in workspace
 * NotificationQueue::dispatch(
 *     type: 'employees',
 *     identifier: $workspaceId, // workspace ID
 *     notificationType: 'policy.published',
 *     data: [
 *         'title' => 'New Policy Published',
 *         'content' => 'A new policy has been published.',
 *         'url' => '/employee/policies/123',
 *         'email' => [
 *             'policy_title' => 'New HR Policy',
 *             'policy_url' => url('/employee/policies/123'),
 *         ]
 *     ]
 * );
 */
class NotificationQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @param string $type Type of notification: 'employee', 'workspace', or 'employees'
     * @param int $identifier Employee ID (if type='employee') or Workspace ID (if type='workspace' or 'employees')
     * @param string $notificationType Notification type from config (e.g., 'leave.approved', 'leave.request')
     * @param array $data Notification data ['title' => string, 'content' => string, 'url' => string|null, 'email' => array]
     */
    public function __construct(
        public string $type, 
        public int $identifier,
        public string $notificationType,
        public array $data = []
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(NotificationService $notificationService): void
    {
        $this->data = $notificationService->enrichData($this->data);
        if ($this->type === 'member') {
            // Notify a single employee
            $notificationService->notifyMember(
                memberId: $this->identifier,
                notificationType: $this->notificationType,
                data: $this->data
            );
        } elseif ($this->type === 'team') {
            // Notify workspace admins
            $notificationService->notifyTeam(
                workspaceId: $this->identifier,
                notificationType: $this->notificationType,
                data: $this->data
            );
        } elseif ($this->type === 'members') {
            // Notify all members in team
            $notificationService->notifyMembers(
                workspaceId: $this->identifier,
                notificationType: $this->notificationType,
                data: $this->data
            );
        }
    }
}
