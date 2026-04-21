<?php

namespace App\Filament\Resources\UpworkCampaigns\Pages;

use App\Filament\Resources\BaseListRecords;
use App\Filament\Resources\UpworkCampaigns\UpworkCampaignResource;
use Filament\Actions\CreateAction;

class ListUpworkCampaigns extends BaseListRecords
{
    protected static string $resource = UpworkCampaignResource::class;

    protected string $searchPlaceholder = 'Search campaigns by title...';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
