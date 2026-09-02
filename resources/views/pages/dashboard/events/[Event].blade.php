<?php

use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.admin')] class extends  Component {

    public $event;

    public function mount($event)
    {
        $this->event = \App\Models\Event::where('slug', $event)->firstOrFail();
    }
};
?>



<x-slot name="title">
    {{ __('Event Detail: ') . $event->title }}
</x-slot>

<x-slot name="header">
    <h2 class="text-lg font-semibold leading-tight text-gray-800 dark:text-gray-200">
        {{ __('Event Detail: ') . $event->title }}
    </h2>
</x-slot>


<div class="flex flex-col flex-1">
    <div class="flex flex-col  flex-1 pb-5 mx-auto  w-full">
        <div class="relative flex-1 w-full ">
            <div class="flex justify-end mb-4">
                @can('view-event', $this->event)
                <x-ui.text-link href="{{ route('events.registrations.show', ['event' => $this->event->slug]) }}" class="btn-ghost btn-sm text-red-600 p-2">
                    Registrations
                </x-ui.text-link>

                <x-ui.text-link href="{{ route('events.registrations.export', ['event' => $this->event->slug]) }}" class="btn-ghost btn-sm text-red-600 p-2">
                    Export Registration List
                </x-ui.text-link>
                @endcan
                @can('update-event', $this->event)
                <x-ui.text-link href="{{ route('events.update', ['event' => $this->event->slug]) }}" class="btn-ghost btn-sm text-red-600 p-2">
                    Update Event
                </x-ui.text-link>
                @endcan
            </div>

            <div class="bg-white dark:bg-gray-800 shadow rounded p-6">
                <div class="mb-4">
                    <strong>{{ __('Title:') }}</strong> {{ $this->event->title }}
                </div>
                <div class="mb-4">
                    <strong>{{ __('Date:') }}</strong> {{ $this->event->start_time->format('F j, Y') }}
                </div>
                <div class="mb-4">
                    <strong>{{ __('Registration Ends At:') }}</strong> {{ $this->event->registration_deadline?->format('F j, Y') }}
                </div>
                <div class="mb-4">
                    <strong>{{ __('Location:') }}</strong> {{ $this->event->location }}
                </div>
                <div class="mb-4">
                    <strong>{{ __('Organizer:') }}</strong> {{ $this->event->organizer }}
                </div>
                <div class="mb-4">
                    <strong>{{ __('Status:') }}</strong> {{ $this->event->status->value }}
                </div>
                <div class="mb-4">
                    <strong>{{ __('Visiblity:') }}</strong> {{ $this->event->public_status }}
                    <span class="bg-slate-200 p-2 font-italic">{{ !$this->event->is_public ? 'Registration code: ' .$this->event->registration_code : 'N/A' }}</span>
                </div>
                <div class="mb-4">
                    <strong>{{ __('Description:') }}</strong>
                    <p>{{ $this->event->description }}</p>
                </div>


            </div>
        </div>
    </div>

</div>