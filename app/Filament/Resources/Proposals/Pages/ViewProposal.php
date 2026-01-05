<?php

namespace App\Filament\Resources\Proposals\Pages;

use App\Filament\Resources\Proposals\ProposalResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewProposal extends ViewRecord
{
    protected static string $resource = ProposalResource::class;
    protected string $view = 'filament.resources.proposals.view-proposal';

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->modalHeading('Edit Proposal')
                ->modalSubmitActionLabel('Save'),
        ];
    }
}


