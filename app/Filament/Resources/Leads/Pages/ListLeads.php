<?php

namespace App\Filament\Resources\Leads\Pages;

use App\Filament\Resources\Leads\LeadResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListLeads extends ListRecords
{
    protected static string $resource = LeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->modalHeading('Create Lead')
                ->modalSubmitActionLabel('Create')
                ->slideOver()
                ->mutateFormDataUsing(function (array $data): array {
                    $data['created_by_id'] = auth()->id();
                    
                    // Auto-set team_id from session
                    $teamId = session('team_id');
                    if ($teamId) {
                        $data['team_id'] = $teamId;
                    }
                    
                    return $data;
                }),
        ];
    }

    protected function getTableQuery(): Builder
    {
        $teamId = session('team_id');
        
        return parent::getTableQuery()
            ->when($teamId, fn ($query) => $query->where('team_id', $teamId));
    }
}
