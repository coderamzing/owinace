<?php

namespace App\Filament\Resources\LeadTags\Pages;

use App\Filament\Resources\LeadTags\LeadTagResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLeadTag extends CreateRecord
{
    protected static string $resource = LeadTagResource::class;

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
