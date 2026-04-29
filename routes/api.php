<?php

use App\Http\Controllers\Api\BotController;
use App\Http\Controllers\Api\ExtensionController;
use Illuminate\Support\Facades\Route;

Route::prefix('/extension')->controller(ExtensionController::class)->group(function () {
    Route::get('/test', 'test')->name('extension_test');
    Route::post('/login', 'login')->name('extension_login');
    Route::post('/logout', 'logout')->name('extension_logout');
    Route::post('/coverletter', 'coverLetter')->name('extension_coverletter');
    Route::post('/lead', 'createLead')->name('extension_lead_create');
});

Route::prefix('/bot')->middleware('workspace.token')->controller(BotController::class)->group(function () {
    Route::get('/test', 'test')->name('bot_test'); // return hello
    Route::post('/campaign', 'campaign')->name('bot_campaign'); // return active campaign data for the bot
    Route::post('/job/expired', 'jobExpired')->name('bot_job_expired'); // set the job as expired
    Route::post('/job', 'job')->name('bot_job'); // Insert update job data in database
    Route::post('/writer', 'writer')->name('bot_writer'); // write the cover letter for the job, it return cover letter, and answer for the job quetions
    Route::post('/apply', 'apply')->name('bot_apply'); // it create the lead entery in databsae from job and assigned member of campaine
    Route::post('/analysis', 'analysis')->name('bot_analysis'); // it analysis the job related to campign if the poritfilio is matched or not
});
