<?php

namespace App\Filament\Resources\Portfolios\Schemas;

use App\Models\Portfolio;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
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
                TextInput::make('url')
                    ->label('URL')
                    ->required()
                    ->url()
                    ->maxLength(2000)
                    ->placeholder('https://example.com/portfolio')
                    ->helperText('Must return HTTP 200 when checked.')
                    ->columnSpanFull(),
                Textarea::make('description')
                    ->required()
                    ->rows(12)
                    ->helperText('Maximum '.number_format(Portfolio::DESCRIPTION_MAX_WORDS).' words.')
                    ->rules([
                        fn (): \Closure => function (string $attribute, mixed $value, \Closure $fail): void {
                            if (Portfolio::exceedsDescriptionWordLimit((string) $value)) {
                                $fail('The description must not exceed '.number_format(Portfolio::DESCRIPTION_MAX_WORDS).' words.');
                            }
                        },
                    ])
                    ->columnSpanFull(),
                TagsInput::make('keywords')
                    ->required()
                    ->rules(['array', 'max:15'])
                    ->reorderable(false)
                    ->placeholder('Add up to 15 keywords')
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->required()
                    ->default(true),
            ]);
    }
}
