<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    protected $fillable = [
        'user_id',
        'notification_type',
        'required_permission',
        'email_enabled',
        'in_app_enabled',
    ];

    protected $casts = [
        'email_enabled' => 'boolean',
        'in_app_enabled' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get notification types for employees from config
     */
    public static function getEmployeeNotificationTypes(): array
    {
        return config('notifications.employee', []);
    }

    /**
     * Get notification types for admins from config
     */
    public static function getAdminNotificationTypes(): array
    {
        return config('notifications.admin', []);
    }

    /**
     * Get notification types based on user type
     */
    public static function getNotificationTypesForUser(?string $userType = null): array
    {
        if ($userType === 'employee') {
            return self::getEmployeeNotificationTypes();
        }
        
        return self::getAdminNotificationTypes();
    }

    /**
     * Get all notification types (for validation purposes)
     */
    public static function getAllNotificationTypes(): array
    {
        return array_merge(
            self::getEmployeeNotificationTypes(),
            self::getAdminNotificationTypes()
        );
    }

    /**
     * Check if notification type exists
     */
    public static function notificationTypeExists(string $type, ?string $userType = null): bool
    {
        $types = $userType 
            ? self::getNotificationTypesForUser($userType)
            : self::getAllNotificationTypes();
            
        return isset($types[$type]);
    }

    /**
     * Get notification config by type
     */
    public static function getNotificationConfig(string $type, ?string $userType = null): ?array
    {
        $types = $userType 
            ? self::getNotificationTypesForUser($userType)
            : self::getAllNotificationTypes();
            
        return $types[$type] ?? null;
    }
}
