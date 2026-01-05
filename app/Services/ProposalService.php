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

    public function generateProposal(
        string $jobDescription, 
        int $teamId, 
        string $type = 'pitch', 
        int $words = 180
    ): array
    {
        $portfolioMatches = $this->matchJobWithPortfolios(
            $teamId, 
            $jobDescription
        );

        $portfolioText = $this->buildPortfolioText($portfolioMatches);

        //dd($portfolioText);

        $prompt = $this->buildPrompt($jobDescription, $portfolioText, $type, $words);

        return $this->openAIService->generateProposal($prompt);
    }

    
    /**
     * Build the prompt based on type.
     */
    private function buildPrompt(string $description, string $portfolioText, string $type, int $words): string
    {
        if ($type === 'pitch') {
            return $this->buildPitchPrompt($description, $portfolioText, $words);
        } elseif ($type === 'experience') {
            return $this->buildExperiencePrompt($description, $portfolioText, $words);
        } else {
            return $this->buildApproachPrompt($description, $portfolioText, $words);
        }
    }
    
    public function matchJobWithPortfolios(
        int $teamId,
        string $jobDescription,
        int $limit = 3
    ) {
        // 1️⃣ Create job embedding
        $jobEmbedding = $this->openAIService->createEmbedding(
            $this->buildJobSemanticText($jobDescription)
        );

        // 2️⃣ Load portfolios (already embedded)
        $portfolios = Portfolio::where('team_id', $teamId)
            ->where('is_active', true)
            ->whereNotNull('embedding')
            ->get(['id', 'title', 'keywords', 'description', 'embedding']);

        // 3️⃣ Match job ↔ portfolio embeddings
        $scored = $portfolios->map(function ($portfolio) use ($jobEmbedding) {
            return [
                'portfolio' => $portfolio,
                'score' => $this->cosineSimilarity(
                    $jobEmbedding,
                    $portfolio->embedding
                ),
            ];
        });

        // 4️⃣ Sort & return best matches
        return $scored
            ->sortByDesc('score')
            ->take($limit)
            ->values();
    }

    private function buildPortfolioText(Collection $matches): string
    {
        if ($matches->isEmpty()) {
            return 'No portfolio items available yet.';
        }

        return $matches
            ->map(function (array $match, int $index) {
                /** @var \App\Models\Portfolio $portfolio */
                $portfolio = $match['portfolio'];
                $parts = [];

                $parts[] = ($index + 1) . ". {$portfolio->title}";

                if (!empty($portfolio->keywords)) {
                    $parts[] = "Keywords: " . implode(', ', $portfolio->keywords);
                }

                $description = trim((string) $portfolio->description);
                if ($description !== '') {
                    $parts[] = "Description: {$description}";
                }

                return implode(' | ', $parts);
            })
            ->implode("\n");
    }

    private function buildJobSemanticText(string $jobDescription): string
    {
        return trim(strip_tags($jobDescription));
    }

    private function cosineSimilarity(array $a, array $b): float
    {
        $dot = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        foreach ($a as $i => $v) {
            $dot += $v * $b[$i];
            $normA += $v * $v;
            $normB += $b[$i] * $b[$i];
        }

        return $dot / (sqrt($normA) * sqrt($normB));
    }

    private function buildPitchPrompt(string $description, string $portfolioText, int $words): string
    {
        return "Write a personalized Upwork proposal of {$words} words.
    
        Job:
        {$description}
        
        Relevant example from my work:
        {$portfolioText}
        
        Instructions:
        
        - Start with a strong hook that clearly shows you understand the client's problem. No greetings.
        
        - Immediately propose a clear, practical solution approach (what you would do).
        
        - Use ONE strong, relevant example from the portfolio to support the solution (rephrase naturally; include links only if present).
        
        - Outline a simple 3–4 step execution plan focused on outcomes, not theory.
        
        - Keep the tone confident, practical, and friendly — like a problem-solver, not a salesperson.
        
        - End with a clear call-to-action and optionally up to 2–3 short, relevant questions.
        
        - Use short paragraphs; light formatting (bullets, **bold**, icons) is allowed.
        
        - Avoid repeating or copying job text; sound human, decisive, and tailored.
        
        Output format (STRICT):
        Return ONLY valid JSON with keys \"title\" and \"content\".
        No markdown. No code fences. No explanations.";
    }

    private function buildExperiencePrompt(string $description, string $portfolioText, int $words): string
    {
        return "Write a {$words}-word Upwork proposal that positions me as an experienced and reliable professional.
    
        Client requirements:
        {$description}
        
        Relevant experience:
        {$portfolioText}
        
        Guidelines:
        
        - Start confidently with understanding + credibility. No greetings.
        
        - Tone: experienced freelancer — calm, assured, professional, and friendly.
        
        - Emphasize depth of experience, similar projects, and proven results.
        
        - Clearly connect past experience to the client’s specific needs and goals.
        
        - Structure:
        Intro → Understanding of needs → Relevant experience & proof → How I’ll apply it here → Why I’m a safe choice → CTA
        
        - Use light formatting (**bold**, • bullets, ✅ icons) to improve readability.
        
        - Optionally include up to 2–3 thoughtful, relevant questions.
        
        - Avoid buzzwords and exaggeration; focus on clarity, trust, and competence.
        
        Output format (STRICT):
        Return ONLY valid JSON with keys \"title\" and \"content\".
        No markdown. No code fences. No explanations.";
    }

    private function buildApproachPrompt(string $description, string $portfolioText, int $words): string
    {
        return "Write a {$words}-word Upwork proposal focused on my approach and thinking process.
    
        Client goal:
        {$description}
        
        Relevant background (use only where helpful):
        {$portfolioText}
        
        Guidelines:
        
        - Start by reframing the client’s problem in your own words to show deep understanding. No greetings.
        
        - Tone: consultant-level — thoughtful, confident, and clear.
        
        - Identify key challenges, risks, or decisions the client may be facing.
        
        - Explain your proposed approach or strategy step-by-step, including reasoning and trade-offs.
        
        - Reference experience only where it strengthens the approach (do not over-list achievements).
        
        - Structure:
        Understanding → Key challenges → Proposed approach → Why this approach works → Why I’m a good fit → CTA
        
        - Use light formatting (**bold**, • bullets, ✅ icons) for clarity.
        
        - Optionally include up to 2–3 sharp, insight-driven questions.
        
        - Keep language concise, logical, and original; avoid generic advice.
        
        Output format (STRICT):
        Return ONLY valid JSON with keys \"title\" and \"content\".
        No markdown. No code fences. No explanations.";
    }
}

