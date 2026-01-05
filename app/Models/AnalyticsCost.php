<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\TeamTraits;

class AnalyticsCost extends Model
{
    use HasFactory, TeamTraits;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'analyticscost';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'month',
        'year',
        'type',
        'avg_cost_per_lead',
        'total_cost',
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
            'avg_cost_per_lead' => 'decimal:2',
            'total_cost' => 'decimal:2',
        ];
    }

    /**
     * Get the team that owns the analytics cost.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id');
    }
}
