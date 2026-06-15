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
        - CAMPAING_MATCHING_CRITERIA (set of rules from campaign data)

        # INSTRUCTIONS
        - Analyze the job requirements, technologies, features, integrations, domain, and experience level.
        - Compare the requirements against CAMPAIGN_PORTFOLIOS, CAMPAIGN_EXPERIENCE, and CAMPAING_MATCHING_CRITERIA.
        - Determine whether the available portfolios and experience demonstrate similar work and relevant expertise.
        - Determine whether JOB_QUESTIONS can be reasonably answered using CAMPAIGN_QUESTIONS_CONTEXT, CAMPAIGN_EXPERIENCE, or CAMPAIGN_PORTFOLIOS.
        - A partial match is acceptable if the core requirements are strongly aligned.
        - Reject the opportunity if major requirements are unsupported or if important questions cannot be answered.
        - Base the decision only on the supplied information.
        - Never fabricate experience or assume unsupported knowledge.

        ### CAMPAING_MATCHING_CRITERIA

        CAMPAING_MATCHING_CRITERIA contains human-written conditions that define which jobs are suitable.

        These rules may describe:

        - Required technologies.
        - Required skills.
        - Allowed or forbidden locations.
        - Project types.
        - Experience requirements.
        - Feature work vs. new development.
        - Budget constraints.
        - Availability requirements.
        - Any other inclusion or exclusion conditions.

        Interpret these rules semantically rather than requiring exact wording.

        Examples:

        - "Job skills must include Laravel"
            → Match against JOB_SKILLS and JOB_DESCRIPTION.

        - "Job should be about adding new features or fixing an existing website"
            → Accept jobs involving maintenance, bug fixes, feature additions, or existing systems.

        - "If any location is mentioned and it is not India, return false"
            → Treat explicit country requirements such as South Africa, USA, Canada, Germany, etc. as exclusion rules.

        - "Must be long-term"
            → Reject short-term projects.

        - "Avoid full-time positions"
            → Reject jobs requiring fixed working hours or full-time commitments.

        CAMPAING_MATCHING_CRITERIA has higher priority than portfolio matching.

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
                    'CAMPAING_MATCHING_CRITERIA' => $campaignData['matching_criteria'] ?? $campaignData['matching_critieria'] ?? '',
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
        You specialize in analyzing job descriptions, identifying the most relevant skills and experience, and writing concise, natural, and persuasive cover letters that maximize response rates.
        You prioritize relevance, clarity, and professionalism while avoiding generic or overly sales-oriented language.

        # OBJECTIVE
        Create a concise and professional Upwork proposal that highlights the most relevant experience, addresses the client's requirements, and encourages further discussion.

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
        ## Greeting
        - Start with one of:
            - Hi
            - Hello
            - Hey
        - If CLIENT_NAME is available, append the client's name.
        Example: Hi John,

        ## Client Instructions
        - If the job description requests a specific starting word or phrase, follow that requirement.

        ## Placeholder Replacement
        The proposal template may contain:
        - [CLIENT_NAME]
        - [CTA]
        - [PORTFOLIOS]
        - [HOOK]
        - [INLINE_QUESTIONS]
        Replace all placeholders.

        ### [CTA]
        Generate an appropriate call-to-action.
        - Do not mention any person's name.

        ### [PORTFOLIOS]
        Select the most relevant portfolio items based on:
        - keywords
        - technologies
        - project type
        - business domain
        Use only relevant portfolio information.


        ### [INLINE_QUESTIONS]
        Questions which are come from job description itself.
        All questions must be answered & append to cover letter not to questions output
        Answers should be derived from:
        - CAMPAIGN_EXPERIENCE
        - CAMPAIGN_QUESTIONS_CONTEXT
        - PORTFOLIOS

        ## Questions
        Questions may come from:
        1. JOB_QUESTIONS expect INLINE_QUESTIONS.
        All questions must be answered.
        Answers should be derived from:
        - CAMPAIGN_EXPERIENCE
        - CAMPAIGN_QUESTIONS_CONTEXT
        - PORTFOLIOS

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

        # CONSTRAINTS
        - [HOOK] and Greetings should be written in the first paragraph of the cover letter.
        - No [Your Name] or [Client Name] in the cover letter.
        - No Inline Question's answers in the output questions array.
        - No emojis.
        - No unresolved placeholders.
        - Do not mention names inside the CTA.
        - Do not fabricate experience.
        - Avoid overly sales-oriented language.
        - Maintain a natural tone.
        - Remove INLINE_QUESTIONS placeholder if there is no inline questions in the job description.


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
