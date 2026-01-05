<?php

namespace App\Filament\Resources\LeadKanbans;

use App\Filament\Resources\LeadKanbans\Pages\ListLeadKanbans;
use App\Filament\Resources\LeadKanbans\Schemas\LeadKanbanForm;
use App\Filament\Resources\LeadKanbans\Tables\LeadKanbansTable;
use App\Models\LeadKanban;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Traits\HasPermission;

class LeadKanbanResource extends Resource
{
    use HasPermission;

    protected static ?string $permission = 'settings.manage';

    protected static ?string $model = LeadKanban::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return LeadKanbanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LeadKanbansTable::configure($table);
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
            'index' => ListLeadKanbans::route('/'),
        ];
    }
}
