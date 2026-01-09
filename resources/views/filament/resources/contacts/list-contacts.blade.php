<x-filament-panels::page>
    {{-- Custom search section styled like Bootstrap form-floating --}}
    <div class="mb-5 relative px-4 py-5 rounded-2xl border-[1px] border-gray-200 dark:border-gray-700 dark:bg-gray-800 bg-gradient-to-b from-[#a53740] to-[#872d34]">
        <div class="flex items-center">
            <div class="flex-1">
                <div 
                    class="relative"
                    x-data="{ 
                        focused: false,
                        hasValue: @entangle('tableSearch').live
                    }"
                >
                    {{-- Floating label like Bootstrap form-floating --}}
                    <label 
                        for="contact-search" 
                        class="absolute left-4 transition-all duration-200 pointer-events-none z-10"
                        :class="{
                            'top-2 text-xs text-primary-600 dark:text-primary-400 font-medium': focused || hasValue,
                            'top-1/2 -translate-y-1/2 text-base text-gray-500 dark:text-gray-400': !focused && !hasValue
                        }"
                    >
                        Search by name, email, company...
                    </label>
                    
                    {{-- Search input with floating style --}}
                    <input 
                        type="text"
                        id="contact-search"
                        wire:model.live.debounce.500ms="tableSearch"
                        @focus="focused = true"
                        @blur="focused = false"
                        class="w-full h-14 pt-6 pb-2 px-4 pr-32 border-2 border-gray-300 dark:border-gray-600 rounded-lg
                               focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 
                               bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100
                               transition-all duration-200 outline-none"
                        placeholder="Search by name, email, company..."
                        autocomplete="off"
                    />
                    
                    {{-- Search button positioned inside input on the right --}}
                    <button 
                        type="button"
                        class="absolute top-1/2 -translate-y-1/2 right-2 
                               bg-primary-600 hover:bg-primary-700 active:bg-primary-800 text-white 
                               px-6 py-2 rounded-lg font-semibold text-sm
                               transition-colors duration-200 shadow-sm"
                    >
                        Search
                    </button>
                </div>
            </div>
        </div>
        
        {{-- Decorative vertical lines at bottom (like the Bootstrap example) --}}
        <div class="absolute flex gap-1 top-full" style="height: 48px; left: 50px;">
            <div class="w-0.5 h-full bg-gray-200 dark:bg-gray-700"></div>
            <div class="w-0.5 h-full bg-gray-200 dark:bg-gray-700"></div>
        </div>
    </div>

    {{-- The table WITHOUT its default search (we'll hide it with CSS) --}}
    <style>
        /* Hide the default search field in the table */
        .fi-ta-header-toolbar .fi-ta-search-field {
            display: none !important;
        }
    </style>
    
    {{ $this->table }}
</x-filament-panels::page>

