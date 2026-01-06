<?php

namespace App\Filament\Resources;

use Filament\Resources\Pages\ListRecords;

/**
 * Base class for List pages with custom table layout
 * 
 * To use this custom layout in any resource:
 * 1. Make your ListRecords page extend BaseListRecords instead of ListRecords
 * 2. Optionally override $searchPlaceholder to customize the search text
 * 
 * Example:
 * class ListContacts extends BaseListRecords
 * {
 *     protected static string $resource = ContactResource::class;
 *     protected string $searchPlaceholder = 'Search contacts...';
 * }
 */
abstract class BaseListRecords extends ListRecords
{
    /**
     * Use the custom table layout view
     */
    protected string $view = 'filament.tables.search-table-layout';
    
    /**
     * Search placeholder text - override in child classes to customize
     */
    protected string $searchPlaceholder = 'Search...';
    
    /**
     * Pass the search placeholder to the view
     */
    protected function getViewData(): array
    {
        return array_merge(parent::getViewData(), [
            'searchPlaceholder' => $this->searchPlaceholder,
        ]);
    }
}

