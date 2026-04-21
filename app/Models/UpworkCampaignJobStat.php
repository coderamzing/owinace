<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UpworkCampaignJobStat extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'campaign_id',
        'job_id',
        'is_matched',
        'is_applied',
        'note',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(UpworkCampaign::class, 'campaign_id');
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(UpworkJob::class, 'job_id');
    }
}
