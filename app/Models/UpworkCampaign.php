<?php

namespace App\Models;

use App\Traits\TeamTraits;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class UpworkCampaign extends Model
{
    use HasFactory, TeamTraits;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'is_active',
        'max_connect_per_bid',
        'search_url',
        'timezone',
        'max_daily_bid',
        'auto_bidding',
        'ai_prompt',
        'questions_context',
        'matching_critieria',
        'rule_client_avg_spent',
        'rule_max_interviews',
        'rule_job_posted_ago',
        'rule_max_proposal',
        'rule_min_client_rating',
        'rule_clock_in',
        'rule_clock_out',
        'member_id',
        'team_id',
        'source_id',
        'kanban_id',
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
            'auto_bidding' => 'boolean',
            'rule_client_avg_spent' => 'decimal:2',
            'rule_max_interviews' => 'decimal:2',
            'rule_job_posted_ago' => 'decimal:2',
            'rule_max_proposal' => 'decimal:2',
            'rule_min_client_rating' => 'integer',
            'max_daily_bid' => 'integer',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(TeamMember::class, 'member_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(LeadSource::class, 'source_id');
    }

    public function kanban(): BelongsTo
    {
        return $this->belongsTo(LeadKanban::class, 'kanban_id');
    }

    public function campaignJobStats(): HasMany
    {
        return $this->hasMany(UpworkCampaignJobStat::class, 'campaign_id');
    }

    public function linkedPortfolios(): BelongsToMany
    {
        return $this->belongsToMany(
            Portfolio::class,
            'upwork_campaigns_portfolios',
            'campaign_id',
            'portfolio_id',
        )->orderBy('portfolios.sort_order');
    }

    /**
     * Text block for AI prompts from attached portfolios (falls back to legacy text column).
     */
    public function portfoliosPromptText(): string
    {
        $this->loadMissing('linkedPortfolios');

        if ($this->linkedPortfolios->isNotEmpty()) {
            return $this->formatPortfoliosForPrompt($this->linkedPortfolios);
        }

        return trim((string) ($this->attributes['portfolios'] ?? ''));
    }

    /**
     * @param  Collection<int, Portfolio>  $portfolios
     */
    protected function formatPortfoliosForPrompt(Collection $portfolios): string
    {
        if ($portfolios->isEmpty()) {
            return '';
        }

        return $portfolios
            ->values()
            ->map(function (Portfolio $portfolio, int $index): string {
                $parts = [($index + 1).'. '.$portfolio->title];

                $keywords = $portfolio->keywords;
                if (is_array($keywords) && $keywords !== []) {
                    $parts[] = 'Keywords: '.implode(', ', $keywords);
                }

                $description = trim((string) $portfolio->description);
                if ($description !== '') {
                    $parts[] = 'Description: '.$description;
                }

                return implode(' | ', $parts);
            })
            ->implode("\n");
    }
}
