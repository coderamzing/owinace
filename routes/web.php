<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RazorpayController;
use App\Http\Controllers\RazorpayWebhookController;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/how-it-works', function () {
    return view('how-it-works');
})->name('how-it-works');

Route::get('/contact', function () {
    return view('contact');
})->name('contact');

Route::get('/privacy-policy', function () {
    return view('privacy-policy');
})->name('privacy-policy');

Route::get('/terms', function () {
    return view('terms');
})->name('terms');

Route::get('/refund-policy', function () {
    return view('refund-policy');
})->name('refund-policy');

Route::get('/support', function () {
    return view('support');
})->name('support');

Route::get('/faq', function () {
    return view('faq');
})->name('faq');

Route::get('/dashboard', function () {
    return redirect('/admin'); // Redirect to Filament admin panel
})->middleware(['auth', 'verified'])->name('dashboard');

// Temporary route to clear opcache - REMOVE AFTER USE
Route::get('/clear-opcache-temp', function () {
    if (function_exists('opcache_reset')) {
        opcache_reset();
        return 'OPcache cleared successfully!';
    }
    return 'OPcache not available';
});

// Team invitation routes
Route::get('/team-invitations/{token}', [\App\Http\Controllers\TeamInvitationController::class, 'show'])->name('team-invitations.show');
Route::post('/team-invitations/{token}/accept', [\App\Http\Controllers\TeamInvitationController::class, 'accept'])->name('team-invitations.accept');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Team switching
    Route::post('/team/switch/{team}', [\App\Http\Controllers\TeamSwitchController::class, 'switch'])->name('team.switch');
});


// Razorpay routes
Route::post('/razorpay/create-order', [RazorpayController::class, 'createOrder'])->name('razorpay.create-order');
Route::post('/razorpay/create-credit-order', [RazorpayController::class, 'createCreditOrder'])->name('razorpay.create-credit-order')->middleware('auth');
Route::get('/razorpay/success', [RazorpayController::class, 'success'])->name('razorpay.success');
Route::get('/razorpay/thanks', function () {
    return view('razorpay.thanks');
})->name('razorpay.thanks');
Route::get('/razorpay/cancel', [RazorpayController::class, 'cancel'])->name('razorpay.cancel');
Route::post('/razorpay/webhook', [RazorpayWebhookController::class, 'handle'])->name('razorpay.webhook')->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);


require __DIR__.'/auth.php';
