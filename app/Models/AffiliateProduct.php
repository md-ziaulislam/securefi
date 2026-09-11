<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AffiliateProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'image',
        'price',
        'rating',
        'badge_text',
        'button_text',
        'description',
        'affiliate_url',
        'country_links',
        'fallback_affiliate_url',
        'source_site',
        'category_id',
        'rel_type',
        'click_count',
        'status',
        'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'status'        => 'boolean',
            'is_featured'   => 'boolean',
            'click_count'   => 'integer',
            'rating'        => 'float',
            'country_links' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function ($product) {
            if (empty($product->slug) && !empty($product->name)) {
                $slug = Str::slug($product->name);
                $count = static::where('slug', $slug)->where('id', '!=', $product->id ?? 0)->count();
                $product->slug = $count > 0 ? "{$slug}-" . Str::random(4) : $slug;
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->active()->where('is_featured', true);
    }

    public function cloakedUrl(): string
    {
        return url('/go/' . ($this->slug ?: $this->id));
    }

    /**
     * Resolve target URL for a given country code (or the detected visitor country).
     */
    public function getTargetUrlForCountry(?string $countryCode = null): string
    {
        $countryCode = strtoupper(trim($countryCode ?: \App\Services\GeoIpService::getCountryCode()));

        // Check if there is a specific URL configured for this country code
        if (!empty($this->country_links) && is_array($this->country_links) && !empty($this->country_links[$countryCode])) {
            return $this->country_links[$countryCode];
        }

        // Check if fallback affiliate URL exists
        if (!empty($this->fallback_affiliate_url)) {
            return $this->fallback_affiliate_url;
        }

        return $this->affiliate_url;
    }

    /**
     * Count active country overrides
     */
    public function countryLinkCount(): int
    {
        if (empty($this->country_links) || !is_array($this->country_links)) {
            return 0;
        }
        return count(array_filter($this->country_links, fn($url) => !empty(trim($url))));
    }
}
