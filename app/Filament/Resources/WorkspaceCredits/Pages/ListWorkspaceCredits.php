<?php

namespace App\Filament\Resources\WorkspaceCredits\Pages;

use App\Filament\Resources\WorkspaceCredits\WorkspaceCreditResource;
use Filament\Resources\Pages\ListRecords;

class ListWorkspaceCredits extends ListRecords
{
    protected static string $resource = WorkspaceCreditResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No actions - read-only
        ];
    }

    public function getTitle(): string
    {
        return 'Credit History';
    }

    public function getHeading(): string
    {
        return 'Credit History';
    }
}

