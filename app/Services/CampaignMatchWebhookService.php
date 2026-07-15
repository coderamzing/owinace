<?php

namespace App\Services;

use App\Models\UpworkCampaign;
use App\Models\UpworkJob;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class CampaignMatchWebhookService
{
    private const REQUEST_TIMEOUT_SECONDS = 10;

    public function notifyMatch(UpworkCampaign $campaign, UpworkJob $job, string $note = ''): void
    {
        $url = trim((string) $campaign->webhook_url);

        if ($url === '') {
            return;
        }

        $result = $this->post($url, $this->buildMessage($campaign, $job, $note));

        if (! $result['success']) {
            Log::warning('Campaign match webhook failed', [
                'campaign_id' => $campaign->id,
                'job_id' => $job->id,
                'status' => $result['status'],
                'message' => $result['message'],
            ]);
        }
    }

    /**
     * Post a free-form ops/alert message to a campaign webhook (Discord/Slack).
     *
     * @return array{success: bool, status: ?int, message: string}
     */
    public function notifyAlert(UpworkCampaign $campaign, string $message): array
    {
        $url = trim((string) $campaign->webhook_url);

        if ($url === '') {
            return [
                'success' => false,
                'status' => null,
                'message' => 'Campaign has no webhook URL.',
            ];
        }

        return $this->post($url, $message);
    }

    /**
     * @return array{success: bool, status: ?int, message: string}
     */
    public function sendTest(UpworkCampaign $campaign): array
    {
        $url = trim((string) $campaign->webhook_url);

        if ($url === '') {
            return [
                'success' => false,
                'status' => null,
                'message' => 'Set a match webhook URL first.',
            ];
        }

        $message = implode("\n", [
            '**Webhook test**',
            '**Campaign:** '.$campaign->title,
            '**Status:** Connected — match notifications will post here.',
        ]);

        return $this->post($url, $message);
    }

    /**
     * @return array{success: bool, status: ?int, message: string}
     */
    private function post(string $url, string $message): array
    {
        try {
            $response = Http::timeout(self::REQUEST_TIMEOUT_SECONDS)
                ->acceptJson()
                ->asJson()
                ->post($url, $this->buildPayload($url, $message));

            if ($response->successful()) {
                return [
                    'success' => true,
                    'status' => $response->status(),
                    'message' => 'Webhook accepted the request.',
                ];
            }

            return [
                'success' => false,
                'status' => $response->status(),
                'message' => 'Webhook responded with HTTP '.$response->status().'.',
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'status' => null,
                'message' => $e->getMessage(),
            ];
        }
    }

    private function buildMessage(UpworkCampaign $campaign, UpworkJob $job, string $note): string
    {
        $profile = $campaign->relationLoaded('profile')
            ? $campaign->profile
            : $campaign->profile()->first();

        $lines = [
            '**Job matched**',
            '**Profile:** '.($profile?->title ?: $profile?->code ?: 'Unknown'),
            '**Campaign:** '.$campaign->title,
            '**Title:** '.($job->title ?: 'Untitled job'),
        ];

        if (filled($job->url)) {
            $lines[] = '**URL:** '.$job->url;
        }

        if (filled($job->client_name)) {
            $lines[] = '**Client:** '.$job->client_name;
        }

        $meta = [];
        if ($job->client_rating !== null) {
            $meta[] = 'Rating: '.$job->client_rating;
        }
        if ($job->client_totalspent !== null) {
            $meta[] = 'Spent: $'.$job->client_totalspent;
        }
        if ($job->proposals !== null) {
            $meta[] = 'Proposals: '.$job->proposals;
        }
        if ($job->connects !== null) {
            $meta[] = 'Connects: '.$job->connects;
        }
        if ($meta !== []) {
            $lines[] = '**Stats:** '.implode(' · ', $meta);
        }

        if (filled($note)) {
            $lines[] = '**Reason:** '.$note;
        }

        return implode("\n", $lines);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPayload(string $url, string $message): array
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        // Slack incoming webhooks
        if (str_contains($host, 'hooks.slack.com') || str_contains($host, 'slack.com')) {
            return ['text' => $this->plainText($message)];
        }

        // Discord (and most generic chat webhooks that accept content)
        return [
            'content' => $message,
            'text' => $this->plainText($message),
        ];
    }

    private function plainText(string $message): string
    {
        return str_replace(['**', '__'], '', $message);
    }
}
