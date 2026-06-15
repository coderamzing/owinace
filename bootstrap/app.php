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
        $middleware->alias([
            'workspace.token' => \App\Http\Middleware\ValidateWorkspaceApiToken::class,
        ]);
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

        // Daily follow-up reminder - run at midnight UTC
        $schedule->command('reminder:daily-followup')
            ->dailyAt('00:00')
            ->timezone('UTC');

        // Weekly summary - run every Monday at midnight UTC
        $schedule->command('summary:weekly')
            ->weeklyOn(1, '00:00')
            ->timezone('UTC');

        // Monthly AI insights for admin review - run twice per month (1st & 15th) at midnight UTC
        $schedule->command('ai:monthly-insights')
            ->twiceMonthly(1, 15, '00:00')
            ->timezone('UTC');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
