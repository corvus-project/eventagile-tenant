<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class PaymentPermanentlyFailed extends Notification
{
    use Queueable;

    public function __construct(
        private string $tenantName,
        private string $failureReason,
        private string $subscriptionId,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Payment Failed — Action Required')
            ->greeting('Hi ' . $this->tenantName . ',')
            ->line('Your subscription payment has permanently failed.')
            ->line('Reason: ' . $this->failureReason)
            ->line('Please update your payment method or contact support to restore your subscription.')
            ->action('Contact Support', 'mailto:support@example.com')
            ->salutation('Regards, The Team');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'payment_permanently_failed',
            'tenant_name' => $this->tenantName,
            'failure_reason' => $this->failureReason,
            'subscription_id' => $this->subscriptionId,
            'created_at' => now(),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type' => 'payment_permanently_failed',
            'tenant_name' => $this->tenantName,
            'failure_reason' => $this->failureReason,
            'subscription_id' => $this->subscriptionId,
        ]);
    }
}
