<?php

namespace App\Livewire\Forms;

use App\Mail\NewEventRegistration;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Rules\CapacityLimit;
use App\Services\SubscriptionService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Livewire\Form;

class EventRegistrationForm extends Form
{
    public Event $event;

    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public ?int $user_id = null;

    public ?string $registration_code = null;

    public function rules(): array
    {
        return [
            'user_id' => [
                'nullable',
                new CapacityLimit,
                function ($attribute, $value, $fail) {
                    if ($value && EventRegistration::where('event_id', $this->event->id)->where('user_id', auth()->user()->id)->exists()) {
                        Log::warning('Duplicate registration attempt', ['event_id' => $this->event->id, 'user_id' => $this->user_id]);
                    }
                },
            ],
            'registration_code' => [
                'nullable',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    if (! $this->event->is_public && $value !== $this->event->registration_code) {
                        $fail('The registration code is invalid.');
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please enter your name.',
            'email.required' => 'Please enter your email address.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email has already been registered for this event.',
            'phone.required' => 'Please enter your phone number.',
        ];
    }

    public function setEvent(Event $event): void
    {
        $this->event = $event;
    }

    public function store(): void
    {
        $this->validate();

        $tenant = tenant();
        if (! $tenant) {
            throw ValidationException::withMessages(['name' => 'Unable to resolve the current tenant.']);
        }

        $user = auth()->user();
        if (EventRegistration::where('event_id', $this->event->id)->where('user_id', $this->user_id)->exists()) {
            $this->addError('user_id', 'You have already registered for this event.');
            Log::warning('Duplicate registration attempt', ['event_id' => $this->event->id, 'user_id' => $this->user_id]);

            return;
        }

        Log::info('Storing event registration', ['event_id' => $this->event->id, 'user_id' => $user->id]);
        $this->event->registrations()->create([
            'user_id' => $user->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'is_attending' => true,
            'registered_at' => Carbon::now(),
        ]);

        if (SubscriptionService::canSendEmails($tenant)) {
            Mail::to($this->email)->queue(new NewEventRegistration($this->event, [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
            ]));
        }

        $this->reset(['registration_code']);

        session()->flash('register-status', 'Thank you for registering for the event!');
    }
}
