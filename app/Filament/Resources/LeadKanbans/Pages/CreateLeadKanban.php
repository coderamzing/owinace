<?php

namespace App\Filament\Resources\LeadKanbans\Pages;

use App\Filament\Resources\LeadKanbans\LeadKanbanResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLeadKanban extends CreateRecord
{
    protected static string $resource = LeadKanbanResource::class;

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
