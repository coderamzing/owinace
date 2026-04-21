<?php

namespace App\Filament\Resources\UpworkCampaigns;

use App\Filament\Resources\UpworkCampaigns\Pages\CreateUpworkCampaign;
use App\Filament\Resources\UpworkCampaigns\Pages\EditUpworkCampaign;
use App\Filament\Resources\UpworkCampaigns\Pages\ListUpworkCampaigns;
use App\Filament\Resources\UpworkCampaigns\Pages\ViewUpworkCampaign;
use App\Filament\Resources\UpworkCampaigns\Schemas\UpworkCampaignForm;
use App\Filament\Resources\UpworkCampaigns\Schemas\UpworkCampaignInfolist;
use App\Filament\Resources\UpworkCampaigns\Tables\UpworkCampaignsTable;
use App\Models\UpworkCampaign;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UpworkCampaignResource extends Resource
{
    protected static ?string $model = UpworkCampaign::class;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $navigationLabel = 'Campaigns';

    protected static ?string $modelLabel = 'Campaign';

    protected static ?string $pluralModelLabel = 'Campaigns';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    protected static ?int $navigationSort = 9;

    public static function form(Schema $schema): Schema
    {
        return UpworkCampaignForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return UpworkCampaignInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UpworkCampaignsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['member.user', 'source', 'kanban']);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUpworkCampaigns::route('/'),
            'create' => CreateUpworkCampaign::route('/create'),
            'view' => ViewUpworkCampaign::route('/{record}'),
            'edit' => EditUpworkCampaign::route('/{record}/edit'),
        ];
    }
}
