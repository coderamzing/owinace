<?php

namespace App\Filament\Resources\LeadCosts\Schemas;

use App\Models\LeadSource;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class LeadCostForm
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
                
                TextInput::make('monthly_cost')
                    ->label('Monthly Cost')
                    ->numeric()
                    ->prefix('$')
                    ->step(0.01)
                    ->required(),
                
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
                    ->searchable()
                    ->nullable(),
                
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
                    ->nullable(),
                
                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true)
                    ->required(),
            ]);
    }
}
