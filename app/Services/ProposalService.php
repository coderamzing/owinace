<?php

namespace App\Services;

use App\Models\Portfolio;
use App\Models\Team;
use Illuminate\Support\Collection;

class ProposalService
{
    protected OpenAIService $openAIService;

    public function __construct(OpenAIService $openAIService)
    {
        $this->openAIService = $openAIService;
    }

    /**
     * Match portfolio entries to job description using semantic similarity.
     *
     * @param string $jobDescription
     * @param int $teamId
     * @param float $threshold
     * @return string
     */
    public function matchPortfolio(string $jobDescription, int $teamId, float $threshold = 0.40): string
    {
        // Extract keywords from job description
        $jobKeywords = $this->openAIService->extractKeywords($jobDescription);
        $jobKeywordsText = implode(' ', $jobKeywords);

        // Load portfolio entries
        $portfolioEntries = Portfolio::where('team_id', $teamId)
            ->where('is_active', true)
            ->get(['id', 'keywords', 'description']);

        if ($portfolioEntries->isEmpty()) {
            return '';
        }

        // Prepare portfolio data
        $portfolioKeywordTexts = [];
        $portfolioDescriptions = [];

        foreach ($portfolioEntries as $entry) {
            $keywords = $entry->keywords ? explode(',', $entry->keywords) : [];
            $keywordText = implode(' ', array_map('strtolower', $keywords));
            $portfolioKeywordTexts[] = $keywordText;
            $portfolioDescriptions[] = $entry->description ?? '';
        }

        // Get embeddings
        $jobEmbedding = $this->openAIService->getEmbedding($jobKeywordsText);
        $portfolioEmbeddings = [];

        foreach ($portfolioKeywordTexts as $text) {
            if (!empty($text)) {
                $portfolioEmbeddings[] = $this->openAIService->getEmbedding($text);
            } else {
                $portfolioEmbeddings[] = [];
            }
        }

        // Calculate cosine similarity
        $matchedDescriptions = [];
        foreach ($portfolioEmbeddings as $index => $portfolioEmbedding) {
            if (empty($portfolioEmbedding)) {
                continue;
            }

            $similarity = $this->cosineSimilarity($jobEmbedding, $portfolioEmbedding);

            if ($similarity >= $threshold) {
                $matchedDescriptions[] = $portfolioDescriptions[$index];
            }
        }

        return implode(', ', $matchedDescriptions);
    }

    /**
     * Calculate cosine similarity between two vectors.
     *
     * @param array $vectorA
     * @param array $vectorB
     * @return float
     */
    private function cosineSimilarity(array $vectorA, array $vectorB): float
    {
        if (count($vectorA) !== count($vectorB)) {
            return 0.0;
        }

        $dotProduct = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        for ($i = 0; $i < count($vectorA); $i++) {
            $dotProduct += $vectorA[$i] * $vectorB[$i];
            $normA += $vectorA[$i] * $vectorA[$i];
            $normB += $vectorB[$i] * $vectorB[$i];
        }

        $normA = sqrt($normA);
        $normB = sqrt($normB);

        if ($normA == 0 || $normB == 0) {
            return 0.0;
        }

        return $dotProduct / ($normA * $normB);
    }
}

