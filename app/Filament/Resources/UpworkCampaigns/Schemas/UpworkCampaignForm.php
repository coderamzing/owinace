<?php

namespace App\Filament\Resources\UpworkCampaigns\Schemas;

use App\Models\LeadKanban;
use App\Models\LeadSource;
use App\Models\TeamMember;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Tabs;
use Filament\Support\Enums\Alignment;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class UpworkCampaignForm
{
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
                                TextInput::make('max_daily_bid')
                                    ->label('Max daily bid')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(32767)
                                    ->default(0),
                                Toggle::make('auto_bidding')
                                    ->label('Auto bidding')
                                    ->default(false),
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
                                TimePicker::make('rule_clock_in')
                                    ->label('Clock in(UTC)')
                                    ->seconds(false),
                                TimePicker::make('rule_clock_out')
                                    ->label('Clock out(UTC)')
                                    ->seconds(false),
                            ])
                            ->columns(2),
                        Tab::make('Prompts')
                            ->icon('heroicon-o-chat-bubble-left-right')
                            ->schema([
                                Textarea::make('portfolios')
                                    ->rows(6)
                                    ->columnSpanFull(),
                                Textarea::make('ai_prompt')
                                    ->label('AI prompt')
                                    ->rows(8)
                                    ->columnSpanFull(),
                                Textarea::make('questions_context')
                                    ->label('Questions context')
                                    ->rows(6)
                                    ->columnSpanFull(),
                                Textarea::make('matching_critieria')
                                    ->label('Matching criteria')
                                    ->rows(6)
                                    ->columnSpanFull(),
                            ]),
                        Tab::make('Test')
                            ->icon('heroicon-o-beaker')
                            ->visibleOn([EditRecord::class])
                            ->schema([
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
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
