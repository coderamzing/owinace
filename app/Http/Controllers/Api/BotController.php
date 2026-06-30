<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\Proposal;
use App\Models\TeamMember;
use App\Models\UpworkCampaign;
use App\Models\UpworkCampaignJobStat;
use App\Models\UpworkJob;
use App\Models\UpworkProfile;
use App\Services\BotAIService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BotController extends Controller
{
    public function __construct(private BotAIService $botAIService) {}

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

        $isExist = UpworkJob::where('uid', $data['id'])->first();
        if ($isExist) {
            return response()->json($isExist->toArray());
        }

        $jobData = $this->botAIService->parseJob($request->all());

        if (empty($jobData['posted_at'])) {
            $jobData['posted_at'] = $data['posted_at'] ?? null;
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
            'jobId' => ['required', 'integer'],
            'campaignId' => ['required', 'integer'],
        ]);

        $campaign = UpworkCampaign::withoutTeam()->where('id', $request->input('campaignId'))->first();
        $job = UpworkJob::where('uid', $request->input('jobId'))->first();

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
        $jobUid = (int) $request->input('jobId');
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

        return response()->json(['is_matched' => $isMatched]);
    }

    public function jobStat(Request $request): JsonResponse
    {
        $request->validate([
            'jobId' => ['required'],
            'campaignId' => ['required'],
            'note' => ['required', 'string'],
        ]);

        $jobUid = (int) $request->input('jobId');
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

        $workspaceId = (int) ($request->input('workspace_id') ?? $request->workspace_id ?? 0);
        if ($workspaceId > 0 && (int) $profile->team?->workspace_id !== $workspaceId) {
            return response()->json(['error' => 'Profile not in workspace'], 403);
        }

        return $profile;
    }
}
