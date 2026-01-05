<x-filament-panels::page>
    @php
        $kanbans = $this->getKanbans();
        $members = $this->getMembers();
        $sources = $this->getSources();
    @endphp

    <style>
        .kanban-board {
            display: flex;
            gap: 12px;
            padding: 12px;
            overflow-x: auto;
            min-height: calc(100vh - 250px);
            background: #f4f5f7;
        }
        
        .dark .kanban-board {
            background: #1f2937;
        }

        .kanban-column {
            flex: 0 0 300px;
            background: #ebecf0;
            border-radius: 8px;
            padding: 8px;
            display: flex;
            flex-direction: column;
            max-height: calc(100vh - 200px);
        }
        
        .dark .kanban-column {
            background: #374151;
        }

        .kanban-column-header {
            padding: 12px;
            font-weight: 600;
            font-size: 14px;
            color: #172b4d;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        
        .dark .kanban-column-header {
            color: #f3f4f6;
        }

        .kanban-column-content {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 4px;
        }

        .kanban-card {
            background: white;
            border-radius: 4px;
            padding: 12px;
            margin-bottom: 8px;
            cursor: grab;
            box-shadow: 0 1px 0 rgba(9, 30, 66, 0.25);
            transition: all 0.2s;
            position: relative;
        }
        
        .dark .kanban-card {
            background: #1f2937;
            box-shadow: 0 1px 0 rgba(0, 0, 0, 0.3);
        }

        .kanban-card:hover {
            background: #f4f5f7;
            box-shadow: 0 2px 8px rgba(9, 30, 66, 0.15);
        }
        
        .dark .kanban-card:hover {
            background: #374151;
        }

        .kanban-card:active {
            cursor: grabbing;
        }

        .kanban-card.sortable-ghost {
            opacity: 0.4;
            background: #e4e6ea;
        }

        .kanban-card.sortable-drag {
            opacity: 0.8;
            transform: rotate(2deg);
        }

        .kanban-card-title {
            font-weight: 600;
            font-size: 14px;
            color: #172b4d;
            margin-bottom: 8px;
            word-wrap: break-word;
        }
        
        .dark .kanban-card-title {
            color: #f3f4f6;
        }

        .kanban-card-description {
            font-size: 12px;
            color: #5e6c84;
            margin-bottom: 8px;
            line-height: 1.4;
        }
        
        .dark .kanban-card-description {
            color: #9ca3af;
        }

        .kanban-card-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 8px;
            flex-wrap: wrap;
            gap: 4px;
        }

        .kanban-card-badge {
            display: inline-flex;
            align-items: center;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 500;
        }

        .kanban-card-avatar {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: #dfe1e6;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: 600;
            color: #172b4d;
        }
        
        .dark .kanban-card-avatar {
            background: #4b5563;
            color: #f3f4f6;
        }

        .kanban-card-value {
            font-weight: 600;
            font-size: 12px;
            color: #0079bf;
        }
        
        .dark .kanban-card-value {
            color: #60a5fa;
        }

        .kanban-empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #6b778c;
            font-size: 14px;
        }
        
        .dark .kanban-empty-state {
            color: #9ca3af;
        }

        .load-more-btn {
            width: 100%;
            padding: 8px;
            margin-top: 8px;
            background: transparent;
            border: none;
            color: #5e6c84;
            font-size: 13px;
            cursor: pointer;
            border-radius: 4px;
            transition: background 0.2s;
        }
        
        .load-more-btn:hover {
            background: #dfe1e6;
        }
        
        .dark .load-more-btn {
            color: #9ca3af;
        }
        
        .dark .load-more-btn:hover {
            background: #4b5563;
        }

        /* Scrollbar styling */
        .kanban-column-content::-webkit-scrollbar {
            width: 8px;
        }

        .kanban-column-content::-webkit-scrollbar-track {
            background: transparent;
        }

        .kanban-column-content::-webkit-scrollbar-thumb {
            background: #c1c7d0;
            border-radius: 4px;
        }
        
        .dark .kanban-column-content::-webkit-scrollbar-thumb {
            background: #4b5563;
        }

        .kanban-column-content::-webkit-scrollbar-thumb:hover {
            background: #a5adba;
        }
        
        .dark .kanban-column-content::-webkit-scrollbar-thumb:hover {
            background: #6b7280;
        }
    </style>

    @if($kanbans->count() === 0)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="text-center py-12">
                <p class="text-gray-500 dark:text-gray-400 mb-4">No kanban columns found. Please create kanban columns first.</p>
                <a 
                    href="{{ \App\Filament\Resources\LeadKanbans\LeadKanbanResource::getUrl('index') }}"
                    class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors"
                >
                    Create Kanban Columns
                </a>
            </div>
        </div>
    @else
        <!-- Filters Section -->
        <div class="mb-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Full Text Search -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Search
                    </label>
                    <input
                        type="text"
                        id="search"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search leads..."
                        class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                    >
                </div>

                <!-- Member Filter -->
                <div>
                    <label for="memberFilter" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Member
                    </label>
                    <select
                        id="memberFilter"
                        wire:model.live="memberFilter"
                        class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                    >
                        <option value="">All Members</option>
                        @foreach($members as $member)
                            <option value="{{ $member['id'] }}">{{ $member['name'] }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Source Filter -->
                <div>
                    <label for="sourceFilter" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Source
                    </label>
                    <select
                        id="sourceFilter"
                        wire:model.live="sourceFilter"
                        class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                    >
                        <option value="">All Sources</option>
                        @foreach($sources as $source)
                            <option value="{{ $source->id }}">{{ $source->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Clear Filters Button -->
            @if($search || $memberFilter || $sourceFilter)
                <div class="mt-4">
                    <button
                        wire:click="$set('search', ''); $set('memberFilter', ''); $set('sourceFilter', '')"
                        class="text-sm text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300"
                    >
                        Clear Filters
                    </button>
                </div>
            @endif
        </div>

        <!-- Kanban Board -->
        <div class="kanban-board" id="kanban-board">
            @foreach($kanbans as $kanban)
                @php
                    $kanbanData = $this->getLeadsForKanban($kanban->id);
                    $leads = $kanbanData['leads'];
                    $hasMore = $kanbanData['hasMore'];
                    $total = $kanbanData['total'];
                    $currentPage = $kanbanData['currentPage'];
                    $perPage = $kanbanData['perPage'];
                @endphp
                
                <div class="kanban-column" data-kanban-id="{{ $kanban->id }}">
                    <!-- Column Header -->
                    <div class="kanban-column-header">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full" style="background-color: {{ $kanban->color }}"></div>
                            <span>{{ $kanban->name }}</span>
                        </div>
                        <span style="background: rgba(0,0,0,0.1); padding: 2px 8px; border-radius: 12px; font-size: 12px;">
                            {{ $total }}
                        </span>
                    </div>

                    <!-- Leads List -->
                    <div class="kanban-column-content" data-sortable-container="{{ $kanban->id }}">
                        <div class="kanban-cards-list" data-kanban-id="{{ $kanban->id }}">
                            @if($leads->count() > 0)
                                @foreach($leads as $lead)
                                    <div 
                                        class="kanban-card"
                                        data-lead-id="{{ $lead->id }}"
                                        wire:key="lead-{{ $lead->id }}-{{ $kanban->id }}"
                                    >
                                        <div class="kanban-card-title">
                                            {{ $lead->title }}
                                        </div>
                                        
                                        @if($lead->description)
                                            <div class="kanban-card-description">
                                                {{ Str::limit($lead->description, 100) }}
                                            </div>
                                        @endif

                                        <div class="kanban-card-footer">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                @if($lead->source)
                                                    <span class="kanban-card-badge" style="background-color: {{ $lead->source->color }}20; color: {{ $lead->source->color }}">
                                                        {{ $lead->source->name }}
                                                    </span>
                                                @endif
                                                
                                                @if($lead->assignedMember)
                                                    <div class="kanban-card-avatar" title="{{ $lead->assignedMember->name }}">
                                                        {{ strtoupper(substr($lead->assignedMember->name, 0, 1)) }}
                                                    </div>
                                                @endif
                                            </div>
                                            
                                            @if($lead->expected_value)
                                                <span class="kanban-card-value">
                                                    ${{ number_format($lead->expected_value, 0) }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="kanban-empty-state" style="min-height: 40px; display: flex; align-items: center; justify-content: center;">
                                    <p>No leads</p>
                                </div>
                            @endif
                        </div>

                        <!-- Load More Button -->
                        @if($hasMore)
                            <div class="load-more-container">
                                @php
                                    $loaded = $currentPage * $perPage;
                                    $remaining = max(0, $total - $loaded);
                                @endphp
                                <button
                                    wire:click="loadMore({{ $kanban->id }})"
                                    class="load-more-btn"
                                >
                                    Load More ({{ $remaining }} remaining)
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                let sortableInstances = new Map();
                const componentId = @js($this->getId());

                function initializeSortable() {
                    // Clean up existing instances
                    sortableInstances.forEach((instance) => {
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
                    setTimeout(() => {
                        // Initialize Sortable for each column
                        document.querySelectorAll('.kanban-column').forEach(column => {
                            const kanbanId = column.getAttribute('data-kanban-id');
                            const cardsList = column.querySelector('.kanban-cards-list');
                            
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
                                    filter: '.load-more-container, .load-more-btn, .kanban-empty-state',
                                    preventOnFilter: true,
                                    forceFallback: false,
                                    fallbackOnBody: true,
                                    swapThreshold: 0.65,
                                    onEnd: function(evt) {
                                        const leadId = evt.item.getAttribute('data-lead-id');
                                        if (!leadId) return;
                                        
                                        const newColumn = evt.to.closest('.kanban-column');
                                        const oldColumn = evt.from.closest('.kanban-column');
                                        
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
                document.addEventListener('livewire:init', () => {
                    Livewire.hook('morph.updated', () => {
                        setTimeout(initializeSortable, 200);
                    });
                });

                // Also listen for component updates
                Livewire.hook('morph.updated', () => {
                    setTimeout(initializeSortable, 200);
                });
            });
        </script>
    @endpush
</x-filament-panels::page>
