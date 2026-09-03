<?php

use App\Enums\RegistrationStatus;
use App\Mail\EventRegistrationUpdated;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Tenant;
use App\Services\SubscriptionService;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;
use Mary\Traits\Toast;

use Livewire\Attributes\{Computed, Title, Layout};


new #[Layout('layouts.admin')] class extends Component {

    use Toast;

    public EventRegistration $eventRegistration;
    public Event $event;
    public string $status;
    public array $status_options = [];
    public bool $eventRegistrationModal = false;

    public function mount(EventRegistration $eventRegistration)
    {
        $this->eventRegistration = $eventRegistration;
        $this->event = Event::findOrFail($eventRegistration->event_id);
        $this->status_options = RegistrationStatus::toCollection()->toArray();
        $this->status = $eventRegistration->status->name;
    }

    public function with(): array
    {
        return [
            'eventRegistration' => $this->eventRegistration,
            'event' => $this->event
        ];
    }

    public function save()
    {
        $this->validate([
            'status' => 'required',
        ]);

        $this->eventRegistration->status = RegistrationStatus::fromName($this->status);
        $this->eventRegistration->save();

        $this->success('Registration updated successfully!');
        $this->eventRegistrationModal = false;

        $tenant = tenant();
        if ($tenant instanceof Tenant && SubscriptionService::canSendEmails($tenant)) {
            Mail::to($this->eventRegistration->email)->queue(new EventRegistrationUpdated($this->event, [
                'name' => $this->eventRegistration->user->name,
                'email' => $this->eventRegistration->user->email,
                'phone' => $this->eventRegistration->user->phone,
                'status' => $this->eventRegistration->status->value,
            ]));
        }
    }
}
?>

<x-slot name="title">
    {{ __('Registration Details') }}
</x-slot>

<x-slot name="header">
    <h2 class="text-lg font-semibold leading-tight text-gray-800 dark:text-gray-200">
        {{ __('Registration Details') }}
    </h2>
</x-slot>

<div class="flex flex-col flex-1">
    <div class="flex flex-col  flex-1 pb-5 mx-auto  w-full">
        <div class="relative flex-1 w-full ">


            <x-modal wire:model="eventRegistrationModal" title="Update Registration Status" subtitle="Update Registration Status">
                <x-form no-separator wire:submit="save">
                    <x-select label="Status" wire:model="status" :options="$status_options" />

                    <x-slot:actions>
                        <x-button label="Save" class="btn-ghost  btn-sm border-1" type="primary" submit="true" spinner="save" />
                    </x-slot:actions>
                </x-form>
            </x-modal>


            <div class="shadow p-4 dark:bg-gray-800 sm:rounded-lg  bg-slate-50  rounded-lg dark:bg-gray-900/50 dark:border dark:border-gray-200/10">

                <div class="flex justify-end mb-4">
                    <x-button label="Update the registration" @click="$wire.eventRegistrationModal = true" class="btn-ghost btn-sm border-1 border-amber-800 text-red-600 p-2" />
                </div>

                <div class="flex flex-col gap-4 md:flex-row md:items-stretch md:justify-between">
                    <div class="shadow rounded-lg p-6 m-2 flex-1">
                        <h3 class="text-md font-semibold text-gray-800 dark:text-gray-200 mb-4">Event Details</h3>
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-6">
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Event Title</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $event->title }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Event Date</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $event->start_time->format('F j, Y') }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Location</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $event->location }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $event->status }}</dd>
                            </div>
                        </dl>
                    </div>

                    <div class="shadow rounded-lg p-6 m-2 flex-1">
                        <h3 class="text-md font-semibold text-gray-800 dark:text-gray-200 mb-4">Registration Details</h3>
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-6">
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Name</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $eventRegistration->user->name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $eventRegistration->user->email }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Registered At</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $eventRegistration->created_at->format('F j, Y H:i') }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $eventRegistration->status->value }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>