<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionRenewed extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        private string $tenantName,
        private string $startsAt,
        private string $endsAt,
        private float $amount,
        private string $currency,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Subscription Has Been Renewed',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.subscription.renewed',
            with: [
                'tenantName' => $this->tenantName,
                'startsAt' => $this->startsAt,
                'endsAt' => $this->endsAt,
                'amount' => $this->amount,
                'currency' => $this->currency,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
