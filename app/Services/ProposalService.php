<?php

namespace App\Services;

use App\Models\Portfolio;
use App\Models\Team;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ProposalService
{
    protected OpenAIService $openAIService;

    public static array $coverletterTypes = [
        // 'beginner', 
        'intermediate', 
        // 'professional',
        // 'pitch',
        // 'experience',
        // 'approach',
    ];

    private string $proposalGuideline = <<<EOT
    - Job description is the single source of truth.
    - No assumptions beyond the job.
    - proposal flow > start with , Why I best fit, then Pitch with recent relevant expereince by Metioned portfolio items, understand the job, and Finally COA.
    - make list of relevant portfolio items
    - the English should be clear and easy to understand for a non-technical person.
    - Avoind professional writing and use a more human friendly language.
    - Only bold text to highlight the important information.
    - Never write the proposal as a single paragraph make them as proper paragraphs without any bullet points.
    - Dont give heading to any paragraph/section. and Insert a blank line between logical sections.
    - no greetings and no sign-off.
    EOT;

    private string $proposalFormat = <<<EOT
    
    EOT;

    private string $outputFormat = <<<EOT
    Return ONLY valid JSON with keys \"title\" and \"content\".
    No markdown. No code fences. No explanations."
    EOT;



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

        $prompt = $this->buildPrompt($jobDescription, $portfolioText, $type, $words);

        return $this->openAIService->generateProposal($prompt);
    }

    
    /**
     * Build the prompt based on type.
     */
    private function buildPrompt(string $description, string $portfolioText, string $type, int $words): string
    {
        //return $this->buildIntermediatePrompt($description, $portfolioText, $words);
        return match ($type) {
            'pitch' => $this->buildPitchPrompt($description, $portfolioText, $words),
            'experience' => $this->buildExperiencePrompt($description, $portfolioText, $words),
            'approach' => $this->buildApproachPrompt($description, $portfolioText, $words),
            'beginner' => $this->buildBeginnerPrompt($description, $portfolioText, $words),
            'intermediate' => $this->buildIntermediatePrompt($description, $portfolioText, $words),
            'professional' => $this->buildProfessionalPrompt($description, $portfolioText, $words),
            'default' => $this->buildApproachPrompt($description, $portfolioText, $words),
        };
    }
    
    public function matchJobWithPortfolios(
        int $teamId,
        string $jobDescription,
        int $limit = 3
    ) {
        // 1️⃣ Create job embedding, focusing on OpenAI-extracted keywords from the job
        $jobKeywords = $this->openAIService->findKeywords(
            $jobDescription
        );
        
        $jobEmbedding = $this->openAIService->createEmbedding($jobKeywords);

        // 2️⃣ Load portfolios (already embedded)
        $portfolios = Portfolio::withoutGlobalScopes()->where('team_id', $teamId)
            ->where('is_active', true)
            ->whereNotNull('embedding')
            ->get(['id', 'title', 'keywords', 'description', 'embedding']);

        // 3️⃣ Match job ↔ portfolio embeddings
        $scored = $portfolios->map(function ($portfolio) use ($jobEmbedding) {
            $score = $this->cosineSimilarity($jobEmbedding, $portfolio->embedding);
            return [
                'portfolio' => $portfolio,
                'score' => $score,
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
        return <<<EOT
        Write a {$words}-word proposal with a confident, persuasive pitch tone that still feels human and natural.
        Client requirements:
        {$description}
        Relevant experience:
        {$portfolioText}
        Tone & style:
        - Open with a strong, confident statement that positions me as a clear fit.
        - Tone: persuasive, assured, results-focused — but not pushy or salesy.
        - Highlight value, outcomes, and why this approach works for the client.
        - Reference at least one concrete job requirement early.
        - Rephrase experience to emphasize results and impact.
        Guidelines:
        {$this->proposalGuideline}
        Formatting rules:
        {$this->proposalFormat}
        Output format (STRICT):
        Return ONLY valid JSON with keys \"title\" and \"content\".
        No markdown. No code fences. No explanations."
        EOT;
    }

    private function buildExperiencePrompt(string $description, string $portfolioText, int $words): string
    {
        return <<<EOT
        Write a {$words}-word coverletter focused on experience and proven capability.
        Client requirements:
        {$description}
        Relevant experience:
        {$portfolioText}
        Tone & style:
        - Calm, confident, experience-driven.
        - Lead with what I’ve already done that directly matches this job.
        - Avoid selling language; let experience speak.
        - Reference specific job requirements early.
        - Explain how past work reduces risk for the client.
        Guidelines:
        {$this->proposalGuideline}
        Formatting rules:
        {$this->proposalFormat}
        Output format (STRICT):
        Return ONLY valid JSON with keys \"title\" and \"content\".
        No markdown. No code fences. No explanations."
        EOT;
    }

    private function buildApproachPrompt(string $description, string $portfolioText, int $words): string
    {
        return <<<EOT
        Write a {$words}-word coverletter focused on approach and execution.
        Client goal:
        {$description}
        Relevant background (use only where helpful):
        {$portfolioText}
        Tone & intent:
        - Practical, methodical, and collaborative.
        - Focus on how the work will be handled step by step.
        - Emphasize clarity, coordination, and clean execution.
        - Reference workflow or constraints from the job early.
        Guidelines:
        {$this->proposalGuideline}
        Formatting rules:
        {$this->proposalFormat}
        Output format (STRICT):
        Return ONLY valid JSON with keys \"title\" and \"content\".
        No markdown. No code fences. No explanations."
        EOT;
    }

    private function buildBeginnerPrompt(string $description, string $portfolioText, int $words): string
    {
        return <<<EOT
        Write a {$words}-word coverletter with a beginner-friendly but professional tone.
        Job:
        {$description}
        Portfolio:
        {$portfolioText}
        (rephrase naturally to match the client's needs; include links if available)
        Tone & guidance:
        - Honest, motivated, and respectful.
        - Show understanding of the task even if experience is limited.
        - Emphasize willingness to follow instructions and learn.
        - Avoid overconfidence or exaggeration.
        - Reference job requirements early.
        Guidelines:
        {$this->proposalGuideline}
        Formatting rules:
        {$this->proposalFormat}
        Output format (STRICT):
        {$this->outputFormat}
        EOT;
    }

    private function buildIntermediatePrompt(string $description, string $portfolioText, int $words): string
    {
        return <<<EOT
        Job description: {$description}
        Relevant experience or portfolios: {$portfolioText}
        Guidelines:
        - Max words is {$words}
        {$this->proposalGuideline}
        Formatting rules:
        {$this->proposalFormat}
        Output format (STRICT):
        {$this->outputFormat}
        EOT;
    }

    private function buildProfessionalPrompt(string $description, string $portfolioText, int $words): string
    {
        return <<<EOT
        Write a {$words}-word coverletter with a senior professional tone.
        Client requirements:
        {$description}
        My experience (adapt or rephrase to match) with links to projects:
        {$portfolioText}
        Tone & intent:
        - Composed, direct, and confident.
        - Focus on clarity, responsibility, and execution quality.
        - Avoid enthusiasm or sales language.
        - Reference constraints (scope limits, collaboration, review process) early.
        Guidelines:
        {$this->proposalGuideline}
        Formatting rules:
        {$this->proposalFormat}
        Output format (STRICT):
        {$this->outputFormat}
        EOT;
    }
}

