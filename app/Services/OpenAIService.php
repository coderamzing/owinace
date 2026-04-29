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

    /**
     * Create an embedding for a job description, after first asking OpenAI
     * to extract the most relevant skills / tech / domain keywords.
     *
     * This still uses two API calls under the hood (chat + embeddings),
     * but is wrapped in a single helper so callers don't worry about it.
     */
    public function findKeywords(string $jobDescription): string
    {
        $response = OpenAI::chat()->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You extract concise skills / technology / domain keywords from job descriptions.'
                ],
                [
                    'role' => 'user',
                    'content' => <<<EOT
                    Extract up to 10 short keywords or very short phrases (skills, technologies, tools, domains, industries) from this job description. 
                    Return ONLY a comma-separated list of keywords, no explanations, no extra text:
                    {$jobDescription}
                    EOT,
                ],
            ],
            'temperature' => 0.0,
        ]);

        return trim($response['choices'][0]['message']['content'] ?? '');
    }

    public function generateProposal(
        string $prompt
    ): array {
        $response = OpenAI::chat()->create([
            'model' => 'gpt-4o-mini',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are an expert business development specialist who writes cover letters / proposals for job descriptions from clients.'
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

    public function request(
        array $messages
    ): array {
        $response = OpenAI::chat()->create([
            'model' => 'gpt-5-mini',
            'messages' => $messages,
            'temperature' => 1,
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
        return $decoded;
    }

}
