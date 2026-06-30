<?php

namespace App\Models;

use App\Models\Scopes\TeamScope;
use App\Traits\TeamTraits;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Portfolio extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, TeamTraits;

    public const DESCRIPTION_MAX_WORDS = 5000;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'scale',
        'keywords',
        'title',
        'url',
        'description',
        'embedding',
        'is_active',
        'pinged_at',
        'sort_order',
        'created_by_id',
        'team_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'keywords' => 'array',
            'embedding' => 'array',
            'pinged_at' => 'datetime',
        ];
    }

    public static function descriptionWordCount(string $description): int
    {
        return str_word_count(strip_tags($description));
    }

    public static function exceedsDescriptionWordLimit(string $description): bool
    {
        return self::descriptionWordCount($description) > self::DESCRIPTION_MAX_WORDS;
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new TeamScope);
    }

    /**
     * Get the user that owns the portfolio.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    /**
     * Get the team that owns the portfolio.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    public function upworkCampaigns(): BelongsToMany
    {
        return $this->belongsToMany(
            UpworkCampaign::class,
            'upwork_campaigns_portfolios',
            'portfolio_id',
            'campaign_id',
        );
    }

    /**
     * Register media collections for avatar.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')
            ->singleFile()
            ->useDisk('public')
            ->registerMediaConversions(function () {
                $this->addMediaConversion('thumb')
                    ->width(100)
                    ->height(100)
                    ->sharpen(10);
            });
    }

    /**
     * Get the portfolio's avatar URL.
     */
    public function getAvatarUrlAttribute(): string
    {
        return $this->getFirstMediaUrl('avatar') ?: asset('/images/avatars/avatar-1.png');
    }
}
