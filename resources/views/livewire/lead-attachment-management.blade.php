<div class="space-y-4">
    {{-- Upload Section --}}
    <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 hover:border-primary-500 dark:hover:border-primary-400 transition-colors">
        <div class="flex flex-col items-center justify-center space-y-3">
            <x-filament::icon icon="heroicon-o-cloud-arrow-up" class="w-10 h-10 text-gray-400 dark:text-gray-500" />
            
            <div class="text-center">
                <label for="attachment-upload" class="cursor-pointer">
                    <span class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-colors">
                        <x-filament::icon icon="heroicon-o-paper-clip" class="w-5 h-5 mr-2" />
                        Choose File
                    </span>
                    <input 
                        type="file" 
                        id="attachment-upload"
                        wire:model="attachment" 
                        class="hidden"
                        accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif,.webp,.txt,.zip,.rar"
                    />
                </label>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                    Maximum file size: 2MB
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Supported: PDF, Word, Excel, Images, Text, ZIP, RAR
                </p>
            </div>

            @if ($attachment)
                <div class="w-full mt-4 p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <x-filament::icon icon="heroicon-o-document" class="w-6 h-6 text-primary-600 dark:text-primary-400" />
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $attachment->getClientOriginalName() }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ number_format($attachment->getSize() / 1024, 2) }} KB
                                </p>
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <button 
                                type="button"
                                wire:click="uploadAttachment" 
                                wire:loading.attr="disabled"
                                wire:target="uploadAttachment"
                                class="inline-flex items-center px-3 py-1.5 bg-success-600 hover:bg-success-700 text-white text-sm rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <x-filament::icon icon="heroicon-o-check" class="w-4 h-4 mr-1" />
                                <span wire:loading.remove wire:target="uploadAttachment">Upload</span>
                                <span wire:loading wire:target="uploadAttachment">Uploading...</span>
                            </button>
                            <button 
                                type="button"
                                wire:click="$set('attachment', null)" 
                                class="inline-flex items-center px-3 py-1.5 bg-danger-600 hover:bg-danger-700 text-white text-sm rounded-lg transition-colors"
                            >
                                <x-filament::icon icon="heroicon-o-x-mark" class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            @error('attachment')
                <div class="w-full mt-2 p-3 bg-danger-50 dark:bg-danger-900/20 border border-danger-200 dark:border-danger-800 rounded-lg">
                    <p class="text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                </div>
            @enderror

            {{-- Upload Progress --}}
            <div wire:loading wire:target="attachment" class="w-full">
                <div class="flex items-center justify-center space-x-2 text-primary-600 dark:text-primary-400">
                    <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-sm">Processing file...</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Attachments List --}}
    @if($attachments->count() > 0)
        <div class="space-y-3">
            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center">
                <x-filament::icon icon="heroicon-o-paper-clip" class="w-4 h-4 mr-2" />
                Attachments ({{ $attachments->count() }})
            </h4>
            
            <div class="space-y-2">
                @foreach($attachments as $media)
                    <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-900 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors group">
                        <div class="flex items-center space-x-3 flex-1 min-w-0">
                            <div class="flex-shrink-0">
                                @php
                                    $iconClass = 'w-8 h-8';
                                    if (str_starts_with($media->mime_type, 'image/')) {
                                        $iconClass = 'text-blue-500 dark:text-blue-400';
                                    } elseif ($media->mime_type === 'application/pdf') {
                                        $iconClass = 'text-red-500 dark:text-red-400';
                                    } elseif (in_array($media->mime_type, ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])) {
                                        $iconClass = 'text-blue-600 dark:text-blue-400';
                                    } elseif (in_array($media->mime_type, ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])) {
                                        $iconClass = 'text-green-600 dark:text-green-400';
                                    } elseif (in_array($media->mime_type, ['application/zip', 'application/x-rar-compressed'])) {
                                        $iconClass = 'text-yellow-600 dark:text-yellow-400';
                                    } else {
                                        $iconClass = 'text-gray-500 dark:text-gray-400';
                                    }
                                @endphp
                                
                                @if(str_starts_with($media->mime_type, 'image/'))
                                    <x-filament::icon icon="heroicon-o-photo" class="{{ $iconClass }}" />
                                @elseif($media->mime_type === 'application/pdf')
                                    <x-filament::icon icon="heroicon-o-document-text" class="{{ $iconClass }}" />
                                @elseif(in_array($media->mime_type, ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']))
                                    <x-filament::icon icon="heroicon-o-document" class="{{ $iconClass }}" />
                                @elseif(in_array($media->mime_type, ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']))
                                    <x-filament::icon icon="heroicon-o-table-cells" class="{{ $iconClass }}" />
                                @elseif(in_array($media->mime_type, ['application/zip', 'application/x-rar-compressed']))
                                    <x-filament::icon icon="heroicon-o-archive-box" class="{{ $iconClass }}" />
                                @else
                                    <x-filament::icon icon="heroicon-o-document" class="{{ $iconClass }}" />
                                @endif
                            </div>
                            
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                    {{ $media->file_name }}
                                </p>
                                <div class="flex items-center space-x-3 mt-1">
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ number_format($media->size / 1024, 2) }} KB
                                    </p>
                                    <span class="text-gray-400 dark:text-gray-600">•</span>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $media->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex items-center space-x-2 ml-4">
                            {{-- Preview for images --}}
                            @if(str_starts_with($media->mime_type, 'image/'))
                                <a 
                                    href="{{ $media->getUrl() }}" 
                                    target="_blank"
                                    class="inline-flex items-center justify-center w-8 h-8 text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/20 rounded-lg transition-colors"
                                    title="Preview"
                                >
                                    <x-filament::icon icon="heroicon-o-eye" class="w-5 h-5" />
                                </a>
                            @endif
                            
                            {{-- Download button --}}
                            <a 
                                href="{{ $media->getUrl() }}" 
                                download="{{ $media->file_name }}"
                                class="inline-flex items-center justify-center w-8 h-8 text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/20 rounded-lg transition-colors"
                                title="Download"
                            >
                                <x-filament::icon icon="heroicon-o-arrow-down-tray" class="w-5 h-5" />
                            </a>
                            
                            {{-- Delete button --}}
                            <button 
                                type="button"
                                wire:click="deleteAttachment({{ $media->id }})"
                                wire:confirm="Are you sure you want to delete this attachment? This action cannot be undone."
                                class="inline-flex items-center justify-center w-8 h-8 text-gray-600 dark:text-gray-400 hover:text-danger-600 dark:hover:text-danger-400 hover:bg-danger-50 dark:hover:bg-danger-900/20 rounded-lg transition-colors"
                                title="Delete"
                            >
                                <x-filament::icon icon="heroicon-o-trash" class="w-5 h-5" />
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="text-center py-6">
            <x-filament::icon icon="heroicon-o-folder-open" class="w-12 h-12 text-gray-300 dark:text-gray-600 mx-auto mb-3" />
            <p class="text-sm text-gray-500 dark:text-gray-400">No attachments yet</p>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Upload files to attach them to this lead</p>
        </div>
    @endif
</div>

{{-- Notification Script --}}
@script
<script>
    $wire.on('notify', (event) => {
        const notification = event[0];
        
        if (window.Filament) {
            window.Filament.notifications?.notify({
                title: notification.message,
                status: notification.type,
            });
        }
    });
</script>
@endscript

