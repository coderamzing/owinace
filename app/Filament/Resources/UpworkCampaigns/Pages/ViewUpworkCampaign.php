<?php

namespace App\Filament\Resources\UpworkCampaigns\Pages;

use App\Filament\Resources\UpworkCampaigns\UpworkCampaignResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewUpworkCampaign extends ViewRecord
{
    protected static string $resource = UpworkCampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
