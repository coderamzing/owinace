<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class DeepseekService
{
    public function request(
        array $messages,
        mixed $model = 'deepseek-chat'
    ): array {

        $client = new Client([
            'base_uri' => 'https://api.deepseek.com',
            'headers' => [
                'Authorization' => 'Bearer '.env('DEEPSEEK_API_KEY'),
            ],
        ]);
        $rawresponse = $client->post('/v1/chat/completions', [
            'json' => [
                'model' => $model,
                'messages' => $messages,
                'response_format' => [
                    'type' => 'json_object',
                ],
            ],
        ]);

        $response = json_decode($rawresponse->getBody()->getContents(), true);
        // Log::info('Deepseek response: ' . json_encode($response));

        $content = $response['choices'][0]['message']['content'] ?? null;
        if (! $content) {
            throw new RuntimeException('Empty response from OpenAI');
        }

        $decoded = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('Invalid JSON returned by OpenAI');
        }

        return $decoded;
    }
}
