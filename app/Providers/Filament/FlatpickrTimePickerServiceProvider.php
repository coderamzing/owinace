<?php

namespace App\Providers\Filament;

use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;

class FlatpickrTimePickerServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishAssetsToPublic();

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

    private function publishAssetsToPublic(): void
    {
        $assets = [
            resource_path('dist/filament/flatpickr-time-picker.js') => public_path('js/app/components/flatpickr-time-picker.js'),
            resource_path('dist/filament/flatpickr.min.css') => public_path('css/app/flatpickr.css'),
            resource_path('css/filament/flatpickr-time-picker.css') => public_path('css/app/flatpickr-time-picker.css'),
        ];

        foreach ($assets as $source => $destination) {
            if (! is_file($source)) {
                continue;
            }

            File::ensureDirectoryExists(dirname($destination));

            if (! is_file($destination) || filemtime($source) > filemtime($destination)) {
                File::copy($source, $destination);
            }
        }
    }
}
