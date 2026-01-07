<?php

namespace App\Filament\Resources\LeadKanbans\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Schemas\Schema;

class LeadKanbanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Name')
                    ->required()
                    ->maxLength(100)
                    ->disabled(fn ($record) => $record?->is_system ?? false)
                    ->dehydrated(fn ($record) => !($record?->is_system ?? false)),
                TextInput::make('code')
                    ->label('Code')
                    ->maxLength(100)
                    ->disabled(fn ($record) => $record?->is_system ?? false)
                    ->dehydrated(fn ($record) => !($record?->is_system ?? false)),
                ColorPicker::make('color')
                    ->label('Color')
                    ->required(),
                TextInput::make('sort_order')
                    ->label('Sort Order')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
                Toggle::make('is_active')
                    ->label('Active')
                    ->required()
                    ->default(true),
                Toggle::make('is_system')
                    ->label('System')
                    ->required()
                    ->default(false)
                    ->disabled(fn ($record) => $record?->is_system ?? false)
                    ->dehydrated(fn ($record) => !($record?->is_system ?? false))
                    ->helperText('System kanban stages cannot be deleted or have their name/code changed.'),
            ]);
    }
}
