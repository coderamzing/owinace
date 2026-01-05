<?php

namespace App\Filament\Resources\Contacts\Pages;

use App\Filament\Resources\Contacts\ContactResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use App\Traits\HasPermission;

class ListContacts extends ListRecords
{
    use HasPermission;
    
    protected static ?string $permission = 'contact.list';
    
    protected static string $resource = ContactResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalHeading('Create Contact')
                ->modalSubmitActionLabel('Create')
                ->slideOver()
                ->mutateFormDataUsing(function (array $data): array {
                    $teamId = session('team_id');

                    if ($teamId) {
                        $data['team_id'] = $teamId;
                    }

                    return $data;
                }),
        ];
    }
}
