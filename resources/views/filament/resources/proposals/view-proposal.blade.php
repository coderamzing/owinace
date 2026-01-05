@php
    $record = $this->record;
@endphp

<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Header Section --}}
        <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-lg p-6">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-3">
                        <x-filament::badge color="primary" icon="heroicon-o-sparkles">
                            Proposal
                        </x-filament::badge>
                        @if($record->team)
                            <x-filament::badge color="gray" icon="heroicon-o-users">
                                {{ $record->team->name }}
                            </x-filament::badge>
                        @endif
                    </div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">{{ $record->title }}</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Created {{ $record->created_at?->format('M d, Y g:i A') }}
                        · by {{ $record->user?->name ?? 'Unknown' }}
                    </p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6">
            <div class="space-y-4">
                <div class="p-5 bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-gray-900 dark:border-gray-800" 
                     x-data="{ 
                         copied: false,
                         copyText() {
                             const text = this.$refs.proposalContent.innerText;
                             navigator.clipboard.writeText(text).then(() => {
                                 this.copied = true;
                                 setTimeout(() => { this.copied = false; }, 2000);
                             });
                         }
                     }">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Proposal Content</h2>
                        <button 
                            type="button"
                            @click="copyText"
                            class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 transition-colors"
                        >
                            <svg x-show="!copied" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                            <svg x-show="copied" class="w-4 h-4 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span x-text="copied ? 'Copied!' : 'Copy'"></span>
                        </button>
                    </div>
                    <div class="prose prose-slate max-w-none dark:prose-invert" x-ref="proposalContent">
                        {!! nl2br(e($record->description)) !!}
                    </div>
                </div>

                <div class="p-5 bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-gray-900 dark:border-gray-800">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Job Description</h2>
                    <div class="prose prose-slate max-w-none text-sm text-gray-700 dark:text-gray-300 dark:prose-invert">
                        {!! nl2br(e($record->job_description)) !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>


