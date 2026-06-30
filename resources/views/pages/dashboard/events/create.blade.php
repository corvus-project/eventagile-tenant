<?php

use App\Enums\EventStatus;
use App\Exceptions\RateLimiterException;
use App\Livewire\Forms\EventForm;
use App\Models\Tenant;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;
use Livewire\Attributes\Layout;

new #[Layout('layouts.admin')]  class extends Component
{
    public $status;

    public $eventLimit = true;

    public EventForm $form;

    public function mount()
    {
        Gate::authorize('create-event');
        $this->populateStatus();
        $user = auth()->user();

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

<div class="flex flex-col flex-1">
    <div class="flex flex-col  flex-1 pb-5 mx-auto  w-full">
        <div class="relative flex-1 w-full ">
            <div class="flex justify-between items-center w-full bg-pink- overflow-hidden border border-dashed bg-gradient-to-br from-white to-zinc-50 rounded-lg border-zinc-200 dark:border-gray-700 dark:from-gray-950 dark:via-gray-900 dark:to-gray-800">
                <div class="flex relative flex-col   h-full w-full">


                    <section
                        class="p-4 bg-white shadow sm:p-8 dark:bg-gray-800 sm:rounded-lg dark:bg-gray-900/50 dark:border dark:border-gray-200/10">
                        <div class="w-full max-w-2xl mx-auto">

                            @if ($eventLimit)

                            <x-form wire:submit="save" class="mt-6 space-y-6">

                                <x-input label="Title" wire:model="form.title" />
                                <x-textarea label="Description" wire:model="form.description" rows="5" />

                                <x-datetime label="Registration Deadline" wire:model="form.registration_deadline" type="datetime-local" />

                                <x-datetime label="Start Time" wire:model="form.start_time" type="datetime-local" />

                                <x-input label="Location" wire:model="form.location" />
                                <x-input label="Organizer" wire:model="form.organizer" />
                                <x-input label="Capacity" wire:model="form.capacity" />
                                <x-checkbox label="Public" wire:model="form.is_public" hint="Can everyone register this event?" />

                                <x-select label="Status" wire:model="form.status" :options="$status" />


                                <x-slot:actions>
                                    <x-button label="Create" class="btn-seconday" type="primary" submit="true" spinner="save" />
                                </x-slot:actions>
                            </x-form>
                            @else
                            <div class="p-4 text-sm text-yellow-800 bg-yellow-50 rounded-lg dark:bg-gray-800/50 dark:text-yellow-300" role="alert">
                                <span class="font-medium">You have reached the maximum number of events you can create. Please delete an existing event or contact support to increase your limit.</span>
                            </div>
                            @endif
                        </div>
                    </section>

                </div>
            </div>
        </div>
    </div>
</div>