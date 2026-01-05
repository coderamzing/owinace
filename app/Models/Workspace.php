<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workspace extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'owner_id',
        'tier_id',
        'trial_end',
        'expire_at',
        'start_at',
        'onboard',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'trial_end' => 'datetime',
            'expire_at' => 'datetime',
            'start_at' => 'datetime',
            'onboard' => 'boolean',
        ];
    }

    /**
     * Get the owner of the workspace.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get the tier of the workspace.
     */
    public function tier(): BelongsTo
    {
        return $this->belongsTo(Tier::class);
    }

    /**
     * Get the credits for the workspace.
     */
    public function credits(): HasMany
    {
        return $this->hasMany(WorkspaceCredit::class);
    }

    /**
     * Calculate total credits for the workspace.
     */
    public function totalCredits(): int
    {
        return $this->credits()->sum('credits');
    }
}
