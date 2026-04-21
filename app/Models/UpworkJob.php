<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UpworkJob extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'uid',
        'skills',
        'url',
        'location',
        'proposals',
        'client_name',
        'client_rating',
        'client_totalspent',
        'client_jobposted',
        'client_openjob',
        'client_hirerate',
        'client_hires',
        'interviews',
        'client_avgspent',
        'client_avghourlyrate',
        'posted_at',
        'invites_sent',
        'client_since',
        'type',
        'questions',
        'connects',
        'is_expired'
    ];

    protected $casts = [
        'skills' => 'array',
        'questions' => 'array',
    ];
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'client_totalspent' => 'decimal:2',
            'client_hirerate' => 'decimal:2',
        ];
    }

    public function campaignJobStats(): HasMany
    {
        return $this->hasMany(UpworkCampaignJobStat::class, 'job_id');
    }
}
