<?php

use App\Enums\EventStatus;
use App\Exceptions\RateLimiterException;
use App\Livewire\Forms\EventForm;
use App\Models\Tenant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;
use Livewire\Attributes\Layout;

new #[Layout('layouts.admin')]  class extends Component
{
    public Collection $status;

    public $eventLimit = true;

    public EventForm $form;

    public function mount()
    {
        Gate::authorize('create-event');
        $this->populateStatus();
        $user = auth()->user();

        $tenant = $user?->tenant;

        if (RateLimiter::tooManyAttempts('create-event:' . $user->id, $perMinute = 5)) {
            throw new RateLimiterException('You are creating events too quickly. Please wait a moment before trying again.');
        }

        RateLimiter::increment('create-event:' . $user->id);
        $tenant = Tenant::findorFail(tenant('id'));
        $createevet = $tenant->able('create-event');

        if (!$createevet) {
            $this->eventLimit = false;
        }
    }

    public function save()
    {
        $this->form->store();
        return $this->redirect('/dashboard/events');
    }

    public function populateStatus()
    {
        $this->status = EventStatus::creatingStatus();
    }
};
?>

<x-slot name="title">
    {{ __('Create an Event') }}
</x-slot>

<x-slot name="header">
    <h2 class="text-lg font-semibold leading-tight text-gray-800 dark:text-gray-200">
        {{ __('Create Event') }}
    </h2>
</x-slot>

<div class="flex flex-col flex-1 py-6">
    <x-ui.event-form
        action="save"
        :event="null"
        :eventLimit="$eventLimit"
        :status="$status" />
</div>