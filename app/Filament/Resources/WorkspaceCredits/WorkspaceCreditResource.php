<?php

namespace App\Filament\Resources\WorkspaceCredits;

use App\Filament\Resources\WorkspaceCredits\Pages\ListWorkspaceCredits;
use App\Filament\Resources\WorkspaceCredits\Tables\WorkspaceCreditsTable;
use App\Models\WorkspaceCredit;
use App\Traits\HasPermission;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WorkspaceCreditResource extends Resource
{
    use HasPermission;

    protected static ?string $permission = 'workspace.credit';

    protected static ?string $model = WorkspaceCredit::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static ?string $navigationLabel = 'Credit History';

    protected static ?string $pluralModelLabel = 'Credit History';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return WorkspaceCreditsTable::configure($table);
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
            'index' => ListWorkspaceCredits::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}

