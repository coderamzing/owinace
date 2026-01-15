<?php

namespace App\Filament\Resources\Contacts\Schemas;

use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ContactForm
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

                TextInput::make('first_name')
                    ->label('Name')
                    ->required()
                    ->maxLength(255),
                
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->maxLength(254),
                
                TextInput::make('phone_number')
                    ->label('Phone Number')
                    ->tel()
                    ->maxLength(20),
                
                TextInput::make('company')
                    ->label('Company')
                    ->maxLength(255),
                
                TextInput::make('job_title')
                    ->label('Job Title')
                    ->maxLength(255),
                
                TextInput::make('website')
                    ->label('Website')
                    ->url()
                    ->maxLength(200)
                    ->columnSpanFull(),
            ]);
    }
}
