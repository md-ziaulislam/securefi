<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitorLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'ip_address',
        'session_id',
        'country',
        'city',
        'device_type',
        'os',
        'browser',
        'referrer_source',
        'current_page',
        'is_bot',
        'bot_name',
        'is_returning',
        'last_seen_at',
    ];

    protected function casts(): array
    {
        return [
            'is_bot' => 'boolean',
            'is_returning' => 'boolean',
            'last_seen_at' => 'datetime',
        ];
    }

    public function scopeActiveNow(Builder $query, int $minutes = 5): Builder
    {
        return $query->where('last_seen_at', '>=', now()->subMinutes($minutes));
    }

    public function scopeHumans(Builder $query): Builder
    {
        return $query->where('is_bot', false);
    }
}
