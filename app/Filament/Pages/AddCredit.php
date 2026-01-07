<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class AddCredit extends Page
{
    protected static ?string $slug = 'buy-credits';
    
    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.pages.add-credit';

    public function getTitle(): string
    {
        return 'Add Credits to Workspace';
    }

    public function getCreditPackages(): array
    {
        return config('credit.packages', []);
    }

    protected function getViewData(): array
    {
        return [
            'packages' => $this->getCreditPackages(),
            'user' => Auth::user(),
        ];
    }
}

