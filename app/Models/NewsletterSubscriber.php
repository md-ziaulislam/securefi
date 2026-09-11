<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class NewsletterSubscriber extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'email', 'status', 'source',
        'ip_address', 'subscribed_at', 'confirmed_at',
        'unsubscribed_at', 'unsubscribe_token',
    ];

    protected function casts(): array
    {
        return [
            'subscribed_at'    => 'datetime',
            'confirmed_at'     => 'datetime',
            'unsubscribed_at'  => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->unsubscribe_token)) {
                $model->unsubscribe_token = Str::random(40);
            }
            if (empty($model->subscribed_at)) {
                $model->subscribed_at = now();
            }
        });
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['active', 'subscribed']);
    }

    public function scopeUnsubscribed($query)
    {
        return $query->where('status', 'unsubscribed');
    }

    public function unsubscribeUrl(): string
    {
        if (empty($this->unsubscribe_token)) {
            $this->unsubscribe_token = Str::random(40);
            $this->saveQuietly();
        }
        return url('/newsletter/unsubscribe/' . $this->unsubscribe_token);
    }
}

