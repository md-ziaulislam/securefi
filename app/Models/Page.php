<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Page extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'template',
        'status',
        'country_rule',
        'countries',
        'restriction_fallback_message',
        'show_in_menu',
        'show_in_footer',
        'menu_order',
        'meta_title',
        'meta_description',
        'og_image',
    ];

    protected function casts(): array
    {
        return [
            'show_in_menu'   => 'boolean',
            'show_in_footer' => 'boolean',
            'menu_order'     => 'integer',
            'countries'      => 'array',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'slug', 'status', 'show_in_menu', 'show_in_footer'])
            ->logOnlyDirty();
    }

    public function seoMeta(): MorphOne
    {
        return $this->morphOne(SeoMeta::class, 'seoable');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function scopeInMenu(Builder $query): Builder
    {
        return $query->published()->where('show_in_menu', true)->orderBy('menu_order');
    }

    public function scopeInFooter(Builder $query): Builder
    {
        return $query->published()->where('show_in_footer', true)->orderBy('menu_order');
    }

    /**
     * Check if page is accessible in the given country.
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
}
