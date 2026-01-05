<?php

namespace App\Filament\Resources\Leads\Pages;

use App\Filament\Resources\Leads\LeadResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewLead extends ViewRecord
{
    protected static string $resource = LeadResource::class;

    protected string $view = 'filament.resources.leads.view-lead';

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->modalHeading('Edit Lead')
                ->modalSubmitActionLabel('Save')
                ->slideOver()
                ->mutateFormDataUsing(function (array $data): array {
                    // Auto-set team_id from session
                    $teamId = session('team_id');
                    if ($teamId) {
                        $data['team_id'] = $teamId;
                    }
                    
                    return $data;
                }),
        ];
    }
}
