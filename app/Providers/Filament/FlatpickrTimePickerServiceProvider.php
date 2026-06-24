<?php

namespace App\Providers\Filament;

use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\ServiceProvider;

class FlatpickrTimePickerServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        FilamentAsset::register([
            AlpineComponent::make(
                'flatpickr-time-picker',
                resource_path('dist/filament/flatpickr-time-picker.js'),
            ),
            Css::make(
                'flatpickr',
                resource_path('dist/filament/flatpickr.min.css'),
            ),
            Css::make(
                'flatpickr-time-picker',
                resource_path('css/filament/flatpickr-time-picker.css'),
            ),
        ], package: 'app');
    }
}
