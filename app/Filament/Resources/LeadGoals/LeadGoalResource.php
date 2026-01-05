<?php

namespace App\Filament\Resources\LeadGoals;

use App\Filament\Resources\LeadGoals\Pages\ListLeadGoals;
use App\Filament\Resources\LeadGoals\Schemas\LeadGoalForm;
use App\Filament\Resources\LeadGoals\Tables\LeadGoalsTable;
use App\Models\LeadGoal;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LeadGoalResource extends Resource
{
    protected static ?string $model = LeadGoal::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFlag;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return LeadGoalForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LeadGoalsTable::configure($table);
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
            'index' => ListLeadGoals::route('/'),
        ];
    }
}
