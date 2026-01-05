<?php

namespace App\Filament\Resources\LeadTags\Pages;

use App\Filament\Resources\LeadTags\LeadTagResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLeadTag extends EditRecord
{
    protected static string $resource = LeadTagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
