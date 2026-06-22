<?php

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\{Computed, Title, Layout};
use Livewire\Component;
use Mary\Traits\Toast;
use Livewire\WithPagination;

new #[Layout('layouts.admin')] class extends Component {

    use Toast;
    use WithPagination;

    public int $perPage = 10;
    public  User $user;
    public string $search = '';
    public array $sortBy = ['column' => 'name', 'direction' => 'desc'];

    public function mount(User $user)
    {
        $this->user = $user;
    }


    #[Computed()]
    public function registrations()
    {
        return EventRegistration::query()
            ->with('event') // Eager load the related event 
            ->where('user_id', $this->user->id)
            ->orderBy($this->sortBy['column'], $this->sortBy['direction'])
            ->paginate($this->perPage);
    }
};
?>


<x-slot name="title">
    Registrations for User: {{ $user->name }}
</x-slot>
<x-slot name="header">
    <h2 class="text-lg font-semibold leading-tight text-gray-800 dark:text-gray-200">
        Registrations for User: {{ $user->name }}
    </h2>
</x-slot>
<div class="flex flex-col flex-1">
    <div class="flex flex-col  flex-1 pb-5 mx-auto  w-full">
        <div class="relative flex-1 w-full ">


            <div class="pb-5">
                <div class="mx-auto space-y-6">
                    <x-card shadow>
                        <div class="p-6">
                            @if($this->registrations->isEmpty())
                            <div class="text-center py-8 text-gray-500">
                                No registrations found for this event.
                            </div>
                            @else
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-900">
                                        <tr>

                                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Event</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Event Date</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Status</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Registered At</th>
                                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                                        @foreach($this->registrations as $registration)
                                        <tr>
                                            <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-300">{{ $registration->event->title }}</td>
                                            <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-300">{{ $registration->event->start_time->format('F j, Y H:i') }}</td>
                                            <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-300">{{ $registration->status ?? 'Unknown' }}</td>
                                            <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-300">{{ optional($registration->registered_at)->format('F j, Y H:i') ?? 'N/A' }}</td>
                                            <td class="px-4 py-4 text-right">
                                                <a href="{{ route('dashboard.events.registrations.show', ['event' => $registration->event]) }}" class="btn-ghost btn-sm text-sm text-blue-600 p-2">
                                                    All Registrations
                                                </a>

                                                <a href="{{ route('dashboard.registration.view', $registration->id) }}" class="btn-ghost btn-sm text-sm text-blue-600 p-2">
                                                    View Details
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-6">
                                {{ $this->registrations->links() }}
                            </div>
                            @endif
                        </div>
                    </x-card>
                </div>
            </div>
        </div>
    </div>
</div>