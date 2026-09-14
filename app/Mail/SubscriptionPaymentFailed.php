<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionPaymentFailed extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        private string $tenantName,
        private string $failureReason,
        private string $supportEmail,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Action Required: Your Subscription Payment Failed',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.subscription.payment-failed',
            with: [
                'tenantName' => $this->tenantName,
                'failureReason' => $this->failureReason,
                'supportEmail' => $this->supportEmail,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
