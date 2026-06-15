<?php

namespace App\Filament\Support;

use Illuminate\Support\HtmlString;

class ExpandableText
{
    public static function render(?string $text): HtmlString
    {
        $text = trim((string) $text);

        if ($text === '') {
            return new HtmlString('<span class="text-gray-400 dark:text-gray-500">—</span>');
        }

        return new HtmlString(
            view('filament.components.expandable-text', [
                'full' => $text,
            ])->render()
        );
    }
}
