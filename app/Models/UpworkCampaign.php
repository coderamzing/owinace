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
        'bidding_timezone',
        'max_daily_bid',
        'auto_bidding',
        'ai_prompt',
        'ai_cover_letter',
        'ai_instruction',
        'questions_context',
        'matching_critieria',
        'job_do',
        'job_dont',
        'experience',
        'rule_client_avg_spent',
        'rule_client_avghire',
        'rule_max_interviews',
        'rule_job_posted_ago',
        'rule_max_proposal',
        'rule_min_client_rating',
        'member_id',
        'team_id',
        'profile_id',
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
            'max_daily_bid' => 'integer',
            'auto_bidding' => 'boolean',
            'rule_client_avg_spent' => 'decimal:2',
            'rule_client_avghire' => 'decimal:2',
            'rule_max_interviews' => 'integer',
            'rule_job_posted_ago' => 'integer',
            'rule_max_proposal' => 'integer',
            'rule_min_client_rating' => 'integer',
            'ai_cover_letter' => 'boolean',
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

    public function profile(): BelongsTo
    {
        return $this->belongsTo(UpworkProfile::class, 'profile_id');
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

    /**
     * Search keyword from the campaign Upwork jobs search URL (`q` query parameter).
     */
    public function searchQueryTerm(): ?string
    {
        if (blank($this->search_url)) {
            return null;
        }

        $queryString = parse_url($this->search_url, PHP_URL_QUERY);
        if (! is_string($queryString) || $queryString === '') {
            return null;
        }

        parse_str($queryString, $params);
        $term = trim((string) ($params['q'] ?? ''));

        return $term !== '' ? $term : null;
    }

    public function slots(): HasMany
    {
        return $this->hasMany(UpworkCampaignSlot::class, 'campaign_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function ruleRejectionReasonForJob(UpworkJob $job, bool $checkDailyBidLimit = true): ?string
    {
        if ($checkDailyBidLimit && $this->max_daily_bid > 0) {
            $appliedToday = UpworkCampaignJobStat::where('campaign_id', $this->id)
                ->where('is_applied', 1)
                ->where('created_at', '>=', now()->startOfDay())
                ->count();
            if ($appliedToday >= $this->max_daily_bid) {
                return 'Max daily bid reached';
            }
        }

        if ($this->max_connect_per_bid > 0 && $job->connects > $this->max_connect_per_bid) {
            return 'Connects exceed campaign limit';
        }

        if ($this->rule_min_client_rating > 0 && $job->client_rating < $this->rule_min_client_rating) {
            return 'Client rating below campaign minimum';
        }

        if ($this->rule_client_avg_spent > 0 && $job->client_avgspent < $this->rule_client_avg_spent) {
            return 'Client avg. spent below campaign minimum';
        }

        if ($this->rule_client_avghire > 0 && $job->client_hirerate < $this->rule_client_avghire) {
            return 'Client hire rate below campaign minimum';
        }

        if ($this->rule_max_interviews > 0 && $job->interviews > $this->rule_max_interviews) {
            return 'Interviews exceed campaign limit';
        }

        if (isset($job->posted_at)) {
            try {
                $diffMins = \Carbon\Carbon::parse($job->posted_at)->diffInMinutes(now());
                if ($diffMins > $this->rule_job_posted_ago) {
                    return 'Job posted too long ago';
                }
            } catch (\Exception) {
                // Unable to parse posted_at, ignore filter
            }
        }

        if ($this->rule_max_proposal > 0 && $job->proposals > $this->rule_max_proposal) {
            return 'Proposals exceed campaign limit';
        }

        return null;
    }

    public function isWithinClockSlots(?string $nowInTimezone = null): bool
    {
        $this->loadMissing('slots');

        if ($this->slots->isEmpty()) {
            return true;
        }

        $timezone = $this->bidding_timezone ?: 'UTC';

        try {
            $nowInTimezone ??= now($timezone)->format('H:i');
        } catch (\Exception) {
            $nowInTimezone ??= now('UTC')->format('H:i');
        }

        return $this->slots->contains(
            fn (UpworkCampaignSlot $slot): bool => UpworkCampaignSlot::isTimeWithinSlot(
                $nowInTimezone,
                (string) $slot->clock_in,
                (string) $slot->clock_out,
            ),
        );
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

    public function cloneAsCopy(): self
    {
        $this->loadMissing(['linkedPortfolios', 'slots']);

        $clone = $this->replicate();
        $clone->title = $this->title.' (Copy)';
        $clone->is_active = false;
        $clone->save();

        $portfolioIds = $this->linkedPortfolios->pluck('id')->all();
        if ($portfolioIds !== []) {
            $clone->linkedPortfolios()->attach($portfolioIds);
        }

        foreach ($this->slots as $slot) {
            $clone->slots()->create($slot->only(['clock_in', 'clock_out', 'sort_order']));
        }

        return $clone->fresh(['linkedPortfolios', 'slots']);
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
