<?php

namespace App\Providers;

use App\Models\Lead;
use App\Models\Portfolio;
use App\Models\Team;
use App\Models\User;
use App\Observers\LeadObserver;
use App\Observers\PortfolioObserver;
use App\Observers\TeamObserver;
use App\Observers\UserObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register model observers
        Lead::observe(LeadObserver::class);
        Team::observe(TeamObserver::class);
        User::observe(UserObserver::class);
        Portfolio::observe(PortfolioObserver::class);
    }
}
