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
            $kanban->update($data);
            session()->flash('message', 'Lead Kanban updated successfully.');
        } else {
            LeadKanban::create($data);
            session()->flash('message', 'Lead Kanban created successfully.');
        }

        $this->closeModal();
        $this->resetPage();
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

