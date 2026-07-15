<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendCampaignMatchWebhook;
use App\Models\Contact;
use App\Models\Lead;
use App\Models\LeadKanban;
use App\Models\LeadSource;
use App\Models\Proposal;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\UpworkCampaign;
use App\Models\UpworkCampaignJobStat;
use App\Models\UpworkJob;
use App\Models\UpworkProfile;
use App\Models\User;
use App\Services\BotAIService;
use App\Services\CampaignMatchWebhookService;
use App\Services\CapSolverService;
use App\Services\ProposalService;
use App\Services\ProxyValidationService;
use App\Support\ExtensionJwt;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Throwable;

class ExtensionController extends Controller
{
    public function test(): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Extension API is working']);
    }

    public function __construct(
        private ProposalService $proposalService,
        private BotAIService $botAIService,
        private CapSolverService $capSolverService,
        private CampaignMatchWebhookService $campaignMatchWebhookService,
        private ProxyValidationService $proxyValidationService,
    ) {
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

        $teams->loadMissing('workspace:id,token');

        $teamsData = $teams->map(function (Team $team) {
            $sources = LeadSource::forTeam($team->id)
                ->get(['id', 'name', 'is_active', 'sort_order']);

            $stages = LeadKanban::forTeam($team->id)
                ->get(['id', 'name', 'code', 'is_active', 'sort_order']);

            return [
                'id' => $team->id,
                'name' => $team->name,
                'api_token' => $team->workspace?->token,
                'sources' => $sources,
                'stages' => $stages,
                'coverletter_types' => ProposalService::$coverletterTypes,
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
     * Dashboard counts for the logged-in user.
     * GET Bearer auth -> {teams, campaigns, profiles}
     */
    public function dashboard(Request $request): JsonResponse
    {
        try {
            $user = $this->authenticate($request);
        } catch (AuthenticationException $e) {
            return $this->unauthorized($e->getMessage());
        }

        $teams = $this->getTeamsForUser($user);
        $teamIds = $teams->pluck('id');

        $profiles = UpworkProfile::withoutTeam()
            ->whereIn('team_id', $teamIds)
            ->where('is_active', true)
            ->count();

        $campaigns = UpworkCampaign::withoutTeam()
            ->whereIn('team_id', $teamIds)
            ->where('is_active', true)
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'teams' => $teams->count(),
                'profiles' => $profiles,
                'campaigns' => $campaigns,
            ],
        ]);
    }

    /**
     * List active Upwork profiles for a team.
     * POST Bearer auth; body: {team_id} -> [{id, title, email, code}]
     */
    public function profiles(Request $request): JsonResponse
    {
        try {
            $user = $this->authenticate($request);
        } catch (AuthenticationException $e) {
            return $this->unauthorized($e->getMessage());
        }

        $validated = $request->validate([
            'team_id' => ['required', 'integer', 'min:1'],
        ]);

        $teamId = (int) $validated['team_id'];

        if (! $this->userHasTeam($user, $teamId)) {
            return response()->json([
                'success' => false,
                'error' => 'Team not found for this user',
            ], 403);
        }

        $profiles = UpworkProfile::withoutTeam()
            ->where('team_id', $teamId)
            ->where('is_active', true)
            ->orderBy('title')
            ->get(['id', 'title', 'email', 'code', 'team_id', 'proxy_host', 'proxy_port', 'proxy_last_ip', 'proxy_validated_at'])
            ->map(fn (UpworkProfile $p) => [
                'id' => $p->id,
                'title' => $p->title,
                'email' => $p->email,
                'code' => $p->code,
                'team_id' => $p->team_id,
                'has_proxy' => $p->hasProxy(),
                'proxy_last_ip' => $p->proxy_last_ip,
                'proxy_validated_at' => $p->proxy_validated_at?->toIso8601String(),
            ])
            ->values();

        return response()->json([
            'success' => true,
            'data' => $profiles,
        ]);
    }

    /**
     * List active campaigns for a profile (scan mode — no auto_bidding filter).
     * POST Bearer auth; body: {team_id, code} -> campaigns with search_url
     */
    public function campaigns(Request $request): JsonResponse
    {
        try {
            $user = $this->authenticate($request);
        } catch (AuthenticationException $e) {
            return $this->unauthorized($e->getMessage());
        }

        $validated = $request->validate([
            'team_id' => ['required', 'integer', 'min:1'],
            'code' => ['required', 'string'],
        ]);

        $teamId = (int) $validated['team_id'];

        if (! $this->userHasTeam($user, $teamId)) {
            return response()->json([
                'success' => false,
                'error' => 'Team not found for this user',
            ], 403);
        }

        $profile = $this->resolveTeamProfile($teamId, $validated['code']);
        if ($profile instanceof JsonResponse) {
            return $profile;
        }

        $campaigns = UpworkCampaign::withoutTeam()
            ->select('id', 'title', 'search_url', 'is_active', 'auto_bidding', 'profile_id', 'webhook_url')
            ->where('is_active', true)
            ->where('profile_id', $profile->id)
            ->whereNotNull('search_url')
            ->where('search_url', '!=', '')
            ->orderBy('title')
            ->get()
            ->map(fn (UpworkCampaign $c) => [
                'id' => $c->id,
                'title' => $c->title,
                'search_url' => $c->search_url,
                'auto_bidding' => (bool) $c->auto_bidding,
                'has_webhook' => filled($c->webhook_url),
            ])
            ->values();

        return response()->json([
            'success' => true,
            'data' => $campaigns,
        ]);
    }

    /**
     * Recent scanned job UIDs (dedupe).
     * POST Bearer auth -> [uid, ...]
     */
    public function recentJobs(Request $request): JsonResponse
    {
        try {
            $this->authenticate($request);
        } catch (AuthenticationException $e) {
            return $this->unauthorized($e->getMessage());
        }

        $uids = UpworkJob::query()
            ->orderByDesc('id')
            ->limit(500)
            ->pluck('uid');

        return response()->json([
            'success' => true,
            'data' => $uids,
        ]);
    }

    /**
     * Recent analysis stats for a profile's campaigns.
     * POST Bearer auth; body: {team_id, code}
     */
    public function recentAnalysis(Request $request): JsonResponse
    {
        try {
            $user = $this->authenticate($request);
        } catch (AuthenticationException $e) {
            return $this->unauthorized($e->getMessage());
        }

        $validated = $request->validate([
            'team_id' => ['required', 'integer', 'min:1'],
            'code' => ['required', 'string'],
        ]);

        $teamId = (int) $validated['team_id'];

        if (! $this->userHasTeam($user, $teamId)) {
            return response()->json([
                'success' => false,
                'error' => 'Team not found for this user',
            ], 403);
        }

        $profile = $this->resolveTeamProfile($teamId, $validated['code']);
        if ($profile instanceof JsonResponse) {
            return $profile;
        }

        $campaignIds = UpworkCampaign::withoutTeam()
            ->where('is_active', true)
            ->where('profile_id', $profile->id)
            ->pluck('id');

        if ($campaignIds->isEmpty()) {
            return response()->json(['success' => true, 'data' => []]);
        }

        $statFields = ['job_id', 'campaign_id', 'is_matched', 'is_applied'];

        $result = UpworkCampaignJobStat::query()
            ->with('job:id,uid')
            ->select($statFields)
            ->whereIn('campaign_id', $campaignIds)
            ->orderByDesc('created_at')
            ->limit(500)
            ->get()
            ->map(fn (UpworkCampaignJobStat $stat) => array_merge(
                $stat->only($statFields),
                ['job_uid' => $stat->job?->uid],
            ))
            ->values()
            ->all();

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * Upsert Upwork job from scraped raw text (scan pipeline).
     * POST Bearer auth; body: {id, url, rawText, postedAt?, skills?}
     */
    public function pushJob(Request $request): JsonResponse
    {
        try {
            $this->authenticate($request);
        } catch (AuthenticationException $e) {
            return $this->unauthorized($e->getMessage());
        }

        $request->validate([
            'id' => ['required'],
            'url' => ['required'],
            'rawText' => ['required'],
        ]);

        $data = $request->all();

        $isExist = UpworkJob::where('uid', $data['id'])->first();
        if ($isExist) {
            return response()->json([
                'success' => true,
                'data' => $isExist->toArray(),
            ]);
        }

        try {
            $jobData = $this->botAIService->parseJob($request->all());
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }

        if (empty($jobData['posted_at'])) {
            $jobData['posted_at'] = $data['posted_at'] ?? Carbon::now()->format('Y-m-d H:i:s');
        }
        if (! is_array($jobData['skills'] ?? null)) {
            $jobData['skills'] = is_array($data['skills'] ?? null) ? $data['skills'] : [];
        }

        $validator = Validator::make($jobData, [
            'title' => ['required', 'string'],
            'description' => ['required', 'string'],
            'skills' => ['nullable', 'array'],
            'posted_at' => ['required', 'date'],
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid job data from AI JOB ID: '.$data['id'],
                'details' => $validator->errors(),
            ], 422);
        }

        $jobData['skills'] = $jobData['skills'] ?? [];

        UpworkJob::query()->updateOrCreate(
            ['uid' => $data['id']],
            [
                'title' => $jobData['title'],
                'description' => $jobData['description'],
                'skills' => $jobData['skills'],
                'url' => $data['url'],
                'questions' => $jobData['questions'] ?? null,
                'connects' => $jobData['connects'] ?? null,
                'location' => $jobData['location'] ?? null,
                'preferred_location' => $jobData['preferred_location'] ?? null,
                'preferred_talent' => $jobData['preferred_talent'] ?? null,
                'proposals' => $jobData['proposals'] ?? null,
                'client_totalspent' => $jobData['client_totalspent'] ?? null,
                'client_jobposted' => $jobData['client_jobposted'] ?? null,
                'client_avgspent' => ($jobData['client_jobposted'] ?? 0) > 0
                    ? number_format($jobData['client_totalspent'] / $jobData['client_jobposted'], 2, '.', '')
                    : '0.00',
                'client_hirerate' => $jobData['client_hirerate'] ?? null,
                'client_hires' => $jobData['client_hires'] ?? null,
                'interviews' => $jobData['interviews'] ?? null,
                'invites_sent' => $jobData['invitesent'] ?? null,
                'client_name' => $jobData['client_name'] ?? null,
                'client_since' => $jobData['client_since'] ?? null,
                'client_rating' => $jobData['client_rating'] ?? null,
                'type' => $jobData['type'] ?? null,
                'posted_at' => $jobData['posted_at'],
                'client_avghourlyrate' => $jobData['client_avghourlyrate'] ?? null,
                'client_openjob' => $jobData['client_openjob'] ?? null,
                'client_org' => $jobData['client_org'] ?? null,
                'client_website' => $jobData['client_website'] ?? null,
                'client_project' => $jobData['client_project'] ?? null,
                'is_warm' => $jobData['is_warm'] ?? 0,
                'hires' => $jobData['hires'] ?? 0,
            ]
        );

        return response()->json([
            'success' => true,
            'data' => $jobData,
        ]);
    }

    /**
     * Analyze job against campaign; fires match webhook server-side when matched.
     * POST Bearer auth; body: {team_id, code, jobId, campaignId} -> {is_matched}
     */
    public function analyzeJob(Request $request): JsonResponse
    {
        try {
            $user = $this->authenticate($request);
        } catch (AuthenticationException $e) {
            return $this->unauthorized($e->getMessage());
        }

        $validated = $request->validate([
            'team_id' => ['required', 'integer', 'min:1'],
            'code' => ['required', 'string'],
            'jobId' => ['required'],
            'campaignId' => ['required', 'integer'],
        ]);

        $teamId = (int) $validated['team_id'];

        if (! $this->userHasTeam($user, $teamId)) {
            return response()->json([
                'success' => false,
                'error' => 'Team not found for this user',
            ], 403);
        }

        $profile = $this->resolveTeamProfile($teamId, $validated['code']);
        if ($profile instanceof JsonResponse) {
            return $profile;
        }

        $jobUid = $validated['jobId'];
        $campaignId = (int) $validated['campaignId'];

        $campaign = UpworkCampaign::withoutTeam()->where('id', $campaignId)->first();
        $job = UpworkJob::where('uid', $jobUid)->first();

        if (! $job || ! $campaign) {
            return response()->json([
                'success' => false,
                'error' => 'Job or campaign not found',
            ], 404);
        }

        if ((int) $campaign->profile_id !== (int) $profile->id) {
            return response()->json([
                'success' => false,
                'error' => 'Campaign does not belong to profile',
            ], 403);
        }

        if ((int) $campaign->team_id !== $teamId) {
            return response()->json([
                'success' => false,
                'error' => 'Campaign does not belong to team',
            ], 403);
        }

        if ((int) $job->is_expired === 1) {
            return $this->recordAnalysisAndRespond($job, $campaign, false, 'Job expired');
        }

        if ($job->hires > 0) {
            return $this->recordAnalysisAndRespond($job, $campaign, false, 'Already hired');
        }

        $existingStat = UpworkCampaignJobStat::where('job_id', $job->id)
            ->where('campaign_id', $campaignId)
            ->first();

        if ($existingStat) {
            return response()->json([
                'success' => true,
                'is_matched' => (bool) $existingStat->is_matched,
                'is_applied' => (bool) $existingStat->is_applied,
            ]);
        }

        $ruleRejection = $campaign->ruleRejectionReasonForJob($job);
        if ($ruleRejection !== null) {
            return $this->recordAnalysisAndRespond($job, $campaign, false, $ruleRejection);
        }

        $campaignData = $campaign->toArray();
        $campaignData['portfolios'] = $campaign->portfoliosPromptText();

        try {
            $result = $this->botAIService->analyzeJob($job->toArray(), $campaignData);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }

        return $this->recordAnalysisAndRespond(
            $job,
            $campaign,
            (bool) $result['is_matched'],
            $result['reason'] ?? '',
        );
    }

    /**
     * Solve Cloudflare via CapSolver using the profile proxy (API key stays server-side).
     * POST Bearer; body: {team_id, code, websiteURL, userAgent, html}
     * -> {cookies, token?, userAgent?}
     */
    public function solveCaptcha(Request $request): JsonResponse
    {
        try {
            $user = $this->authenticate($request);
        } catch (AuthenticationException $e) {
            return $this->unauthorized($e->getMessage());
        }

        $validated = $request->validate([
            'team_id' => ['required', 'integer', 'min:1'],
            'code' => ['required', 'string'],
            'websiteURL' => ['required', 'url'],
            'userAgent' => ['required', 'string'],
            'html' => ['required', 'string', 'min:50'],
        ]);

        $teamId = (int) $validated['team_id'];

        if (! $this->userHasTeam($user, $teamId)) {
            return response()->json([
                'success' => false,
                'error' => 'Team not found for this user',
            ], 403);
        }

        $profile = $this->resolveTeamProfile($teamId, $validated['code']);
        if ($profile instanceof JsonResponse) {
            return $profile;
        }

        if (! $profile->hasProxy()) {
            return response()->json([
                'success' => false,
                'error' => 'Profile proxy is required for CapSolver',
            ], 422);
        }

        $solution = $this->capSolverService->solveAntiCloudflare(
            $validated['websiteURL'],
            $validated['userAgent'],
            $validated['html'],
            $profile->proxyConfigForBot(),
        );

        if ($solution === null) {
            return response()->json([
                'success' => false,
                'error' => 'CapSolver failed to solve the challenge',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'data' => $solution,
        ]);
    }

    /**
     * Push an ops alert to a campaign webhook (e.g. Upwork logged out).
     * Rate-limited per profile. POST {team_id, code, campaign_id?, type, message?}
     */
    public function alertWebhook(Request $request): JsonResponse
    {
        try {
            $user = $this->authenticate($request);
        } catch (AuthenticationException $e) {
            return $this->unauthorized($e->getMessage());
        }

        $validated = $request->validate([
            'team_id' => ['required', 'integer', 'min:1'],
            'code' => ['required', 'string'],
            'campaign_id' => ['nullable', 'integer', 'min:1'],
            'type' => ['required', 'string', 'in:upwork_logged_out,scan_error'],
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        $teamId = (int) $validated['team_id'];

        if (! $this->userHasTeam($user, $teamId)) {
            return response()->json([
                'success' => false,
                'error' => 'Team not found for this user',
            ], 403);
        }

        $profile = $this->resolveTeamProfile($teamId, $validated['code']);
        if ($profile instanceof JsonResponse) {
            return $profile;
        }

        $cacheKey = 'ext_webhook_alert:'.$profile->id.':'.$validated['type'];
        if (Cache::has($cacheKey)) {
            return response()->json([
                'success' => true,
                'skipped' => true,
                'message' => 'Alert already sent recently',
            ]);
        }

        $campaignQuery = UpworkCampaign::withoutTeam()
            ->where('profile_id', $profile->id)
            ->where('team_id', $teamId)
            ->whereNotNull('webhook_url')
            ->where('webhook_url', '!=', '');

        if (! empty($validated['campaign_id'])) {
            $campaignQuery->where('id', (int) $validated['campaign_id']);
        }

        $campaign = $campaignQuery->orderByDesc('is_active')->first();

        if (! $campaign) {
            return response()->json([
                'success' => false,
                'error' => 'No campaign webhook configured for this profile',
            ], 422);
        }

        $message = match ($validated['type']) {
            'upwork_logged_out' => collect([
                '**Upwork not logged in**',
                '**Profile:** '.($profile->title ?: $profile->code),
                '**Campaign:** '.$campaign->title,
                '**Action:** Log into Upwork in Chrome, then resume LeadCliq scan.',
                filled($validated['message'] ?? null) ? '**Note:** '.$validated['message'] : null,
            ])->filter()->implode("\n"),
            default => (string) ($validated['message']
                ?? ("**LeadCliq scan alert**\n**Profile:** ".($profile->title ?: $profile->code))),
        };

        $result = $this->campaignMatchWebhookService->notifyAlert($campaign, $message);

        if ($result['success']) {
            Cache::put($cacheKey, true, now()->addMinutes(30));
        }

        return response()->json([
            'success' => $result['success'],
            'skipped' => false,
            'message' => $result['message'],
            'status' => $result['status'],
        ], $result['success'] ? 200 : 422);
    }

    /**
     * Write a campaign-based cover letter from a pasted/scraped job description.
     * POST {team_id, campaign_id, job_description, title?, questions?, client_name?}
     */
    public function campaignCoverLetter(Request $request): JsonResponse
    {
        try {
            $user = $this->authenticate($request);
        } catch (AuthenticationException $e) {
            return $this->unauthorized($e->getMessage());
        }

        $validated = $request->validate([
            'team_id' => ['required', 'integer', 'min:1'],
            'campaign_id' => ['required', 'integer', 'min:1'],
            'job_description' => ['required', 'string', 'min:50'],
            'title' => ['nullable', 'string', 'max:500'],
            'client_name' => ['nullable', 'string', 'max:255'],
            'questions' => ['nullable', 'array'],
            'questions.*' => ['nullable', 'string', 'max:2000'],
        ]);

        $teamId = (int) $validated['team_id'];

        if (! $this->userHasTeam($user, $teamId)) {
            return response()->json([
                'success' => false,
                'error' => 'Team not found for this user',
            ], 403);
        }

        $campaign = UpworkCampaign::withoutTeam()
            ->with('linkedPortfolios')
            ->where('id', (int) $validated['campaign_id'])
            ->where('team_id', $teamId)
            ->first();

        if (! $campaign) {
            return response()->json([
                'success' => false,
                'error' => 'Campaign not found',
            ], 404);
        }

        $questions = collect($validated['questions'] ?? [])
            ->map(fn ($q) => trim((string) $q))
            ->filter()
            ->values()
            ->all();

        $jobData = [
            'title' => trim((string) ($validated['title'] ?? '')) ?: 'Upwork job',
            'description' => $validated['job_description'],
            'questions' => $questions,
            'client_name' => trim((string) ($validated['client_name'] ?? '')) ?: 'Client',
        ];

        $campaignData = [
            'ai_prompt' => $campaign->ai_prompt,
            'ai_cover_letter' => (bool) $campaign->ai_cover_letter,
            'ai_instructions' => $campaign->ai_instruction,
            'portfolios' => $campaign->portfoliosPromptText(),
            'experience' => $campaign->experience,
            'questions_context' => $campaign->questions_context,
        ];

        try {
            $result = $this->botAIService->writeCoverLetter($jobData, $campaignData);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }

        return response()->json([
            'success' => true,
            'cover_letter' => $result['cover_letter'] ?? '',
            'questions' => $result['questions'] ?? [],
            'campaign' => [
                'id' => $campaign->id,
                'title' => $campaign->title,
            ],
        ]);
    }

    /**
     * Return profile proxy credentials + expected egress IP for the extension.
     * Optionally re-validates the proxy so expected_ip is fresh.
     * POST {team_id, code, refresh?}
     */
    public function profileProxy(Request $request): JsonResponse
    {
        try {
            $user = $this->authenticate($request);
        } catch (AuthenticationException $e) {
            return $this->unauthorized($e->getMessage());
        }

        $validated = $request->validate([
            'team_id' => ['required', 'integer', 'min:1'],
            'code' => ['required', 'string'],
            'refresh' => ['nullable', 'boolean'],
        ]);

        $teamId = (int) $validated['team_id'];

        if (! $this->userHasTeam($user, $teamId)) {
            return response()->json([
                'success' => false,
                'error' => 'Team not found for this user',
            ], 403);
        }

        $profile = $this->resolveTeamProfile($teamId, $validated['code']);
        if ($profile instanceof JsonResponse) {
            return $profile;
        }

        if (! $profile->hasProxy()) {
            return response()->json([
                'success' => false,
                'error' => 'Profile proxy is not configured',
            ], 422);
        }

        $proxy = $profile->proxyConfigForBot();
        $expectedIp = $profile->proxy_last_ip;
        $refreshed = false;

        $shouldRefresh = (bool) ($validated['refresh'] ?? false)
            || blank($expectedIp)
            || $profile->proxy_validated_at === null
            || $profile->proxy_validated_at->lt(now()->subDays(7));

        if ($shouldRefresh) {
            $result = $this->proxyValidationService->validate([
                'host' => $proxy['host'],
                'port' => $proxy['port'],
                'username' => $proxy['username'],
                'password' => $proxy['password'],
                'protocol' => $proxy['protocol'],
            ]);

            if (! $result['success']) {
                return response()->json([
                    'success' => false,
                    'error' => $result['message'] ?? 'Proxy validation failed',
                ], 422);
            }

            $expectedIp = $result['ip'] ?? null;
            $profile->forceFill([
                'proxy_last_ip' => $expectedIp,
                'proxy_validated_at' => now(),
            ])->save();
            $proxy['last_ip'] = $expectedIp;
            $refreshed = true;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'code' => $profile->code,
                'title' => $profile->title,
                'expected_ip' => $expectedIp,
                'refreshed' => $refreshed,
                'proxy' => [
                    'host' => $proxy['host'],
                    'port' => $proxy['port'],
                    'username' => $proxy['username'],
                    'password' => $proxy['password'],
                    'protocol' => $proxy['protocol'] ?: 'http',
                ],
            ],
        ]);
    }

    private function recordAnalysisAndRespond(
        UpworkJob $job,
        UpworkCampaign $campaign,
        bool $isMatched,
        string $note,
    ): JsonResponse {
        UpworkCampaignJobStat::updateOrCreate(
            [
                'job_id' => $job->id,
                'campaign_id' => $campaign->id,
            ],
            [
                'is_matched' => $isMatched,
                'note' => $note,
            ]
        );

        if ($isMatched && filled($campaign->webhook_url)) {
            SendCampaignMatchWebhook::dispatch($campaign->id, $job->id, $note)
                ->afterResponse();
        }

        return response()->json([
            'success' => true,
            'is_matched' => $isMatched,
        ]);
    }

    /**
     * @return UpworkProfile|JsonResponse
     */
    private function resolveTeamProfile(int $teamId, string $code): UpworkProfile|JsonResponse
    {
        $profile = UpworkProfile::withoutTeam()
            ->where('code', $code)
            ->where('team_id', $teamId)
            ->where('is_active', true)
            ->first();

        if (! $profile) {
            return response()->json([
                'success' => false,
                'error' => 'Profile not found',
            ], 404);
        }

        return $profile;
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

        $user = ExtensionJwt::userFromBearer($token);
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

    private function generateToken(User $user, int $hoursValid = 750): string
    {
        return ExtensionJwt::encode($user, $hoursValid);
    }

    private function unauthorized(string $message): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error' => $message,
        ], 401);
    }
}

