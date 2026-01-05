<?php

namespace App\Filament\Resources\Teams\Pages;

use App\Filament\Resources\Teams\TeamResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateTeam extends CreateRecord
{
    protected static string $resource = TeamResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by_id'] = Auth::id();
        $data['workspace_id'] = session('workspace_id');
        
        if (!$data['workspace_id']) {
            // Fallback to user's workspace_id if session is not set
            $data['workspace_id'] = Auth::user()?->workspace_id;
        }
        
        return $data;
    }
}
