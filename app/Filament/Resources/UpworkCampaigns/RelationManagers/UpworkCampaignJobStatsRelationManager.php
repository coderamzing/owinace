<?php

namespace App\Filament\Resources\UpworkCampaigns\RelationManagers;

use App\Filament\Resources\UpworkCampaigns\Pages\EditUpworkCampaign;
use App\Filament\Resources\UpworkCampaigns\Pages\ViewUpworkCampaign;
use App\Models\UpworkCampaignJobStat;
use App\Models\UpworkJob;
use App\Services\BotAIService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Throwable;

class UpworkCampaignJobStatsRelationManager extends RelationManager
{
    protected static string $relationship = 'campaignJobStats';

    protected static ?string $title = 'Campaign job stats';

    /** @var array<int, array{cover_letter: string, qa: string}> */
    public array $testCoverLetterByStatId = [];

    /** @var array<int, array{is_matched: bool, reason: string}> */
    public array $testAnalyzeByStatId = [];

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return in_array($pageClass, [
            EditUpworkCampaign::class,
            ViewUpworkCampaign::class,
        ], true);
    }

    public function table(Table $table): Table
    {
        $searchTerm = $this->getOwnerRecord()->searchQueryTerm();

        return $table
            ->heading(null)
            ->description($searchTerm !== null
                ? 'Jobs whose skills match the search URL keyword «'.$searchTerm.'».'
                : null)
            ->modifyQueryUsing(function (Builder $query) use ($searchTerm): Builder {
                $query->with(['job']);

                if ($searchTerm === null) {
                    return $query;
                }

                $needle = '%'.mb_strtolower($searchTerm).'%';

                return $query
                    ->join('upwork_jobs', 'upwork_jobs.id', '=', 'upwork_campaign_job_stats.job_id')
                    ->whereRaw('LOWER(upwork_jobs.skills) LIKE ?', [$needle])
                    ->select('upwork_campaign_job_stats.*');
            })
            ->defaultSort('upwork_campaign_job_stats.updated_at', 'desc')
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->columns([
                TextColumn::make('job.title')
                    ->label('Job')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('job', function (Builder $q) use ($search): void {
                            $q->where('title', 'like', '%'.$search.'%');
                        });
                    })
                    ->description(fn ($record) => $record->job?->uid)
                    ->color('primary')
                    ->action($this->viewJobAction())
                    ->wrap(),
                IconColumn::make('is_matched')
                    ->label('Matched')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                TextColumn::make('note')
                    ->label('Note')
                    ->placeholder('—')
                    ->wrap()
                    ->searchable()
                    ->tooltip(fn ($state) => filled($state) ? (string) $state : null),
                IconColumn::make('is_applied')
                    ->label('Applied')
                    ->boolean()
                    ->trueIcon('heroicon-o-paper-airplane')
                    ->falseIcon('heroicon-o-minus-circle')
                    ->trueColor('info')
                    ->falseColor('gray'),
                TextColumn::make('job.posted_at')
                    ->label('Posted')
                    ->dateTime()
                    ->placeholder('—'),
                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query->orderBy('upwork_campaign_job_stats.updated_at', $direction))
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_matched')
                    ->label('Match status')
                    ->trueLabel('Matched')
                    ->falseLabel('Not matched')
                    ->placeholder('All jobs'),
                TernaryFilter::make('is_applied')
                    ->label('Applied')
                    ->trueLabel('Applied')
                    ->falseLabel('Not applied')
                    ->placeholder('All'),
            ])
            ->headerActions([])
            ->recordActions([])
            ->bulkActions([]);
    }

    protected function viewJobAction(): Action
    {
        return Action::make('viewJob')
            ->modalHeading(fn ($record): string => $record->job?->title ?? 'Job details')
            ->slideOver()
            ->modalWidth('2xl')
            ->modalContent(function ($record) {
                $testResult = $this->testCoverLetterByStatId[$record->id] ?? null;
                $analyzeResult = $this->testAnalyzeByStatId[$record->id] ?? null;

                return view(
                    'filament.resources.upwork-campaigns.job-details-modal',
                    [
                        'job' => $record->job,
                        'stat' => $record,
                        'coverLetter' => $testResult['cover_letter'] ?? null,
                        'qa' => $testResult['qa'] ?? null,
                        'analyzeResult' => $analyzeResult,
                    ]
                );
            })
            ->extraModalFooterActions([
                Action::make('analyzeJob')
                    ->label('Analyze')
                    ->icon('heroicon-o-magnifying-glass-circle')
                    ->color('gray')
                    ->disabled(fn (UpworkCampaignJobStat $record): bool => blank($record->job?->description))
                    ->tooltip(fn (UpworkCampaignJobStat $record): ?string => blank($record->job?->description)
                        ? 'This job has no description to analyze.'
                        : null)
                    ->action(fn (UpworkCampaignJobStat $record) => $this->analyzeJobForStat($record)),
                Action::make('testCoverLetter')
                    ->label('Test Cover Letter')
                    ->icon('heroicon-o-sparkles')
                    ->color('primary')
                    ->disabled(fn (UpworkCampaignJobStat $record): bool => blank($record->job?->description))
                    ->tooltip(fn (UpworkCampaignJobStat $record): ?string => blank($record->job?->description)
                        ? 'This job has no description to test with.'
                        : null)
                    ->action(fn (UpworkCampaignJobStat $record) => $this->testCoverLetterForJob($record)),
            ])
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Close');
    }

    public function analyzeJobForStat(UpworkCampaignJobStat $record): void
    {
        $job = $record->job;

        if (! $job || blank($job->description)) {
            Notification::make()
                ->title('Job description required')
                ->body('This job does not have a description to analyze.')
                ->warning()
                ->send();

            return;
        }

        try {
            $result = app(BotAIService::class)->analyzeJob(
                $this->jobDataForAiAnalyze($job),
                $this->campaignDataForAi(),
            );
        } catch (Throwable $e) {
            Notification::make()
                ->title('AI request failed')
                ->body($e->getMessage())
                ->danger()
                ->send();

            return;
        }

        $this->testAnalyzeByStatId[$record->id] = [
            'is_matched' => (bool) ($result['is_matched'] ?? false),
            'reason' => (string) ($result['reason'] ?? ''),
        ];

        Notification::make()
            ->title($result['is_matched'] ? 'Job matched' : 'Job not matched')
            ->body($result['reason'] ?? '')
            ->color($result['is_matched'] ? 'success' : 'warning')
            ->send();
    }

    public function testCoverLetterForJob(UpworkCampaignJobStat $record): void
    {
        $job = $record->job;

        if (! $job || blank($job->description)) {
            Notification::make()
                ->title('Job description required')
                ->body('This job does not have a description to generate a cover letter from.')
                ->warning()
                ->send();

            return;
        }

        $questions = collect(is_array($job->questions) ? $job->questions : [])
            ->map(fn ($row) => is_array($row) ? trim((string) ($row['text'] ?? $row['question'] ?? '')) : trim((string) $row))
            ->filter()
            ->values()
            ->all();

        $jobData = [
            'title' => $job->title,
            'description' => $job->description,
            'questions' => $questions,
            'client_name' => $job->client_name ?? '',
        ];

        try {
            $result = app(BotAIService::class)->writeCoverLetter($jobData, $this->campaignDataForAi());
        } catch (Throwable $e) {
            Notification::make()
                ->title('AI request failed')
                ->body($e->getMessage())
                ->danger()
                ->send();

            return;
        }

        $lines = [];
        foreach ($result['questions'] ?? [] as $row) {
            $q = $row['question'] ?? '';
            $a = $row['answer'] ?? '';
            $lines[] = 'Q: '.$q."\n".'A: '.$a;
        }

        $this->testCoverLetterByStatId[$record->id] = [
            'cover_letter' => $result['cover_letter'] ?? '',
            'qa' => implode("\n\n", $lines),
        ];

        Notification::make()
            ->title('Cover letter generated')
            ->success()
            ->send();
    }

    /**
     * @return array{description: string, questions: array<int, string>, skills: array<int, string>}
     */
    protected function jobDataForAiAnalyze(UpworkJob $job): array
    {
        $questions = collect(is_array($job->questions) ? $job->questions : [])
            ->map(fn ($row) => is_array($row) ? trim((string) ($row['text'] ?? $row['question'] ?? '')) : trim((string) $row))
            ->filter()
            ->values()
            ->all();

        $skills = collect(is_array($job->skills) ? $job->skills : [])
            ->map(fn ($skill) => is_array($skill) ? trim((string) ($skill['name'] ?? '')) : trim((string) $skill))
            ->filter()
            ->values()
            ->all();

        return [
            'description' => $job->description,
            'questions' => $questions,
            'skills' => $skills,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function campaignDataForAi(): array
    {
        $record = $this->getOwnerRecord()->fresh(['linkedPortfolios']);

        return array_merge($record->only([
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
    }
}
