<?php

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;

class OpenAIService
{
    /**
     * Generate a proposal using OpenAI.
     *
     * @param string $description Job description
     * @param string $portfolioText Matched portfolio text
     * @param string $type Proposal type (beginner, intermediate, professional)
     * @param int $words Word count
     * @return array{title: string, content: string}
     */
    public function generateProposal(string $description, string $portfolioText, string $type = 'intermediate', int $words = 300): array
    {
        $prompt = $this->buildPrompt($description, $portfolioText, $type, $words);

        $response = OpenAI::chat()->create([
            'model' => 'gpt-4o-mini', // Using gpt-4o-mini instead of gpt-4.1-mini
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => 0.9,
            'top_p' => 0.9,
        ]);

        $rawContent = $response->choices[0]->message->content;
        $data = json_decode($rawContent, true);

        return [
            'title' => $data['title'] ?? 'Proposal',
            'content' => $data['content'] ?? $rawContent,
        ];
    }

    /**
     * Extract keywords from text using OpenAI.
     *
     * @param string $text
     * @return array
     */
    public function extractKeywords(string $text): array
    {
        $prompt = "Extract the top 10 most important keywords or key phrases from the following job description. Return only a JSON array of strings, no explanations:\n\n" . $text;

        $response = OpenAI::chat()->create([
            'model' => 'gpt-4o-mini',
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => 0.3,
        ]);

        $content = $response->choices[0]->message->content;
        $keywords = json_decode($content, true);

        return is_array($keywords) ? array_map('strtolower', $keywords) : [];
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

    /**
     * Build the prompt based on type.
     */
    private function buildPrompt(string $description, string $portfolioText, string $type, int $words): string
    {
        if ($type === 'beginner') {
            return $this->buildBeginnerPrompt($description, $portfolioText, $words);
        } elseif ($type === 'professional') {
            return $this->buildProfessionalPrompt($description, $portfolioText, $words);
        } else {
            return $this->buildIntermediatePrompt($description, $portfolioText, $words);
        }
    }

    private function buildBeginnerPrompt(string $description, string $portfolioText, int $words): string
    {
        return "Write a personalized Upwork proposal of {$words} words.

Job: {$description}  

Portfolio: {$portfolioText} (rephrase naturally to match the client's needs; include links if available)

Instructions:

- Start with an engaging first sentence showing understanding of the project. No greetings.  

- Use a friendly, approachable, confident tone; short, clear sentences.  

- Highlight the most relevant portfolio points first.  

- Outline a simple 3–4 step plan.  

- Explain why you're eager and adaptable.  

- Include a call-to-action and optionally up to 3 relevant questions.  

- Use paragraphs naturally; bullets, bold, or icons are allowed.  

- Avoid copy-pasting job text; make it sound human-written and natural.

Output format (STRICT): return ONLY valid JSON with keys \"title\" and \"content\". No markdown, no code fences, no explanations.";
    }

    private function buildIntermediatePrompt(string $description, string $portfolioText, int $words): string
    {
        return "Write a {$words}-word Upwork proposal that sounds professional yet friendly.

Client needs: {$description}  

My experience (adapt to match): {$portfolioText}

Guidelines:

- Start confidently; no greetings like \"Hello\" or \"Hi\".

- Tone: mid-level freelancer — clear, friendly, assured.

- Rephrase experience to show relevance and client benefit.

- Structure: Intro → Understanding → Simple plan → Proof → Why me → CTA → Closing

- Use light formatting (**bold**, • bullets, ✅ icons) for readability.

- Optionally include up to 3 short, relevant questions.

- Keep sentences natural, concise, and varied each time.

Output format (STRICT): return ONLY valid JSON with keys \"title\" and \"content\". No markdown, no code fences, no explanations.";
    }

    private function buildProfessionalPrompt(string $description, string $portfolioText, int $words): string
    {
        return "Write a {$words}-word Upwork proposal that is polished, persuasive, and executive-level.

Client needs: {$description}  

My experience (adapt or rephrase to match): {$portfolioText}

Guidelines:

- Start confidently; no greetings like \"Hello\" or \"Hi\".

- Tone: expert freelancer/consultant — formal, clear, persuasive, yet approachable.

- Highlight achievements, proof, and results relevant to client needs.

- Structure: Intro → Deep understanding of client's goal → Detailed plan → Proof / credentials → Why I'm ideal → CTA → Closing

- Use light formatting (**bold**, • bullets, ✅ icons) for readability.

- Optionally include up to 3 sharp, relevant questions.

- Keep sentences concise, confident, and unique each time.

Output format (STRICT): return ONLY valid JSON with keys \"title\" and \"content\". No markdown, no code fences, no explanations.";
    }
}

