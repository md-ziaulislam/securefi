<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailCampaign extends Model
{
    protected $fillable = [
        'name', 'subject', 'template_id', 'body',
        'status', 'audience', 'total_recipients',
        'sent_count', 'failed_count', 'scheduled_at', 'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'sent_at'      => 'datetime',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, 'template_id');
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'draft'   => 'bg-gray-100 text-gray-700',
            'sending' => 'bg-amber-100 text-amber-700',
            'sent'    => 'bg-emerald-100 text-emerald-700',
            'failed'  => 'bg-rose-100 text-rose-700',
            default   => 'bg-gray-100 text-gray-500',
        };
    }
}
