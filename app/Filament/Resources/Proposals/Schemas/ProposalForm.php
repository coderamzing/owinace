<?php

namespace App\Filament\Resources\Proposals\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProposalForm
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
                    ->required()
                    ->minLength(100)
                    ->maxLength(2000)
                    ->columnSpanFull(),
                Textarea::make('job_description')
                    ->label('Job Description')
                    ->nullable()
                    ->maxLength(2000),
                Textarea::make('keywords')
                    ->label('Keywords')
                    ->required()
                    ->maxLength(2000),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->required()
                    ->default(true),
            ]);
    }
}

