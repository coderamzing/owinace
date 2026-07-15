<?php

namespace App\Filament\Resources\UpworkCampaigns\Schemas;

use App\Filament\Resources\UpworkCampaigns\Pages\EditUpworkCampaign;
use App\Filament\Resources\UpworkCampaigns\RelationManagers\LinkedPortfoliosRelationManager;
use App\Filament\Resources\UpworkCampaigns\RelationManagers\UpworkCampaignJobStatsRelationManager;
use App\Models\LeadKanban;
use App\Models\LeadSource;
use App\Models\TeamMember;
use App\Models\UpworkProfile;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use App\Filament\Forms\Components\FlatpickrTimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Support\Icons\Heroicon;
use Closure;
use Illuminate\Support\HtmlString;

class UpworkCampaignForm
{
    /**
     * Shared Write/Analyze test fields used on Edit form and View infolist.
     *
     * @return array<int, mixed>
     */
    public static function testTabSchema(): array
    {
        return [
            Callout::make('Test before you go live')
                ->description('Paste a job description (and optional client questions). Use Write to preview a proposal, or Analyze to see if the job fits this campaign. Results open in a dialog; nothing here is saved.')
                ->icon('heroicon-o-information-circle')
                ->columnSpanFull(),
            Textarea::make('test_job_description')
                ->label('Job description')
                ->placeholder('Full job post text from Upwork…')
                ->rows(12)
                ->columnSpanFull()
                ->dehydrated(false),
            Repeater::make('test_job_questions')
                ->label('Screening questions (optional)')
                ->schema([
                    TextInput::make('text')
                        ->label('Question')
                        ->maxLength(2000)
                        ->columnSpanFull(),
                ])
                ->reorderableWithButtons()
                ->addActionLabel('Add question')
                ->default([])
                ->columnSpanFull()
                ->dehydrated(false),
            Actions::make([
                Action::make('writeCoverLetterTest')
                    ->label('Write')
                    ->icon('heroicon-o-sparkles')
                    ->color('primary')
                    ->action('writeCoverLetterTest'),
                Action::make('analyzeJobTest')
                    ->label('Analyze')
                    ->icon('heroicon-o-magnifying-glass-circle')
                    ->color('gray')
                    ->action('analyzeJobTest'),
            ])
                ->alignment(Alignment::Start),
        ];
    }

