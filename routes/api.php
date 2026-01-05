<?php

use App\Http\Controllers\Api\ExtensionController;
use Illuminate\Support\Facades\Route;

Route::prefix('extension')->controller(ExtensionController::class)->group(function () {
    Route::post('/login', 'login')->name('extension_login');
    Route::post('/logout', 'logout')->name('extension_logout');
    Route::post('/coverletter', 'coverLetter')->name('extension_coverletter');
    Route::post('/lead', 'createLead')->name('extension_lead_create');
});
