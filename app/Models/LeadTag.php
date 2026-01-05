<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Traits\TeamTraits;

class LeadTag extends Model
{
    use HasFactory, TeamTraits;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'color',
        'sort_order',
        'is_active',
        'team_id',
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
        ];
    }
    
    /**
     * Get the team that owns the lead tag.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    /**
     * Leads associated to this tag.
     */
    public function leads(): BelongsToMany
    {
        return $this->belongsToMany(Lead::class, 'lead_lead_tag', 'lead_tag_id', 'lead_id');
    }
}
