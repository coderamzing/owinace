<?php

namespace App\Models;

use App\Models\Scopes\TeamScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\TeamTraits;

class LeadKanban extends Model
{
    use HasFactory, TeamTraits;

    protected $table = 'lead_kanban';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'color',
        'sort_order',
        'is_active',
        'team_id',
        'code',
        'is_system',
    ];


    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_system' => 'boolean',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        // Prevent deletion of system kanban stages
        static::deleting(function (LeadKanban $kanban) {
            if ($kanban->is_system) {
                throw new \Exception('Cannot delete system kanban stage.');
            }

            // Prevent deletion if there are leads associated
            if ($kanban->leads()->exists()) {
                throw new \Exception('Cannot delete kanban stage that has associated leads.');
            }
        });

        // Prevent changing name, code, or is_system flag for system records
        static::updating(function (LeadKanban $kanban) {
            if ($kanban->is_system && $kanban->isDirty(['name', 'code'])) {
                throw new \Exception('Cannot change name or code of system kanban stage.');
            }

            // Prevent changing is_system from true to false
            if ($kanban->getOriginal('is_system') && !$kanban->is_system) {
                throw new \Exception('Cannot change system flag once set to true.');
            }
        });
    }

    /**
     * Get the team that owns the lead kanban.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    /**
     * Leads currently in this kanban stage.
     */
    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'kanban_id');
    }
}
