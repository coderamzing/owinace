<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendCampaignMatchWebhook;
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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Throwable;

class BotController extends Controller
{
    public function __construct(
        private BotAIService $botAIService,
        private CampaignMatchWebhookService $campaignMatchWebhookService,
        private CapSolverService $capSolverService,
        private ProxyValidationService $proxyValidationService,
    ) {}

    public function test(): JsonResponse
    {
        return response()->json(['message' => 'hello']);
    }

    public function recent(): JsonResponse
    {
        $uids = UpworkJob::query()
            ->orderByDesc('id')
            ->limit(500)
            ->pluck('uid');

        return response()->json($uids);
    }

    public function campaign(Request $request): JsonResponse
    {
        $profileOrError = $this->resolveProfileFromRequest($request);
        if ($profileOrError instanceof JsonResponse) {
            return $profileOrError;
        }

        $campaign = UpworkCampaign::withoutTeam()
            ->select('id', 'title', 'bidding_timezone', 'auto_bidding', 'is_active', 'search_url', 'max_daily_bid')
            ->with('slots:id,campaign_id,clock_in,clock_out')
            ->where('is_active', true)
            ->where('auto_bidding', true)
            ->where('profile_id', $profileOrError->id)
            ->get();

        $campaign = $campaign->filter(function ($item) {
            if ($item->max_daily_bid <= 0) {
                return true;
            }

            return ! $item->hasReachedDailyBidLimit();
        })->values();

        $campaign = $campaign->filter(function ($item) {
            return $item->isWithinClockSlots();
        })->values();

        return response()->json($campaign);
    }

    /**
     * Upsert job by uid. All listed fields are required.
     */
    public function job(Request $request): JsonResponse
    {
        $request->validate([
            'id' => ['required'],
            'url' => ['required'],
            'rawText' => ['required'],
        ]);

        $data = $request->all();
        // Upwork job UIDs exceed JS safe-integer range — always store as string
        $uid = trim((string) $data['id']);
        if ($uid === '') {
            return response()->json(['error' => 'Job id is required'], 422);
        }

        $isExist = UpworkJob::where('uid', $uid)->first();
        if ($isExist) {
            return response()->json($isExist->toArray());
        }

        $jobData = $this->botAIService->parseJob(array_merge($data, ['id' => $uid]));

        if (empty($jobData['posted_at'])) {
            $jobData['posted_at'] = $data['posted_at'] ?? Carbon::now()->format('Y-m-d H:i:s');
        }
        if (! is_array($jobData['skills'] ?? null)) {
            $jobData['skills'] = is_array($data['skills'] ?? null) ? $data['skills'] : [];
        }

        // Validate AI output mandatory fields
        $validator = Validator::make($jobData, [
            'title' => ['required', 'string'],
            'description' => ['required', 'string'],
            'skills' => ['nullable', 'array'],
            'posted_at' => ['required', 'date'],
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => 'Invalid job data from AI JOB ID: '.$uid,
                'details' => $validator->errors(),
            ], 422);
        }

        $jobData['skills'] = $jobData['skills'] ?? [];

        UpworkJob::query()->updateOrCreate(
            ['uid' => $uid],
            [
                'title' => $jobData['title'],
                'description' => $jobData['description'],
                'skills' => $jobData['skills'],
                'url' => $data['url'],
                'questions' => $jobData['questions'],
                'connects' => $jobData['connects'],
                'location' => $jobData['location'],
                'preferred_location' => $jobData['preferred_location'] ?? null,
                'preferred_talent' => $jobData['preferred_talent'] ?? null,
                'proposals' => $jobData['proposals'],
                'client_totalspent' => $jobData['client_totalspent'],
                'client_jobposted' => $jobData['client_jobposted'],
                'client_avgspent' => ($jobData['client_jobposted'] ?? 0) > 0
                    ? number_format($jobData['client_totalspent'] / $jobData['client_jobposted'], 2, '.', '')
                    : '0.00',
                'client_hirerate' => $jobData['client_hirerate'],
                'client_hires' => $jobData['client_hires'],
                'interviews' => $jobData['interviews'],
                'invites_sent' => $jobData['invitesent'],
                'client_name' => $jobData['client_name'],
                'client_since' => $jobData['client_since'],
                'client_rating' => $jobData['client_rating'],
                'type' => $jobData['type'],
                'posted_at' => $jobData['posted_at'],
                'client_avghourlyrate' => $jobData['client_avghourlyrate'],
                'client_openjob' => $jobData['client_openjob'],
                'client_org' => $jobData['client_org'],
                'client_website' => $jobData['client_website'],
                'client_project' => $jobData['client_project'],
                'is_warm' => $jobData['is_warm'] ?? 0,
                'hires' => $jobData['hires'] ?? 0,
            ]
        );

