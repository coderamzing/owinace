<?php

namespace App\Http\Middleware;

use App\Models\Workspace;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateWorkspaceApiToken
{
    /**
     * Validate X-Api-Token against workspaces.token and attach workspace_id to the request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header('X-Api-Token');

        if (! is_string($token) || $token === '') {
            return response()->json(['message' => 'Invalid or missing API token.'], 401);
        }

        $workspace = Workspace::query()->where('token', $token)->first();

        if ($workspace === null) {
            return response()->json(['message' => 'Invalid or missing API token.'], 401);
        }

        $request->merge([
            'workspace_id' => $workspace->id,
        ]);

        return $next($request);
    }
}
