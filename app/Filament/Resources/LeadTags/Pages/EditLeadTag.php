<?php

namespace App\Filament\Resources\LeadTags\Pages;

use App\Filament\Resources\LeadTags\LeadTagResource;
use App\Models\LeadTag;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditLeadTag extends EditRecord
{
    protected static string $resource = LeadTagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->action(function (LeadTag $record) {
                    if ($record->leads()->exists()) {
                        Notification::make()
                            ->title('Cannot delete lead tag')
                            ->body('This tag is assigned to one or more leads.')
                            ->danger()
                            ->send();

                        return;
                    }

                    $record->delete();

                    Notification::make()
                        ->title('Lead tag deleted')
                        ->success()
                        ->send();

                    return redirect(static::getResource()::getUrl('index'));
                }),
        ];
    }
}
