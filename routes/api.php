<?php

use App\Http\Controllers\Api\BotController;
use App\Http\Controllers\Api\ExtensionController;
use Illuminate\Support\Facades\Route;

/*
| Shared API for upbot2 + Chrome extension.
| Auth: X-Api-Token (workspace) or Authorization: Bearer (extension JWT).
*/
Route::prefix('/bot')->controller(BotController::class)->group(function () {
    // No token — issues JWT / api_token
    Route::post('/login', 'login')->name('bot_login');
    Route::post('/logout', 'logout')->name('bot_logout');

    Route::middleware('workspace.token')->group(function () {
        Route::get('/test', 'test')->name('bot_test');
        Route::post('/dashboard', 'dashboard')->name('bot_dashboard');
        Route::post('/profiles', 'profiles')->name('bot_profiles');
        Route::post('/campaigns', 'campaigns')->name('bot_campaigns'); // scan list
        Route::post('/campaign', 'campaign')->name('bot_campaign'); // auto-bid filter
        Route::post('/proxy', 'proxy')->name('bot_proxy');
        Route::post('/capsolver', 'capsolver')->name('bot_capsolver');
        Route::post('/coverletter', 'coverletter')->name('bot_coverletter');

        Route::post('/recent', 'recent')->name('bot_recent');
        Route::post('/profile/validate', 'validateProfile')->name('bot_profile_validate');
        Route::post('/job/expired', 'jobExpired')->name('bot_job_expired');
        Route::post('/job', 'job')->name('bot_job');
        Route::post('/writer', 'writer')->name('bot_writer');
        Route::post('/apply', 'apply')->name('bot_apply');
        Route::post('/job-stat', 'jobStat')->name('bot_job_stat');
        Route::post('/analysis', 'analysis')->name('bot_analysis');
        Route::post('/analysis/recent', 'recentAnalysis')->name('bot_recent_analysis');
        Route::post('/alert', 'alert')->name('bot_alert');
    });
});

// Legacy extension paths (login aliases + old CRM helpers)
Route::prefix('/extension')->controller(ExtensionController::class)->group(function () {
    Route::get('/test', 'test')->name('extension_test');
    Route::post('/login', 'login')->name('extension_login');
    Route::post('/logout', 'logout')->name('extension_logout');
    Route::post('/coverletter', 'coverLetter')->name('extension_coverletter');
    Route::post('/lead', 'createLead')->name('extension_lead_create');
});
