<?php

use App\Models\Event;
use App\Models\EventRegistration;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\{Computed, Title, Layout};
use Livewire\Component;
use Mary\Traits\Toast;
use Livewire\WithPagination;

new #[Layout('layouts.admin')] class extends Component {

    use Toast;
    use WithPagination;

    public int $perPage = 10;
    public  Event $event;
    public string $search = '';
    public array $sortBy = ['column' => 'registered_at', 'direction' => 'desc'];

    private const SORTABLE_COLUMNS = ['name', 'registered_at', 'status'];

    public function mount(Event $event)
    {
        Gate::authorize('view-event', $event);
        $this->event = $event;
    }

    public function sortByColumn(string $column): void
    {
        if (! in_array($column, self::SORTABLE_COLUMNS, true)) {
            return;
        }

        $this->sortBy = $this->sortBy['column'] === $column
            ? ['column' => $column, 'direction' => $this->sortBy['direction'] === 'asc' ? 'desc' : 'asc']
            : ['column' => $column, 'direction' => 'asc'];

        $this->resetPage();
    }


    #[Computed()]
    public function registrations()
    {
        return EventRegistration::query()
            ->with('user') // Eager load the related user
            ->whereBelongsTo($this->event)
            ->orderBy($this->sortBy['column'], $this->sortBy['direction'])
            ->paginate($this->perPage);
    }
};
?>


<x-slot name="title">
    Registrations for Event: {{ $event->title }}
</x-slot>
<x-slot name="header">
    <h2 class="text-lg font-semibold leading-tight text-gray-800 dark:text-gray-200">
        Registrations for Event: {{ $event->title }}
    </h2>
</x-slot>
<div class="flex flex-col flex-1">
    <div class="flex flex-col  flex-1 pb-5 mx-auto  w-full">
        <div class="relative flex-1 w-full ">


            <div class="pb-5">
                <div class="mx-auto space-y-6">
                    <x-card shadow>


                        <div class="flex justify-end mb-4">
                            <x-ui.text-link href="{{ route('dashboard.events.update', ['event' => $event->slug]) }}" class="btn-ghost btn-sm text-sm text-blue-600 p-2">
                                Update Event
                            </x-ui.text-link>

                            <x-ui.text-link href="{{ route('dashboard.events.registrations.export', ['event' => $event->slug]) }}" class="btn-ghost btn-sm text-sm text-blue-600 p-2">
                                Export Registration List
                            </x-ui.text-link>

                        </div>
                        @if($this->registrations->isEmpty())
                        <div class="text-center py-8 text-gray-500">
                            No registrations found for this event.
                        </div>
                        @else
                        <div class="space-y-4">
                            <div class="space-y-4 sm:hidden">
                                @foreach($this->registrations as $registration)
                                <div class="rounded-lg border border-gray-200 bg-white px-4 py-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                                    <div class="flex items-start justify-between">
                                        <div>
                                            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">Registration #{{ $registration->id }}</div>
                                            <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">User: {{ $registration->user->name }}</div>
                                        </div>
                                        <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-blue-700 dark:bg-blue-900 dark:text-blue-200">
                                            {{ $registration->status ?? 'Unknown' }}
                                        </span>
                                    </div>
                                    <div class="mt-3 text-sm text-gray-700 dark:text-gray-300">
                                        Registered At: {{ optional($registration->registered_at)->format('F j, Y H:i') ?? 'N/A' }}
                                    </div>
                                    <div class="mt-4 text-right">
                                        <a href="{{ route('dashboard.registration.view', $registration->id) }}" class="btn-ghost btn-sm text-sm text-blue-600 p-2">
                                            View Details
                                        </a>
                                    </div>
                                </div>
                                @endforeach
                            </div>

                            <div class="overflow-x-auto hidden sm:block">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-900">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">#</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">User</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Status</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Registered At</th>
                                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                                        @foreach($this->registrations as $registration)
                                        <tr>
                                            <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-300">{{ $registration->id }}</td>
                                            <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-300">{{ $registration->user->name }}</td>
                                            <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-300">{{ $registration->status ?? 'Unknown' }}</td>
                                            <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-300">{{ optional($registration->registered_at)->format('F j, Y H:i') ?? 'N/A' }}</td>
                                            <td class="px-4 py-4 text-right">
                                                <a href="{{ route('dashboard.registration.view', $registration->id) }}" class="btn-ghost btn-sm text-sm text-blue-600 p-2">
                                                    View Details
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>


                        <div class="mt-6">
                            {{ $this->registrations->links() }}
                        </div>
                        @endif

                    </x-card>
                </div>
            </div>
        </div>
    </div>
</div>