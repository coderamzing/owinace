<?php

namespace App\Models;

use App\Models\Traits\BelongsToWorkspace;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TierOrder extends Model
{
    use BelongsToWorkspace;

    protected $fillable = [
        'tier_id',
        'email',
        'transaction_id',
        'amount_paid',
        'first_name',
        'last_name',
        'workspace_id',
        'status',
    ];

    protected $casts = [
        'amount_paid' => 'decimal:2',
    ];

    public function tier(): BelongsTo
    {
        return $this->belongsTo(Tier::class);
    }
}
