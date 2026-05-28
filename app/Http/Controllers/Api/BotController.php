<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UpworkCampaign;
use App\Models\UpworkJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Services\BotAIService;
use App\Models\Lead;
use App\Models\UpworkCampaignJobStat;
use App\Models\TeamMember;
use App\Models\Proposal;

class BotController extends Controller
{
    public function __construct(private BotAIService $botAIService)
    {
    }

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
        $workspaceId = (int) $request->input('workspace_id');

        $campaign = UpworkCampaign::withoutTeam()
            ->select('id','title','rule_clock_in','rule_clock_out','timezone','auto_bidding','is_active','search_url')
            ->where('is_active', true)
            ->where('auto_bidding', true)
            ->whereHas('team', fn ($q) => $q->where('workspace_id', $workspaceId))
            ->get();

        // Filter out campaigns that have already reached max_daily_bid for today

        // Get current date in the campaign's timezone, but fallback to UTC if missing
        $campaign = $campaign->filter(function ($item) {
            // Check if max_daily_bid is set for campaign
            if (empty($item->max_daily_bid)) {
                return true; // No quota set, so always include
            }

            // Try to get the campaign's timezone, else use UTC
            $tz = $item->timezone ?? 'UTC';
            try {
                $todayStart = now($tz)->startOfDay()->timezone('UTC');
                $todayEnd = now($tz)->endOfDay()->timezone('UTC');
            } catch (\Exception $e) {
                // Fallback if bad timezone
                $todayStart = now('UTC')->startOfDay();
                $todayEnd = now('UTC')->endOfDay();
            }

            // Count bids applied by this campaign since the start of "today"
            $appliedCount = \App\Models\UpworkCampaignJobStat::where('campaign_id', $item->id)
                ->whereBetween('applied_at', [$todayStart->toDateTimeString(), $todayEnd->toDateTimeString()])
                ->count();

            // If quota reached, filter campaign out
            return $appliedCount < $item->max_daily_bid;
        })->values();

        $campaign = $campaign->filter(function($item) {
            if (empty($item->rule_clock_in) || empty($item->rule_clock_out)) {
                return true;
            }
            $nowUtc = now('UTC')->format('H:i');
            $clockIn = $item->rule_clock_in;
            $clockOut = $item->rule_clock_out;
            if ($clockIn <= $clockOut) {
                return ($nowUtc >= $clockIn) && ($nowUtc <= $clockOut);
            } else {
                return ($nowUtc >= $clockIn) || ($nowUtc <= $clockOut);
            }
        })->values();

