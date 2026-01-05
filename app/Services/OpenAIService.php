<?php

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;
use RuntimeException;

class OpenAIService
{
    /**
     * Create an embedding vector for arbitrary text.
     *
     * @param string $text
     * @return array<float|int>
     */
    public function createEmbedding(string $text): array
    {
        return $this->getEmbedding($text);
    }

    public function generateProposal(
        string $prompt
    ): array {
        $response = OpenAI::chat()->create([
            'model' => 'gpt-4o-mini',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are an expert Upwork proposal writer. Always follow instructions strictly.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ],
            ],
            'temperature' => 0.7,
            'response_format' => [
                'type' => 'json_object'
            ],
        ]);

        $content = $response['choices'][0]['message']['content'] ?? null;

        if (!$content) {
            throw new RuntimeException('Empty response from OpenAI');
        }

        $decoded = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('Invalid JSON returned by OpenAI');
        }

        if (!isset($decoded['title'], $decoded['content'])) {
            throw new RuntimeException('Missing title or content in proposal');
        }

        return [
            'title' => trim($decoded['title']),
            'content' => trim($decoded['content']),
        ];
    }

    /**
     * Get embeddings for text using OpenAI.
     *
     * @param string $text
     * @return array
     */
    public function getEmbedding(string $text): array
    {
        $response = OpenAI::embeddings()->create([
            'model' => 'text-embedding-3-small',
            'input' => $text,
        ]);

        return $response->embeddings[0]->embedding;
    }


}
