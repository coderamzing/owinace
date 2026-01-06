<?php

namespace App\Filament\Resources\LeadTags\Pages;

use App\Filament\Resources\LeadTags\LeadTagResource;
use App\Filament\Resources\BaseListRecords;
use Filament\Actions\CreateAction;

class ListLeadTags extends BaseListRecords
{
    protected static string $resource = LeadTagResource::class;
    
    protected string $searchPlaceholder = 'Search tags by name...';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalHeading('Create Lead Tag')
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
