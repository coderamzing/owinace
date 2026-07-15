<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class CapSolverService
{
    private const CREATE_TASK_URL = 'https://api.capsolver.com/createTask';

    private const GET_RESULT_URL = 'https://api.capsolver.com/getTaskResult';

    private const MAX_POLL_ATTEMPTS = 60;

    private const POLL_INTERVAL_SECONDS = 1;

    /**
     * @param  array{host?: string, port?: int|string, username?: ?string, password?: ?string, protocol?: string}|null  $proxy
     * @return array{cookies: array<string, string>, token?: string, userAgent?: string}|null
     */
    public function solveAntiCloudflare(
        string $websiteURL,
        string $userAgent,
        string $html,
        ?array $proxy,
    ): ?array {
        $apiKey = (string) config('services.capsolver.key', env('CAPSOLVER_API_KEY', ''));

        if ($apiKey === '') {
            Log::error('CapSolver: CAPSOLVER_API_KEY is not set');

            return null;
        }

        if ($websiteURL === '' || $html === '' || $userAgent === '') {
            Log::error('CapSolver: websiteURL, userAgent, and html are required');

            return null;
        }

        $proxyFields = $this->buildProxyTaskFields($proxy);
        if ($proxyFields === null) {
            Log::error('CapSolver: profile proxy is required for AntiCloudflareTask');

            return null;
        }

        $task = array_merge([
            'type' => 'AntiCloudflareTask',
            'websiteURL' => $websiteURL,
            'userAgent' => $userAgent,
            'html' => $html,
        ], $proxyFields);

        try {
            $create = Http::timeout(30)
                ->acceptJson()
                ->asJson()
                ->post(self::CREATE_TASK_URL, [
                    'clientKey' => $apiKey,
                    'task' => $task,
                ]);

            $createData = $create->json() ?? [];
            $taskId = $createData['taskId'] ?? null;

            if (! $taskId) {
                Log::error('CapSolver createTask failed', ['response' => $createData]);

                return null;
            }

            Log::info('CapSolver task created', ['taskId' => $taskId]);

            for ($attempt = 0; $attempt < self::MAX_POLL_ATTEMPTS; $attempt++) {
                sleep(self::POLL_INTERVAL_SECONDS);

                $result = Http::timeout(30)
                    ->acceptJson()
                    ->asJson()
                    ->post(self::GET_RESULT_URL, [
                        'clientKey' => $apiKey,
                        'taskId' => $taskId,
                    ]);

                $data = $result->json() ?? [];
                $status = $data['status'] ?? null;

                if ($status === 'ready') {
                    $solution = $data['solution'] ?? null;
                    if (! is_array($solution)) {
                        return null;
                    }

                    $cookies = is_array($solution['cookies'] ?? null) ? $solution['cookies'] : [];
                    if (! empty($solution['token']) && empty($cookies['cf_clearance'])) {
                        $cookies['cf_clearance'] = $solution['token'];
                    }

                    return [
                        'cookies' => $cookies,
                        'token' => $solution['token'] ?? null,
                        'userAgent' => $solution['userAgent'] ?? null,
                    ];
                }

                if ($status === 'failed' || ! empty($data['errorId'])) {
                    Log::error('CapSolver solve failed', ['response' => $data]);

                    return null;
                }
            }

            Log::error('CapSolver timed out waiting for solution', ['taskId' => $taskId]);

            return null;
        } catch (Throwable $e) {
            Log::error('CapSolver request error', ['error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * @param  array{host?: string, port?: int|string, username?: ?string, password?: ?string, protocol?: string}|null  $proxy
     * @return array{proxyType: string, proxyAddress: string, proxyPort: int, proxyLogin?: string, proxyPassword?: string}|null
     */
    private function buildProxyTaskFields(?array $proxy): ?array
    {
        $host = trim((string) ($proxy['host'] ?? ''));
        $port = (int) ($proxy['port'] ?? 0);

        if ($host === '' || $port <= 0) {
            return null;
        }

        $fields = [
            'proxyType' => $this->proxyType($proxy['protocol'] ?? 'http'),
            'proxyAddress' => $this->resolveProxyHost($host),
            'proxyPort' => $port,
        ];

        $username = $proxy['username'] ?? null;
        $password = $proxy['password'] ?? null;
        if (filled($username) && filled($password)) {
            $fields['proxyLogin'] = (string) $username;
            $fields['proxyPassword'] = (string) $password;
        }

        return $fields;
    }

    private function proxyType(string $protocol): string
    {
        $protocol = strtolower($protocol);
        if ($protocol === 'socks5') {
            return 'socks5';
        }
        if ($protocol === 'https') {
            return 'https';
        }

        return 'http';
    }

    private function resolveProxyHost(string $host): string
    {
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return $host;
        }

        $ip = gethostbyname($host);
        if (is_string($ip) && $ip !== $host && filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }

        return $host;
    }
}
