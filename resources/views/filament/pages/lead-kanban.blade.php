<x-filament-panels::page>
    @php
        $kanbans = $this->getKanbans();
        $members = $this->getMembers();
        $sources = $this->getSources();
    @endphp

    @if($kanbans->count() === 0)
        <div class="bg-gray-50 dark:bg-gray-900/50 border border-gray-300 dark:border-gray-600 rounded-lg p-6">
            <div class="text-center py-12">
                <div class="w-16 h-16 bg-gray-200 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                    <x-filament::icon icon="heroicon-o-squares-2x2" class="w-8 h-8 text-gray-400" />
                </div>
                <p class="text-gray-600 dark:text-gray-400 text-lg mb-4">No kanban columns found</p>
                <p class="text-gray-500 dark:text-gray-500 text-sm mb-6">Get started by creating your first kanban column to organize your leads</p>
                <a 
                    href="{{ \App\Filament\Resources\LeadKanbans\LeadKanbanResource::getUrl('index') }}"
                    class="inline-flex items-center px-6 py-3 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 transition-colors shadow-sm hover:shadow-md"
                >
                    <x-filament::icon icon="heroicon-o-plus" class="w-5 h-5 mr-2" />
                    Create Kanban Columns
                </a>
            </div>
        </div>
    @else
        <!-- Filters Section -->
        <div class="mb-6 bg-gray-50 dark:bg-gray-900/50 border border-gray-300 dark:border-gray-600 rounded-lg p-6 filter-section">
            <div class="fi-ta-filters-header flex justify-between items-center gap-4 mb-[16px]">
                <h2 class="fi-ta-filters-heading text-white">
                    Filters
                </h2>
                <div>
                    <button class="fi-color fi-color-danger fi-text-color-700 bg-[#fff] px-[10px] py-[4px] dark:fi-text-color-400 fi-link fi-size-md" type="button">   
                        Reset
                    </button>            
                </div>
            </div>

            <div class="flex justify-between items-end gap-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 w-[91%]">
                    <!-- Full Text Search -->
                    <div>
                        <label for="search" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2 text-white">
                            <!-- <x-filament::icon icon="heroicon-o-magnifying-glass" class="w-4 h-4 inline mr-1" /> -->
                            Search
                        </label>
                        <input
                            type="text"
                            id="search"
                            wire:model.live.debounce.300ms="search"
                            placeholder="Search leads by title, description..."
                            class="w-full rounded-lg border-gray-300 bg-[#fff] dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 focus:border-primary-500 focus:ring-primary-500 focus:ring-2 transition-all"
                        >
                    </div>

                    <!-- Member Filter -->
                    <div>
                        <label for="memberFilter" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2 text-white">
                            <!-- <x-filament::icon icon="heroicon-o-user" class="w-4 h-4 inline mr-1" /> -->
                            Assigned Member
                        </label>
                        <select
                            id="memberFilter"
                            wire:model.live="memberFilter"
                            class="w-full rounded-lg bg-[#fff] border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 focus:border-primary-500 focus:ring-primary-500 focus:ring-2 transition-all"
                        >
                            <option value="">All Members</option>
                            @foreach($members as $member)
                                <option value="{{ $member['id'] }}">{{ $member['name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Source Filter -->
                    <div>
                        <label for="sourceFilter" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2 text-white">
                            <!-- <x-filament::icon icon="heroicon-o-tag" class="w-4 h-4 inline mr-1" /> -->
                            Lead Source
                        </label>
                        <select
                            id="sourceFilter"
                            wire:model.live="sourceFilter"
                            class="w-full rounded-lg bg-[#fff] border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 focus:border-primary-500 focus:ring-primary-500 focus:ring-2 transition-all"
                        >
                            <option value="">All Sources</option>
                            @foreach($sources as $source)
                                <option value="{{ $source->id }}">{{ $source->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="fi-ta-filters-actions-ctn">
                    <button class="fi-color fi-color-primary leading-[16px] mb-[5px] fi-bg-color-400 hover:fi-bg-color-300 dark:fi-bg-color-600 dark:hover:fi-bg-color-700 fi-text-color-950 hover:fi-text-color-800 dark:fi-text-color-0 dark:hover:fi-text-color-0 fi-btn fi-size-md  fi-ac-btn-action" type="button">                                    
                        Apply Filters
                    </button>     
                </div>
            </div>

            <!-- Clear Filters Button -->
            @if($search || $memberFilter || $sourceFilter)
                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button
                        wire:click="$set('search', ''); $set('memberFilter', ''); $set('sourceFilter', '')"
                        class="inline-flex items-center text-sm font-medium text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 transition-colors"
                    >
                        <x-filament::icon icon="heroicon-o-x-circle" class="w-4 h-4 mr-1" />
                        Clear All Filters
                    </button>
                </div>
            @endif
        </div>

        <!-- Kanban Board -->
        <div class="flex gap-6 overflow-x-auto min-h-[calc(100vh-320px)] dark:bg-gray-900 rounded-lg" id="kanban-board">
            @foreach($kanbans as $kanban)
                @php
                    $kanbanData = $this->getLeadsForKanban($kanban->id);
                    $leads = $kanbanData['leads'];
                    $hasMore = $kanbanData['hasMore'];
                    $total = $kanbanData['total'];
                    $currentPage = $kanbanData['currentPage'];
                    $perPage = $kanbanData['perPage'];
                @endphp
                
                <div class="flex-shrink-0 w-80 flex flex-col max-h-[calc(100vh-250px)]" data-kanban-id="{{ $kanban->id }}">
                    <!-- Column Header -->
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-t-lg px-4 py-3 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full shadow-sm" style="background-color: {{ $kanban->color }}"></div>
                            <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $kanban->name }}</span>
                        </div>
                        <span class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-2.5 py-1 rounded-full text-xs font-semibold">
                            {{ $total }}
                        </span>
                    </div>

                    <!-- Leads List -->
                    <div class="flex-1 bg-gray-50 dark:bg-gray-800/50 border-x-2 border-b-2 border-gray-200 dark:border-gray-700 rounded-b-lg p-2 overflow-y-auto overflow-x-hidden" data-sortable-container="{{ $kanban->id }}">
                        <div class="space-y-2" data-kanban-id="{{ $kanban->id }}">
                            @if($leads->count() > 0)
                                @foreach($leads as $lead)
                                    <div 
                                        class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 cursor-grab hover:border-primary-300 dark:hover:border-primary-600 transition-all duration-200 hover:shadow-md group"
                                        data-lead-id="{{ $lead->id }}"
                                        wire:key="lead-{{ $lead->id }}-{{ $kanban->id }}"
                                        style="border-left: 2px solid {{ $lead->source->color ?? '#3b82f6' }}"
                                        onclick="if (!window.isDragging) { event.stopPropagation(); @this.openLeadSidebar({{ $lead->id }}); }"
                                    >
                                        <div class="text-gray-900 dark:text-gray-100 mb-2 text-sm group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
                                            {{ $lead->title }}
                                        </div>
                                        
                                        @if($lead->description)
                                            <div class="text-xs text-gray-600 dark:text-gray-400 mb-3 leading-relaxed line-clamp-2">
                                                {{ Str::limit($lead->description, 100) }}
                                            </div>
                                        @endif

                                        <div class="flex items-center justify-between mt-3 flex-wrap gap-2">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                @if($lead->source)
                                                    <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium" style="background-color: {{ $lead->source->color }}20; color: {{ $lead->source->color }}">
                                                        {{ $lead->source->name }}
                                                    </span>
                                                @endif
                                                
                                                @if($lead->assignedMember)
                                                    <div 
                                                        class="w-6 h-6 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white text-xs font-bold shadow-sm"
                                                        title="{{ $lead->assignedMember->name }}"
                                                    >
                                                        {{ strtoupper(substr($lead->assignedMember->name, 0, 1)) }}
                                                    </div>
                                                @endif
                                            </div>
                                            
                                            @if($lead->expected_value)
                                                <span class="text-xs font-bold text-green-600 dark:text-green-400">
                                                    ${{ number_format($lead->expected_value, 0) }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="text-center py-12 text-gray-400 dark:text-gray-600 text-sm">
                                    <x-filament::icon icon="heroicon-o-inbox" class="w-12 h-12 mx-auto mb-2 opacity-50" />
                                    <p>No leads</p>
                                </div>
                            @endif
                        </div>

                        <!-- Load More Button -->
                        @if($hasMore)
                            <div class="mt-2">
                                @php
                                    $loaded = $currentPage * $perPage;
                                    $remaining = max(0, $total - $loaded);
                                @endphp
                                <button
                                    wire:click="loadMore({{ $kanban->id }})"
                                    class="w-full py-2 px-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-primary-300 dark:hover:border-primary-600 transition-all font-medium"
                                >
                                    <x-filament::icon icon="heroicon-o-arrow-down-circle" class="w-4 h-4 inline mr-1" />
                                    Load More ({{ $remaining }} remaining)
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Lead Details Sidebar -->
    @if($showSidebar)
        @php
            $selectedLead = $this->getSelectedLead();
        @endphp
        
        <div 
            class="fixed inset-0 z-50 overflow-hidden"
            x-data="{ show: @entangle('showSidebar') }"
            x-show="show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        >
            <!-- Backdrop -->
            <div 
                class="absolute inset-0 bg-gray-900/20 dark:bg-gray-900/40 backdrop-blur-sm transition-all"
                wire:click="closeSidebar"
            ></div>
            
            <!-- Sidebar Panel -->
            <div class="fixed inset-y-0 right-0 flex max-w-full pl-10">
                <div 
                    class="w-screen max-w-2xl"
                    x-show="show"
                    x-transition:enter="transform transition ease-in-out duration-300"
                    x-transition:enter-start="translate-x-full"
                    x-transition:enter-end="translate-x-0"
                    x-transition:leave="transform transition ease-in-out duration-300"
                    x-transition:leave-start="translate-x-0"
                    x-transition:leave-end="translate-x-full"
                >
                    <div class="flex h-full flex-col overflow-y-scroll bg-white dark:bg-gray-800 shadow-xl">
                        @if($selectedLead)
                            <!-- Header -->
                            <div class="bg-primary-600 dark:bg-primary-700 px-6 py-6">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h2 class="text-xl font-semibold text-white">
                                            {{ $selectedLead->title }}
                                        </h2>
                                        <div class="mt-2 flex items-center gap-2 flex-wrap">
                                            @if($selectedLead->kanban)
                                                <span 
                                                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium text-white"
                                                    style="background-color: {{ $selectedLead->kanban->color }}"
                                                >
                                                    {{ $selectedLead->kanban->name }}
                                                </span>
                                            @endif
                                            @if($selectedLead->source)
                                                <span 
                                                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium"
                                                    style="background-color: {{ $selectedLead->source->color }}20; color: {{ $selectedLead->source->color }}"
                                                >
                                                    {{ $selectedLead->source->name }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <button
                                        wire:click="closeSidebar"
                                        class="ml-3 text-white hover:text-gray-200 transition-colors"
                                    >
                                        <x-filament::icon icon="heroicon-o-x-mark" class="w-6 h-6" />
                                    </button>
                                </div>
                            </div>

                            <!-- Content -->
                            <div class="flex-1 px-6 py-6 space-y-6">
                                <!-- Description -->
                                @if($selectedLead->description)
                                    <div>
                                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">Description</h3>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 whitespace-pre-wrap">{{ $selectedLead->description }}</p>
                                    </div>
                                @endif

                                <!-- Key Information -->
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Key Information</h3>
                                    <div class="grid grid-cols-2 gap-4">
                                        @if($selectedLead->expected_value)
                                            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                                                <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Expected Value</div>
                                                <div class="text-lg font-bold text-green-600 dark:text-green-400">
                                                    ${{ number_format($selectedLead->expected_value, 2) }}
                                                </div>
                                            </div>
                                        @endif

                                        @if($selectedLead->actual_value)
                                            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                                                <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Actual Value</div>
                                                <div class="text-lg font-bold text-blue-600 dark:text-blue-400">
                                                    ${{ number_format($selectedLead->actual_value, 2) }}
                                                </div>
                                            </div>
                                        @endif

                                        @if($selectedLead->cost)
                                            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                                                <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Cost</div>
                                                <div class="text-lg font-bold text-red-600 dark:text-red-400">
                                                    ${{ number_format($selectedLead->cost, 2) }}
                                                </div>
                                            </div>
                                        @endif

                                        @if($selectedLead->next_follow_up)
                                            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                                                <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Next Follow-up</div>
                                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    {{ $selectedLead->next_follow_up->format('M d, Y') }}
                                                </div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ $selectedLead->next_follow_up->format('h:i A') }}
                                                </div>
                                            </div>
                                        @endif

                                        @if($selectedLead->conversion_date)
                                            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                                                <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Conversion Date</div>
                                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    {{ $selectedLead->conversion_date->format('M d, Y') }}
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Assigned Member -->
                                @if($selectedLead->assignedMember)
                                    <div>
                                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">Assigned To</h3>
                                        <div class="flex items-center gap-3 bg-gray-50 dark:bg-gray-900 rounded-lg p-3">
                                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white text-sm font-bold">
                                                {{ strtoupper(substr($selectedLead->assignedMember->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    {{ $selectedLead->assignedMember->name }}
                                                </div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ $selectedLead->assignedMember->email }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <!-- Tags -->
                                @if($selectedLead->tags && $selectedLead->tags->count() > 0)
                                    <div>
                                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">Tags</h3>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($selectedLead->tags as $tag)
                                                <span 
                                                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium"
                                                    style="background-color: {{ $tag->color }}20; color: {{ $tag->color }}"
                                                >
                                                    {{ $tag->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <!-- Contacts -->
                                @if($selectedLead->contacts && $selectedLead->contacts->count() > 0)
                                    <div>
                                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">Contacts</h3>
                                        <div class="space-y-2">
                                            @foreach($selectedLead->contacts as $contact)
                                                <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-3">
                                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                        {{ $contact->first_name }} {{ $contact->last_name }}
                                                    </div>
                                                    @if($contact->email)
                                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                            <x-filament::icon icon="heroicon-o-envelope" class="w-3 h-3 inline mr-1" />
                                                            {{ $contact->email }}
                                                        </div>
                                                    @endif
                                                    @if($contact->phone_number)
                                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                            <x-filament::icon icon="heroicon-o-phone" class="w-3 h-3 inline mr-1" />
                                                            {{ $contact->phone_number }}
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <!-- URL -->
                                @if($selectedLead->url)
                                    <div>
                                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">URL</h3>
                                        <a 
                                            href="{{ $selectedLead->url }}" 
                                            target="_blank"
                                            class="text-sm text-primary-600 dark:text-primary-400 hover:underline break-all"
                                        >
                                            <x-filament::icon icon="heroicon-o-link" class="w-4 h-4 inline mr-1" />
                                            {{ $selectedLead->url }}
                                        </a>
                                    </div>
                                @endif

                                <!-- Notes -->
                                @if($selectedLead->notes)
                                    <div>
                                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">Notes</h3>
                                        <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                                            <p class="text-sm text-gray-600 dark:text-gray-400 whitespace-pre-wrap">{{ $selectedLead->notes }}</p>
                                        </div>
                                    </div>
                                @endif

                                <!-- Metadata -->
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">Metadata</h3>
                                    <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 space-y-2">
                                        <div class="flex justify-between text-xs">
                                            <span class="text-gray-500 dark:text-gray-400">Created</span>
                                            <span class="text-gray-900 dark:text-gray-100">{{ $selectedLead->created_at->format('M d, Y h:i A') }}</span>
                                        </div>
                                        @if($selectedLead->createdBy)
                                            <div class="flex justify-between text-xs">
                                                <span class="text-gray-500 dark:text-gray-400">Created By</span>
                                                <span class="text-gray-900 dark:text-gray-100">{{ $selectedLead->createdBy->name }}</span>
                                            </div>
                                        @endif
                                        <div class="flex justify-between text-xs">
                                            <span class="text-gray-500 dark:text-gray-400">Last Updated</span>
                                            <span class="text-gray-900 dark:text-gray-100">{{ $selectedLead->updated_at->format('M d, Y h:i A') }}</span>
                                        </div>
                                        @if($selectedLead->conversionBy)
                                            <div class="flex justify-between text-xs">
                                                <span class="text-gray-500 dark:text-gray-400">Converted By</span>
                                                <span class="text-gray-900 dark:text-gray-100">{{ $selectedLead->conversionBy->name }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                                    <a
                                        href="{{ \App\Filament\Resources\Leads\LeadResource::getUrl('view', ['record' => $selectedLead->id]) }}"
                                        class="w-full inline-flex items-center justify-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors font-medium"
                                    >
                                        <x-filament::icon icon="heroicon-o-arrow-right-circle" class="w-4 h-4 mr-2" />
                                        Open Full Lead Details
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

        @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

        <script>
            // Global variable to track dragging state
            window.isDragging = false;
            
            document.addEventListener('DOMContentLoaded', function() {
                let sortableInstances = new Map();
                const componentId = @js($this->getId());

                function initializeSortable() {
                    // Clean up existing instances
                    sortableInstances.forEach(function(instance) {
                        if (instance && instance.el) {
                            try {
                                instance.destroy();
                            } catch(e) {
                                console.log('Error destroying sortable:', e);
                            }
                        }
                    });
                    sortableInstances.clear();

                    // Small delay to ensure DOM is ready
                    setTimeout(function() {
                        // Initialize Sortable for each column
                        document.querySelectorAll('[data-kanban-id]').forEach(function(column) {
                            const kanbanId = column.getAttribute('data-kanban-id');
                            const cardsList = column.querySelector('[data-kanban-id="' + kanbanId + '"]');
                            
                            if (cardsList && !sortableInstances.has(kanbanId)) {
                                const sortable = new Sortable(cardsList, {
                                    group: {
                                        name: 'kanban-leads',
                                        pull: true,
                                        put: true
                                    },
                                    animation: 200,
                                    ghostClass: 'sortable-ghost',
                                    chosenClass: 'sortable-chosen',
                                    dragClass: 'sortable-drag',
                                    forceFallback: false,
                                    fallbackOnBody: true,
                                    swapThreshold: 0.65,
                                    onStart: function(evt) {
                                        window.isDragging = true;
                                    },
                                    onEnd: function(evt) {
                                        // Reset dragging state after a short delay
                                        setTimeout(function() {
                                            window.isDragging = false;
                                        }, 100);
                                        const leadId = evt.item.getAttribute('data-lead-id');
                                        if (!leadId) return;
                                        
                                        const newColumn = evt.to.closest('[data-kanban-id]');
                                        const oldColumn = evt.from.closest('[data-kanban-id]');
                                        
                                        if (!newColumn || !oldColumn) {
                                            // Revert
                                            if (evt.oldIndex !== undefined && evt.from) {
                                                const children = Array.from(evt.from.children);
                                                const item = evt.item;
                                                if (children[evt.oldIndex] && children[evt.oldIndex] !== item) {
                                                    evt.from.insertBefore(item, children[evt.oldIndex]);
                                                } else if (evt.oldIndex < children.length) {
                                                    evt.from.insertBefore(item, children[evt.oldIndex]);
                                                } else {
                                                    evt.from.appendChild(item);
                                                }
                                            }
                                            return;
                                        }
                                        
                                        const newKanbanId = newColumn.getAttribute('data-kanban-id');
                                        const oldKanbanId = oldColumn.getAttribute('data-kanban-id');

                                        // Only update if moved to a different column
                                        if (newKanbanId !== oldKanbanId && leadId && newKanbanId) {
                                            // Try to get Livewire component
                                            let component = null;
                                            try {
                                                component = Livewire.find(componentId);
                                            } catch(e) {
                                                console.log('Could not find Livewire component:', e);
                                            }
                                            
                                            if (component) {
                                                component.dispatch('lead-moved', {
                                                    leadId: parseInt(leadId),
                                                    newKanbanId: parseInt(newKanbanId)
                                                });
                                            } else {
                                                // Fallback to global dispatch
                                                Livewire.dispatch('lead-moved', {
                                                    leadId: parseInt(leadId),
                                                    newKanbanId: parseInt(newKanbanId)
                                                });
                                            }
                                        } else {
                                            // Revert if same column or invalid
                                            if (evt.oldIndex !== undefined && evt.from) {
                                                const children = Array.from(evt.from.children);
                                                const item = evt.item;
                                                if (children[evt.oldIndex] && children[evt.oldIndex] !== item) {
                                                    evt.from.insertBefore(item, children[evt.oldIndex]);
                                                } else if (evt.oldIndex < children.length) {
                                                    evt.from.insertBefore(item, children[evt.oldIndex]);
                                                } else {
                                                    evt.from.appendChild(item);
                                                }
                                            }
                                        }
                                    }
                                });
                                
                                sortableInstances.set(kanbanId, sortable);
                            }
                        });
                    }, 100);
                }

                // Initialize immediately
                initializeSortable();

                // Reinitialize after Livewire updates
                document.addEventListener('livewire:init', function() {
                    Livewire.hook('morph.updated', function() {
                        setTimeout(initializeSortable, 200);
                    });
                });

                // Also listen for component updates
                Livewire.hook('morph.updated', function() {
                    setTimeout(initializeSortable, 200);
                });
            });
        </script>
    @endpush
</x-filament-panels::page>
