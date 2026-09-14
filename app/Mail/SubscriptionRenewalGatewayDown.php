<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionRenewalGatewayDown extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        private string $tenantName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Subscription Renewal In Progress — We\'ll Notify You When Complete',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.subscription.renewal-gateway-down',
            with: [
                'tenantName' => $this->tenantName,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
