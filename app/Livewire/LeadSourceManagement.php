<?php

namespace App\Livewire;

use App\Models\LeadSource;
use Livewire\Component;
use Livewire\WithPagination;

class LeadSourceManagement extends Component
{
    use WithPagination;

    public $showModal = false;
    public $editingId = null;
    public $name = '';
    public $description = '';
    public $color = '#3B82F6';
    public $sort_order = 0;
    public $is_active = true;

    protected $rules = [
        'name' => 'required|string|max:100',
        'description' => 'nullable|string',
        'color' => 'required|string|max:7',
        'sort_order' => 'required|integer|min:0',
        'is_active' => 'boolean',
    ];

    public function mount()
    {
        $this->resetForm();
    }

    public function openModal($id = null)
    {
        $this->editingId = $id;
        
        if ($id) {
            $source = LeadSource::where('team_id', session('team_id'))->findOrFail($id);
            $this->name = $source->name;
            $this->description = $source->description ?? '';
            $this->color = $source->color;
            $this->sort_order = $source->sort_order;
            $this->is_active = $source->is_active;
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
            'description' => $this->description,
            'color' => $this->color,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
            'team_id' => $teamId,
        ];

        if ($this->editingId) {
            $source = LeadSource::where('team_id', $teamId)->findOrFail($this->editingId);
            $source->update($data);
            session()->flash('message', 'Lead Source updated successfully.');
        } else {
            LeadSource::create($data);
            session()->flash('message', 'Lead Source created successfully.');
        }

        $this->closeModal();
        $this->resetPage();
    }

    public function delete($id)
    {
        $teamId = session('team_id');
        $source = LeadSource::where('team_id', $teamId)->findOrFail($id);
        $source->delete();
        session()->flash('message', 'Lead Source deleted successfully.');
        $this->resetPage();
    }

    private function resetForm()
    {
        $this->name = '';
        $this->description = '';
        $this->color = '#3B82F6';
        $this->sort_order = 0;
        $this->is_active = true;
    }

    public function render()
    {
        $teamId = session('team_id');
        
        if (!$teamId) {
            $emptySources = new \Illuminate\Pagination\LengthAwarePaginator(
                collect([]),
                0,
                10,
                1,
                ['path' => request()->url(), 'query' => request()->query()]
            );
            
            return view('livewire.lead-source-management', [
                'sources' => $emptySources,
                'noTeam' => true,
            ]);
        }
        
        $sources = LeadSource::where('team_id', $teamId)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.lead-source-management', [
            'sources' => $sources,
            'noTeam' => false,
        ]);
    }
}

