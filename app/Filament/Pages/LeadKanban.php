<?php

namespace App\Filament\Pages;

use App\Models\Lead;
use App\Models\LeadKanban as LeadKanbanModel;
use App\Models\LeadSource;
use App\Models\TeamMember;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Livewire\Attributes\On;
use UnitEnum;

class LeadKanban extends Page
{
    public $search = '';
    public $memberFilter = '';
    public $sourceFilter = '';
    
    // Track pagination for each kanban column
    public $kanbanPages = [];

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-squares-2x2';
    
    protected static ?string $navigationLabel = 'Kanban Board';
    
    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Leads Kanban Board';

    protected static ?string $slug = 'kanban';

    protected string $view = 'filament.pages.lead-kanban';

    public function mount(): void
    {
        $this->resetPagination();
    }

    public function resetPagination()
    {
        $this->kanbanPages = [];
    }

    public function loadMore($kanbanId)
    {
        if (!isset($this->kanbanPages[$kanbanId])) {
            $this->kanbanPages[$kanbanId] = 1;
        }
        $this->kanbanPages[$kanbanId]++;
    }

    public function updatedSearch()
    {
        $this->resetPagination();
    }

    public function updatedMemberFilter()
    {
        $this->resetPagination();
    }

    public function updatedSourceFilter()
    {
        $this->resetPagination();
    }

    #[On('lead-moved')]
    public function updateLeadKanban($leadId, $newKanbanId): void
    {
        try {
            $teamId = session('team_id');
            
            if (!$teamId) {
                Notification::make()
                    ->title('Error')
                    ->body('No team selected.')
                    ->danger()
                    ->send();
                return;
            }

            $lead = Lead::where('team_id', $teamId)
                ->where('id', $leadId)
                ->first();

            if (!$lead) {
                Notification::make()
                    ->title('Error')
                    ->body('Lead not found.')
                    ->danger()
                    ->send();
                return;
            }

            // Verify the new kanban belongs to the team
            $kanban = LeadKanbanModel::where('team_id', $teamId)
                ->where('id', $newKanbanId)
                ->where('is_active', true)
                ->first();

            if (!$kanban) {
                Notification::make()
                    ->title('Error')
                    ->body('Invalid kanban column.')
                    ->danger()
                    ->send();
                return;
            }

            $lead->update(['kanban_id' => $newKanbanId]);

            Notification::make()
                ->title('Success')
                ->body('Lead moved successfully.')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body('Failed to move lead: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function getLeadsForKanban($kanbanId, $perPage = 20)
    {
        $teamId = session('team_id');
        
        if (!$teamId) {
            return [
                'leads' => collect([]),
                'total' => 0,
                'currentPage' => 1,
                'hasMore' => false,
                'perPage' => $perPage,
            ];
        }

        $baseQuery = Lead::where('team_id', $teamId)
            ->where('kanban_id', $kanbanId)
            ->where('is_archived', false);

        // Apply filters
        if ($this->search) {
            $baseQuery->where(function($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%')
                  ->orWhere('notes', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->memberFilter) {
            $baseQuery->where('assigned_member_id', $this->memberFilter);
        }

        if ($this->sourceFilter) {
            $baseQuery->where('source_id', $this->sourceFilter);
        }

        // Get total count for this kanban
        $total = $baseQuery->count();
        
        // Get current page for this kanban (starts at 1, shows first 20)
        $currentPage = $this->kanbanPages[$kanbanId] ?? 1;
        
        // Calculate how many items to load (accumulate: page 1 = 20, page 2 = 40, etc.)
        $itemsToLoad = $currentPage * $perPage;
        
        // Get all leads up to current page
        $leads = $baseQuery->with(['assignedMember', 'source', 'kanban'])
            ->orderBy('created_at', 'desc')
            ->take($itemsToLoad)
            ->get();

        return [
            'leads' => $leads,
            'total' => $total,
            'currentPage' => $currentPage,
            'hasMore' => $itemsToLoad < $total,
            'perPage' => $perPage,
        ];
    }

    public function getKanbans()
    {
        $teamId = session('team_id');
        
        if (!$teamId) {
            return collect([]);
        }

        return LeadKanbanModel::where('team_id', $teamId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function getMembers()
    {
        $teamId = session('team_id');
        
        if (!$teamId) {
            return collect([]);
        }

        return TeamMember::where('team_id', $teamId)
            ->where(function($query) {
                $query->whereNull('status')
                      ->orWhere('status', 'active');
            })
            ->with('user')
            ->get()
            ->filter(function($member) {
                return $member->user !== null;
            })
            ->map(function($member) {
                return [
                    'id' => $member->user_id,
                    'name' => $member->user->name ?? $member->email,
                ];
            })
            ->unique('id')
            ->values();
    }

    public function getSources()
    {
        $teamId = session('team_id');
        
        if (!$teamId) {
            return collect([]);
        }

        return LeadSource::where('team_id', $teamId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }
}

