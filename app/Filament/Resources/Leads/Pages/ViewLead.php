<?php

namespace App\Filament\Resources\Leads\Pages;

use App\Filament\Resources\Leads\LeadResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use App\Traits\HasPermission;

class ViewLead extends ViewRecord
{
    use HasPermission;
    protected static ?string $permission = 'leads.view';
    
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
            DeleteAction::make()
                ->requiresConfirmation()
                ->modalHeading('Delete Lead')
                ->modalDescription('Are you sure you want to delete this lead? This action cannot be undone.')
                ->modalSubmitActionLabel('Delete')
                ->visible(fn ($record) => self::hasPermissionTo('leads.delete'))
                ->successRedirectUrl(route('filament.admin.resources.leads.index')),
        ];
    }
}
