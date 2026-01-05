<?php

namespace App\Filament\Resources\LeadSources\Pages;

use App\Filament\Resources\LeadSources\LeadSourceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLeadSource extends CreateRecord
{
    protected static string $resource = LeadSourceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set team_id from session if available
        $teamId = session('team_id');
        if ($teamId && !isset($data['team_id'])) {
            $data['team_id'] = $teamId;
        }
        
        return $data;
    }
}
