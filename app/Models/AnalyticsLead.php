<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\TeamTraits;

class AnalyticsLead extends Model
{
    use HasFactory, TeamTraits;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'analyticslead';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'fullname',
        'month',
        'year',
        'total_lead',
        'total_won',
        'total_lost',
        'total_value',
        'total_cost',
        'total_expected_value',
        'total_roi',
        'avg_cost_per_lead',
        'team_id',
        'user_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_value' => 'decimal:2',
            'avg_cost_per_lead' => 'decimal:2',
        ];
    }

    /**
     * Get the team that owns the analytics lead.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    /**
     * Get the user associated with the analytics lead.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
