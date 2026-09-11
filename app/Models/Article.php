<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Article extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'excerpt',
        'featured_image',
        'category_id',
        'author_id',
        'status',
        'country_rule',
        'countries',
        'restriction_fallback_message',
        'master_article_id',
        'variant_country',
        'reading_time',
        'view_count',
        'is_featured',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_featured'   => 'boolean',
            'published_at'  => 'datetime',
            'reading_time'  => 'integer',
            'view_count'    => 'integer',
            'countries'     => 'array',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'slug', 'status', 'category_id', 'is_featured'])
            ->logOnlyDirty();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function masterArticle(): BelongsTo
    {
        return $this->belongsTo(Article::class, 'master_article_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(Article::class, 'master_article_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'article_tag');
    }

    public function seoMeta(): MorphOne
    {
        return $this->morphOne(SeoMeta::class, 'seoable');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function approvedComments(): HasMany
    {
        return $this->hasMany(Comment::class)
            ->where('status', 'approved')
            ->whereNull('parent_id')
            ->with(['replies.user', 'user'])
            ->latest();
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->published()->where('is_featured', true);
    }

    /**
     * Calculate reading time in minutes based on content word count.
     */
    public static function calculateReadingTime(string $content): int
    {
        $wordCount = str_word_count(strip_tags($content));
        return (int) max(1, ceil($wordCount / 200));
    }

    /**
     * Compute comprehensive Content Performance Score (0-100).
     */
    public function getPerformanceScoreAttribute(): int
    {
        $score = 0;

        // 1. Views Volume (max 35)
        $score += (int) min(35, ($this->view_count / 50) * 35);

        // 2. SEO Health (max 25)
        if ($this->relationLoaded('seoMeta') || $this->seoMeta()->exists()) {
            $meta = $this->seoMeta;
            if (!empty($meta?->meta_title)) $score += 10;
            if (!empty($meta?->meta_description)) $score += 10;
            if (!empty($meta?->og_image)) $score += 5;
        }

        // 3. Monetization & Affiliate Link Presence (max 20)
        if (str_contains($this->content, '[affiliate_') || str_contains($this->content, '/go/')) {
            $score += 20;
        }

        // 4. Content Depth & Asset Richness (max 20)
        if ($this->reading_time >= 3) $score += 10;
        if (!empty($this->featured_image)) $score += 10;

        return min(100, max(0, $score));
    }

    /**
     * Rating label for performance score.
     */
    public function getPerformanceLabelAttribute(): string
    {
        $score = $this->performance_score;
        if ($score >= 80) return 'Exceptional';
        if ($score >= 60) return 'High Performing';
        if ($score >= 40) return 'Moderate';
        return 'Needs Polish';
    }

    /**
     * Determine if article is accessible for a visitor from a specific country.
     */
    public function isAccessibleInCountry(?string $countryCode = null): bool
    {
        $rule = $this->country_rule ?? 'all';
        if ($rule === 'all') {
            return true;
        }

        $countryCode = strtoupper(trim($countryCode ?: \App\Services\GeoIpService::getCountryCode()));
        $targetCountries = array_map('strtoupper', (array) ($this->countries ?? []));

        if ($rule === 'include') {
            return in_array($countryCode, $targetCountries, true);
        }

        if ($rule === 'exclude') {
            return !in_array($countryCode, $targetCountries, true);
        }

        return true;
    }

    /**
     * Find a country-specific localized variant of this article.
     */
    public function getVariantForCountry(?string $countryCode = null): ?Article
    {
        $countryCode = strtoupper(trim($countryCode ?: \App\Services\GeoIpService::getCountryCode()));
        
        // If this article itself is a variant, check the master article
        $master = $this->master_article_id ? $this->masterArticle : $this;
        if (!$master) {
            return null;
        }

        // If master matches target country
        if (strtoupper((string)$master->variant_country) === $countryCode) {
            return $master;
        }

        // Look for variant among siblings
        return $master->variants()
            ->published()
            ->where('variant_country', $countryCode)
            ->first();
    }

    /**
     * Retrieve all sibling variants including master for hreflang tags.
     */
    public function allVariants(): \Illuminate\Database\Eloquent\Collection
    {
        $master = $this->master_article_id ? $this->masterArticle : $this;
        if (!$master) {
            return new \Illuminate\Database\Eloquent\Collection([$this]);
        }

        return $master->variants()->published()->get()->prepend($master);
    }

    /**
     * Human-readable country rule summary.
     */
    public function countryRuleLabel(): string
    {
        $rule = $this->country_rule ?? 'all';
        if ($rule === 'all') {
            return 'All Countries';
        }

        $count = count($this->countries ?? []);
        $preview = implode(', ', array_slice($this->countries ?? [], 0, 3));
        if ($count > 3) {
            $preview .= ' +' . ($count - 3);
        }

        return ($rule === 'include' ? 'Only [' : 'Blocked in [') . $preview . ']';
    }
}
