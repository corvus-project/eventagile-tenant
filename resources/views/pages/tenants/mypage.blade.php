<?php

use App\Models\EventRegistration;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;

new #[Layout('layouts.tenant')]  class extends Component
{
    use WithPagination;

    public function mount() {}

    #[Computed]
    public function eventRegistrations()
    {
        return EventRegistration::where('user_id', auth()->id())->paginate();
    }

    public function update(EventRegistration $eventRegistration)
    {
        $this->authorize('update', $eventRegistration);
        $eventRegistration->status = 'Cancelled';
        $eventRegistration->save();
    }
}
?>
<x-slot name="title">
    {{ tenant('name') }} - My Page
</x-slot>
<x-slot name="header">
    <h2 class="text-3xl font-semibold leading-tight text-gray-800 dark:text-gray-200">

    </h2>
</x-slot>
<div class="pb-5">
    <div class="mx-auto space-y-6">

        <h2 class="text-3xl">Registered events:</h2>

        @if ($this->eventRegistrations->count() < 1)

            <div role="alert" class="alert alert-warning">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0 stroke-current" fill="none" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <span>You have not registered for any events!</span>
    </div>
    @endif
    @foreach($this->eventRegistrations as $eventRegistration)
    <div class="p-4 bg-white rounded-lg shadow mt-8  dark:bg-gray-800 dark:border dark:border-gray-200/10">

        <div class="grid grid-cols-3 gap-4 md:grid-cols-3">
            <div class="col-span-2">
                <a href="{{ route('tenant.event.view', $eventRegistration->event) }}" class="block">
                    <h4 class="text-lg font-semibold">{{ $eventRegistration->event->title }}</h4>
                </a>
                <p class="text-sm text-gray-600">
                    Date: {{ $eventRegistration->event->start_time->format('F j, Y H:i') }}
                </p>
                <p class="mt-2 flex items-center gap-2 overflow-x-auto whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                    <span class="inline-flex items-center"><x-heroicon-o-envelope class="text-primary mr-2 size-5 shrink-0" />Organizer: {{ $eventRegistration->event->organizer }}</span>
                    <span class="inline-flex items-center"><x-heroicon-o-map-pin class="text-primary mr-2 size-5 shrink-0" />Location: {{ $eventRegistration->event->location }}</span>
                    <span class="inline-flex items-center"><x-heroicon-o-users class="text-primary mr-2 size-5 shrink-0" />Capacity: {{ $eventRegistration->event->capacity }}</span>
                </p>
                <blockquote class="mt-2">{{ $eventRegistration->event->description }}</blockquote>
            </div>

            <div class="border border-gray-200 rounded-lg p-4 flex items-center justify-center">
                <div class="mt-4">
                    @if($eventRegistration->status->value === 'Confirmed')
                    <span class="px-3 py-1 text-sm font-medium text-green-800 bg-green-100 rounded-full">{{ $eventRegistration->status }}</span>
                    @elseif($eventRegistration->status->value === 'Cancelled')
                    <span class="px-3 py-1 text-sm font-medium text-red-800 bg-red-100 rounded-full">{{ $eventRegistration->status }}</span>
                    @elseif($eventRegistration->status->value === 'Waitlisted')
                    <span class="px-3 py-1 text-sm font-medium text-yellow-800 bg-yellow-100 rounded-full">{{ $eventRegistration->status }}</span>
                    @else
                    <span class="px-3 py-1 text-sm font-medium text-gray-800 bg-gray-100 rounded-full">{{ $eventRegistration->status }}</span>
                    @endif
                    <span class="block mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Registered at {{ $eventRegistration->created_at->format('F j, Y H:i') }}
                    </span>

                    @if ($eventRegistration->status->value !== 'Cancelled')
                    <button wire:confirm="Are you sure you want to cancel this registration?" wire:click="update({{ $eventRegistration->id }})" class="mt-2 px-4 py-2 bg-indigo-600 text-white rounded-md text-xs hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Cancel Registration
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endforeach

    <div class="mt-4 flex justify-end-safe gap-1">
        {{ $this->eventRegistrations->links() }}
    </div>
</div>
</div>