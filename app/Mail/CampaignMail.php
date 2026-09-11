<?php

namespace App\Mail;

use App\Models\NewsletterSubscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CampaignMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $emailSubject,
        public string $renderedBody,
        public ?NewsletterSubscriber $subscriber = null,
        public ?string $replyToEmail = null,
        public ?string $recipientName = null,
    ) {}

    public function envelope(): Envelope
    {
        $replyTo = [];
        if (!empty($this->replyToEmail) && filter_var($this->replyToEmail, FILTER_VALIDATE_EMAIL)) {
            $replyTo[] = new Address($this->replyToEmail);
        }

        return new Envelope(
            subject: $this->emailSubject,
            replyTo: $replyTo,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.campaign',
            with: [
                'emailSubject'  => $this->emailSubject,
                'renderedBody'  => $this->renderedBody,
                'subscriber'    => $this->subscriber,
                'recipientName' => $this->recipientName,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
