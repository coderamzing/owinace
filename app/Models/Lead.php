<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Traits\TeamTraits;

class Lead extends Model
{
    use HasFactory, TeamTraits;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'expected_value',
        'actual_value',
        'cost',
        'next_follow_up',
        'conversion_date',
        'notes',
        'assigned_member_id',
        'team_id',
        'kanban_id',
        'source_id',
        'is_archived',
        'conversion_by_id',
        'created_by_id',
        'url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expected_value' => 'decimal:2',
            'actual_value' => 'decimal:2',
            'cost' => 'decimal:2',
            'next_follow_up' => 'datetime',
            'conversion_date' => 'datetime',
            'is_archived' => 'boolean',
        ];
    }

    /**
     * Get the kanban status of the lead.
     */
    public function kanban(): BelongsTo
    {
        return $this->belongsTo(LeadKanban::class, 'kanban_id');
    }

    /**
     * Get the source of the lead.
     */
    public function source(): BelongsTo
    {
        return $this->belongsTo(LeadSource::class, 'source_id');
    }

    /**
     * Get the assigned member (user).
     */
    public function assignedMember(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_member_id');
    }

    /**
     * Get the user who converted the lead.
     */
    public function conversionBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'conversion_by_id');
    }

    /**
     * Get the user who created the lead.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    /**
     * Get the team that owns the lead.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    /**
     * Get the contacts associated with the lead.
     */
    public function contacts(): BelongsToMany
    {
        return $this->belongsToMany(Contact::class, 'lead_contact');
    }

    /**
     * Tags assigned to the lead.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(LeadTag::class, 'lead_lead_tag', 'lead_id', 'lead_tag_id');
    }
}
