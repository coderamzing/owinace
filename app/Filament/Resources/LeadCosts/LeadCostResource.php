<?php

namespace App\Filament\Resources\LeadCosts;

use App\Filament\Resources\LeadCosts\Pages\ListLeadCosts;
use App\Filament\Resources\LeadCosts\Schemas\LeadCostForm;
use App\Filament\Resources\LeadCosts\Tables\LeadCostsTable;
use App\Models\LeadCost;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LeadCostResource extends Resource
{
    protected static ?string $model = LeadCost::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return LeadCostForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LeadCostsTable::configure($table);
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
            'index' => ListLeadCosts::route('/'),
        ];
    }
}
