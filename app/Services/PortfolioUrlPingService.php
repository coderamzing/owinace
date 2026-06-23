<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class PortfolioUrlPingService
{
    public const PING_INTERVAL_DAYS = 3;

    public const DAILY_PING_LIMIT = 500;

    public const REQUEST_TIMEOUT_SECONDS = 15;

    private const BROWSER_USER_AGENT = 'Mozilla/5.0 (compatible; LeadCliq-PortfolioHealthCheck/1.0; +https://leadcliq.com)';

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
                    'User-Agent' => self::BROWSER_USER_AGENT,
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'Accept-Language' => 'en-US,en;q=0.9',
                ])
                ->get($url);

            $status = $response->status();

            if ($this->isReachableStatus($status)) {
                return [
                    'success' => true,
                    'status' => $status,
                    'message' => $this->reachableStatusMessage($status),
                ];
            }

            return [
                'success' => false,
                'status' => $status,
                'message' => "URL responded with HTTP {$status}. Expected a reachable response (2xx/3xx, or 401/403 from bot protection).",
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

    private function isReachableStatus(int $status): bool
    {
        if ($status >= 200 && $status < 400) {
            return true;
        }

        // Site exists but blocks automated/server requests (common WAF behaviour).
        return in_array($status, [401, 403], true);
    }

    private function reachableStatusMessage(int $status): string
    {
        if ($status === 403 || $status === 401) {
            return "URL responded with HTTP {$status} (reachable, but blocks automated checks).";
        }

        return "URL responded with HTTP {$status}.";
    }
}
