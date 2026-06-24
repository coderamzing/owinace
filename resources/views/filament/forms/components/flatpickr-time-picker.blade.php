@php
    $id = $getId();
    $isDisabled = $isDisabled();
    $isAutofocused = $isAutofocused();
    $isRequired = $isRequired();
    $placeholder = $getPlaceholder();
    $statePath = $getStatePath();
    $extraInputAttributeBag = $getExtraInputAttributeBag();
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
    :inline-label-vertical-alignment="\Filament\Support\Enums\VerticalAlignment::Center"
>
    <div
        @if (\Filament\Support\Facades\FilamentView::hasSpaMode())
            x-load="visible || event (ax-modal-opened)"
        @else
            x-load
        @endif
        x-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('flatpickr-time-picker') }}"
        x-data="flatpickrTimePickerComponent({
            state: $wire.{{ $applyStateBindingModifiers("\$entangle('{$statePath}')") }},
            minuteIncrement: @js($getMinuteIncrement()),
            isDisabled: @js($isDisabled),
            placeholder: @js($placeholder),
        })"
        wire:ignore.self
        wire:key="{{ $getLivewireKey() }}.flatpickr-time-picker"
        {{ $getExtraAlpineAttributeBag() }}
        {{
            $attributes
                ->merge($getExtraAttributes(), escape: false)
                ->class(['fi-fo-flatpickr-time-picker'])
        }}
    >
        <x-filament::input.wrapper
            :valid="! $errors->has($statePath)"
            :disabled="$isDisabled"
        >
            <x-filament::input
                x-ref="input"
                :attributes="\Filament\Support\prepare_inherited_attributes($extraInputAttributeBag)->merge([
                    'autofocus' => $isAutofocused,
                    'disabled' => $isDisabled,
                    'id' => $id,
                    'placeholder' => filled($placeholder) ? e($placeholder) : 'HH:MM',
                    'required' => $isRequired,
                    'type' => 'text',
                    'inputmode' => 'numeric',
                    'autocomplete' => 'off',
                ], escape: false)"
            />
        </x-filament::input.wrapper>
    </div>
</x-dynamic-component>
