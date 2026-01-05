<?php

namespace App\Filament\Resources\Leads\Schemas;

use App\Models\LeadKanban;
use App\Models\LeadSource;
use App\Models\Team;
use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class LeadForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Title')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                
                Textarea::make('description')
                    ->label('Description')
                    ->rows(3)
                    ->columnSpanFull(),
                
                Select::make('team_id')
                    ->label('Team')
                    ->options(function () {
                        $workspaceId = session('workspace_id');
                        if (!$workspaceId) {
                            return [];
                        }
                        return Team::where('workspace_id', $workspaceId)
                            ->pluck('name', 'id');
                    })
                    ->searchable()
                    ->required(),
                
                Select::make('kanban_id')
                    ->label('Status')
                    ->options(function () {
                        $teamId = session('team_id');
                        if (!$teamId) {
                            return [];
                        }
                        return LeadKanban::where('team_id', $teamId)
                            ->where('is_active', true)
                            ->orderBy('sort_order')
                            ->pluck('name', 'id');
                    })
                    ->searchable(),
                
                Select::make('source_id')
                    ->label('Source')
                    ->options(function () {
                        $teamId = session('team_id');
                        if (!$teamId) {
                            return [];
                        }
                        return LeadSource::where('team_id', $teamId)
                            ->where('is_active', true)
                            ->orderBy('sort_order')
                            ->pluck('name', 'id');
                    })
                    ->searchable(),
                
                Select::make('assigned_member_id')
                    ->label('Assigned To')
                    ->options(function () {
                        $workspaceId = session('workspace_id');
                        if (!$workspaceId) {
                            return [];
                        }
                        return User::where('workspace_id', $workspaceId)
                            ->pluck('name', 'id');
                    })
                    ->searchable(),
                
                TextInput::make('expected_value')
                    ->label('Expected Value')
                    ->numeric()
                    ->prefix('$')
                    ->step(0.01),
                
                TextInput::make('actual_value')
                    ->label('Actual Value')
                    ->numeric()
                    ->prefix('$')
                    ->step(0.01),
                
                TextInput::make('cost')
                    ->label('Cost')
                    ->numeric()
                    ->prefix('$')
                    ->step(0.01),
                
                DateTimePicker::make('next_follow_up')
                    ->label('Next Follow Up')
                    ->timezone('UTC'),
                
                DateTimePicker::make('conversion_date')
                    ->label('Conversion Date')
                    ->timezone('UTC'),
                
                TextInput::make('url')
                    ->label('URL')
                    ->url()
                    ->maxLength(255)
                    ->columnSpanFull(),
                
                Textarea::make('notes')
                    ->label('Notes')
                    ->rows(4)
                    ->columnSpanFull(),
                
                Toggle::make('is_archived')
                    ->label('Archived')
                    ->default(false),
            ]);
    }
}
