<?php

namespace App\Services;

use App\Models\UpworkProfile;
use RuntimeException;

class ProxyValidationService
{
    /**
     * @param  array{host: string, port: int, username?: string|null, password?: string|null, protocol?: string|null}  $config
     * @return array{success: bool, message: string, ip?: string}
     */
    public function validate(array $config): array
    {
        $host = trim((string) ($config['host'] ?? ''));
        $port = (int) ($config['port'] ?? 0);

        if ($host === '' || $port < 1) {
            return [
                'success' => false,
                'message' => 'Proxy host and port are required.',
            ];
        }

        $protocol = strtolower((string) ($config['protocol'] ?? 'http'));
        if (! in_array($protocol, ['http', 'socks5'], true)) {
            return [
                'success' => false,
                'message' => 'Proxy protocol must be http or socks5.',
            ];
        }

        $username = filled($config['username'] ?? null) ? (string) $config['username'] : null;
        $password = filled($config['password'] ?? null) ? (string) $config['password'] : null;

        if (! function_exists('curl_init')) {
            return [
                'success' => false,
                'message' => 'cURL is not available on this server.',
            ];
        }

        $ch = curl_init('https://api.ipify.org?format=json');
        if ($ch === false) {
            return [
                'success' => false,
                'message' => 'Unable to initialize proxy test request.',
            ];
        }

        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
            CURLOPT_PROXY => "{$host}:{$port}",
        ];

        if ($protocol === 'socks5') {
            $options[CURLOPT_PROXYTYPE] = CURLPROXY_SOCKS5_HOSTNAME;
        }

        if ($username !== null) {
            $options[CURLOPT_PROXYUSERPWD] = $password !== null
                ? "{$username}:{$password}"
                : $username;
        }

        curl_setopt_array($ch, $options);

        $body = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($body === false || $error !== '') {
            return [
                'success' => false,
                'message' => $error !== '' ? $error : 'Proxy connection failed.',
            ];
        }

        if ($status < 200 || $status >= 300) {
            return [
                'success' => false,
                'message' => "Proxy check failed with HTTP {$status}.",
            ];
        }

        $decoded = json_decode((string) $body, true);
        $ip = is_array($decoded) ? trim((string) ($decoded['ip'] ?? '')) : '';

        if ($ip === '') {
            return [
                'success' => false,
                'message' => 'Proxy connected but egress IP could not be read.',
            ];
        }

        return [
            'success' => true,
            'message' => "Connected. Egress IP: {$ip}",
            'ip' => $ip,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{success: bool, message: string, ip?: string}
     */
    public function validateFromFormData(array $data, ?UpworkProfile $record = null): array
    {
        $host = trim((string) ($data['proxy_host'] ?? ''));

        if ($host === '') {
            return [
                'success' => false,
                'message' => 'Proxy is required for every profile.',
            ];
        }

        $password = filled($data['proxy_password'] ?? null)
            ? (string) $data['proxy_password']
            : $record?->proxy_password;

        if ($password === null || $password === '') {
            return [
                'success' => false,
                'message' => 'Proxy password is required.',
            ];
        }

        return $this->validate([
            'host' => $host,
            'port' => (int) ($data['proxy_port'] ?? 0),
            'username' => filled($data['proxy_username'] ?? null) ? (string) $data['proxy_username'] : null,
            'password' => $password,
            'protocol' => (string) ($data['proxy_protocol'] ?? 'http'),
        ]);
    }

    public function findProfileWithEgressIp(string $ip, ?int $exceptProfileId = null): ?UpworkProfile
    {
        $query = UpworkProfile::withoutGlobalScopes()
            ->where('proxy_last_ip', $ip);

        if ($exceptProfileId !== null) {
            $query->where('id', '!=', $exceptProfileId);
        }

        return $query->first();
    }

    public function assertUniqueEgressIp(string $ip, ?int $exceptProfileId = null): void
    {
        $existing = $this->findProfileWithEgressIp($ip, $exceptProfileId);

        if ($existing) {
            throw new RuntimeException(
                "Proxy egress IP {$ip} is already assigned to profile \"{$existing->title}\"."
            );
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{success: bool, message: string, ip?: string}
     */
    public function validateForSave(array $data, ?UpworkProfile $record = null): array
    {
        $result = $this->validateFromFormData($data, $record);

        if (! $result['success']) {
            return $result;
        }

        $ip = $result['ip'] ?? null;
        if (! is_string($ip) || $ip === '') {
            return [
                'success' => false,
                'message' => 'Proxy connected but egress IP could not be verified.',
            ];
        }

        try {
            $this->assertUniqueEgressIp($ip, $record?->id);
        } catch (RuntimeException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function assertValidOnSave(array $data, ?UpworkProfile $record = null): string
    {
        $result = $this->validateForSave($data, $record);

        if (! $result['success']) {
            throw new RuntimeException($result['message']);
        }

        return (string) $result['ip'];
    }
}
