<?php

namespace App\Models;

use App\Services\AnalyticsGoalService;
use App\Traits\TeamTraits;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadGoal extends Model
{
    use HasFactory, TeamTraits;

    protected static function boot(): void
    {
        parent::boot();

        static::saved(function (LeadGoal $goal) {
            app(AnalyticsGoalService::class)->syncSingleLeadGoal($goal);
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'goal_type',
        'period',
        'target_value',
        'current_value',
        'is_active',
        'member_id',
        'team_id',
        'description',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'target_value' => 'decimal:2',
            'current_value' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the member (user) associated with the lead goal.
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(User::class, 'member_id');
    }

    /**
     * Get the team that owns the lead goal.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id');
    }
}
