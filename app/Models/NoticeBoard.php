<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\TeamTraits;

class NoticeBoard extends Model
{
    use TeamTraits;

    protected $fillable = [
        'team_id',
        'title',
        'description',
        'status',
        'notify',
        'published_at',
        'expire_at',
        'notifications_sent',
    ];

    protected $casts = [
        'notify' => 'boolean',
        'notifications_sent' => 'boolean',
        'published_at' => 'datetime',
        'expire_at' => 'datetime',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Scope to get only published notices
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope to get only active (not expired) notices
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('expire_at')
              ->orWhere('expire_at', '>', now());
        });
    }

    /**
     * Scope to get notices that are visible to employees
     * (published, not expired, and published_at is in the past)
     */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->published()
            ->active()
            ->where('published_at', '<=', now());
    }

    /**
     * Scope to get notices for a specific team or all teams (null)
     */
    public function scopeForTeam(Builder $query, ?int $teamId): Builder
    {
        return $query->where(function ($q) use ($teamId) {
            $q->whereNull('team_id')
              ->orWhere('team_id', $teamId);
        });
    }

    /**
     * Check if notice is visible to employees
     */
    public function isVisible(): bool
    {
        return $this->status === 'published'
            && $this->published_at !== null
            && $this->published_at->isPast()
            && ($this->expire_at === null || $this->expire_at->isFuture());
    }
}

