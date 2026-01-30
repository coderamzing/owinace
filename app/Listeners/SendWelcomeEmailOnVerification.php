<?php

namespace App\Listeners;

use App\Models\Team;
use App\Models\Workspace;
use Illuminate\Auth\Events\Verified;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SendWelcomeEmailOnVerification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Verified $event): void
    {
        $user = $event->user;
        
        // Prevent duplicate sends using cache lock
        // This ensures the welcome email is only sent once per user verification
        $cacheKey = 'welcome_email_sent_' . $user->id;
        
        // Try to acquire a lock for 60 seconds
        // If lock already exists, another process is handling this, so skip
        $lock = Cache::lock($cacheKey, 60);
        
        if (!$lock->get()) {
            // Another process is already sending the email, skip
            return;
        }
        
        try {
            // Get the user's workspace and team
            $workspace = $user->workspace;
            
            if (!$workspace) {
                // User doesn't have a workspace yet, skip welcome email
                return;
            }
            
            // Get the default team for the workspace
            $team = Team::where('workspace_id', $workspace->id)->first();
            
            // Send welcome email to user/admin using EmailQueue job
            \App\Jobs\EmailQueue::dispatch(
                $user->email,
                'Welcome to ' . config('app.name'),
                null,
                null,
                'emails.workspace-welcome',
                [
                    'name' => $user->name,
                    'email' => $user->email,
                    'workspace' => $workspace->name,
                    'team' => $team?->name ?? $workspace->name,
                    'url' => url('/dashboard'),
                ]
            );
            
            // Mark as sent in cache for 1 hour to prevent any duplicate sends
            Cache::put($cacheKey, true, 3600);
        } catch (\Throwable $e) {
            // Log the error but do not block email verification
            Log::error('Failed to queue welcome email: ' . $e->getMessage(), ['user_id' => $user->id]);
        } finally {
            // Release the lock
            $lock->release();
        }
    }
}
