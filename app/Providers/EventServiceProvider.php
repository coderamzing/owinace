<?php

namespace App\Providers;

use App\Events\LeadWon;
use App\Events\TeamCreated;
use App\Listeners\SendLeadWonNotification;
use App\Listeners\SetupTeamDefaults;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        LeadWon::class => [
            SendLeadWonNotification::class,
        ],
        TeamCreated::class => [
            SetupTeamDefaults::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}

