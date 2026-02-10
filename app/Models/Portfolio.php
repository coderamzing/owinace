<?php

namespace App\Models;

use App\Models\Scopes\TeamScope;
use App\Services\OpenAIService;
use App\Traits\TeamTraits;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Portfolio extends Model implements HasMedia
{
    use HasFactory, TeamTraits, InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'scale',
        'keywords',
        'title',
        'description',
        'embedding',
        'is_active',
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
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new TeamScope);
        
        static::saving(function (Portfolio $portfolio): void {
            $portfolio->updateEmbedding();
        });
    }

    /**
     * Build semantic text and refresh the embedding field.
     */
    protected function updateEmbedding(): void
    {
        $keywordsValue = $this->keywords;
        $keywordsArray = is_array($keywordsValue)
            ? $keywordsValue
            : array_filter(array_map('trim', explode(',', (string) $keywordsValue)));

        $semanticText = implode(' | ', [
            "{$this->title}",
            implode(', ', $keywordsArray),
            Str::limit(strip_tags((string) $this->description), 300),
        ]);

        try {
            /** @var OpenAIService $openAI */
            $openAI = app(OpenAIService::class);
            $keywords = $openAI->findKeywords($semanticText);
            Log::info('Portfolio keywords', ['keywords' => $keywords]);
            $this->embedding = $openAI->createEmbedding($keywords);
        } catch (\Throwable $exception) {
            Log::warning('Failed to create portfolio embedding', [
                'portfolio_id' => $this->id,
                'message' => $exception->getMessage(),
            ]);
        }
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
