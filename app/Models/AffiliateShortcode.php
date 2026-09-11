<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AffiliateShortcode extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'type',
        'product_ids',
        'shortcode',
    ];

    protected function casts(): array
    {
        return [
            'product_ids' => 'array',
        ];
    }

    /**
     * Get products attached to this shortcode.
     */
    public function getProductsAttribute()
    {
        return AffiliateProduct::whereIn('id', $this->product_ids ?? [])->get();
    }
}
