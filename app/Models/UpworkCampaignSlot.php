<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UpworkCampaignSlot extends Model
{
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'campaign_id',
        'clock_in',
        'clock_out',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(UpworkCampaign::class, 'campaign_id');
    }

    public static function isTimeWithinSlot(string $nowUtc, string $clockIn, string $clockOut): bool
    {
        $clockIn = substr($clockIn, 0, 5);
        $clockOut = substr($clockOut, 0, 5);
        $nowUtc = substr($nowUtc, 0, 5);

        if ($clockIn <= $clockOut) {
            return ($nowUtc >= $clockIn) && ($nowUtc <= $clockOut);
        }

        return ($nowUtc >= $clockIn) || ($nowUtc <= $clockOut);
    }
}
