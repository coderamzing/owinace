<?php

namespace App\Filament\Resources\UpworkCampaigns\Pages;

use App\Filament\Resources\BaseListRecords;
use App\Filament\Resources\UpworkCampaigns\UpworkCampaignResource;
use App\Filament\Resources\UpworkProfiles\UpworkProfileResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;

class ListUpworkCampaigns extends BaseListRecords
{
    protected static string $resource = UpworkCampaignResource::class;

    protected string $searchPlaceholder = 'Search campaigns by title...';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('profiles')
                ->label('Profiles')
                ->icon('heroicon-o-user-circle')
                ->color('gray')
                ->size('lg')
                ->url(fn () => UpworkProfileResource::getUrl('index')),

            CreateAction::make(),
        ];
    }
}
