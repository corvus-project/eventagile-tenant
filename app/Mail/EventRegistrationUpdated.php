<?php

namespace App\Mail;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EventRegistrationUpdated extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
   public function __construct(private Event $event, private array $registration = [])
    {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Event Registration Updated',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mail.registration.update-event-registration',
            with: [
                'name' => $this->registration['name'] ?? '',
                'email' => $this->registration['email'] ?? '',
                'phone' => $this->registration['phone'] ?? '',
                'status' => $this->registration['status'] ?? '',
                'title' => $this->event->title,
                'start_time' => $this->event->start_time->format('F j, Y, g:i A'),
                'location' => $this->event->location,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
