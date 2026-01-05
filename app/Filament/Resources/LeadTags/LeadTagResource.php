<?php

namespace App\Filament\Resources\LeadTags;

use App\Filament\Resources\LeadTags\Pages\ListLeadTags;
use App\Filament\Resources\LeadTags\Schemas\LeadTagForm;
use App\Filament\Resources\LeadTags\Tables\LeadTagsTable;
use App\Models\LeadTag;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Traits\HasPermission;

class LeadTagResource extends Resource
{
    use HasPermission;

    protected static ?string $permission = 'settings.manage';

    protected static ?string $model = LeadTag::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return LeadTagForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LeadTagsTable::configure($table);
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
            'index' => ListLeadTags::route('/'),
        ];
    }
}
