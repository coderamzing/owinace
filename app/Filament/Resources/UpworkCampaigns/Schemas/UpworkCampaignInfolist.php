<?php

namespace App\Filament\Resources\UpworkCampaigns\Schemas;

use App\Filament\Resources\UpworkCampaigns\Pages\ViewUpworkCampaign;
use App\Filament\Resources\UpworkCampaigns\RelationManagers\LinkedPortfoliosRelationManager;
use App\Filament\Resources\UpworkCampaigns\RelationManagers\UpworkCampaignJobStatsRelationManager;
use App\Filament\Support\ExpandableText;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class UpworkCampaignInfolist
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
                                TextEntry::make('title'),
                                IconEntry::make('is_active')
                                    ->label('Active')
                                    ->boolean(),

                                TextEntry::make('search_url')
                                    ->label('Search URL')
                                    ->url(fn ($state) => filled($state) ? $state : null)
                                    ->columnSpanFull(),
                                TextEntry::make('timezone')
                                    ->placeholder('—'),
                                TextEntry::make('max_daily_bid')
                                    ->label('Max daily bid'),
                                IconEntry::make('auto_bidding')
                                    ->label('Auto bidding')
                                    ->boolean(),
                                TextEntry::make('profile.title')
                                    ->label('Upwork profile')
                                    ->placeholder('—'),
                                TextEntry::make('profile.code')
                                    ->label('Profile bot code')
                                    ->placeholder('—')
                                    ->copyable()
                                    ->fontFamily('mono'),
                                TextEntry::make('member_id')
                                    ->label('Team member')
                                    ->formatStateUsing(function ($state, $record): string {
                                        $member = $record->member;
                                        if (! $member) {
                                            return '—';
                                        }

                                        return $member->user?->name ?? ('Member #'.$member->id);
                                    }),
                                TextEntry::make('source.name')
                                    ->label('Lead source')
                                    ->placeholder('—'),
                                TextEntry::make('kanban.name')
                                    ->label('Kanban')
                                    ->placeholder('—'),
                                TextEntry::make('created_at')
                                    ->dateTime(),
                                TextEntry::make('updated_at')
                                    ->dateTime(),
                            ])
                            ->columns(2),
                        Tab::make('Rules')
                            ->icon('heroicon-o-funnel')
                            ->schema([
                                TextEntry::make('max_connect_per_bid')
                                    ->label('Max connect per bid'),
                                TextEntry::make('rule_client_avg_spent')
                                    ->label('Client avg. spent'),
                                TextEntry::make('rule_max_interviews')
                                    ->label('Max interviews'),
                                TextEntry::make('rule_job_posted_ago')
                                    ->label('Job posted ago (In Minutes)'),
                                TextEntry::make('rule_max_proposal')
                                    ->label('Max proposal'),
                                TextEntry::make('rule_min_client_rating')
                                    ->label('Min client rating')
                                    ->placeholder('—'),
                                TextEntry::make('slots')
                                    ->label('Bidding time slots (UTC)')
                                    ->formatStateUsing(function ($state, $record): string {
                                        $slots = $record->slots;
                                        if ($slots->isEmpty()) {
                                            return '—';
                                        }

                                        return $slots
                                            ->map(fn ($slot): string => substr((string) $slot->clock_in, 0, 5)
                                                .' – '
                                                .substr((string) $slot->clock_out, 0, 5))
                                            ->implode(', ');
                                    })
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),
                        Tab::make('Portfolios')
                            ->icon('heroicon-o-briefcase')
                            ->schema([
                                Livewire::make(LinkedPortfoliosRelationManager::class)
                                    ->key('campaign-linked-portfolios-view')
                                    ->data(fn (ViewUpworkCampaign $livewire): array => [
                                        'ownerRecord' => $livewire->getRecord(),
                                        'pageClass' => ViewUpworkCampaign::class,
                                    ])
                                    ->columnSpanFull(),
                            ]),
                        Tab::make('Job stats')
                            ->icon('heroicon-o-chart-bar')
                            ->schema([
                                Livewire::make(UpworkCampaignJobStatsRelationManager::class)
                                    ->key('campaign-job-stats')
                                    ->data(fn (ViewUpworkCampaign $livewire): array => [
                                        'ownerRecord' => $livewire->getRecord(),
                                        'pageClass' => ViewUpworkCampaign::class,
                                    ])
                                    ->columnSpanFull(),
                            ]),
                        Tab::make('Prompts')
                            ->icon('heroicon-o-chat-bubble-left-right')
                            ->schema([
                                TextEntry::make('ai_cover_letter')
                                    ->label('AI cover letter')
                                    ->formatStateUsing(fn ($state): string => $state ? 'Yes' : 'No'),
                                TextEntry::make('ai_prompt')
                                    ->label('AI prompt')
                                    ->formatStateUsing(fn (?string $state) => ExpandableText::render($state))
                                    ->html()
                                    ->columnSpanFull(),
                                TextEntry::make('experience')
                                    ->label('Experience')
                                    ->formatStateUsing(fn (?string $state) => ExpandableText::render($state))
                                    ->html()
                                    ->columnSpanFull(),
                                TextEntry::make('questions_context')
                                    ->label('Questions context')
                                    ->formatStateUsing(fn (?string $state) => ExpandableText::render($state))
                                    ->html()
                                    ->columnSpanFull(),
                                TextEntry::make('matching_critieria')
                                    ->label('Matching criteria')
                                    ->formatStateUsing(fn (?string $state) => ExpandableText::render($state))
                                    ->html()
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
