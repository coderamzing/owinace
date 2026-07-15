<?php

namespace App\Http\Middleware;

use App\Models\Team;
use App\Models\TeamMember;
use App\Models\UpworkProfile;
use App\Models\User;
use App\Models\Workspace;
use App\Support\ExtensionJwt;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateWorkspaceApiToken
{
    /**
     * Accept either:
     * - X-Api-Token (workspace token — upbot2 Leadcliq)
     * - Authorization: Bearer (extension JWT — same login as chrome ext)
     *
     * Attaches workspace_id to the request for BotController.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiToken = $request->header('X-Api-Token');
        if (is_string($apiToken) && $apiToken !== '') {
            $workspace = Workspace::query()->where('token', $apiToken)->first();
            if ($workspace === null) {
                return response()->json(['message' => 'Invalid or missing API token.'], 401);
            }

            $request->merge(['workspace_id' => $workspace->id]);

            return $next($request);
        }

        $bearer = $this->bearerToken($request);
        if ($bearer !== null) {
            try {
                $user = ExtensionJwt::userFromBearer($bearer);
            } catch (AuthenticationException $e) {
                return response()->json(['message' => $e->getMessage()], 401);
            }

            $workspace = $this->resolveWorkspaceForUser($request, $user);
            if ($workspace === null) {
                return response()->json(['message' => 'No workspace found for this user/team.'], 403);
            }

            $request->attributes->set('auth_user', $user);
            $request->merge(['workspace_id' => $workspace->id]);

            return $next($request);
        }

        return response()->json(['message' => 'Invalid or missing API token.'], 401);
    }

    private function bearerToken(Request $request): ?string
    {
        $header = $request->header('Authorization');
        if (! is_string($header) || ! str_starts_with($header, 'Bearer ')) {
            return null;
        }

        $token = trim(substr($header, 7));

        return $token !== '' ? $token : null;
    }

    private function resolveWorkspaceForUser(Request $request, User $user): ?Workspace
    {
        $teamId = (int) $request->input('team_id', 0);
        if ($teamId > 0 && $this->userHasTeam($user, $teamId)) {
            return Team::query()->with('workspace')->find($teamId)?->workspace;
        }

        $code = $request->input('code');
        if (is_string($code) && $code !== '') {
            $profile = UpworkProfile::withoutTeam()
                ->with('team.workspace')
                ->where('code', $code)
                ->where('is_active', true)
                ->first();

            if ($profile?->team_id && $this->userHasTeam($user, (int) $profile->team_id)) {
                return $profile->team?->workspace;
            }
        }

        $memberTeamIds = TeamMember::withoutTeam()
            ->where('user_id', $user->id)
            ->pluck('team_id');

        $team = Team::query()
            ->with('workspace')
            ->where(function ($q) use ($user, $memberTeamIds) {
                $q->where('created_by_id', $user->id)
                    ->orWhereIn('id', $memberTeamIds);
            })
            ->orderBy('id')
            ->first();

        return $team?->workspace;
    }

    private function userHasTeam(User $user, int $teamId): bool
    {
        if (Team::query()->where('id', $teamId)->where('created_by_id', $user->id)->exists()) {
            return true;
        }

        return TeamMember::withoutTeam()
            ->where('team_id', $teamId)
            ->where('user_id', $user->id)
            ->exists();
    }
}
