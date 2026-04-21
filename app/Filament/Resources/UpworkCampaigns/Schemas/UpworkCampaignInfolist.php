<?php

namespace App\Filament\Resources\UpworkCampaigns\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
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
                                TextEntry::make('max_connect_per_bid')
                                    ->label('Max connect per bid'),
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
                                TextEntry::make('rule_client_avg_spent')
                                    ->label('Client avg. spent'),
                                TextEntry::make('rule_max_interviews')
                                    ->label('Max interviews'),
                                TextEntry::make('rule_job_posted_ago')
                                    ->label('Job posted ago (In Minutes)'),
                                TextEntry::make('rule_max_proposal')
                                    ->label('Max proposal Per Day'),
                                TextEntry::make('rule_clock_in')
                                    ->label('Clock in')
                                    ->placeholder('—'),
                                TextEntry::make('rule_clock_out')
                                    ->label('Clock out')
                                    ->placeholder('—'),
                            ])
                            ->columns(2),
                        Tab::make('Prompts')
                            ->icon('heroicon-o-chat-bubble-left-right')
                            ->schema([
                                TextEntry::make('portfolios')
                                    ->columnSpanFull(),
                                TextEntry::make('ai_prompt')
                                    ->label('AI prompt')
                                    ->columnSpanFull(),
                                TextEntry::make('questions_context')
                                    ->label('Questions context')
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
