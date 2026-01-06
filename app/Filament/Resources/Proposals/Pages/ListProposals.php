<?php

namespace App\Filament\Resources\Proposals\Pages;

use App\Filament\Resources\Proposals\ProposalResource;
use App\Filament\Resources\BaseListRecords;
use Filament\Actions\CreateAction;

class ListProposals extends BaseListRecords
{
    protected static string $resource = ProposalResource::class;
    
    protected string $searchPlaceholder = 'Search proposals by title, keywords...';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalHeading('Create Proposal')
                ->modalSubmitActionLabel('Create')
                ->slideOver()
                ->mutateFormDataUsing(function (array $data): array {
                    // Set user_id to authenticated user
                    $data['user_id'] = auth()->id();
                    
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

