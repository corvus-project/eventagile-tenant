<?php

use App\Enums\EventStatus;
use App\Livewire\Forms\EventForm;
use App\Models\Event;
use Livewire\Component;
use Mary\Traits\Toast;
use Livewire\Attributes\Layout;

new #[Layout('layouts.admin')]  class extends Component
{

    use Toast;
    public $status;

    public $eventLimit = true;

    public EventForm $form;

    public Event $event;

    public function mount(Event $event)
    {
        $this->event = $event;
        $this->populateStatus();
        $this->form->setEvent($event);
    }

    public function save()
    {
        $this->form->save();

        return $this->redirect('/dashboard/events');
    }

    public function populateStatus()
    {
        $this->status = EventStatus::creatingStatus();
    }
};
?>

<x-slot name="title">
    {{ __('Update Event') }}
</x-slot>

<x-slot name="header">
    <h2 class="text-lg font-semibold leading-tight text-gray-800 dark:text-gray-200">
        {{ __('Update Event') }}
    </h2>
</x-slot>

<div class="flex flex-col flex-1 py-6">
    <x-ui.event-form
        action="save"
        :event="$event"
        :eventLimit="$eventLimit"
        :status="$status" />
</div>