<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'recipient_email',
        'recipient_name',
        'subject',
        'body_preview',
        'type',
        'status',
        'error_message',
        'sent_by_user_id',
        'campaign_id',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by_user_id');
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(EmailCampaign::class, 'campaign_id');
    }

    public static function logDelivery(
        string $email,
        ?string $name,
        string $subject,
        string $body,
        string $type = 'direct',
        string $status = 'sent',
        ?string $errorMessage = null,
        ?int $campaignId = null
    ): self {
        return self::create([
            'recipient_email'  => $email,
            'recipient_name'   => $name,
            'subject'          => $subject,
            'body_preview'     => mb_substr(strip_tags($body), 0, 250),
            'type'             => $type,
            'status'           => $status,
            'error_message'    => $errorMessage,
            'sent_by_user_id'  => auth()->id(),
            'campaign_id'      => $campaignId,
        ]);
    }
}
