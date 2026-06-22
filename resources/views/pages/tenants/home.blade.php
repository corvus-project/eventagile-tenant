<?php

use App\Models\Event;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;

new #[Layout('layouts.tenant')]  class extends Component
{
    use WithPagination;

    public function mount()
    {
        if (!tenant()) {
            Log::error('No tenant context found when fetching events.');
            $this->redirect(route('tenant.notenant'));
        }
    }

    #[Computed]
    public function events()
    {
        try {
            return Event::paginate();
        } catch (QueryException $ex) {
            Log::error('Tenant Id:' . tenant('id') .  $ex->getMessage());
            abort(500, 'Tenant does not exist or database is not migrated.');
        } catch (Exception $ex) {
            Log::error('Tenant Id:' . tenant('id') .  $ex->getMessage());
            abort(500, 'Tenant does not exist or database is not migrated!');
        }
    }
}
?>
<x-slot name="title">
    {{ tenant('name') }} - Home
</x-slot>
<x-slot name="header">
    <h2 class="text-3xl font-semibold leading-tight text-gray-800 dark:text-gray-200">

    </h2>
</x-slot>
<div class="pb-5">
    <div class="mx-auto space-y-6">

        <h2 class="text-3xl">Welcome to {{ tenant('name') }}</h2>

        @if ($this->events->count() < 1)

            <div role="alert" class="alert alert-warning">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0 stroke-current" fill="none" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <span>There is no active events!</span>
    </div>

    @endif
    @foreach($this->events as $event)
    <div class="p-4 bg-white rounded-lg shadow mt-8  dark:bg-gray-800 dark:border dark:border-gray-200/10">

        <a href="{{ route('tenant.event.view', $event) }}" class="block">
            <h4 class="text-lg font-semibold">{{ $event->title }}</h4>
        </a>
        <p class="text-sm text-gray-600">
            Date: {{ $event->start_time->format('F j, Y H:i') }}
            Please register until {{ $event->registration_deadline ? $event->registration_deadline->format('F j, Y H:i') : 'N/A' }}.
        </p>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            <x-icon name="o-envelope" /> Organizer: {{ $event->organizer }}
            <x-icon name="o-map-pin" /> Location: {{ $event->location }}
            <x-icon name="o-users" /> Capacity: {{ $event->capacity }}
        </p>
        <blockquote class="mt-2">{{ $event->description }}</blockquote>
    </div>
    @endforeach

    <div class="mt-4 flex justify-end-safe gap-1">
        {{ $this->events->links() }}
    </div>
</div>
</div>