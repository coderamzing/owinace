<?php

namespace App\Filament\Resources\UpworkCampaigns\Pages;

use App\Filament\Resources\UpworkCampaigns\UpworkCampaignResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUpworkCampaign extends CreateRecord
{
    protected static string $resource = UpworkCampaignResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $teamId = session('team_id');
        if ($teamId && $teamId > 0) {
            $data['team_id'] = $teamId;
        }

        return $data;
    }
}
