<?php

namespace App\Services;

use RuntimeException;
use Carbon\Carbon;

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
        $now = Carbon::now();
        $nowInUtc = Carbon::parse($now)->setTimezone('UTC')->format('Y-m-d H:i:s');

        $jobJson = json_encode($jobData, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        $campaignJson = json_encode($campaignData, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);

        $decoded = $this->openAIService->request([
            [
                'role' => 'system',
                'content' => <<<EOT
                You are a helpful assistant that evaluates if an Upwork job matches a campaign.
                Return JSON only: {"is_matched":true|false,"reason":"max 100 chars"}
                True when portfolios show strong technical relevance.
                Prioritize platform, migration type, rebuild type, customization scope, integrations, similar deliverables over industry niche.
                Do not require same business category.
                EOT
            ],
            [
            'role' => 'user',
            'content' => <<<EOT
                Time now IN UTC: {$nowInUtc}
                Job Data:
                {$jobJson}

                *INFO:*
                    title: title of the job
                    description: about of the job
                    questions: the questions asked in the job
                    skills: skills stack for jon as comma separated string
                    url: URL of the job
                    location: location of the job
                    proposals: the number of proposals already sent for the job
                    client_totalspent: cleint total spent on Upwork
                    client_jobposted: client total jobs posted on Upwork
                    client_openjob: client total open jobs on Upwork
                    client_hirerate: client hire rate %
                    client_avgspent: client average spent per job
                    client_avghourlyrate: client average hourly rate
                    posted_at: date and time the job was posted in UTC timezone
                    client_since: date and time the client was a member of Upwork
                    invites_sent: client total invites sent for the job
                    type: type of the job "fixed" or "hourly"
                    interviews: client total interviews for the job
                    connects: connects required for the job
                
                Campaign Data:
                {$campaignJson}

                *INFO:*
                    portfolios: portfolios of the campaign
                    max_connect_per_bid: max connects per job
                    matching_critieria: matching criteria
                    rule_client_avg_spent: client average spent per job
                    rule_max_interviews: max interviews for the job
                    rule_job_posted_ago: max job posted ago in minutes
                    rule_max_proposal: max proposals for the job
                    rule_clock_in: clock in time
                    rule_clock_out: clock out time
                
                *Rules:*
                - ignore campaign data if any field is null or empty.
                - match matching_critieria against job description.
                - match portfolios against job skills or job description to find if they are right fit to convince the client.

                Output:
                {
                "is_matched":true|false,
                "reason":"max 100 chars"
                }

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

        $coverSkeleton   = (string) ($campaignData['ai_prompt'] ?? '');
        $portfolios      = (string) ($campaignData['portfolios'] ?? '');
        $questionsCtx    = (string) ($campaignData['questions_context'] ?? '');

        $response = $this->deepseekService->request([
            [
                        'role' => 'system',
                        'content' => <<<EOT
                You are a deterministic proposal generator.
                
                Your job is to STRICTLY COMPILE a cover letter using the provided COVERLETTERSKELETON.
                
                Return JSON only:
                {
                "cover_letter": "string",
                "questions": [
                    {"question": "string", "answer": "string"}
                ]
                }
                
                EXECUTION RULES (MUST FOLLOW IN ORDER):
                
                1. TREAT COVERLETTERSKELETON AS FINAL TEMPLATE
                - Do NOT change structure
                - Do NOT add new sections
                - Do NOT remove any lines
                - Only replace placeholders
               
                
                2. PLACEHOLDER REPLACEMENT (MANDATORY)
                - Replace ALL placeholders like [something] or {{something}}
                - NO placeholder should remain
                - If unclear, infer best possible content
                - Never Write [Your Name] placeholder in the cover letter
                - For greeting only use spintax if provided in the skeleton else dont add greeting.
                
                3. SPINTAX PROCESSING
                - If skeleton contains {option1|option2|option3}
                - Select ONLY ONE best option
                - Remove spintax syntax completely
                
                4. PORTFOLIO INSERTION
                - Use CAMPAIGN PORTFOLIOS
                - Select ONLY 1–2 most relevant items
                - Include URL if available
                - Insert ONLY where skeleton requires
                
                5. JOB ALIGNMENT
                - Tailor using JOB TITLE and JOB DESCRIPTION
                - Keep content concise and relevant
                
                6. QUESTIONS ANSWERING
                - Use CAMPAIGN QUESTIONS CONTEXT
                - Answer ONLY from that context
                - Do NOT hallucinate
                
                7. STRICT OUTPUT RULES
                - No explanations
                - No extra text
                - No placeholders
                - Output must be ready to send

                8. Formatting Rules:
                - Add a Nice formatting to the cover letter as needed.

                9. Word Count Rules:
                - the cover letter should not be more than 350 words.
                
                FAIL CONDITIONS (STRICTLY AVOID):
                - Leaving placeholders like [] or {{}}
                - Ignoring skeleton structure
                - Adding extra paragraphs not in skeleton
                - Writing generic content
                
                EOT
                    ],
                    [
                        'role' => 'user',
                        'content' => <<<EOT
                IMPORTANT:
                The COVERLETTERSKELETON is a STRICT TEMPLATE.
                Do NOT modify its structure. Only replace placeholders and resolve spintax.
                
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
                
                Now STRICTLY COMPILE the final proposal.
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
