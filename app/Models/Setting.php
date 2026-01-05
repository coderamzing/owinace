<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Setting extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'label',
        'group_id',
        'key',
        'order',
        'type',
        'default_value',
        'hint',
    ];

    /**
     * Get the group that owns the setting.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(SettingGroup::class, 'group_id');
    }

    /**
     * Get the values for this setting.
     */
    public function values(): HasMany
    {
        return $this->hasMany(SettingValue::class, 'setting_id');
    }
}
