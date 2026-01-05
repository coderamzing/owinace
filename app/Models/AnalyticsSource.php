<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\TeamTraits;

class AnalyticsSource extends Model
{
    use HasFactory, TeamTraits;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'analyticssource';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'month',
        'year',
        'total_cost',
        'total_lead',
        'total_won',
        'total_lost',
        'total_value',
        'total_expected_value',
        'total_roi',
        'avg_cost_per_lead',
        'title',
        'source_id',
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
            'total_value' => 'decimal:2',
            'avg_cost_per_lead' => 'decimal:2',
        ];
    }

    /**
     * Get the team that owns the analytics source.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    /**
     * Get the lead source associated with the analytics source.
     */
    public function source(): BelongsTo
    {
        return $this->belongsTo(LeadSource::class, 'source_id');
    }
}
