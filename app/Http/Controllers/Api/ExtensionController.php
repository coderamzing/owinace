<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Lead;
use App\Models\LeadKanban;
use App\Models\LeadSource;
use App\Models\Proposal;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use App\Services\ProposalService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class ExtensionController extends Controller
{
    public function test(): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Extension API is working']);
    }

    public function __construct(private ProposalService $proposalService)
    {
    }

    /**
     * Chrome extension login: POST {email, password} -> {token, team_id, data}.
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt(['email' => $validated['email'], 'password' => $validated['password']])) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid credentials',
            ], 401);
        }

        /** @var User|null $user */
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'error' => 'User not found',
            ], 401);
        }

        if (method_exists($user, 'hasVerifiedEmail') && !$user->hasVerifiedEmail()) {
            Auth::logout();

            return response()->json([
                'success' => false,
                'error' => 'Please verify your email address before logging in.',
            ], 403);
        }

        $teams = $this->getTeamsForUser($user);

        if ($teams->isEmpty()) {
            return response()->json([
                'success' => false,
                'error' => 'No teams associated with this user',
            ], 403);
        }

        $token = $this->generateToken($user);

        $teamsData = $teams->map(function (Team $team) {
            $sources = LeadSource::forTeam($team->id)
                ->get(['id', 'name', 'is_active', 'sort_order']);

            $stages = LeadKanban::forTeam($team->id)
                ->get(['id', 'name', 'code', 'is_active', 'sort_order']);

            return [
                'id' => $team->id,
                'name' => $team->name,
                'sources' => $sources,
                'stages' => $stages,
            ];
        });

        return response()->json([
            'success' => true,
            'token' => $token,
            'default_team_id' => $teams->first()->id,
            'data' => $teamsData,
        ]);
    }

    /**
     * Chrome extension logout. Stateless JWT, so client should discard token.
     */
    public function logout(): JsonResponse
    {
        return response()->json(['success' => true]);
    }

    /**
     * Generate a cover letter.
     * POST Bearer auth; body: {team_id, job_description, words?, type?} -> {title, content}
     */
    public function coverLetter(Request $request): JsonResponse
    {
        try {
            $user = $this->authenticate($request);
        } catch (AuthenticationException $e) {
            return $this->unauthorized($e->getMessage());
        }

        $validated = $request->validate([
            'team_id' => ['required', 'integer', 'min:1'],
            'job_description' => ['required', 'string'],
            'type' => ['nullable', 'string', 'in:beginner,intermediate,professional'],
            'words' => ['nullable', 'integer', 'min:50', 'max:2000'],
        ]);

        $teamId = (int) $validated['team_id'];

        if (!$this->userHasTeam($user, $teamId)) {
            return response()->json([
                'success' => false,
                'error' => 'Team not found for this user',
            ], 403);
        }

        $description = $validated['job_description'];
        $type = $validated['type'] ?? 'intermediate';

        $mapping = [
          'beginner' => 'pitch',  
          'intermediate' => 'experience',  
          'professional' => 'approach',  
        ];

        $type = $mapping[$type] ?? 'experience';

        $words = isset($validated['words']) ? (int) $validated['words'] : 180;

        try {
            $result = $this->proposalService->generateProposal(
                $description,
                $teamId,
                $type,
                $words
            );

            $proposal = Proposal::withoutTeam()->create([
                'user_id' => $user->id,
                'team_id' => $teamId,
                'title' => mb_substr($result['title'], 0, 255),
                'description' => $result['content'],
                'keywords' => '',
                'job_description' => $description,
                'sort_order' => 0,
            ]);

            return response()->json([
                'success' => true,
                'proposal_id' => $proposal->id,
                'title' => $proposal->title,
                'content' => $proposal->description,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Create a lead from the extension.
     * POST Bearer auth; body: {team_id, title, description?, url?, source_id, stage_id, expected_value?, contact?}
     */
    public function createLead(Request $request): JsonResponse
    {
        try {
            $user = $this->authenticate($request);
        } catch (AuthenticationException $e) {
            return $this->unauthorized($e->getMessage());
        }

        $validated = $request->validate([
            'team_id' => ['required', 'integer', 'min:1'],
            'title' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'url' => ['nullable', 'string', 'max:2000'],
            'source_id' => ['required', 'integer', 'min:1'],
            'stage_id' => ['required', 'integer', 'min:1'],
            'expected_value' => ['nullable', 'numeric'],
            'contact' => ['nullable', 'string'],
        ]);

        $teamId = (int) $validated['team_id'];

        if (!$this->userHasTeam($user, $teamId)) {
            return response()->json([
                'success' => false,
                'error' => 'Team not found for this user',
            ], 403);
        }

        $source = LeadSource::forTeam($teamId)->whereKey($validated['source_id'])->first();
        $stage = LeadKanban::forTeam($teamId)->whereKey($validated['stage_id'])->first();

        if (!$source || !$stage) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid source or stage for this team',
            ], 422);
        }

        try {
            $lead = Lead::withoutTeam()->create([
                'team_id' => $teamId,
                'title' => $validated['title'],
                'description' => $validated['description'] ?? '',
                'url' => $validated['url'] ?? '',
                'kanban_id' => $stage->id,
                'source_id' => $source->id,
                'expected_value' => $validated['expected_value'] ?? null,
                'assigned_member_id' => $user->id,
                'created_by_id' => $user->id,
            ]);

            if (!empty($validated['contact'])) {
                $this->attachContact($lead, $validated['contact'], $teamId);
            }

            return response()->json([
                'success' => true,
                'lead_id' => $lead->id,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function getTeamsForUser(User $user)
    {
        $memberTeamIds = TeamMember::withoutTeam()
            ->where('user_id', $user->id)
            ->pluck('team_id');

        return Team::where('created_by_id', $user->id)
            ->orWhereIn('id', $memberTeamIds)
            ->get()
            ->unique('id')
            ->values();
    }

    /**
     * @throws AuthenticationException
     */
    private function authenticate(Request $request): User
    {
        $token = $this->getBearerToken($request);

        if (!$token) {
            throw new AuthenticationException('Missing bearer token');
        }

        $payload = $this->decodeToken($token);

        $userId = $payload['sub'] ?? null;

        if (!$userId) {
            throw new AuthenticationException('Invalid token payload');
        }

        $user = User::find($userId);

        if (!$user) {
            throw new AuthenticationException('User not found');
        }

        Auth::setUser($user);

        return $user;
    }

    private function userHasTeam(User $user, int $teamId): bool
    {
        $isOwner = Team::where('id', $teamId)
            ->where('created_by_id', $user->id)
            ->exists();

        if ($isOwner) {
            return true;
        }

        return TeamMember::withoutTeam()
            ->where('team_id', $teamId)
            ->where('user_id', $user->id)
            ->exists();
    }

    private function attachContact(Lead $lead, string $contact, int $teamId): void
    {
        $names = preg_split('/\s+/', trim($contact), 2) ?: [];

        $firstName = $names[0] ?? '';
        $lastName = $names[1] ?? '';

        $contactModel = Contact::withoutTeam()->create([
            'team_id' => $teamId,
            'email' => $contact,
            'first_name' => $firstName,
            'last_name' => $lastName,
        ]);

        $lead->contacts()->attach($contactModel->id);
    }

    private function getBearerToken(Request $request): ?string
    {
        $header = $request->header('Authorization');

        if (!$header || !str_starts_with($header, 'Bearer ')) {
            return null;
        }

        return trim(substr($header, 7));
    }

    private function generateToken(User $user, int $hoursValid = 24): string
    {
        $header = [
            'alg' => 'HS256',
            'typ' => 'JWT',
        ];

        $payload = [
            'sub' => $user->id,
            'exp' => now()->addHours($hoursValid)->timestamp,
            'iat' => now()->timestamp,
            'type' => 'chrome_ext',
        ];

        $headerSegment = $this->base64UrlEncode(json_encode($header));
        $payloadSegment = $this->base64UrlEncode(json_encode($payload));
        $signature = $this->sign("{$headerSegment}.{$payloadSegment}");

        return "{$headerSegment}.{$payloadSegment}.{$signature}";
    }

    /**
     * @return array<string, mixed>
     *
     * @throws AuthenticationException
     */
    private function decodeToken(string $token): array
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            throw new AuthenticationException('Invalid token format');
        }

        [$headerSegment, $payloadSegment, $signature] = $parts;
        $expectedSignature = $this->sign("{$headerSegment}.{$payloadSegment}");

        if (!hash_equals($expectedSignature, $signature)) {
            throw new AuthenticationException('Invalid token signature');
        }

        $payload = json_decode($this->base64UrlDecode($payloadSegment), true);

        if (!is_array($payload)) {
            throw new AuthenticationException('Invalid token payload');
        }

        if (($payload['exp'] ?? 0) < now()->timestamp) {
            throw new AuthenticationException('Token expired');
        }

        return $payload;
    }

    private function sign(string $data): string
    {
        $secret = $this->getJwtSecret();

        return $this->base64UrlEncode(hash_hmac('sha256', $data, $secret, true));
    }

    private function getJwtSecret(): string
    {
        $key = config('app.key');

        if (str_starts_with($key, 'base64:')) {
            $decoded = base64_decode(substr($key, 7));

            if ($decoded !== false) {
                return $decoded;
            }
        }

        return $key;
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $data): string
    {
        $padding = strlen($data) % 4;

        if ($padding > 0) {
            $data .= str_repeat('=', 4 - $padding);
        }

        return base64_decode(strtr($data, '-_', '+/')) ?: '';
    }

    private function unauthorized(string $message): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error' => $message,
        ], 401);
    }
}

