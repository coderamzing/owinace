<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RazorpayController;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/dashboard', function () {
    return redirect('/admin'); // Redirect to Filament admin panel
})->middleware(['auth', 'verified'])->name('dashboard');

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
Route::get('/razorpay/success', [RazorpayController::class, 'success'])->name('razorpay.success');
Route::get('/razorpay/thanks', function () {
    return view('razorpay.thanks');
})->name('razorpay.thanks');
Route::get('/razorpay/cancel', [RazorpayController::class, 'cancel'])->name('razorpay.cancel');
Route::post('/razorpay/webhook', [RazorpayController::class, 'handle'])->name('razorpay.webhook')->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);


require __DIR__.'/auth.php';
