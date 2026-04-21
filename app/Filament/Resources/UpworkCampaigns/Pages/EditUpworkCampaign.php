<?php

namespace App\Filament\Resources\UpworkCampaigns\Pages;

use App\Filament\Resources\UpworkCampaigns\UpworkCampaignResource;
use App\Services\BotAIService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Throwable;

class EditUpworkCampaign extends EditRecord
{
    protected static string $resource = UpworkCampaignResource::class;

    /** @var array{is_matched: bool, reason: string}|null */
    public ?array $modalAnalyzeResult = null;

    public ?string $modalWriteCoverLetter = null;

    public ?string $modalWriteQa = null;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

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

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data = parent::mutateFormDataBeforeFill($data);

        foreach ([
            'test_job_description' => '',
            'test_job_questions' => [],
        ] as $key => $default) {
            if (! array_key_exists($key, $data)) {
                $data[$key] = $default;
            }
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        foreach (array_keys($data) as $key) {
            if (str_starts_with((string) $key, 'test_')) {
                unset($data[$key]);
            }
        }

        return parent::mutateFormDataBeforeSave($data);
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
        ];

        $record = $this->getRecord()->fresh();

        $campaignData = $record->only([
            'title',
            'portfolios',
            'ai_prompt',
            'questions_context',
            'rule_client_avg_spent',
            'rule_max_interviews',
            'rule_job_posted_ago',
            'rule_max_proposal',
            'rule_clock_in',
            'rule_clock_out',
            'search_url',
            'max_connect_per_bid',
            'max_daily_bid',
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
        ];

        $record = $this->getRecord()->fresh();

        $campaignData = $record->only([
            'title',
            'portfolios',
            'ai_prompt',
            'questions_context',
            'rule_client_avg_spent',
            'rule_max_interviews',
            'rule_job_posted_ago',
            'rule_max_proposal',
            'rule_clock_in',
            'rule_clock_out',
            'search_url',
            'max_connect_per_bid',
            'max_daily_bid',
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
