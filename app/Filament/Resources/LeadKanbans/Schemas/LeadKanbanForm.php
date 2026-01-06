<?php

namespace App\Filament\Resources\LeadKanbans\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
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
                    ->maxLength(100),
                TextInput::make('code')
                    ->label('Code')
                    ->maxLength(100),
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
                    ->default(false),
            ]);
    }
}
