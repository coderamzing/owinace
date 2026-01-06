<?php

namespace App\Filament\Resources\LeadSources\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class LeadSourceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Name')
                    ->required()
                    ->maxLength(100),
                Textarea::make('description')
                    ->label('Description')
                    ->maxLength(2000)
                    ->columnSpanFull(),
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
            ]);
    }
}
