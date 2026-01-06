<?php

namespace App\Filament\Resources\WorkspaceCredits\Pages;

use App\Filament\Resources\WorkspaceCredits\WorkspaceCreditResource;
use App\Filament\Resources\BaseListRecords;

class ListWorkspaceCredits extends BaseListRecords
{
    protected static string $resource = WorkspaceCreditResource::class;
    
    protected string $searchPlaceholder = 'Search credit history...';

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

