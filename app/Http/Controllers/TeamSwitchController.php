<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeamSwitchController extends Controller
{
    public function switch(Request $request, Team $team): RedirectResponse
    {
        $workspaceId = session('workspace_id');
        
        // Verify the team belongs to the user's workspace
        if ($team->workspace_id === $workspaceId) {
            session(['team_id' => $team->id]);
        }
        
        return redirect()->back();
    }
}

