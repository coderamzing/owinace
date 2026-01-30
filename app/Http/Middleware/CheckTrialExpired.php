<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckTrialExpired
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();
        
        // Check if user has a workspace
        if (!$user->workspace_id || !$user->workspace) {
            return $next($request);
        }

        $workspace = $user->workspace;

        // Check if tier_status is 'trial' and trial_end is less than today
        if ($workspace->tier_status === 'trial' && $workspace->trial_end && $workspace->trial_end->isPast()) {
            // Redirect to pricing page with a message
            return redirect()->route('pricing')
                ->with('error', 'Your trial period has expired. Please purchase a plan to continue using the platform.');
        }

        return $next($request);
    }
}
