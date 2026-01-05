<?php

namespace App\Filament\Resources\LeadGoals\Schemas;

use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class LeadGoalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('goal_type')
                    ->label('Goal Type')
                    ->options([
                        'revenue' => 'Revenue',
                        'leads' => 'Leads',
                        'conversions' => 'Conversions',
                        'calls' => 'Calls',
                        'meetings' => 'Meetings',
                    ])
                    ->required()
                    ->searchable(),
                
                Select::make('period')
                    ->label('Period')
                    ->options([
                        'daily' => 'Daily',
                        'weekly' => 'Weekly',
                        'monthly' => 'Monthly',
                        'quarterly' => 'Quarterly',
                        'yearly' => 'Yearly',
                    ])
                    ->required()
                    ->searchable(),
                
                TextInput::make('target_value')
                    ->label('Target Value')
                    ->numeric()
                    ->prefix('$')
                    ->step(0.01)
                    ->required(),
                
                TextInput::make('current_value')
                    ->label('Current Value')
                    ->numeric()
                    ->prefix('$')
                    ->step(0.01)
                    ->default(0)
                    ->required(),
                
                Select::make('member_id')
                    ->label('Member')
                    ->options(function () {
                        $workspaceId = session('workspace_id');
                        if (!$workspaceId) {
                            return [];
                        }
                        return User::where('workspace_id', $workspaceId)
                            ->pluck('name', 'id');
                    })
                    ->searchable()
                    ->required(),
                
                Textarea::make('description')
                    ->label('Description')
                    ->rows(3)
                    ->columnSpanFull(),
                
                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true)
                    ->required(),
            ]);
    }
}
