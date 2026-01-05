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
                    ->required()
                    ->maxLength(100),
                TextInput::make('code')
                    ->maxLength(100),
                ColorPicker::make('color')
                    ->required(),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric(),
                Toggle::make('is_active')
                    ->required(),
                Toggle::make('is_system')
                    ->required(),
            ]);
    }
}
