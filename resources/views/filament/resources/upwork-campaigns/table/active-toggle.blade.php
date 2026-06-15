@php
    use Filament\Support\View\Components\ToggleComponent;
    use Illuminate\Support\Arr;

    $isActive = (bool) $record->is_active;
@endphp

<div class="fi-ta-toggle fi-inline pointer-events-none">
    <div
        @class(Arr::toCssClasses([
            'fi-toggle',
            $isActive ? 'fi-toggle-on' : 'fi-toggle-off',
            ...($isActive
                ? \Filament\Support\get_component_color_classes(ToggleComponent::class, 'primary')
                : \Filament\Support\get_component_color_classes(ToggleComponent::class, 'gray')),
        ]))
        role="switch"
        aria-checked="{{ $isActive ? 'true' : 'false' }}"
        aria-label="{{ $isActive ? 'Active' : 'Inactive' }}"
    >
        <div>
            <div aria-hidden="true"></div>
            <div aria-hidden="true"></div>
        </div>
    </div>
</div>
