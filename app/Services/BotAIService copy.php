<?php

namespace App\Services;

use RuntimeException;

class BotAIService
{
    protected OpenAIService $openAIService;

    protected DeepseekService $deepseekService;

    public function __construct(OpenAIService $openAIService, DeepseekService $deepseekService)
    {
        $this->openAIService = $openAIService;
        $this->deepseekService = $deepseekService;
    }

    public function parseJob(array $jobData): array
    {
        // remove not needed fields
        $rawText = $jobData['rawText'];

        $prompt = <<<EOT
        Return only valid JSON.

        DATA:
        {$rawText}

        Rules:
        - Extract values from selected text only.
        - Missing values = null.
        - Numbers only.
        - K=1000, M=1000000.
        - %=number only.
        - proposals:
        "5-10"=6
        "10-15"=12
        "10-20"=15
        "20-50"=40
        "50+"=55
        "Less than 5"=4
        - title = detect title from JD.
        - description = detect description from "Summary".
        - skills = detect skills as [] from section "Mandatory skills".
        - url = detect url from "Job link".
        - questions = detect questions as [] from section "questions".
        - posted_at = return into mysql datetime format.
        - client_since = date after "Member since" in YYYY-MM-DD.
        - type = fixed|hourly.
        - client_rating = overall rating only return 0 if rating is not found.
        - client_totalspent = detect "total spent" from section "About the client" or return 0 if not found.
        - client_jobposted = detect "jobs posted" from section "About the client" or return 0 if not found.
        - client_openjob = detect "open jobs" from section "About the client" or return 0 if not found.
        - client_hirerate = calculate hirerate from jobposted / detect "hires" from section "About the client" or return 0 if not found.
        - client_avgspent = detect "hire rate" from section "About the client" or return 0 if not found.
        - client_avghourlyrate = detect "avg hourly rate paid" from section "About the client" or return 0 if not found.
        - client_hires = detect "hires" from section "About the client" or return 0 if not found.
        - client_org = detect company/business/org name from JD.
        - client_website = detect website/domain/url from JD.
        - client_project = detect product/project/platform/app/SaaS name from JD.
        - client_name = detect client/person name from reviews or JD.
        - is_warm = 1 if client_name OR client_org OR client_website OR client_project found, else 0.
        - location = client location from section About the client.
        - interviews = detect interviews from activity on this job or 0 if not found.
        - invitesent = detect invitesent from activity on this job or 0 if not found.
        - connects = detect connects from required connects for this job or 0 if not found.
        - hires = detect hires from activity on this job or 0 if not found.

        Output format:
        {
            "title": null,
            "description": null,
            "skills": [],
            "url": null,
            "questions": [],
            "location": null,
            "proposals": null,
            "client_name": null,
            "client_rating": null,
            "client_totalspent": null,
            "client_jobposted": null,
            "client_openjob": null,
            "client_hirerate": null,
            "client_avgspent": null,
            "client_avghourlyrate": null,
            "client_hires": null,
            "interviews": null,
            "invitesent": null,
            "client_since": null,
            "type": null,
            "posted_at": null,
            "connects": null,
            "client_org": null,
            "client_website": null,
            "client_project": null,
            "is_warm": 0,
            "hires": 0
        }
        EOT;

        $response = $this->deepseekService->request([
            [
                'role' => 'system',
                'content' => $prompt,
            ],
            [
                'role' => 'user',
                'content' => json_encode($jobData, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
            ],
        ]);

        if (! is_array($response)) {
            throw new RuntimeException('Invalid AI response shape');
        }

        return $response;
    }

    /**
     * @param  array{description: string}  $jobData
     * @param  array<string, mixed>  $campaignData
     * @return array{is_matched: bool, reason: string}
     */
    public function analyzeJob(array $jobData, array $campaignData): array
    {
        $jobDescription = trim((string) ($jobData['description'] ?? ''));
        $camPortfolios = trim((string) ($campaignData['portfolios'] ?? ''));
        $camMatchingCriteria = trim((string) (
            $campaignData['matching_criteria']
            ?? $campaignData['matching_critieria']
            ?? ''
        ));

        $useAiCoverLetter = filter_var(
            $campaignData['ai_cover_letter'] ?? true,
            FILTER_VALIDATE_BOOLEAN
        );
        $aiCoverLetterSetting = $useAiCoverLetter ? 'enabled' : 'disabled';

        $decoded = $this->deepseekService->request([
            [
                'role' => 'system',
                'content' => <<<EOT
        You are a strict job alignment validator.

        Return ONLY valid JSON:

        {
        "is_matched": true|false,
        "reason": "max 80 chars"
        }

        CAMPAIGN SETTING:
        - AI_COVER_LETTER: {$aiCoverLetterSetting}

        GOAL:
        Determine whether the job aligns with the campaign matching criteria.
        If AI_COVER_LETTER is enabled, also check that at least one portfolio is strongly
        relevant to the actual work required.

        STRICT RULES:

        STEP 1 — MATCHING CRITERIA (always required)
        - Compare the matching criteria against the job description
        - Reject if the job clearly does not satisfy the criteria
        - Focus on:
        - required skills
        - technologies
        - project scope
        - business domain
        - deliverables
        - experience expectations

        STEP 2 — PORTFOLIO RELEVANCE (only when AI_COVER_LETTER is enabled)
        - If AI_COVER_LETTER is disabled: skip this step entirely. The admin uses a fixed
          cover letter and has already configured the proposal content.
        - If AI_COVER_LETTER is enabled: compare portfolios against the job requirements.
        - At least ONE portfolio must strongly align with:
        - technologies used
        - type of solution
        - integrations
        - business use case
        - complexity level
        - services requested
        - Reject weak keyword overlap, vague similarity, and unrelated experience
        - Accept only if alignment is clear and meaningful

        FINAL RULE:
        - matching criteria must always pass
        - if AI_COVER_LETTER is enabled, portfolio relevance must also pass
        - otherwise reject

        IMPORTANT:
        - Be conservative
        - Avoid false positives
        - Do not assume missing information
        - Keep reason very short and specific

        EOT,
            ],
            [
                'role' => 'user',
                'content' => <<<EOT
        JOB DESCRIPTION:
        {$jobDescription}

        MATCHING CRITERIA:
        {$camMatchingCriteria}

        PORTFOLIOS:
        {$camPortfolios}

        AI_COVER_LETTER: {$aiCoverLetterSetting}

        Now validate strictly.

        EOT,
            ],
        ]);

        if (! is_array($decoded)) {
            throw new RuntimeException('Invalid AI response shape');
        }

        $matched = filter_var($decoded['is_matched'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $reason = isset($decoded['reason']) ? (string) $decoded['reason'] : '';

        return [
            'is_matched' => $matched,
            'reason' => $reason,
        ];
    }

    /**
     * @param  array{title?: string, description: string, questions?: array<int, string>}  $jobData
     * @param  array<string, mixed>  $campaignData
     * @return array{cover_letter: string, questions: array<int, array{question: string, answer: string}>}
     */
    public function writeCoverLetter(array $jobData, array $campaignData): array
    {
        $title = (string) ($jobData['title'] ?? 'Untitled');
        $description = (string) ($jobData['description'] ?? '');

        $questions = $jobData['questions'] ?? [];
        if (! is_array($questions)) {
            $questions = [];
        }

        $useAiCoverLetter = filter_var(
            $campaignData['ai_cover_letter'] ?? true,
            FILTER_VALIDATE_BOOLEAN
        );

        $coverSkeleton = (string) ($campaignData['ai_prompt'] ?? '');
        $portfolios = (string) ($campaignData['portfolios'] ?? '');
        $questionsCtx = (string) ($campaignData['questions_context'] ?? '');

        if (! $useAiCoverLetter) {
            return [
                'cover_letter' => $coverSkeleton,
                'questions' => $questions === []
                    ? []
                    : $this->answerScreeningQuestions($title, $description, $questions, $questionsCtx),
            ];
        }

        $questionsJson = json_encode($questions, JSON_UNESCAPED_UNICODE);

        $response = $this->deepseekService->request([
            [
                'role' => 'system',
                'content' => <<<'EOT'
                            You are a proposal compiler and proposal writer.

                            Your task is to generate:
                            1. A finalized cover letter
                            2. Answers for screening questions

                            Return ONLY valid JSON:
                            {
                            "cover_letter": "string",
                            "questions": [
                                {
                                "question": "string",
                                "answer": "string"
                                }
                            ]
                            }

                            RULES:

                            1. COVER LETTER SKELETON
                            - Treat COVERLETTERSKELETON as the base structure.
                            - Resolve all placeholders and instructions.
                            - Expand spintax by selecting only ONE option.
                            - Never leave placeholders unresolved.

                            2. PLACEHOLDERS
                            Examples:
                            [AI: ...]
                            [PORTFOLIO_MATCH]
                            [CTA]

                            - AI blocks should be naturally written.
                            - Portfolio blocks must use the most relevant portfolio items.
                            - CTA blocks should sound natural and professional.

                            3. PORTFOLIO MATCHING
                            - Use only the most relevant 1-2 portfolio items.
                            - Match based on job requirements.
                            - Mention project name, tech relevance, and URL naturally.

                            4. WRITING STYLE
                            - Human sounding
                            - Professional
                            - Concise
                            - Avoid AI sounding phrases
                            - Avoid buzzwords
                            - Avoid generic filler
                            - Keep flow natural

                            5. QUESTIONS
                            - Answer using CAMPAIGN QUESTIONS CONTEXT.
                            - Keep answers concise and relevant.

                            6. STRICT RULES
                            - No markdown
                            - No explanations
                            - No extra keys
                            - No placeholders remaining
                            - Cover letter max 350 words
                            - Not Add [Your Name] Or [Client Name] in the cover letter.
                            - the Cover letter should be ready to submit
                
                EOT
            ],
            [
                'role' => 'user',
                'content' => <<<EOT
                            JOB TITLE:
                            {$title}

                            JOB DESCRIPTION:
                            {$description}

                            JOB QUESTIONS:
                            {$questionsJson}

                            COVERLETTERSKELETON:
                            {$coverSkeleton}

                            CAMPAIGN PORTFOLIOS:
                            {$portfolios}

                            CAMPAIGN QUESTIONS CONTEXT:
                            {$questionsCtx}

                            Generate the final proposal JSON.
                EOT
            ],
        ]);

        if (! is_array($response)) {
            throw new RuntimeException('Invalid AI response');
        }

        $coverLetter = (string) ($response['cover_letter'] ?? '');
        $rows = $response['questions'] ?? [];

        if (! is_array($rows)) {
            $rows = [];
        }

        $normalized = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $normalized[] = [
                'question' => (string) ($row['question'] ?? ''),
                'answer' => (string) ($row['answer'] ?? ''),
            ];
        }

        return [
            'cover_letter' => $coverLetter,
            'questions' => $normalized,
        ];
    }

    /**
     * @param  array<int, string>  $questions
     * @return array<int, array{question: string, answer: string}>
     */
    private function answerScreeningQuestions(
        string $title,
        string $description,
        array $questions,
        string $questionsCtx,
    ): array {
        $questionsJson = json_encode($questions, JSON_UNESCAPED_UNICODE);

        $response = $this->deepseekService->request([
            [
                'role' => 'system',
                'content' => <<<'EOT'
                    You answer Upwork screening questions for a proposal.

                    Return ONLY valid JSON:
                    {
                    "questions": [
                        {
                        "question": "string",
                        "answer": "string"
                        }
                    ]
                    }

                    RULES:
                    - Use CAMPAIGN QUESTIONS CONTEXT for answers.
                    - Keep answers concise and relevant.
                    - No markdown, no explanations, no extra keys.
                    EOT
            ],
            [
                'role' => 'user',
                'content' => <<<EOT
                    JOB TITLE:
                    {$title}

                    JOB DESCRIPTION:
                    {$description}

                    JOB QUESTIONS:
                    {$questionsJson}

                    CAMPAIGN QUESTIONS CONTEXT:
                    {$questionsCtx}

                    Generate answers JSON.
                EOT
            ],
        ]);

        if (! is_array($response)) {
            throw new RuntimeException('Invalid AI response');
        }

        $rows = $response['questions'] ?? [];
        if (! is_array($rows)) {
            $rows = [];
        }

        $normalized = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $normalized[] = [
                'question' => (string) ($row['question'] ?? ''),
                'answer' => (string) ($row['answer'] ?? ''),
            ];
        }

        return $normalized;
    }
}
