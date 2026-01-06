<?php

namespace App\Filament\Resources\LeadCosts\Pages;

use App\Filament\Resources\LeadCosts\LeadCostResource;
use App\Filament\Resources\BaseListRecords;
use Filament\Actions\CreateAction;

class ListLeadCosts extends BaseListRecords
{
    protected static string $resource = LeadCostResource::class;
    
    protected string $searchPlaceholder = 'Search costs by name, amount...';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalHeading('Create Lead Cost')
                ->modalSubmitActionLabel('Create')
                ->slideOver()
                ->mutateFormDataUsing(function (array $data): array {
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
