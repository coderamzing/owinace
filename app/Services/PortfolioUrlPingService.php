<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class PortfolioUrlPingService
{
    public const PING_INTERVAL_DAYS = 3;

    public const DAILY_PING_LIMIT = 500;

    public const REQUEST_TIMEOUT_SECONDS = 15;

    /**
     * @return array{success: bool, status: ?int, message: string}
     */
    public function ping(string $url): array
    {
        $url = trim($url);

        if ($url === '') {
            return [
                'success' => false,
                'status' => null,
                'message' => 'URL is required.',
            ];
        }

        try {
            $response = Http::withOptions([
                'allow_redirects' => true,
                'verify' => true,
            ])
                ->timeout(self::REQUEST_TIMEOUT_SECONDS)
                ->connectTimeout(10)
                ->withHeaders([
                    'User-Agent' => 'LeadCliq-PortfolioHealthCheck/1.0',
                    'Accept' => '*/*',
                ])
                ->get($url);

            $status = $response->status();

            if ($status === 200) {
                return [
                    'success' => true,
                    'status' => $status,
                    'message' => 'URL responded with HTTP 200.',
                ];
            }

            return [
                'success' => false,
                'status' => $status,
                'message' => "URL responded with HTTP {$status}. A 200 response is required.",
            ];
        } catch (\Throwable $exception) {
            return [
                'success' => false,
                'status' => null,
                'message' => 'Unable to reach URL: '.$exception->getMessage(),
            ];
        }
    }

    public function assertReachable(string $url): void
    {
        $result = $this->ping($url);

        if (! $result['success']) {
            throw new RuntimeException($result['message']);
        }
    }
}
