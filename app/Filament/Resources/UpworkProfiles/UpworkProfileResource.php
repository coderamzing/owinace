<?php

namespace App\Filament\Resources\UpworkProfiles;

use App\Filament\Resources\UpworkProfiles\Pages\ListUpworkProfiles;
use App\Filament\Resources\UpworkProfiles\Schemas\UpworkProfileForm;
use App\Filament\Resources\UpworkProfiles\Tables\UpworkProfilesTable;
use App\Models\UpworkProfile;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UpworkProfileResource extends Resource
{
    protected static ?string $model = UpworkProfile::class;

    protected static ?string $navigationLabel = 'Profiles';

    protected static ?string $modelLabel = 'Profile';

    protected static ?string $pluralModelLabel = 'Profiles';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserCircle;

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Schema $schema): Schema
    {
        return UpworkProfileForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UpworkProfilesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUpworkProfiles::route('/'),
        ];
    }
}
