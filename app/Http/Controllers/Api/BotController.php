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

    public function campaign(Request $request): JsonResponse
    {
        $workspaceId = (int) $request->input('workspace_id');

        $campaign = UpworkCampaign::withoutTeam()
            ->select('id','title','rule_clock_in','rule_clock_out','timezone','auto_bidding','is_active','search_url')
            ->where('is_active', true)
            ->where('auto_bidding', true)
            ->whereHas('team', fn ($q) => $q->where('workspace_id', $workspaceId))
            ->get();

        return response()->json($campaign);
    }

    /**
     * Upsert job by uid. All listed fields are required.
     */
    public function job(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => ['required'],
        ]);

        $data = $request->all();

        $isExist = UpworkJob::where('uid', $data['id'])->first();
        if ($isExist) {
            return response()->json($isExist->toArray());
        }

        $jobData = $this->botAIService->parseJob($request->all());

        // $skills = $validated['skills'];
        // if (is_array($skills)) {
        //     $skills = json_encode($skills);
        // }

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
            'jobID' => ['required', 'integer'],
            'campaignID' => ['required', 'integer'],
        ]);

        $campaign = UpworkCampaign::withoutTeam()->where('id', $request->input('campaignID'))->first();
        $job = UpworkJob::where('uid', $request->input('jobID'))->first();

        $jobData = [
            'title' => $job->title,
            'description' => $job->description,
            'questions' => $job->questions,
        ];
        $campaignData = [
            'ai_prompt' => $campaign->ai_prompt,
            'portfolios' => $campaign->portfolios,
            'questions_context' => $campaign->questions_context,
        ];

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
            'jobID' => ['required'],
            'campaignID' => ['required'],
        ]);
        $jobId = (int) $request->input('jobID');
        $campaignId = (int) $request->input('campaignID');

        $campaign = UpworkCampaign::withoutTeam()->where('id', $campaignId)->first();
        $job = UpworkJob::where('uid', $jobId)->first();

        //validate if campaign has max daily bid
        if($campaign->max_daily_bid > 0) {
            $appliedToday = UpworkCampaignJobStat::where('campaign_id', $campaignId)
                ->where('is_applied', 1)
                ->where('created_at', '>=', now()->startOfDay())
                ->count();
            if($appliedToday >= $campaign->max_daily_bid) {
                return response()->json(false);
            }
        }

        //check if job is already analyzed
        $campaignJobStat = UpworkCampaignJobStat::where('job_id', $jobId)
            ->where('campaign_id', $campaignId)
            ->first();

        if ($campaignJobStat) { //return if job is already analyzed
            return response()->json(['is_matched' => $campaignJobStat->is_matched]);
        }
        

        if (!$campaign || !$job || $job->is_expired == 1) {
            return response()->json(false);
        }

        $jobData = $job->toArray();
        $campaignData = $campaign->toArray();

        //analyze the job
        $result = $this->botAIService->analyzeJob($jobData, $campaignData);
        $reason = $result['reason'];
        $isMatched = $result['is_matched'];

        UpworkCampaignJobStat::updateOrCreate(
            [
                'job_id' => $job->id,
                'campaign_id' => $campaign->id,
            ],
            [
                'is_matched' => $isMatched,
                'note' => $reason,
            ]
        );

        return response()->json($result['is_matched']);
    }

    public function apply(Request $request): JsonResponse
    {
        $request->validate([
            'jobID' => ['required'],
            'campaignID' => ['required'],
        ]);

        $jobId = (int) $request->input('jobID');
        $campaignId = (int) $request->input('campaignID');

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
}
