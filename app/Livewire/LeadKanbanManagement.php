<?php

namespace App\Livewire;

use App\Models\Lead;
use App\Models\LeadKanban;
use Livewire\Component;
use Livewire\WithPagination;

class LeadKanbanManagement extends Component
{
    use WithPagination;

    public $showModal = false;
    public $editingId = null;
    public $name = '';
    public $color = '#3B82F6';
    public $code = '';
    public $sort_order = 0;
    public $is_active = true;
    public $is_system = false;

    protected $rules = [
        'name' => 'required|string|max:100',
        'color' => 'required|string|max:7',
        'code' => 'nullable|string|max:100',
        'sort_order' => 'required|integer|min:0',
        'is_active' => 'boolean',
        'is_system' => 'boolean',
    ];

    public function mount()
    {
        $this->resetForm();
    }

    public function openModal($id = null)
    {
        $this->editingId = $id;
        
        if ($id) {
            $kanban = LeadKanban::where('team_id', session('team_id'))->findOrFail($id);
            $this->name = $kanban->name;
            $this->color = $kanban->color;
            $this->code = $kanban->code;
            $this->sort_order = $kanban->sort_order;
            $this->is_active = $kanban->is_active;
            $this->is_system = $kanban->is_system;
        } else {
            $this->resetForm();
        }
        
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
        $this->editingId = null;
    }

    public function save()
    {
        $this->validate();

        $teamId = session('team_id');
        
        if (!$teamId) {
            session()->flash('error', 'No team selected. Please select a team first.');
            return;
        }

        // Auto-generate code from name if code is empty
        if (empty($this->code)) {
            $this->code = $this->generateCodeFromName($this->name);
        }

        $data = [
            'name' => $this->name,
            'color' => $this->color,
            'code' => $this->code,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
            'is_system' => $this->is_system,
            'team_id' => $teamId,
        ];

        if ($this->editingId) {
            $kanban = LeadKanban::where('team_id', $teamId)->findOrFail($this->editingId);
            
            // Prevent editing critical fields for system records
            if ($kanban->is_system) {
                // Only allow certain fields to be updated
                $data = [
                    'color' => $this->color,
                    'sort_order' => $this->sort_order,
                    'is_active' => $this->is_active,
                ];
            }
            
            try {
                $kanban->update($data);
                session()->flash('message', 'Lead Kanban updated successfully.');
            } catch (\Exception $e) {
                session()->flash('error', $e->getMessage());
                return;
            }
        } else {
            try {
                LeadKanban::create($data);
                session()->flash('message', 'Lead Kanban created successfully.');
            } catch (\Exception $e) {
                session()->flash('error', $e->getMessage());
                return;
            }
        }

        $this->closeModal();
        $this->resetPage();
    }

    /**
     * Generate a code from the name (slug format)
     */
    private function generateCodeFromName(string $name): string
    {
        // Convert to lowercase
        $code = strtolower($name);
        
        // Replace spaces with underscores
        $code = str_replace(' ', '_', $code);
        
        // Remove special characters except underscores and dashes
        $code = preg_replace('/[^a-z0-9_\-]/', '', $code);
        
        // Replace multiple underscores/dashes with single ones
        $code = preg_replace('/[_\-]+/', '_', $code);
        
        // Trim underscores from start and end
        $code = trim($code, '_-');
        
        return $code;
    }

    public function delete($id)
    {
        $teamId = session('team_id');
        $kanban = LeadKanban::where('team_id', $teamId)->findOrFail($id);
        
        // Prevent deletion of system kanban
        if ($kanban->is_system) {
            session()->flash('error', 'Cannot delete system kanban.');
            return;
        }

        if (Lead::where('team_id', $teamId)->where('kanban_id', $kanban->id)->exists()) {
            session()->flash('error', 'Cannot delete this stage because it is linked to existing leads.');
            return;
        }
        
        $kanban->delete();
        session()->flash('message', 'Lead Kanban deleted successfully.');
        $this->resetPage();
    }

    private function resetForm()
    {
        $this->name = '';
        $this->color = '#3B82F6';
        $this->code = '';
        $this->sort_order = 0;
        $this->is_active = true;
        $this->is_system = false;
    }

    public function render()
    {
        $teamId = session('team_id');
        
        if (!$teamId) {
            $emptyKanbans = new \Illuminate\Pagination\LengthAwarePaginator(
                collect([]),
                0,
                10,
                1,
                ['path' => request()->url(), 'query' => request()->query()]
            );
            
            return view('livewire.lead-kanban-management', [
                'kanbans' => $emptyKanbans,
                'noTeam' => true,
            ]);
        }
        
        $kanbans = LeadKanban::where('team_id', $teamId)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.lead-kanban-management', [
            'kanbans' => $kanbans,
            'noTeam' => false,
        ]);
    }
}

