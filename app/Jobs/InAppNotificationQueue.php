<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\CustomDatabaseNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Actions\Action;

/**
 * In-App Notification Queue Job
 * 
 * This is used internally by NotificationService to send in-app notifications.
 * For general use, use NotificationQueue instead which handles permissions and preferences.
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
            $notification = FilamentNotification::make()
            ->title($this->title)
            ->body($this->content);
            if ($this->url) {
                $notification->actions([
                    Action::make('view')
                        ->button()
                        ->url($this->url)
                        ->markAsRead(),
                ]);
            }
        }
    }
}

