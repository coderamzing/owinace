<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\OnBoardService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class RegisterController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected OnBoardService $onBoardService
    ) {
        // Middleware is applied in routes/auth.php
    }

    /**
     * Show the registration form.
     */
    public function showRegistrationForm(): View
    {
        return view('auth.register');
    }

    /**
     * Handle a registration request for the application.
     */
    public function register(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'workspace_name' => ['required', 'string', 'max:255', 'unique:workspaces,name'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $result = $this->onBoardService->createWorkspaceWithAdmin(
                [
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => $request->password,
                ],
                [
                    'name' => $request->workspace_name,
                    'description' => $request->workspace_description ?? null,
                ]
            );

            $user = $result['user'];
            $workspace = $result['workspace'];

            event(new Registered($user));

            // Don't auto-login - user must verify email first
            return redirect()->route('login')->with('status', 'Thanks for signing up! Please check your email to verify your account before logging in.');
        } catch (\Exception $e) {
            Log::error("RegisterController: ".$e->getMessage());
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }
}
