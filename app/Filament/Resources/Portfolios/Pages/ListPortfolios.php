<?php

namespace App\Filament\Resources\Portfolios\Pages;

use App\Filament\Resources\Portfolios\PortfolioResource;
use App\Filament\Resources\BaseListRecords;
use Filament\Actions\CreateAction;

class ListPortfolios extends BaseListRecords
{
    protected static string $resource = PortfolioResource::class;
    
    protected string $searchPlaceholder = 'Search portfolios by title, keywords...';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalHeading('Create Portfolio')
                ->modalSubmitActionLabel('Create')
                ->slideOver()
                ->mutateFormDataUsing(function (array $data): array {
                    $data['created_by_id'] = auth()->id();
                    
                    // Set team_id from session if available
                    $teamId = session('team_id');
                    if ($teamId && !isset($data['team_id'])) {
                        $data['team_id'] = $teamId;
                    }
                    
                    return $data;
                }),
        ];
    }
}

