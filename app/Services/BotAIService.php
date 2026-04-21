<?php

namespace App\Services;

use RuntimeException;

class BotAIService
{
    protected OpenAIService $openAIService;

    public function __construct(OpenAIService $openAIService)
    {
        $this->openAIService = $openAIService;
    }

    public function parseJob(array $jobData): array
    {
        //remove not needed fields
        unset($jobData['title'], $jobData['description']);

        $jobJson = json_encode($jobData, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);

        $prompt = <<<EOT
            Return only JSON.
            DATA: {$jobJson}
            Rules:
            * Parse aboutclient, activity, features.
            * posted_at = Convert JobJson[timeZone] & JobJson[nowISO] to UTC timzone in MYSQL FORMAT.
            * Numbers only. Missing=null.
            * K=1000, M=1000000.
            * %=number.
            * Proposals:
            "5-10"=10
            "10-15"=15
            "10-20"=20
            "20-50"=50
            "50+"=50
            "Less than 5"=4
            * client_avgspent = totalspent / hires
            * client_since = date after "Member since"
            * type = "fixed" or "hourly" from features
            * connects in number only privide in jobjson data
            * invitesent = Invites sent in Activity section
            * client_rating = in clientsection interpret text like 4.93 of 68 reviews and return the overall rating

            Output:
            {
            "location":null,
            "proposals":null,
            "client_name":null,
            "client_rating":null,
            "client_totalspent":null,
            "client_jobposted":null,
            "client_openjob":null,
            "client_hirerate":null,
            "client_avgspent":null,
            "client_avghourlyrate":null,
            "client_hires":null,
            "interviews":null,
            "invitesent":null,
            "client_since":null,
            "type":null,
            "posted_at":null,
            "connects":null
            }
        EOT;

        $response = $this->openAIService->request([
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
        $jobJson = json_encode($jobData, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        $campaignJson = json_encode($campaignData, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);

        $decoded = $this->openAIService->request([
        [
        'role' => 'system',
        'content' => 'You evaluate if an Upwork job matches campaign portfolios. Reply JSON only: {"is_matched":true|false,"reason":"max 100 chars"} True when portfolios show strong technical relevance. Prioritize platform, migration type, rebuild type, customization scope, integrations, similar deliverables over industry niche. Do not require same business category.',
        ],
        [
        'role' => 'user',
        'content' => <<<EOT
        Job JSON:
        {$jobJson}
        
        Campaign JSON:
        {$campaignJson}
        
        Use campaignData.portfolios only.
        
        High-value match signals:
        - Same CMS/framework/platform
        - Version migration / upgrade experience
        - Clone/redesign/rebuild of existing site
        - Theme recreation from design files
        - Custom feature parity
        - Similar backend/frontend complexity
        
        Low-value signals:
        - Generic developer claims only
        - Unrelated stack with no transferable relevance
        
        If multiple portfolios support the job, mark true.
        
        Return only JSON.
        EOT
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
        if (!is_array($questions)) $questions = [];

        $questionsJson = json_encode($questions, JSON_UNESCAPED_UNICODE);

        $coverSkeleton   = (string) ($campaignData['coverletter_skeleton'] ?? '');
        $portfolios      = (string) ($campaignData['portfolios'] ?? '');
        $questionsCtx    = (string) ($campaignData['questions_context'] ?? '');

        $response = $this->openAIService->request([
            [
                'role' => 'system',
                'content' => '
    You write high-converting Upwork proposals for any industry.

    Return JSON only:
    {
    "cover_letter":"string",
    "questions":[
        {"question":"string","answer":"string"}
    ]
    }

    Rules:
    - dont put any placeholder like [] in the cover_letter, it should be ready to use.
    - Works for any niche: development, design, SEO, video, VA, data entry, marketing, etc.
    - Use job title + description deeply.
    - Use campaign cover letter skeleton as preferred structure/tone.
    - Final cover_letter must be fully completed text, no placeholders.
    - Select ONLY the most relevant portfolio examples from provided list.
    - Mention portfolio naturally if useful.
    - Be concise, human, confident, not robotic.
    - Avoid generic fluff.
    - Focus on client outcome, trust, capability, next step.
    - Do not invent fake claims.
    - If job has questions, answer each clearly.
    - Use questions_context when helpful to craft stronger answers.
    - If no questions, return empty array.
    - No markdown fences.
    '
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

    CAMPAIGN COVER LETTER SKELETON:
    {$coverSkeleton}

    CAMPAIGN PORTFOLIOS:
    {$portfolios}

    CAMPAIGN QUESTIONS CONTEXT:
    {$questionsCtx}

    Generate best proposal now.
    EOT
            ]
        ]);

        if (!is_array($response)) {
            throw new RuntimeException('Invalid AI response');
        }

        $coverLetter = (string)($response['cover_letter'] ?? '');
        $rows = $response['questions'] ?? [];

        if (!is_array($rows)) $rows = [];

        $normalized = [];
        foreach ($rows as $row) {
            if (!is_array($row)) continue;

            $normalized[] = [
                'question' => (string)($row['question'] ?? ''),
                'answer'   => (string)($row['answer'] ?? ''),
            ];
        }

        return [
            'cover_letter' => $coverLetter,
            'questions' => $normalized,
        ];
    }
}
