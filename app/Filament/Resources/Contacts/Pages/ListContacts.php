<?php

namespace App\Filament\Resources\Contacts\Pages;

use App\Filament\Resources\Contacts\ContactResource;
use App\Filament\Resources\BaseListRecords;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use App\Traits\HasPermission;

class ListContacts extends BaseListRecords
{
    use HasPermission;
    
    protected static ?string $permission = 'contact.list';
    
    protected static string $resource = ContactResource::class;
    
    // Custom search placeholder for this table
    protected string $searchPlaceholder = 'Search by name, email, company...';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('import')
                ->label('Import CSV')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->url(fn () => ContactResource::getUrl('import'))
                ->visible(fn () => self::hasPermissionTo('contact.import')),
            
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
