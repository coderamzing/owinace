<?php

namespace App\Filament\Resources\UpworkCampaigns\Concerns;

use App\Services\BotAIService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Throwable;

trait TestsUpworkCampaignJobs
{
    /** @var array{is_matched: bool, reason: string}|null */
    public ?array $modalAnalyzeResult = null;

    public ?string $modalWriteCoverLetter = null;

    public ?string $modalWriteQa = null;

    /**
     * Result modal: cover letter + Q&A (after Write).
     */
    public function viewWriteResultAction(): Action
    {
        return Action::make('viewWriteResult')
            ->modalHeading('Cover letter & answers')
            ->modalWidth('4xl')
            ->modalContent(fn (): \Illuminate\Contracts\View\View => view(
                'filament.resources.upwork-campaigns.test-modal-write',
                [
                    'coverLetter' => $this->modalWriteCoverLetter ?? '',
                    'qa' => $this->modalWriteQa ?? '',
                ],
            ))
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Close');
    }

    /**
     * Result modal: job vs campaign match (after Analyze).
     */
    public function viewAnalyzeResultAction(): Action
    {
        return Action::make('viewAnalyzeResult')
            ->modalHeading('Job match analysis')
            ->modalWidth('2xl')
            ->modalContent(fn (): \Illuminate\Contracts\View\View => view(
                'filament.resources.upwork-campaigns.test-modal-analyze',
                [
                    'result' => $this->modalAnalyzeResult,
                ],
            ))
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Close');
    }

    public function writeCoverLetterTest(): void
    {
        $description = trim((string) ($this->data['test_job_description'] ?? ''));
        $questionRows = $this->data['test_job_questions'] ?? [];

        if ($description === '') {
            Notification::make()
                ->title('Add a job description')
                ->body('Enter a sample job description before running Write.')
                ->warning()
                ->send();

            return;
        }

        $questions = collect(is_array($questionRows) ? $questionRows : [])
            ->map(fn ($row) => is_array($row) ? trim((string) ($row['text'] ?? '')) : '')
            ->filter()
            ->values()
            ->all();

        $jobData = [
            'title' => 'Test job',
            'description' => $description,
            'questions' => $questions,
            'client_name' => 'Test client',
        ];

        $record = $this->getRecord()->fresh(['linkedPortfolios']);

        $campaignData = array_merge($record->only([
            'title',
            'ai_prompt',
            'ai_cover_letter',
            'experience',
            'questions_context',
            'job_do',
            'job_dont',
            'rule_client_avg_spent',
            'rule_client_avghire',
            'rule_max_interviews',
            'rule_job_posted_ago',
            'rule_max_proposal',
            'rule_min_client_rating',
            'search_url',
            'max_connect_per_bid',
            'max_daily_bid',
        ]), [
            'portfolios' => $record->portfoliosPromptText(),
        ]);

        try {
            $result = app(BotAIService::class)->writeCoverLetter($jobData, $campaignData);
        } catch (Throwable $e) {
            Notification::make()
                ->title('AI request failed')
                ->body($e->getMessage())
                ->danger()
                ->send();

            return;
        }

        $this->modalWriteCoverLetter = $result['cover_letter'] ?? '';

        $lines = [];
        foreach ($result['questions'] ?? [] as $row) {
            $q = $row['question'] ?? '';
            $a = $row['answer'] ?? '';
            $lines[] = 'Q: '.$q."\n".'A: '.$a;
        }
        $this->modalWriteQa = implode("\n\n", $lines);

        $this->mountAction('viewWriteResult');
    }

    public function analyzeJobTest(): void
    {
        $description = trim((string) ($this->data['test_job_description'] ?? ''));

        if ($description === '') {
            Notification::make()
                ->title('Add a job description')
                ->body('Enter a job description to analyze the match.')
                ->warning()
                ->send();

            return;
        }

        $jobData = [
            'description' => $description,
            'questions' => [],
            'skills' => [],
        ];

        $record = $this->getRecord()->fresh(['linkedPortfolios']);

        $campaignData = array_merge($record->only([
            'title',
            'ai_prompt',
            'ai_cover_letter',
            'experience',
            'questions_context',
            'job_do',
            'job_dont',
            'rule_client_avg_spent',
            'rule_client_avghire',
            'rule_max_interviews',
            'rule_job_posted_ago',
            'rule_max_proposal',
            'rule_min_client_rating',
            'search_url',
            'max_connect_per_bid',
            'max_daily_bid',
        ]), [
            'portfolios' => $record->portfoliosPromptText(),
        ]);

        try {
            $this->modalAnalyzeResult = app(BotAIService::class)->analyzeJob($jobData, $campaignData);
        } catch (Throwable $e) {
            Notification::make()
                ->title('AI request failed')
                ->body($e->getMessage())
                ->danger()
                ->send();

            return;
        }

        $this->mountAction('viewAnalyzeResult');
    }
}
