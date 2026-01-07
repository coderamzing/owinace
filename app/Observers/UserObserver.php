<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\File;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        $this->setRandomDefaultAvatar($user);
    }

    /**
     * Set a random default avatar for the user
     */
    private function setRandomDefaultAvatar(User $user): void
    {
        try {
            // Get all avatar files from the avatars directory
            $avatarsPath = public_path('images/avatars');
            
            if (!File::exists($avatarsPath)) {
                \Log::warning("Avatars directory not found: {$avatarsPath}");
                return;
            }

            $avatarFiles = File::files($avatarsPath);
            
            if (empty($avatarFiles)) {
                \Log::warning("No avatar files found in: {$avatarsPath}");
                return;
            }

            // Select a random avatar
            $randomAvatar = $avatarFiles[array_rand($avatarFiles)];
            
            // Add the random avatar to the user's media collection
            $user->addMedia($randomAvatar->getPathname())
                ->preservingOriginal()
                ->toMediaCollection('avatar');

            \Log::info("Set random avatar for user {$user->id}: {$randomAvatar->getFilename()}");
        } catch (\Exception $e) {
            \Log::error("Failed to set random avatar for user {$user->id}: " . $e->getMessage());
        }
    }
}

