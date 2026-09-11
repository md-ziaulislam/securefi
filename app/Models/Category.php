<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Category extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'description',
        'meta_title',
        'meta_description',
        'order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'parent_id' => 'integer',
            'is_active' => 'boolean',
            'order' => 'integer',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'slug', 'parent_id', 'is_active'])
            ->logOnlyDirty();
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('order')->orderBy('name');
    }

    public function subcategories(): HasMany
    {
        return $this->children();
    }

    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeSubcategories($query)
    {
        return $query->whereNotNull('parent_id');
    }

    public function isParent(): bool
    {
        return is_null($this->parent_id);
    }

    public function isSubcategory(): bool
    {
        return !is_null($this->parent_id);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->isSubcategory() ? '— ' . $this->name : $this->name;
    }

    public static function treeList(bool $activeOnly = false)
    {
        $query = static::whereNull('parent_id')->orderBy('order')->orderBy('name');
        if ($activeOnly) {
            $query->where('is_active', true);
        }

        $roots = $query->with(['children' => function ($q) use ($activeOnly) {
            if ($activeOnly) {
                $q->where('is_active', true);
            }
            $q->orderBy('order')->orderBy('name');
        }])->get();

        $list = collect();
        foreach ($roots as $root) {
            $root->depth = 0;
            $list->push($root);

            foreach ($root->children as $child) {
                $child->depth = 1;
                $list->push($child);
            }
        }

        return $list;
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    public function seoMeta(): MorphOne
    {
        return $this->morphOne(SeoMeta::class, 'seoable');
    }
}
