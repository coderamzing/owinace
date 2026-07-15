<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Auth\AuthenticationException;

class ExtensionJwt
{
    public static function encode(User $user, int $hoursValid = 750): string
    {
        $header = [
            'alg' => 'HS256',
            'typ' => 'JWT',
        ];

        $payload = [
            'sub' => $user->id,
            'exp' => now()->addHours($hoursValid)->timestamp,
            'iat' => now()->timestamp,
            'type' => 'chrome_ext',
        ];

        $headerSegment = self::base64UrlEncode(json_encode($header));
        $payloadSegment = self::base64UrlEncode(json_encode($payload));
        $signature = self::sign("{$headerSegment}.{$payloadSegment}");

        return "{$headerSegment}.{$payloadSegment}.{$signature}";
    }

    /**
     * @return array<string, mixed>
     *
     * @throws AuthenticationException
     */
    public static function decode(string $token): array
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            throw new AuthenticationException('Invalid token format');
        }

        [$headerSegment, $payloadSegment, $signature] = $parts;
        $expectedSignature = self::sign("{$headerSegment}.{$payloadSegment}");

        if (! hash_equals($expectedSignature, $signature)) {
            throw new AuthenticationException('Invalid token signature');
        }

        $payload = json_decode(self::base64UrlDecode($payloadSegment), true);

        if (! is_array($payload)) {
            throw new AuthenticationException('Invalid token payload');
        }

        if (($payload['exp'] ?? 0) < now()->timestamp) {
            throw new AuthenticationException('Token expired');
        }

        return $payload;
    }

    /**
     * @throws AuthenticationException
     */
    public static function userFromBearer(?string $bearer): User
    {
        if (! is_string($bearer) || $bearer === '') {
            throw new AuthenticationException('Missing bearer token');
        }

        $payload = self::decode($bearer);
        $userId = $payload['sub'] ?? null;

        if (! $userId) {
            throw new AuthenticationException('Invalid token payload');
        }

        $user = User::find($userId);

        if (! $user) {
            throw new AuthenticationException('User not found');
        }

        return $user;
    }

    private static function sign(string $data): string
    {
        return self::base64UrlEncode(hash_hmac('sha256', $data, self::secret(), true));
    }

    private static function secret(): string
    {
        $key = config('app.key');

        if (str_starts_with($key, 'base64:')) {
            $decoded = base64_decode(substr($key, 7));

            if ($decoded !== false) {
                return $decoded;
            }
        }

        return $key;
    }

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $data): string
    {
        $padding = strlen($data) % 4;

        if ($padding > 0) {
            $data .= str_repeat('=', 4 - $padding);
        }

        return base64_decode(strtr($data, '-_', '+/')) ?: '';
    }
}
