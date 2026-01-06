<?php

namespace App\Filament\Resources\LeadKanbans\Pages;

use App\Filament\Resources\LeadKanbans\LeadKanbanResource;
use App\Filament\Resources\BaseListRecords;
use Filament\Actions\CreateAction;

class ListLeadKanbans extends BaseListRecords
{
    protected static string $resource = LeadKanbanResource::class;
    
    protected string $searchPlaceholder = 'Search kanban boards by name...';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalHeading('Create Lead Kanban')
                ->modalSubmitActionLabel('Create')
                ->slideOver()
                ->mutateFormDataUsing(function (array $data): array {
                    // Set team_id from session if available
                    $teamId = session('team_id');
                    if ($teamId && !isset($data['team_id'])) {
                        $data['team_id'] = $teamId;
                    }
                    
                    // Auto-generate code from name if code is empty
                    if (empty($data['code']) && !empty($data['name'])) {
                        $data['code'] = $this->generateCodeFromName($data['name']);
                    }
                    
                    return $data;
                }),
        ];
    }

    /**
     * Generate a code from the name (slug format)
     */
    private function generateCodeFromName(string $name): string
    {
        // Convert to lowercase
        $code = strtolower($name);
        
        // Replace spaces with underscores
        $code = str_replace(' ', '_', $code);
        
        // Remove special characters except underscores and dashes
        $code = preg_replace('/[^a-z0-9_\-]/', '', $code);
        
        // Replace multiple underscores/dashes with single ones
        $code = preg_replace('/[_\-]+/', '_', $code);
        
        // Trim underscores from start and end
        $code = trim($code, '_-');
        
        return $code;
    }
}
