<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'position',
        'code',
        'banner_image',
        'target_url',
        'is_banner',
        'category_ids',
        'status',
        'impressions',
        'clicks',
        'device',
        'country_rule',
        'countries',
        'fallback_code',
        'start_date',
        'end_date',
    ];

    protected function casts(): array
    {
        return [
            'is_banner'    => 'boolean',
            'status'       => 'boolean',
            'category_ids' => 'array',
            'countries'    => 'array',
            'impressions'  => 'integer',
            'clicks'       => 'integer',
            'start_date'   => 'datetime',
            'end_date'     => 'datetime',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    public function scopeLive(Builder $query): Builder
    {
        return $query->active()
            ->where(function ($q) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', now());
            });
    }

    public function scopeForPosition(Builder $query, string $position): Builder
    {
        return $query->live()->where('position', $position);
    }

    public function isLive(): bool
    {
        if (!$this->status) return false;
        if ($this->start_date && now()->lt($this->start_date)) return false;
        if ($this->end_date && now()->gt($this->end_date)) return false;
        return true;
    }

    public function ctr(): float
    {
        if ($this->impressions <= 0) return 0.0;
        return round(($this->clicks / $this->impressions) * 100, 2);
    }

    public function trackingUrl(): string
    {
        return url('/ad/click/' . $this->id);
    }

    /**
     * Determine if this ad applies to a specific category ID.
     */
    public function appliesToCategory(?int $categoryId): bool
    {
        if (empty($this->category_ids)) {
            return true;
        }

        if (!$categoryId) {
            return true;
        }

        return in_array($categoryId, $this->category_ids);
    }

    /**
     * Determine if this ad applies to a specific visitor country code.
     */
    public function appliesToCountry(?string $countryCode): bool
    {
        if (empty($this->country_rule) || $this->country_rule === 'all') {
            return true;
        }

        $countryCode = strtoupper(trim($countryCode ?? ''));
        $targetCountries = array_map('strtoupper', (array)($this->countries ?? []));

        if ($this->country_rule === 'include') {
            return in_array($countryCode, $targetCountries, true);
        }

        if ($this->country_rule === 'exclude') {
            return !in_array($countryCode, $targetCountries, true);
        }

        return true;
    }

    /**
     * Human-readable label for country rule.
     */
    public function countryRuleLabel(): string
    {
        if (empty($this->country_rule) || $this->country_rule === 'all') {
            return 'All Countries';
        }

        $count = count($this->countries ?? []);
        $preview = implode(', ', array_slice($this->countries ?? [], 0, 3));
        if ($count > 3) $preview .= ' +' . ($count - 3);

        if ($this->country_rule === 'include') {
            return "Only [{$preview}]";
        }

        return "Exclude [{$preview}]";
    }
}
