<?php

namespace App\Http\Controllers;

use App\Services\TeamService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TeamInvitationController extends Controller
{
    public function __construct(
        protected TeamService $teamService
    ) {
    }

    /**
     * Show invitation acceptance page
     */
    public function show(string $token): View|RedirectResponse
    {
        $invitation = \App\Models\TeamInvitation::where('token', $token)
            ->where('status', 'pending')
            ->with(['team', 'workspace'])
            ->firstOrFail();

        if ($invitation->isExpired()) {
            return redirect()->route('login')
                ->with('error', 'This invitation has expired.');
        }

        $user = Auth::user();
        
        // If user is logged in and email matches, accept immediately
        if ($user && $user->email === $invitation->email) {
            try {
                $this->teamService->acceptInvitation($token, $user);
                return redirect()->route('dashboard')
                    ->with('success', 'You have successfully joined the team!');
            } catch (\Exception $e) {
                return redirect()->route('login')
                    ->with('error', $e->getMessage());
            }
        }

        return view('team-invitations.accept', [
            'invitation' => $invitation,
            'user' => $user,
        ]);
    }

    /**
     * Accept invitation (for logged in users)
     */
    public function accept(Request $request, string $token): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $invitation = \App\Models\TeamInvitation::where('token', $token)
            ->where('status', 'pending')
            ->firstOrFail();

        if ($invitation->email !== $request->email) {
            return back()->withErrors(['email' => 'Email does not match invitation.']);
        }

        $user = Auth::user();
        
        if (!$user || $user->email !== $request->email) {
            // Redirect to register with invitation token
            return redirect()->route('register', ['invitation' => $token])
                ->with('invitation_email', $request->email);
        }

        try {
            $this->teamService->acceptInvitation($token, $user);
            return redirect()->route('dashboard')
                ->with('success', 'You have successfully joined the team!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
