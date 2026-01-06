<?php

namespace App\Filament\Resources\LeadGoals\Pages;

use App\Filament\Resources\LeadGoals\LeadGoalResource;
use App\Filament\Resources\BaseListRecords;
use Filament\Actions\CreateAction;

class ListLeadGoals extends BaseListRecords
{
    protected static string $resource = LeadGoalResource::class;
    
    protected string $searchPlaceholder = 'Search goals by name, target amount...';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalHeading('Create Lead Goal')
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