        return response()->json($campaign);
    }

    /**
     * Upsert job by uid. All listed fields are required.
     */
    public function job(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => ['required'],
            'title' => ['required'],
            'description' => ['required'],
            'skills' => ['required'],
            'url' => ['required'],
            'questions' => ['nullable'],
        ]);

        $data = $request->all();

        $isExist = UpworkJob::where('uid', $data['id'])->first();
        if ($isExist) {
            return response()->json($isExist->toArray());
        }

        $jobData = $this->botAIService->parseJob($request->all());

        UpworkJob::query()->updateOrCreate(
            ['uid' => $data['id']],
            [
                'title' => $data['title'],
                'description' => $data['description'],
                'skills' => $data['skills'],
                'url' => $data['url'],
                'questions' => $data['questions'],
                'connects' => $jobData['connects'],
                'location' => $jobData['location'],
                'proposals' => $jobData['proposals'],
                'client_totalspent' => $jobData['client_totalspent'],
                'client_jobposted' => $jobData['client_jobposted'],
                'client_avgspent' => $jobData['client_avgspent'],
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
                'is_warm' => $jobData['is_warm'] || 0,
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
            'jobId'  => ['required', 'integer'],
            'campaignId' => ['required', 'integer'],
        ]);

        $campaign = UpworkCampaign::withoutTeam()->where('id', $request->input('campaignId'))->first();
        $job = UpworkJob::where('uid', $request->input('jobId'))->first();

        if(!$job || !$campaign) {
            return response()->json(['error' => 'Job or campaign not found'], 404);
        }

        $jobData = [
            'title' => $job->title,
            'description' => $job->description,
            'questions' => $job->questions,
        ];
        $campaignData = [
            'ai_prompt' => $campaign->ai_prompt,
            'portfolios' => $campaign->portfoliosPromptText(),
            'questions_context' => $campaign->questions_context,
        ];


        //return ['cover_letter' => 'test', 'questions' => [
        //    ['question' => 'test', 'answer' => 'test'],
        //]];
        $result = $this->botAIService->writeCoverLetter($jobData, $campaignData);
        
        //Make a entry in proposals table
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
        ]);
        $jobUid = (int) $request->input('jobId');
        $campaignId = (int) $request->input('campaignId');

        $campaign = UpworkCampaign::withoutTeam()->where('id', $campaignId)->first();
        $job = UpworkJob::where('uid', $jobUid)->first();

        if (! $job || ! $campaign) {
            return response()->json(false);
        }

        if ($job->is_expired == 1) {
            return $this->recordAnalysisAndRespond($job, $campaign, false, 'Job expired');
        }

        $existingStat = UpworkCampaignJobStat::where('job_id', $job->id)
            ->where('campaign_id', $campaignId)
            ->first();

        if ($existingStat) {
            return response()->json(['is_matched' => (bool) $existingStat->is_matched]);
        }
        
        $ruleRejection = $this->campaignRuleRejectionReason($job, $campaign);
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

    private function campaignRuleRejectionReason(UpworkJob $job, UpworkCampaign $campaign): ?string
    {
        if ($campaign->max_daily_bid > 0) {
            $appliedToday = UpworkCampaignJobStat::where('campaign_id', $campaign->id)
                ->where('is_applied', 1)
                ->where('created_at', '>=', now()->startOfDay())
                ->count();
            if ($appliedToday >= $campaign->max_daily_bid) {
                return 'Max daily bid reached';
            }
        }

        if (isset($job->connects) && $job->connects > $campaign->max_connect_per_bid) {
            return 'Connects exceed campaign limit';
        }

        if ($campaign->rule_min_client_rating !== null
            && isset($job->client_rating)
            && is_numeric($job->client_rating)
            && (float) $job->client_rating > 0
            && (float) $job->client_rating < (float) $campaign->rule_min_client_rating
        ) {
            return 'Client rating below campaign minimum';
        }

        if (isset($job->client_avgspent) && $job->client_avgspent < $campaign->rule_client_avg_spent) {
            return 'Client avg. spent below campaign minimum';
        }

        if (isset($job->interviews) && $job->interviews > $campaign->rule_max_interviews) {
            return 'Interviews exceed campaign limit';
        }

        if (isset($job->posted_at)) {
            try {
                $diffMins = \Carbon\Carbon::parse($job->posted_at)->diffInMinutes(now());
                if ($diffMins > $campaign->rule_job_posted_ago) {
                    return 'Job posted too long ago';
                }
            } catch (\Exception) {
                // Unable to parse posted_at, ignore filter
            }
        }

        if (isset($job->proposals) && is_numeric($job->proposals) && $job->proposals > $campaign->rule_max_proposal) {
            return 'Proposals exceed campaign limit';
        }

        return null;
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


        //Get team id from campaign member whcih set for lead creation
        $teamId = TeamMember::withoutTeam()->where('user_id', $campaign->member_id)->first()->team_id;

        //create lead
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

    public function recentAnalysis(Request $request): JsonResponse
    {
        // Get workspace ID from request (set by middleware)
        $workspaceId = request()->workspace_id ?? null;
        if (!$workspaceId) {
            return response()->json(['error' => 'Workspace ID missing'], 400);
        }

        // Get campaigns for this workspace
        $campaigns = \App\Models\UpworkCampaign::withoutTeam()
            ->select('id', 'title')
            ->where('is_active', true)
            ->whereHas('team', fn($q) => $q->where('workspace_id', $workspaceId))
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
}
