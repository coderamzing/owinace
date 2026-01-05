<?php

namespace App\Livewire;

use App\Models\LeadTag;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class LeadTagManagement extends Component
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
            $tag = LeadTag::where('team_id', session('team_id'))->findOrFail($id);
            $this->name = $tag->name;
            $this->description = $tag->description ?? '';
            $this->color = $tag->color;
            $this->sort_order = $tag->sort_order;
            $this->is_active = $tag->is_active;
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
            $tag = LeadTag::where('team_id', $teamId)->findOrFail($this->editingId);
            $tag->update($data);
            session()->flash('message', 'Lead Tag updated successfully.');
        } else {
            LeadTag::create($data);
            session()->flash('message', 'Lead Tag created successfully.');
        }

        $this->closeModal();
        $this->resetPage();
    }

    public function delete($id)
    {
        $teamId = session('team_id');
        $tag = LeadTag::where('team_id', $teamId)->findOrFail($id);
        $tag->delete();
        session()->flash('message', 'Lead Tag deleted successfully.');
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
            $emptyTags = new \Illuminate\Pagination\LengthAwarePaginator(
                collect([]),
                0,
                10,
                1,
                ['path' => request()->url(), 'query' => request()->query()]
            );
            
            return view('livewire.lead-tag-management', [
                'tags' => $emptyTags,
                'noTeam' => true,
            ]);
        }
        
        $tags = LeadTag::where('team_id', $teamId)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.lead-tag-management', [
            'tags' => $tags,
            'noTeam' => false,
        ]);
    }
}

