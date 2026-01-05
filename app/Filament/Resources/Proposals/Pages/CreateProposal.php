<?php

namespace App\Filament\Resources\Proposals\Pages;

use App\Filament\Resources\Proposals\ProposalResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProposal extends CreateRecord
{
    protected static string $resource = ProposalResource::class;

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

