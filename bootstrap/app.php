<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withSchedule(function (Schedule $schedule): void {
        // Daily analytics commands - run at midnight UTC
        $schedule->command('analytics:daily-cost')
            ->dailyAt('00:00')
            ->timezone('UTC');

        $schedule->command('analytics:daily-goal')
            ->dailyAt('00:00')
            ->timezone('UTC');

        $schedule->command('analytics:daily-lead')
            ->dailyAt('00:00')
            ->timezone('UTC');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