        return response()->json($jobData);
    }

    /**
     * @return array{cover_letter: string, questions: array<int, array{question: string, answer: string}>}
     */
    public function writer(Request $request): JsonResponse
    {
        $request->validate([
            'jobId' => ['required'],
            'campaignId' => ['required', 'integer'],
        ]);

        $campaign = UpworkCampaign::withoutTeam()->where('id', $request->input('campaignId'))->first();
        $job = UpworkJob::where('uid', (string) $request->input('jobId'))->first();

        if (! $job || ! $campaign) {
            return response()->json(['error' => 'Job or campaign not found'], 404);
        }

        $jobData = [
            'title' => $job->title,
            'description' => $job->description,
            'questions' => $job->questions,
            'client_name' => $job->client_name,
        ];
        $campaignData = [
            'ai_prompt' => $campaign->ai_prompt,
            'ai_cover_letter' => $campaign->ai_cover_letter,
            'portfolios' => $campaign->portfoliosPromptText(),
            'experience' => $campaign->experience,
            'questions_context' => $campaign->questions_context,
        ];

        // return ['cover_letter' => 'test', 'questions' => [
        //    ['question' => 'test', 'answer' => 'test'],
        // ]];
        $result = $this->botAIService->writeCoverLetter($jobData, $campaignData);

        // Make a entry in proposals table
        Proposal::create([
            'title' => $job->title,
            'description' => $result['cover_letter'],
            'keywords' => '',
            'job_description' => $job->description,
            'sort_order' => 0,
            'user_id' => $campaign->member_id,
            'team_id' => $campaign->team_id,
        ]);

        return response()->json($result);
    }

    public function jobExpired(Request $request): JsonResponse
    {
        $request->validate([
            'jobID' => ['required'],
        ]);
        $jobId = $request->input('jobID');
        UpworkJob::where('uid', $jobId)->update(['is_expired' => 1]);

        return response()->json(true);
    }

    public function analysis(Request $request): JsonResponse
    {
        $request->validate([
            'jobId' => ['required'],
            'campaignId' => ['required'],
            'code' => ['required', 'string'],
        ]);
        $jobUid = (string) $request->input('jobId');
        $campaignId = (int) $request->input('campaignId');

        $profileOrError = $this->resolveProfileFromRequest($request);
        if ($profileOrError instanceof JsonResponse) {
            return $profileOrError;
        }

        $campaign = UpworkCampaign::withoutTeam()->where('id', $campaignId)->first();
        $job = UpworkJob::where('uid', $jobUid)->first();

        if (! $job || ! $campaign) {
            return response()->json(false);
        }

        if ((int) $campaign->profile_id !== (int) $profileOrError->id) {
            return response()->json(['error' => 'Campaign does not belong to profile'], 403);
        }

        if ($job->is_expired == 1) {
            return $this->recordAnalysisAndRespond($job, $campaign, false, 'Job expired');
        }

        if ($job->hires > 0) {
            return $this->recordAnalysisAndRespond($job, $campaign, false, 'Already hired');
        }

        $existingStat = UpworkCampaignJobStat::where('job_id', $job->id)
            ->where('campaign_id', $campaignId)
            ->first();

        if ($existingStat) {
            return response()->json(['is_matched' => (bool) $existingStat->is_matched]);
        }

        $ruleRejection = $campaign->ruleRejectionReasonForJob($job);
        if ($ruleRejection !== null) {
            return $this->recordAnalysisAndRespond($job, $campaign, false, $ruleRejection);
        }

        $campaignData = $campaign->toArray();
        $campaignData['portfolios'] = $campaign->portfoliosPromptText();

        $result = $this->botAIService->analyzeJob($job->toArray(), $campaignData);

        return $this->recordAnalysisAndRespond(
            $job,
            $campaign,
            (bool) $result['is_matched'],
            $result['reason'],
        );
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

        return response()->json(['is_matched' => $isMatched]);
    }

    public function jobStat(Request $request): JsonResponse
    {
        $request->validate([
            'jobId' => ['required'],
            'campaignId' => ['required'],
            'note' => ['required', 'string'],
        ]);

        $jobUid = (string) $request->input('jobId');
        $campaignId = (int) $request->input('campaignId');
        $note = $request->input('note');

        $job = UpworkJob::where('uid', $jobUid)->first();
        $campaign = UpworkCampaign::withoutTeam()->where('id', $campaignId)->first();

        if (! $job || ! $campaign) {
            return response()->json(['error' => 'Job or campaign not found'], 404);
        }

        return $this->recordAnalysisAndRespond($job, $campaign, false, $note);
    }

    public function apply(Request $request): JsonResponse
    {
        $request->validate([
            'jobId' => ['required'],
            'campaignId' => ['required'],
        ]);

        $jobId = (int) $request->input('jobId');
        $campaignId = (int) $request->input('campaignId');

        $job = UpworkJob::where('uid', $jobId)->first();
        $campaign = UpworkCampaign::withoutTeam()->where('id', $campaignId)->first();

        // Get team id from campaign member whcih set for lead creation
        $teamId = TeamMember::withoutTeam()->where('user_id', $campaign->member_id)->first()->team_id;

        // create lead
        Lead::create([
            'title' => $job->title,
            'description' => $job->description,
            'expected_value' => $job->client_avgspent,
            'actual_value' => 0,
            'cost' => 0,
            'assigned_member_id' => $campaign->member_id,
            'team_id' => $teamId,
            'kanban_id' => $campaign->kanban_id,
            'source_id' => $campaign->source_id,
            'url' => $job->url,
            'created_by_id' => $campaign->member_id,
        ]);

        UpworkCampaignJobStat::updateOrCreate(
            [
                'job_id' => $job->id,
                'campaign_id' => $campaignId,
            ],
            [
                'is_applied' => 1,
            ]
        );

        return response()->json(['success' => true]);
    }

    public function validateProfile(Request $request): JsonResponse
    {
        $request->validate([
            'code' => ['required', 'string'],
        ]);

        $profileOrError = $this->resolveProfileFromRequest($request);
        if ($profileOrError instanceof JsonResponse) {
            return $profileOrError;
        }

        if (! $profileOrError->hasProxy()) {
            return response()->json([
                'valid' => false,
                'error' => 'Profile proxy is not configured',
            ], 422);
        }

        return response()->json([
            'valid' => true,
            'profile' => [
                'id' => $profileOrError->id,
                'title' => $profileOrError->title,
                'email' => $profileOrError->email,
                'code' => $profileOrError->code,
                'proxy' => $profileOrError->proxyConfigForBot(),
            ],
        ]);
    }

    public function recentAnalysis(Request $request): JsonResponse
    {
        $profileOrError = $this->resolveProfileFromRequest($request);
        if ($profileOrError instanceof JsonResponse) {
            return $profileOrError;
        }

        $campaigns = UpworkCampaign::withoutTeam()
            ->select('id', 'title')
            ->where('is_active', true)
            ->where('profile_id', $profileOrError->id)
            ->get();

        // Get job stats for these campaigns; recently analyzed
        $campaignIds = $campaigns->pluck('id')->toArray();

        if ($campaignIds === []) {
            return response()->json([]);
        }

        $statFields = ['job_id', 'campaign_id', 'is_matched', 'is_applied'];

        $result = UpworkCampaignJobStat::query()
            ->with('job:id,uid')
            ->select($statFields)
            ->whereIn('campaign_id', $campaignIds)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (UpworkCampaignJobStat $stat) => array_merge(
                $stat->only($statFields),
                ['job_uid' => $stat->job?->uid],
            ))
            ->values()
            ->all();

        return response()->json($result);
    }

    /**
     * Ops alert to a campaign Discord/Slack webhook (e.g. Upwork logged out).
     * POST {code, campaignId?, type, message?}
     */
    public function alert(Request $request): JsonResponse
    {
        $request->validate([
            'code' => ['required', 'string'],
            'campaignId' => ['nullable', 'integer', 'min:1'],
            'type' => ['required', 'string', 'in:upwork_logged_out,scan_error'],
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        $profileOrError = $this->resolveProfileFromRequest($request);
        if ($profileOrError instanceof JsonResponse) {
            return $profileOrError;
        }

        $type = (string) $request->input('type');
        $cacheKey = 'bot_webhook_alert:'.$profileOrError->id.':'.$type;
        if (Cache::has($cacheKey)) {
            return response()->json([
                'success' => true,
                'skipped' => true,
                'message' => 'Alert already sent recently',
            ]);
        }

        $campaignQuery = UpworkCampaign::withoutTeam()
            ->where('profile_id', $profileOrError->id)
            ->whereNotNull('webhook_url')
            ->where('webhook_url', '!=', '');

        if ($request->filled('campaignId')) {
            $campaignQuery->where('id', (int) $request->input('campaignId'));
        }

        $campaign = $campaignQuery->orderByDesc('is_active')->first();

        if (! $campaign) {
            return response()->json([
                'success' => false,
                'error' => 'No campaign webhook configured for this profile',
            ], 422);
        }

        $note = $request->input('message');
        $message = match ($type) {
            'upwork_logged_out' => collect([
                '**Upwork not logged in**',
                '**Profile:** '.($profileOrError->title ?: $profileOrError->code),
                '**Campaign:** '.$campaign->title,
                '**Action:** Log into Upwork, then resume the bot.',
                filled($note) ? '**Note:** '.$note : null,
            ])->filter()->implode("\n"),
            default => (string) ($note
                ?? ("**Bot scan alert**\n**Profile:** ".($profileOrError->title ?: $profileOrError->code)."\n**Campaign:** ".$campaign->title)),
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
     * Extension / shared login: POST {email, password} -> {token, data: teams[]}.
     * No workspace.token middleware — issues JWT + per-team api_token.
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt(['email' => $validated['email'], 'password' => $validated['password']])) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid credentials',
            ], 401);
        }

        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            return response()->json([
                'success' => false,
                'error' => 'User not found',
            ], 401);
        }

        if (method_exists($user, 'hasVerifiedEmail') && ! $user->hasVerifiedEmail()) {
            Auth::logout();

            return response()->json([
                'success' => false,
                'error' => 'Please verify your email address before logging in.',
            ], 403);
        }

        $teams = $this->teamsForUser($user);
        if ($teams->isEmpty()) {
            return response()->json([
                'success' => false,
                'error' => 'No teams associated with this user',
            ], 403);
        }

        $teams->loadMissing('workspace:id,token');

        $teamsData = $teams->map(function (Team $team) {
            return [
                'id' => $team->id,
                'name' => $team->name,
                'api_token' => $team->workspace?->token,
                'sources' => LeadSource::forTeam($team->id)
                    ->get(['id', 'name', 'is_active', 'sort_order']),
                'stages' => LeadKanban::forTeam($team->id)
                    ->get(['id', 'name', 'code', 'is_active', 'sort_order']),
                'coverletter_types' => ProposalService::$coverletterTypes,
            ];
        });

        return response()->json([
            'success' => true,
            'token' => ExtensionJwt::encode($user),
            'default_team_id' => $teams->first()->id,
            'data' => $teamsData,
        ]);
    }

    public function logout(): JsonResponse
    {
        return response()->json(['success' => true]);
    }

    /**
     * Dashboard counts for the authenticated workspace.
     * POST optional {team_id}
     */
    public function dashboard(Request $request): JsonResponse
    {
        $workspaceId = $this->workspaceId($request);
        $teamIds = $this->teamIdsInWorkspace($workspaceId, $request->input('team_id'));

        return response()->json([
            'success' => true,
            'data' => [
                'teams' => count($teamIds),
                'profiles' => UpworkProfile::withoutTeam()
                    ->whereIn('team_id', $teamIds)
                    ->where('is_active', true)
                    ->count(),
                'campaigns' => UpworkCampaign::withoutTeam()
                    ->whereIn('team_id', $teamIds)
                    ->where('is_active', true)
                    ->count(),
            ],
        ]);
    }

    /**
     * Active Upwork profiles for a team in this workspace.
     * POST {team_id}
     */
    public function profiles(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'team_id' => ['required', 'integer', 'min:1'],
        ]);

        $team = $this->resolveTeamInWorkspace($request, (int) $validated['team_id']);
        if ($team instanceof JsonResponse) {
            return $team;
        }

        $profiles = UpworkProfile::withoutTeam()
            ->where('team_id', $team->id)
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
     * Scan campaigns for a profile (active + search_url; no auto_bidding filter).
     * POST {code} or {team_id, code}
     * Distinct from campaign() which is the auto-bid bot loop filter.
     */
    public function campaigns(Request $request): JsonResponse
    {
        $profileOrError = $this->resolveProfileFromRequest($request);
        if ($profileOrError instanceof JsonResponse) {
            return $profileOrError;
        }

        $campaigns = UpworkCampaign::withoutTeam()
            ->select('id', 'title', 'search_url', 'is_active', 'auto_bidding', 'profile_id', 'webhook_url')
            ->where('is_active', true)
            ->where('profile_id', $profileOrError->id)
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
     * Profile proxy credentials + expected egress IP.
     * POST {code, refresh?}
     */
    public function proxy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string'],
            'refresh' => ['nullable', 'boolean'],
        ]);

        $profileOrError = $this->resolveProfileFromRequest($request);
        if ($profileOrError instanceof JsonResponse) {
            return $profileOrError;
        }

        if (! $profileOrError->hasProxy()) {
            return response()->json([
                'success' => false,
                'error' => 'Profile proxy is not configured',
            ], 422);
        }

        $proxy = $profileOrError->proxyConfigForBot();
        $expectedIp = $profileOrError->proxy_last_ip;
        $refreshed = false;

        $shouldRefresh = (bool) ($validated['refresh'] ?? false)
            || blank($expectedIp)
            || $profileOrError->proxy_validated_at === null
            || $profileOrError->proxy_validated_at->lt(now()->subDays(7));

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
            $profileOrError->forceFill([
                'proxy_last_ip' => $expectedIp,
                'proxy_validated_at' => now(),
            ])->save();
            $proxy['last_ip'] = $expectedIp;
            $refreshed = true;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'code' => $profileOrError->code,
                'title' => $profileOrError->title,
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

    /**
     * CapSolver AntiCloudflare via profile proxy.
     * POST {code, websiteURL, userAgent, html}
     */
    public function capsolver(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string'],
            'websiteURL' => ['required', 'url'],
            'userAgent' => ['required', 'string'],
            'html' => ['required', 'string', 'min:50'],
        ]);

        $profileOrError = $this->resolveProfileFromRequest($request);
        if ($profileOrError instanceof JsonResponse) {
            return $profileOrError;
        }

        if (! $profileOrError->hasProxy()) {
            return response()->json([
                'success' => false,
                'error' => 'Profile proxy is required for CapSolver',
            ], 422);
        }

        $solution = $this->capSolverService->solveAntiCloudflare(
            $validated['websiteURL'],
            $validated['userAgent'],
            $validated['html'],
            $profileOrError->proxyConfigForBot(),
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
     * Cover letter from pasted/scraped job text (extension UI).
     * POST {campaign_id, job_description, title?, questions?, client_name?}
     */
    public function coverletter(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'campaign_id' => ['required', 'integer', 'min:1'],
            'job_description' => ['required', 'string', 'min:50'],
            'title' => ['nullable', 'string', 'max:500'],
            'client_name' => ['nullable', 'string', 'max:255'],
            'questions' => ['nullable', 'array'],
            'questions.*' => ['nullable', 'string', 'max:2000'],
        ]);

        $workspaceId = $this->workspaceId($request);
        $campaign = UpworkCampaign::withoutTeam()
            ->with(['linkedPortfolios', 'team'])
            ->where('id', (int) $validated['campaign_id'])
            ->first();

        if (! $campaign || (int) $campaign->team?->workspace_id !== $workspaceId) {
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

    private function workspaceId(Request $request): int
    {
        return (int) ($request->input('workspace_id') ?? $request->workspace_id ?? 0);
    }

    /**
     * @return array<int, int>
     */
    private function teamIdsInWorkspace(int $workspaceId, mixed $teamIdFilter = null): array
    {
        $query = Team::query()->where('workspace_id', $workspaceId);
        if ($teamIdFilter !== null && $teamIdFilter !== '') {
            $query->where('id', (int) $teamIdFilter);
        }

        return $query->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    private function resolveTeamInWorkspace(Request $request, int $teamId): Team|JsonResponse
    {
        $workspaceId = $this->workspaceId($request);
        $team = Team::query()
            ->where('id', $teamId)
            ->where('workspace_id', $workspaceId)
            ->first();

        if (! $team) {
            return response()->json([
                'success' => false,
                'error' => 'Team not found for this workspace',
            ], 403);
        }

        return $team;
    }

    /**
     * @return \Illuminate\Support\Collection<int, Team>
     */
    private function teamsForUser(User $user)
    {
        $memberTeamIds = TeamMember::withoutTeam()
            ->where('user_id', $user->id)
            ->pluck('team_id');

        return Team::query()
            ->where('created_by_id', $user->id)
            ->orWhereIn('id', $memberTeamIds)
            ->get()
            ->unique('id')
            ->values();
    }

    private function resolveProfileFromRequest(Request $request): UpworkProfile|JsonResponse
    {
        $code = $request->input('code');
        if (! is_string($code) || $code === '') {
            return response()->json(['error' => 'Profile code missing'], 400);
        }

        $profile = UpworkProfile::withoutTeam()
            ->with('team')
            ->where('code', $code)
            ->where('is_active', true)
            ->first();

        if (! $profile) {
            return response()->json(['error' => 'Profile not found'], 404);
        }

        $workspaceId = $this->workspaceId($request);
        if ($workspaceId > 0 && (int) $profile->team?->workspace_id !== $workspaceId) {
            return response()->json(['error' => 'Profile not in workspace'], 403);
        }

        return $profile;
    }
}
