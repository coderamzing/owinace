<?php

namespace App\Filament\Forms\Components;

use Closure;
use Filament\Forms\Components\Concerns\HasExtraInputAttributes;
use Filament\Forms\Components\Concerns\HasPlaceholder;
use Filament\Forms\Components\Field;
use Filament\Support\Concerns\HasExtraAlpineAttributes;

class FlatpickrTimePicker extends Field
{
    use HasExtraAlpineAttributes;
    use HasExtraInputAttributes;
    use HasPlaceholder;

    protected string $view = 'filament.forms.components.flatpickr-time-picker';

    protected int | Closure $minuteIncrement = 5;

    protected function setUp(): void
    {
        parent::setUp();

        $this->formatStateUsing(function (mixed $state): ?string {
            if (blank($state)) {
                return null;
            }

            $time = (string) $state;

            if (preg_match('/^\d{2}:\d{2}$/', $time)) {
                return $time;
            }

            if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $time)) {
                return substr($time, 0, 5);
            }

            return $time;
        });

        $this->dehydrateStateUsing(function (mixed $state): ?string {
            if (blank($state)) {
                return null;
            }

            $time = (string) $state;

            if (preg_match('/^\d{2}:\d{2}$/', $time)) {
                return $time;
            }

            if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $time)) {
                return substr($time, 0, 5);
            }

            return $time;
        });
    }

    public function minuteIncrement(int | Closure $increment): static
    {
        $this->minuteIncrement = $increment;

        return $this;
    }

    public function getMinuteIncrement(): int
    {
        return (int) $this->evaluate($this->minuteIncrement);
    }
}
