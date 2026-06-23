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
        $prompt = <<<'EOT'
        # ROLE
        You are an expert information extraction and data organization specialist.

        # OBJECTIVE
        Extract key information from the raw text of an Upwork job details page and convert it into structured JSON.

        # Context
        - CURRENT_UTC_TIME = current UTC time in YYYY-MM-DD HH:MM:SS format.

        # INSTRUCTIONS
        - Ignore navigation elements and irrelevant text.
        - Identify the job title, description, skills, url, questions, location, preferred_location, preferred_talent, proposals, client_name, client_rating, client_totalspent, client_jobposted, client_openjob, client_hirerate, client_avghourlyrate, client_hires, interviews, invitesent, connects, hires, client_since, type, posted_at, client_org, client_website, client_project, is_warm.
            - proposals: detect from section "Activity on this job" as integer the pattern you can see "Proposals: {number} to {number}".
                'Proposals:5 to 10' => 6
                'Proposals:10-15' => 12
                'Proposals:10-20' => 15
                'Proposals:20-50' => 40
                'Proposals:50+' => 55
                'Proposals:Less than 5' => 4
            - title = detect title from jobdescription in the first line before "Posted yesterday".
            - description = detect description from "Summary".
            - skills: detect form "Mandatory skills" section and return as array.
            - url = detect url from "Job link".
            - questions = detect questions as [] from section "questions".
            - posted_at:
                - REQUIRED FIELD.
                - Search only for the FIRST line beginning with "Posted ".
                - Ignore all other dates in the page.
                - Convert:
                    Posted just now
                    Posted x minutes
                    Posted x hours
                    Posted yesterday
                    Posted x days
                    Posted x weeks
                    Posted x months
                    Posted x years
                relative to CURRENT_UTC_TIME.
                - Return YYYY-MM-DD HH:MM:SS.
                - Never return null if a "Posted ..." line exists.
            - type = fixed|hourly.
            - client_rating = overall rating only return 0 if rating is not found.
            - client_totalspent = detect "total spent" from section "About the client", Process value from K, M, B to actual number format or return 0 if not found.
            - client_jobposted = detect "jobs posted" from section "About the client" or return 0 if not found.
            - client_openjob = detect "open jobs" from section "About the client" or return 0 if not found.
            - client_hirerate = detect from section "About the client" as integer the pattern you can see "{number}% hire rate".
            - client_avghourlyrate = detect "avg hourly rate paid" from section "About the client" or return 0 if not found.
            - client_hires = detect "hires" from section "About the client" or return 0 if not found.
            - client_org = detect company/business/org name from JD.
            - client_website = detect website/domain/url from JD.
            - client_project = detect product/project/platform/app/SaaS name from JD.
            - client_name = detect client/person name from reviews or JD.
            - is_warm = 1 if client_name OR client_org OR client_website OR client_project found, else 0.
            - location = detect location from section About the client, return in format "City, Country".
            - preferred_location = detect from section "Preferred qualifications" under "Location:" or return null if not found.
            - preferred_talent = detect from section "Preferred qualifications" under "Talent Type:" or return null if not found.
            - interviews = detect interviews from activity on this job or 0 if not found.
            - invitesent = detect invitesent from activity on this job or 0 if not found.
            - connects = detect connects from required connects for this job or 0 if not found.
            - hires = detect hires from "Activity on this job" section if job as already hired or 0 if not found.
            - client_since = date after "Member since" in YYYY-MM-DD.
        - Normalize values where possible.
        - Return null for missing fields.
        - Do not invent information.

        # OUTPUT FORMAT
        {
            "title": null,
            "description": null,
            "skills": [],
            "url": null,
            "questions": [],
            "location": null,
            "preferred_location": null,
            "preferred_talent": null,
            "proposals": null,
            "client_name": null,
            "client_rating": null,
            "client_totalspent": null,
            "client_jobposted": null,
            "client_openjob": null,
            "client_hirerate": null,
            "client_avghourlyrate": null,
            "client_hires": null,
            "interviews": null,
            "invitesent": null,
            "client_since": null,
            "type": null,
            "posted_at": YYYY-MM-DD HH:MM:SS,
            "connects": null,
            "client_org": null,
            "client_website": null,
            "client_project": null,
            "is_warm": 0,
            "hires": 0
        }
        # VALIDATION
        - Every value must come from the provided text.
        - Never guess.
        - Use null for unavailable fields.
        - posted_at must be in YYYY-MM-DD HH:MM:SS format.
        
        EOT;

        $response = $this->deepseekService->request([
            [
                'role' => 'system',
                'content' => $prompt,
            ],
            [
                'role' => 'user',
                'content' => json_encode([
                    'source' => 'Upwork job details page',
                    'raw_text' => $jobData['rawText'],
                    'CURRENT_UTC_TIME' => now('UTC')->format('Y-m-d H:i:s'),
                ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
            ],
        ], 'deepseek-reasoner');

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
        $prompt = <<<'EOT'
        # ROLE

        You are a senior technical recruiter and freelance opportunity evaluator with extensive experience assessing software development projects.

        You specialize in analyzing job descriptions, identifying the required skills and experience, and determining whether a project is a good match based on previous work and available expertise.

        You prioritize accuracy and evidence-based matching and never assume experience that is not supported by the provided information.

        # OBJECTIVE

        Determine whether the opportunity is a suitable match for the candidate's experience and whether the available portfolios and question context provide enough evidence to confidently apply for the job.

        # CONTEXT

        The user message contains:

        - JOB_DESCRIPTION (job description from job data)
        - JOB_QUESTIONS (job questions from job data)
        - JOB_SKILLS (job skills from job data)
        - CAMPAIGN_PORTFOLIOS (campaign portfolios from campaign data)
        - CAMPAIGN_EXPERIENCE (candidate experience from campaign data)
        - CAMPAIGN_QUESTIONS_CONTEXT (campaign questions context from campaign data)
        - CAMPAIGN_JOB_DO (required rules — job must meet all of these)
        - CAMPAIGN_JOB_DONT (disqualification rules — job is rejected if any match)

        # INSTRUCTIONS
        - Analyze the job requirements, technologies, features, integrations, domain, and experience level.
        - Compare the requirements against CAMPAIGN_PORTFOLIOS, CAMPAIGN_EXPERIENCE, CAMPAIGN_JOB_DO, and CAMPAIGN_JOB_DONT.
        - Determine whether the available portfolios and experience demonstrate similar work and relevant expertise.
        - Determine whether JOB_QUESTIONS can be reasonably answered using CAMPAIGN_QUESTIONS_CONTEXT, CAMPAIGN_EXPERIENCE, or CAMPAIGN_PORTFOLIOS.
        - A partial match is acceptable if the core requirements are strongly aligned.
        - Reject the opportunity if major requirements are unsupported or if important questions cannot be answered.
        - Base the decision only on the supplied information.
        - Never fabricate experience or assume unsupported knowledge.

        ### CAMPAIGN_JOB_DO

        CAMPAIGN_JOB_DO lists rules the job **must** satisfy. If any rule is not met, reject the job.

        Examples:
        - "Job skills must include Laravel" → check JOB_SKILLS and JOB_DESCRIPTION.
        - "Must be long-term" → reject short-term projects.
        - "Job should involve feature work on an existing site" → accept maintenance, bug fixes, or additions.

        ### CAMPAIGN_JOB_DONT

        CAMPAIGN_JOB_DONT lists disqualifiers. If **any** rule matches the job, reject immediately.

        Examples:
        - "Full-time or fixed hours" → reject full-time commitments.
        - "Location outside India" → reject if job requires a country other than India.
        - "WordPress only" → reject if the stack is unsupported.

        CAMPAIGN_JOB_DO and CAMPAIGN_JOB_DONT have higher priority than portfolio matching.

        # CONSTRAINTS

        - Never invent experience.
        - Never assume skills that are not supported by the provided information.
        - Favor accuracy over optimism.
        - The reason must be concise and no longer than 80 characters.

        # OUTPUT FORMAT

        Return ONLY valid JSON:

        {
            "is_matched": true,
            "reason": "Strong React and Node.js experience with similar SaaS projects"
        }

        # VALIDATION

        - Ensure every conclusion is supported by the supplied data.
        - Ensure the response is valid JSON.
        - Ensure the reason is no longer than 80 characters.
        - Ensure no text exists outside the JSON.
        EOT;

        $decoded = $this->deepseekService->request([
            [
                'role' => 'system',
                'content' => $prompt,
            ],
            [
                'role' => 'user',
                'content' => json_encode([
                    'JOB_DESCRIPTION' => $jobData['description'],
                    'JOB_QUESTIONS' => $jobData['questions'],
                    'JOB_SKILLS' => implode(', ', $jobData['skills']),
                    'CAMPAIGN_PORTFOLIOS' => $campaignData['portfolios'],
                    'CAMPAIGN_EXPERIENCE' => $campaignData['experience'] ?? '',
                    'CAMPAIGN_QUESTIONS_CONTEXT' => $campaignData['questions_context'],
                    'CAMPAIGN_JOB_DO' => $campaignData['job_do'] ?? '',
                    'CAMPAIGN_JOB_DONT' => $campaignData['job_dont'] ?? '',
                ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
            ],
        ], 'deepseek-reasoner');

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
        $prompt = <<<'EOT'
        # ROLE
        You are a senior freelance proposal and cover letter specialist with extensive experience helping full-stack developers win projects on platforms like Upwork.
        You specialize in analyzing job descriptions, identifying the most relevant skills and experience, and writing natural, and persuasive cover letters that maximize response rates.
        You prioritize relevance, clarity, and professionalism while avoiding generic or overly sales-oriented language.

        # OBJECTIVE
        Create a natural tone Upwork proposal that highlights the most relevant experience, addresses the client's requirements, and encourages further discussion.

        # CONTEXT
        - CLIENT_NAME = client name from job data.
        - JOB_TITLE = job title from job data.
        - JOB_DESCRIPTION = job description from job data.
        - JOB_QUESTIONS = job questions from job data which we need to answer.
        - COVERLETTER_SKELETON = cover letter skeleton from campaign data.
        - CAMPAIGN_PORTFOLIOS = campaign portfolios from campaign data.
        - CAMPAIGN_EXPERIENCE = candidate experience from campaign data.
        - CAMPAIGN_QUESTIONS_CONTEXT = campaign questions context from campaign data.
        - AI_COVER_LETTER = ai cover letter flag from campaign data.
    
        # INSTRUCTIONS
        ## Placeholder Replacement
        COVERLETTER_SKELETON may contain ONLY these placeholders. Replace every one that appears:
        - [START_WITH]
        - [GREETINGS]
        - [HOOK]
        - [PORTFOLIOS_LIST]
        - [PORTFOLIOS_PARAGRAPH]
        - [INLINE_QUESTIONS]
        - [CTA]

        Never leave unresolved placeholders in the final cover letter.

        ### [START_WITH]
        - If JOB_DESCRIPTION requires a specific opening word or phrase (e.g. "Start your proposal with BLUE"), output that exact phrase.
        - Otherwise output nothing (remove the placeholder with no replacement text).
        - When present, [START_WITH] is concatenated directly before [GREETINGS] on the first line with no extra space unless the required phrase ends with one.

        ### [GREETINGS]
        - Choose one of: Hi, Hello, or Hey.
        - If CLIENT_NAME is available, append the client's name followed by a comma.
        - Example: Hi John,
        - [START_WITH][GREETINGS] and [HOOK] belong on the same first line: "[START_WITH][GREETINGS] [HOOK]" with no line break between them.

        ### [HOOK]
        - One concise sentence that proves you read the job and states why you are a strong fit.
        - Must appear on the same line as [GREETINGS], separated by a single space.

        ### [PORTFOLIOS_LIST]
        - Select the most relevant portfolio items from CAMPAIGN_PORTFOLIOS based on keywords, technologies, project type, and business domain.
        - Format as a short bullet list (one item per line, prefixed with "- ").
        - Include project name, brief relevance, and URL when available.
        - Use only items that match the job; omit weak matches.

        ### [PORTFOLIOS_PARAGRAPH]
        - Select the most relevant portfolio items from CAMPAIGN_PORTFOLIOS (same matching rules as [PORTFOLIOS_LIST]).
        - Weave them into one or two natural paragraphs (no bullets).
        - Mention project name, tech relevance, and outcome; include URLs inline when available.

        ### [INLINE_QUESTIONS]
        - Questions embedded in JOB_DESCRIPTION itself (not JOB_QUESTIONS).
        - Answer each inline question in the cover letter body.
        - Remove this placeholder entirely if the job has no inline questions.
        - Answers must come from CAMPAIGN_EXPERIENCE, CAMPAIGN_QUESTIONS_CONTEXT, and CAMPAIGN_PORTFOLIOS only.

        ### [CTA]
        - Generate a natural call-to-action encouraging further discussion.
        - Must be its own paragraph, separated by a blank line from the body above.
        - Do not mention any person's name.

        ### Sign-off
        - End with "Thanks," on its own final line after a blank line following [CTA].
        - Do not add a name, title, email, or signature block after "Thanks,".

        ## Questions
        JOB_QUESTIONS (screening questions, not inline) must be answered in the questions output array only — not in the cover letter.
        Answers should be derived from:
        - CAMPAIGN_EXPERIENCE
        - CAMPAIGN_QUESTIONS_CONTEXT
        - CAMPAIGN_PORTFOLIOS

        Never invent experience or facts.

        ## AI_COVER_LETTER = enabled
        - Write a completely personalized proposal.
        - Use the template only as guidance.
        - Adjust the proposal length according to the complexity of the job.
        - Naturally incorporate relevant portfolio information.

        ## AI_COVER_LETTER = disabled
        - Preserve the original COVERLETTER_SKELETON.
        - Only replace placeholders.
        - Answer questions.
        - Do not rewrite the template structure.

        ## LENGTH
        - Target 200–280 words for the cover letter body.
        - Simple jobs (1–2 requirements): 180–200 words.
        - Complex jobs (5+ requirements, multiple integrations): 220–300 words.
        - Never exceed 320 words.
        - Count words before returning; if under 150 or over 320, rewrite once to fit.
        - Must write in paragraphs with blank lines between them; never a single wall of text.
        - [CTA] must be its own paragraph, separated by a blank line from the body.
        - "Thanks," must be on its own final line after a blank line following [CTA].

        # CONSTRAINTS
        - Minimum 150 words in the cover letter.
        - End with "Thanks," only. Do not add a name, title, email, or full signature block.
        - Never output "[Your Name]", "[Client Name]", or any other unresolved placeholder in the final cover letter.
        - [START_WITH], [GREETINGS], and [HOOK] MUST be on one line with no line break between them.
        - No inline-question answers in the output questions array.
        - No emojis.
        - Do not mention names inside the CTA.
        - Do not fabricate experience.
        - Avoid overly sales-oriented language.
        - Maintain a natural tone.
        - Remove [INLINE_QUESTIONS] if there are no inline questions in the job description.
        - Use [PORTFOLIOS_LIST] or [PORTFOLIOS_PARAGRAPH] as dictated by the skeleton — do not invent the other format if it is not in the template.

        # OUTPUT FORMAT
        Return ONLY valid JSON.
        {
            "cover_letter": "string",
            "questions": [
                {
                    "question": "string",
                    "answer": "string"
                }
            ]
        }

        # VALIDATION
        Before returning:
        - Ensure all placeholders are replaced.
        - Ensure every question has an answer.
        - Ensure answers are based only on the supplied context.
        - Ensure the response is valid JSON.
        - Ensure no explanatory text exists outside the JSON.
        - Ensure [START_WITH], [GREETINGS], and [HOOK] are on the same first line.
        - Ensure [CTA] is a separate paragraph before "Thanks,".
        - Ensure "Thanks," is the final line.
        EOT;

        $response = $this->deepseekService->request([
            [
                'role' => 'system',
                'content' => $prompt,
            ],
            [
                'role' => 'user',
                'content' => json_encode([
                    'CLIENT_NAME' => $jobData['client_name'],
                    'JOB_TITLE' => $jobData['title'],
                    'JOB_DESCRIPTION' => $jobData['description'],
                    'JOB_QUESTIONS' => $jobData['questions'],
                    'COVERLETTER_SKELETON' => $campaignData['ai_prompt'],
                    'CAMPAIGN_PORTFOLIOS' => $campaignData['portfolios'],
                    'CAMPAIGN_EXPERIENCE' => $campaignData['experience'] ?? '',
                    'CAMPAIGN_QUESTIONS_CONTEXT' => $campaignData['questions_context'],
                    'AI_COVER_LETTER' => $campaignData['ai_cover_letter'] ? 'enabled' : 'disabled',
                ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
            ],
        ], 'deepseek-reasoner');
        if (! is_array($response)) {
            throw new RuntimeException('Invalid AI response');
        }
        $coverLetter = (string) ($response['cover_letter'] ?? '');
        $rows = $response['questions'] ?? [];

        return [
            'cover_letter' => $coverLetter,
            'questions' => $rows,
        ];
    }
}
