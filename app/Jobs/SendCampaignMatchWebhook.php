<?php

namespace App\Jobs;

use App\Models\UpworkCampaign;
use App\Models\UpworkJob;
use App\Services\CampaignMatchWebhookService;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * Runs after the bot API response so matching is not delayed by the webhook HTTP call.
 * Intentionally not queued — executes inline afterResponse().
 */
class SendCampaignMatchWebhook
{
    use Dispatchable;

    public function __construct(
        public int $campaignId,
        public int $jobId,
        public string $note = '',
    ) {}

    public function handle(CampaignMatchWebhookService $webhookService): void
    {
        $campaign = UpworkCampaign::withoutTeam()->with('profile')->find($this->campaignId);
        $job = UpworkJob::find($this->jobId);

        if (! $campaign || ! $job || blank($campaign->webhook_url)) {
            return;
        }

        $webhookService->notifyMatch($campaign, $job, $this->note);
    }
}
