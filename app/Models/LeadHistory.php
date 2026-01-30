<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadHistory extends Model
{
    use HasFactory;

    protected $table = 'leads_history';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'lead_id',
        'kanban_id',
        'note',
    ];

    /**
     * Get the lead associated with this history record.
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    /**
     * Get the kanban stage associated with this history record.
     */
    public function kanban(): BelongsTo
    {
        return $this->belongsTo(LeadKanban::class, 'kanban_id');
    }
}

