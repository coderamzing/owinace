<?php

namespace App\Jobs;

use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * In-App Notification Queue Job
 * 
 * This is used internally by NotificationService to send in-app notifications.
 * Uses Filament's built-in database notification system.
 */
class InAppNotificationQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     * 
     * @param int $userId The ID of the user to notify
     * @param string $title Notification title
     * @param string $content Notification content
     * @param string|null $url Optional URL to link to
     */
    public function __construct(
        public int $userId,
        public string $title,
        public string $content,
        public ?string $url = null
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $user = User::find($this->userId);
        
        if ($user) {
            // Use Filament's built-in database notification system
            $notification = Notification::make()
                ->title($this->title)
                ->body($this->content)
                ->info();
            
            // Send to database (appears in Filament's notification bell icon)
            $notification->sendToDatabase($user);
        }
    }
}

