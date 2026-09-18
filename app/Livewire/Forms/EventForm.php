<?php

namespace App\Livewire\Forms;

use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\Tenant;
use App\Services\SubscriptionService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Form;

class EventForm extends Form
{
    public ?Event $event;

    #[Validate('required|string|max:255')]
    public string $title;

    public $description;

    public $full_description;

    #[Validate('required|date|after:today')]
    public $start_time;

    #[Validate('nullable|date|after:today|before:start_time')]
    public $registration_deadline;

    #[Validate('required|string|max:255')]
    public string $location;

    #[Validate('required|string|max:255')]
    public string $organizer;

    #[Validate('required|integer|min:1')]
    public int $capacity;

    #[Validate('boolean')]
    public bool $is_public = false;

    #[Validate('required')]
    public string $status;

    public function setEvent(Event $event): void
    {
        $this->event = $event;

        $this->title = $event->title;
        $this->description = $event->description;
        $this->full_description = $event->full_description;
        $this->start_time = $event->start_time->format('Y-m-d H:i'); // '2025-10-12 13:10'; //$event->start_time->format('dd/mm/Y h:i'); // Ensure the format is compatible with datetime-local input
        $this->registration_deadline = $event->registration_deadline?->format('Y-m-d H:i');
        $this->location = $event->location;
        $this->organizer = $event->organizer;
        $this->capacity = $event->capacity;
        $this->is_public = $event->is_public;
        $this->status = $event->status->name; // Default status, can be changed based on your logic
    }

    public function store(): void
    {
        Gate::authorize('create-event');

        $this->validate();

        $tenant = tenant();
        if (! $tenant instanceof Tenant) {
            throw ValidationException::withMessages([
                'title' => 'Unable to resolve the current tenant.',
            ]);
        }

        $result = SubscriptionService::canWithReason($tenant, 'create-event');

        if (! $result['allowed']) {
            throw ValidationException::withMessages([
                'title' => $result['reason'],
            ]);
        }

        $status = EventStatus::fromKey($this->status) ?? EventStatus::DRAFT;
        $user = auth()->user();
        Event::create([
            'title' => $this->title,
            'description' => $this->description,
            'full_description' => $this->full_description,
            'start_time' => $this->start_time,
            'registration_deadline' => $this->registration_deadline,
            'location' => $this->location,
            'organizer' => $this->organizer,
            'capacity' => $this->capacity,
            'is_public' => ($this->is_public) ? true : false,
            'status' => $status,
            'organizer_id' => $user->id,
        ]);
    }

    public function save(): void
    {
        if (! $this->event) {
            throw ValidationException::withMessages([
                'title' => 'Unable to resolve the event being updated.',
            ]);
        }

        Gate::authorize('update-event', $this->event);

        $this->validate();

        $status = EventStatus::fromName($this->status) ?? EventStatus::DRAFT;

        $this->event->update([
            'title' => $this->title,
            'description' => $this->description,
            'full_description' => $this->full_description,
            'start_time' => $this->start_time,
            'registration_deadline' => $this->registration_deadline,
            'location' => $this->location,
            'organizer' => $this->organizer,
            'capacity' => $this->capacity,
            'is_public' => ($this->is_public) ? true : false,
            'status' => $status->value,
        ]);
    }
}
