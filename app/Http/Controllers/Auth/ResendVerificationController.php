<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ResendVerificationController extends Controller
{
    /**
     * Resend verification email for unverified users (accessible without authentication)
     */
    public function resend(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors([
                'email' => __('We could not find a user with that email address.'),
            ]);
        }

        if ($user->hasVerifiedEmail()) {
            return back()->with('status', __('Your email address is already verified. You can login now.'));
        }

        // Send the verification email
        $user->sendEmailVerificationNotification();

        return back()->with('status', __('A fresh verification link has been sent to your email address.'));
    }
}

