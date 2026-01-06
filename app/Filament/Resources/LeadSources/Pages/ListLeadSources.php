<?php

namespace App\Filament\Resources\LeadSources\Pages;

use App\Filament\Resources\LeadSources\LeadSourceResource;
use App\Filament\Resources\BaseListRecords;
use Filament\Actions\CreateAction;

class ListLeadSources extends BaseListRecords
{
    protected static string $resource = LeadSourceResource::class;
    
    protected string $searchPlaceholder = 'Search sources by name...';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalHeading('Create Lead Source')
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
