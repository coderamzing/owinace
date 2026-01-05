<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\TeamTraits;

class AnalyticsGoal extends Model
{
    use HasFactory, TeamTraits;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'analyticsgoal';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'fullname',
        'month',
        'year',
        'goal_type',
        'acheived',
        'progress_value',
        'target_value',
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
            'acheived' => 'decimal:2',
            'progress_value' => 'decimal:2',
            'target_value' => 'decimal:2',
        ];
    }

    /**
     * Get the team that owns the analytics goal.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    /**
     * Get the user associated with the analytics goal.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
