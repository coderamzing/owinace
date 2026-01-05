<?php

namespace App\Filament\Resources\NoticeBoards\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class NoticeBoardForm
{
    public static function configure(Schema $schema): Schema
    {
        $workspaceId = session('workspace_id') ?? auth()->user()?->workspace_id;

        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Title')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Textarea::make('description')
                    ->label('Description')
                    ->rows(4)
                    ->columnSpanFull(),
                Select::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                    ])
                    ->required()
                    ->default('draft'),
                DateTimePicker::make('published_at')
                    ->label('Published At')
                    ->native(false),
                DateTimePicker::make('expire_at')
                    ->label('Expire At')
                    ->native(false),
                Toggle::make('notify')
                    ->label('Send Notifications')
                    ->default(false),
            ]);
    }
}

