<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class PaymentGatewayDown extends Notification
{
    use Queueable;

    public function __construct(
        private string $tenantName,
        private string $message,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Payment Gateway Temporarily Unavailable')
            ->greeting('Hi ' . $this->tenantName . ',')
            ->line($this->message)
            ->line('Your subscription access is not affected. We will automatically retry the payment.')
            ->action('Contact Support', 'mailto:support@example.com')
            ->salutation('Regards, The Team');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'payment_gateway_down',
            'tenant_name' => $this->tenantName,
            'message' => $this->message,
            'created_at' => now(),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type' => 'payment_gateway_down',
            'tenant_name' => $this->tenantName,
            'message' => $this->message,
        ]);
    }
}