    private static function coverLetterTemplateHelpAction(): Action
    {
        return Action::make('coverLetterTemplateHelp')
            ->icon(Heroicon::OutlinedQuestionMarkCircle)
            ->iconButton()
            ->color('gray')
            ->label('Template help')
            ->tooltip('Cover letter placeholders & sample')
            ->modalHeading('Cover letter template')
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Close')
            ->modalContent(fn (): HtmlString => new HtmlString(<<<'HTML'
<div class="space-y-4 text-sm">
    <div>
        <p class="font-medium text-gray-950 dark:text-white">Allowed placeholders</p>
        <p class="mt-1 font-mono text-xs text-gray-600 dark:text-gray-300">[START_WITH] [GREETINGS] [HOOK] [UNDERSTANDING] [WHY_ME] [SIMILAR_PROJECT] [PORTFOLIOS_PARAGRAPH] [PORTFOLIOS_LIST] [TECH_MATCH] [APPROACH] [QUICK_WIN] [INLINE_QUESTIONS] [DISCOVERY_QUESTIONS] [VALUE_STATEMENT] [CTA]</p>
    </div>
    <div>
        <p class="font-medium text-gray-950 dark:text-white">Sample structure</p>
        <pre class="mt-1 overflow-x-auto rounded-lg bg-gray-50 p-3 font-mono text-xs text-gray-800 dark:bg-gray-900 dark:text-gray-100">[START_WITH]
[GREETINGS]
[HOOK]

[UNDERSTANDING]

[WHY_ME]

[SIMILAR_PROJECT]

[PORTFOLIOS_PARAGRAPH]

[TECH_MATCH]

[APPROACH]

[QUICK_WIN]

[INLINE_QUESTIONS]

[DISCOVERY_QUESTIONS]

[VALUE_STATEMENT]

[CTA]

Thanks</pre>
    </div>
    <ul class="list-disc space-y-1 pl-5 text-gray-600 dark:text-gray-300">
        <li><strong>[START_WITH]</strong> — required opening word from the job, or empty</li>
        <li><strong>[GREETINGS]</strong> — Hi/Hello/Hey + client name</li>
        <li><strong>[HOOK]</strong> — one-line fit statement</li>
        <li><strong>[UNDERSTANDING]</strong> — show you understand the client's problem</li>
        <li><strong>[WHY_ME]</strong> — why you are the right fit for this job</li>
        <li><strong>[SIMILAR_PROJECT]</strong> — most relevant past project</li>
        <li><strong>[PORTFOLIOS_PARAGRAPH]</strong> — relevant work as prose</li>
        <li><strong>[PORTFOLIOS_LIST]</strong> — relevant work as bullets</li>
        <li><strong>[TECH_MATCH]</strong> — map job tech/skills to your experience</li>
        <li><strong>[APPROACH]</strong> — how you would tackle the project</li>
        <li><strong>[QUICK_WIN]</strong> — early deliverable or fast value</li>
        <li><strong>[INLINE_QUESTIONS]</strong> — answers to questions in the job post</li>
        <li><strong>[DISCOVERY_QUESTIONS]</strong> — thoughtful questions for the client</li>
        <li><strong>[VALUE_STATEMENT]</strong> — outcome/value you deliver</li>
        <li><strong>[CTA]</strong> — call-to-action paragraph</li>
        <li>Static text (e.g. <strong>Thanks</strong>, your name) stays exactly where you place it</li>
    </ul>
</div>
HTML));
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('CampaignTabs')
                    ->tabs([
                        Tab::make('General')
                            ->icon('heroicon-o-adjustments-horizontal')
                            ->schema([
                                TextInput::make('title')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(),
                                Toggle::make('is_active')
                                    ->label('Active')
                                    ->default(true),

                                TextInput::make('search_url')
                                    ->label('Search URL')
                                    ->url()
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(),
                                TextInput::make('webhook_url')
                                    ->label('Match webhook URL')
                                    ->url()
                                    ->nullable()
                                    ->maxLength(500)
                                    ->helperText('Discord or Slack incoming webhook. Fired once when a job matches this campaign.')
                                    ->columnSpanFull()
                                    ->hintAction(
                                        Action::make('testWebhook')
                                            ->label('Test')
                                            ->icon('heroicon-o-paper-airplane')
                                            ->visible(fn (?Get $get, $record): bool => filled($get('webhook_url') ?: $record?->webhook_url))
                                            ->action('testWebhook'),
                                    ),
                                TextInput::make('max_daily_bid')
                                    ->label('Max daily bid')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(32767)
                                    ->default(0),
                                Toggle::make('auto_bidding')
                                    ->label('Auto bidding')
                                    ->default(false),
                                Select::make('profile_id')
                                    ->label('Upwork profile')
                                    ->options(function (): array {
                                        $teamId = session('team_id');
                                        if (! $teamId || $teamId < 1) {
                                            return [];
                                        }

                                        return UpworkProfile::query()
                                            ->where('team_id', $teamId)
                                            ->where('is_active', true)
                                            ->orderBy('title')
                                            ->pluck('title', 'id')
                                            ->all();
                                    })
                                    ->searchable()
                                    ->helperText('Required for the auto-bidding bot. Create profiles via the Profiles button on the campaigns list.')
                                    ->nullable(),
                                Select::make('member_id')
                                    ->label('Team member')
                                    ->options(function (): array {
                                        $teamId = session('team_id');
                                        if (! $teamId || $teamId < 1) {
                                            return [];
                                        }

                                        return TeamMember::query()
                                            ->where('team_id', $teamId)
                                            ->with('user')
                                            ->get()
                                            ->mapWithKeys(fn (TeamMember $m): array => [
                                                $m->id => $m->user?->name ?? ('Member #'.$m->id),
                                            ])
                                            ->all();
                                    })
                                    ->searchable()
                                    ->nullable(),
                                Select::make('source_id')
                                    ->label('Lead source')
                                    ->options(function (): array {
                                        $teamId = session('team_id');
                                        if (! $teamId) {
                                            return [];
                                        }

                                        return LeadSource::query()
                                            ->where('team_id', $teamId)
                                            ->where('is_active', true)
                                            ->orderBy('sort_order')
                                            ->pluck('name', 'id')
                                            ->all();
                                    })
                                    ->searchable()
                                    ->nullable(),
                                Select::make('kanban_id')
                                    ->label('Kanban')
                                    ->options(function (): array {
                                        $teamId = session('team_id');
                                        if (! $teamId) {
                                            return [];
                                        }

                                        return LeadKanban::query()
                                            ->where('team_id', $teamId)
                                            ->where('is_active', true)
                                            ->orderBy('sort_order')
                                            ->pluck('name', 'id')
                                            ->all();
                                    })
                                    ->searchable()
                                    ->nullable(),
                            ])
                            ->columns(2),
                        Tab::make('Rules')
                            ->icon('heroicon-o-funnel')
                            ->schema([
                                TextInput::make('max_connect_per_bid')
                                    ->label('Max connect per bid')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(32767)
                                    ->default(0),
                                TextInput::make('rule_client_avg_spent')
                                    ->label('Client avg. spent')
                                    ->numeric()
                                    ->step(0.01),
                                TextInput::make('rule_client_avghire')
                                    ->label('Min client hire rate (%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01),
                                TextInput::make('rule_max_interviews')
                                    ->label('Max interviews')
                                    ->numeric()
                                    ->step(0.01),
                                TextInput::make('rule_job_posted_ago')
                                    ->label('Job posted ago (In Minutes)')
                                    ->numeric()
                                    ->step(0.01),
                                TextInput::make('rule_max_proposal')
                                    ->label('Max proposal')
                                    ->numeric()
                                    ->step(0.01),
                                Select::make('rule_min_client_rating')
                                    ->label('Min client rating')
                                    ->options([
                                        1 => '1',
                                        2 => '2',
                                        3 => '3',
                                        4 => '4',
                                        5 => '5',
                                    ])
                                    ->nullable()
                                    ->placeholder('No minimum'),
                                Select::make('bidding_timezone')
                                    ->label('Bidding timezone')
                                    ->options(fn (): array => array_combine(
                                        timezone_identifiers_list(),
                                        timezone_identifiers_list(),
                                    ))
                                    ->searchable()
                                    ->default('UTC')
                                    ->required()
                                    ->helperText('Clock-in and clock-out times below are interpreted in this timezone.'),
                                Repeater::make('slots')
                                    ->label('Bidding time slots')
                                    ->relationship('slots')
                                    ->table([
                                        TableColumn::make('Clock in')
                                            ->markAsRequired(),
                                        TableColumn::make('Clock out')
                                            ->markAsRequired(),
                                    ])
                                    ->compact()
                                    ->schema([
                                        FlatpickrTimePicker::make('clock_in')
                                            ->label('Clock in')
                                            ->hiddenLabel()
                                            ->placeholder('09:00')
                                            ->minuteIncrement(5)
                                            ->live(onBlur: true)
                                            ->required(),
                                        FlatpickrTimePicker::make('clock_out')
                                            ->label('Clock out')
                                            ->hiddenLabel()
                                            ->placeholder('17:00')
                                            ->minuteIncrement(5)
                                            ->live(onBlur: true)
                                            ->required()
                                            ->rules([
                                                fn (Get $get): Closure => function (string $attribute, mixed $value, Closure $fail) use ($get): void {
                                                    $clockIn = $get('clock_in');

                                                    if (blank($clockIn) || blank($value)) {
                                                        return;
                                                    }

                                                    $clockIn = substr((string) $clockIn, 0, 5);
                                                    $clockOut = substr((string) $value, 0, 5);

                                                    if ($clockIn >= $clockOut) {
                                                        $fail('Clock out must be after clock in.');
                                                    }
                                                },
                                            ]),
                                    ])
                                    ->reorderableWithDragAndDrop()
                                    ->orderColumn('sort_order')
                                    ->addActionLabel('Add time slot')
                                    ->defaultItems(0)
                                    ->columnSpanFull()
                                    ->helperText('Leave empty to allow bidding at any time. Add multiple slots for split schedules (e.g. morning and evening).'),
                            ])
                            ->columns(2),
                        Tab::make('Portfolios')
                            ->icon('heroicon-o-briefcase')
                            ->schema([
                                Placeholder::make('portfolios_create_hint')
                                    ->label('Portfolios')
                                    ->content('Save this campaign first, then attach portfolios from your library below.')
                                    ->visibleOn([CreateRecord::class])
                                    ->columnSpanFull(),
                                Livewire::make(LinkedPortfoliosRelationManager::class)
                                    ->key('campaign-linked-portfolios')
                                    ->data(fn (EditUpworkCampaign $livewire): array => [
                                        'ownerRecord' => $livewire->getRecord(),
                                        'pageClass' => EditUpworkCampaign::class,
                                    ])
                                    ->visibleOn([EditRecord::class])
                                    ->columnSpanFull(),
                            ]),
                        Tab::make('Job stats')
                            ->icon('heroicon-o-chart-bar')
                            ->visibleOn([EditRecord::class])
                            ->schema([
                                Livewire::make(UpworkCampaignJobStatsRelationManager::class)
                                    ->key('campaign-job-stats-edit')
                                    ->data(fn (EditUpworkCampaign $livewire): array => [
                                        'ownerRecord' => $livewire->getRecord(),
                                        'pageClass' => EditUpworkCampaign::class,
                                    ])
                                    ->columnSpanFull(),
                            ]),
                        Tab::make('Prompts')
                            ->icon('heroicon-o-chat-bubble-left-right')
                            ->schema([
                                Toggle::make('ai_cover_letter')
                                    ->label('AI cover letter')
                                    ->helperText('When off, the AI prompt is submitted as the cover letter without AI rewriting. Screening questions still use AI when present.')
                                    ->default(true),
                                Textarea::make('ai_prompt')
                                    ->label('AI prompt')
                                    ->afterLabel(self::coverLetterTemplateHelpAction())
                                    ->rows(12)
                                    ->placeholder(<<<'TXT'
[START_WITH]
[GREETINGS]
[HOOK]

[UNDERSTANDING]

[WHY_ME]

[SIMILAR_PROJECT]

[PORTFOLIOS_PARAGRAPH]

[TECH_MATCH]

[APPROACH]

[QUICK_WIN]

[INLINE_QUESTIONS]

[DISCOVERY_QUESTIONS]

[VALUE_STATEMENT]

[CTA]

Thanks
TXT)
                                    ->helperText('Cover letter skeleton. Use ? next to the label for placeholders and a sample structure.')
                                    ->columnSpanFull(),
                                Textarea::make('ai_instruction')
                                    ->label('AI instruction')
                                    ->rows(6)
                                    ->helperText('Extra instructions for the AI when writing proposals or analyzing jobs.')
                                    ->columnSpanFull(),
                                Textarea::make('experience')
                                    ->label('Experience')
                                    ->rows(8)
                                    ->columnSpanFull(),
                                Textarea::make('questions_context')
                                    ->label('Questions context')
                                    ->rows(6)
                                    ->columnSpanFull(),
                                Textarea::make('job_do')
                                    ->label('Job do')
                                    ->helperText('The job must meet all of these rules to qualify (e.g. required skills, technologies, project type).')
                                    ->rows(6)
                                    ->columnSpanFull(),
                                Textarea::make('job_dont')
                                    ->label('Job don\'t')
                                    ->helperText('The job is disqualified if any of these rules match (e.g. forbidden locations, full-time only, unsupported stacks).')
                                    ->rows(6)
                                    ->columnSpanFull(),
                            ]),
                        Tab::make('Test')
                            ->icon('heroicon-o-beaker')
                            ->visibleOn([EditRecord::class])
                            ->schema(self::testTabSchema()),
                    ])
                    ->persistTabInQueryString()
                    ->columnSpanFull(),
            ]);
    }
}
