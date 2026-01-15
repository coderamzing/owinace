<?php

namespace App\Filament\Resources\Portfolios\Schemas;

use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PortfolioForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                SpatieMediaLibraryFileUpload::make('avatar')
                    ->label('Avatar')
                    ->collection('avatar')
                    ->image()
                    ->avatar()
                    ->circleCropper()
                    ->maxSize(2048)
                    ->columnSpanFull(),
                TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Textarea::make('description')
                    ->required()
                    ->maxLength(500)
                    ->rule('max:500')
                    ->columnSpanFull(),
                TagsInput::make('keywords')
                    ->required()
                    ->rules(['array', 'max:10'])
                    ->reorderable(false)
                    ->placeholder('Add up to 10 keywords')
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->required()
                    ->default(true),
            ]);
    }
}

