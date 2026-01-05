<?php

namespace App\Filament\Resources\LeadSources\Pages;

use App\Filament\Resources\LeadSources\LeadSourceResource;
use App\Models\LeadSource;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditLeadSource extends EditRecord
{
    protected static string $resource = LeadSourceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->action(function (LeadSource $record) {
                    if ($record->leads()->exists()) {
                        Notification::make()
                            ->title('Cannot delete lead source')
                            ->body('This source is assigned to one or more leads.')
                            ->danger()
                            ->send();

                        return;
                    }

                    $record->delete();

                    Notification::make()
                        ->title('Lead source deleted')
                        ->success()
                        ->send();

                    return redirect(static::getResource()::getUrl('index'));
                }),
        ];
    }
}
