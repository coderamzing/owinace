<?php

namespace App\Filament\Resources\LeadKanbans\Pages;

use App\Filament\Resources\LeadKanbans\LeadKanbanResource;
use App\Models\LeadKanban;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditLeadKanban extends EditRecord
{
    protected static string $resource = LeadKanbanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->action(function (LeadKanban $record) {
                    if ($record->is_system) {
                        Notification::make()
                            ->title('Cannot delete system stage')
                            ->body('System kanban stages cannot be removed.')
                            ->danger()
                            ->send();

                        return;
                    }

                    if ($record->leads()->exists()) {
                        Notification::make()
                            ->title('Cannot delete kanban stage')
                            ->body('This stage is assigned to one or more leads.')
                            ->danger()
                            ->send();

                        return;
                    }

                    $record->delete();

                    Notification::make()
                        ->title('Kanban stage deleted')
                        ->success()
                        ->send();

                    return redirect(static::getResource()::getUrl('index'));
                }),
        ];
    }
}
