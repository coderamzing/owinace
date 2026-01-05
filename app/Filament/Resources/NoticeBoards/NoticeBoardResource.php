<?php

namespace App\Filament\Resources\NoticeBoards;

use App\Filament\Resources\NoticeBoards\Pages\ListNoticeBoards;
use App\Filament\Resources\NoticeBoards\Schemas\NoticeBoardForm;
use App\Filament\Resources\NoticeBoards\Tables\NoticeBoardsTable;
use App\Models\NoticeBoard;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class NoticeBoardResource extends Resource
{
    protected static ?string $model = NoticeBoard::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    public static function form(Schema $schema): Schema
    {
        return NoticeBoardForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NoticeBoardsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNoticeBoards::route('/'),
        ];
    }
}

