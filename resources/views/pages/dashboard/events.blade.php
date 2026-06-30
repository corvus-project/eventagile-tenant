<?php

use App\Enums\EventStatus;
use App\Models\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;

new #[Layout('layouts.admin')]  class extends Component
{
    use WithPagination;

    public int $perPage = 10;
    public string $search = '';
    public array $sortBy = ['column' => 'start_time', 'direction' => 'desc'];

    public function mount() {}

    #[Computed]
    public function events()
    {
        return Event::query()
            ->when($this->search, function ($query) {
                $search = Str::lower($this->search);
                Log::debug('Searching events with query', ['search' => $search]);
                $query->where(function ($query) use ($search) {
                    $query->whereRaw('LOWER(title) LIKE ?', ['%' . $search . '%'])
                        ->orWhereRaw('LOWER(organizer) LIKE ?', ['%' . $search . '%'])
                        ->orWhereRaw('LOWER(location) LIKE ?', ['%' . $search . '%']);
                });
            })
            ->orderBy($this->sortBy['column'], $this->sortBy['direction'])
            ->paginate($this->perPage);
    }

    public function updatedSearch()
    {
        Log::debug('Search term updated', ['search' => $this->search]);

        $this->resetPage();
    }

    public function sortByColumn(string $column): void
    {
        if ($this->sortBy['column'] === $column) {
            $this->sortBy['direction'] = $this->sortBy['direction'] === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = ['column' => $column, 'direction' => 'asc'];
        }

        $this->resetPage();
    }

    public function show(int $id)
    {
        $slug = Event::findOrFail($id);
        return redirect()->route('events.show', ['event' => $slug]);
    }

    public function cloneEvent(int $id)
    {
        $event = Event::findOrFail($id);

        $clonedEvent = $event->replicate();
        $clonedEvent->title = $event->title . ' (Copy)';
        $clonedEvent->slug = null; // Ensure slug is regenerated
        $clonedEvent->status = EventStatus::DRAFT->value; // Set status to draft for cloned event
        $clonedEvent->save();

        return redirect()->route('dashboard.events.update', ['event' => $clonedEvent->slug]);
    }
};
?>

<x-slot name="title">
    {{ __('Events') }}
</x-slot>

<x-slot name="header">
    <h2 class="text-lg font-semibold leading-tight text-gray-800 dark:text-gray-200">
        {{ __('Events') }}
    </h2>
</x-slot>

<div class="flex flex-col flex-1">
    <div class="flex flex-col  flex-1 pb-5 mx-auto  w-full">
        <div class="relative flex-1 w-full ">
            <div class="flex justify-between items-center w-full bg-pink- overflow-hidden border border-dashed bg-gradient-to-br from-white to-zinc-50 rounded-lg border-zinc-200 dark:border-gray-700 dark:from-gray-950 dark:via-gray-900 dark:to-gray-800">
                <div class="flex relative flex-col   h-full w-full">


                    <div class="mx-auto min-w-full">
                        @if ($this->events->isEmpty())
                        <div class="p-4 text-center text-gray-500 dark:text-gray-400
                                ">
                            No events found.
                        </div>
                        @else
                        <div class="shadow p-4 dark:bg-gray-800 sm:rounded-lg  bg-slate-50  rounded-lg dark:bg-gray-900/50 dark:border dark:border-gray-200/10">
                            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between ">
                                <div class="w-full md:w-1/2">
                                    <x-ui.input
                                        id="search"
                                        type="search"
                                        wire:model.live.debounce.300ms="search"
                                        placeholder="Search events by title, organizer, or location"
                                        class="w-full" />
                                </div>
                                <div class="flex flex-wrap items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                                    <span class="font-medium">Sort by:</span>
                                    <button type="button" wire:click="sortByColumn('title')" class="btn-ghost btn-xs">Title</button>
                                    <button type="button" wire:click="sortByColumn('start_time')" class="btn-ghost btn-xs">Start date</button>

                                </div>
                            </div>

                            <div class="overflow-x-auto ">

                                <table class="min-w-full text-left divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="hidden sm:table-header-group bg-gray-50 dark:bg-gray-900">
                                        <tr class="sm:table-row block">
                                            <th scope="col" class="px-4 py-3 text-xs font-semibold tracking-wider uppercase cursor-pointer sm:table-cell block" wire:click="sortByColumn('title')">
                                                Title
                                                @if($sortBy['column'] === 'title')
                                                <span>{{ $sortBy['direction'] === 'asc' ? '↑' : '↓' }}</span>
                                                @endif
                                            </th>
                                            <th scope="col" class="px-4 py-3 text-xs font-semibold tracking-wider uppercase cursor-pointer sm:table-cell block" wire:click="sortByColumn('start_time')">
                                                Start date
                                                @if($sortBy['column'] === 'start_time')
                                                <span>{{ $sortBy['direction'] === 'asc' ? '↑' : '↓' }}</span>
                                                @endif
                                            </th>
                                            <th scope="col" class="px-4 py-3 text-xs font-semibold tracking-wider uppercase cursor-pointer sm:table-cell block" wire:click="sortByColumn('organizer')">
                                                Organizer
                                                @if($sortBy['column'] === 'organizer')
                                                <span>{{ $sortBy['direction'] === 'asc' ? '↑' : '↓' }}</span>
                                                @endif
                                            </th>
                                            <th scope="col" class="px-4 py-3 text-xs font-semibold tracking-wider uppercase cursor-pointer sm:table-cell block" wire:click="sortByColumn('status')">
                                                Status
                                                @if($sortBy['column'] === 'status')
                                                <span>{{ $sortBy['direction'] === 'asc' ? '↑' : '↓' }}</span>
                                                @endif
                                            </th>
                                            <th scope="col" class="px-4 py-3 text-xs font-semibold tracking-wider uppercase sm:table-cell block">Capacity</th>
                                            <th scope="col" class="px-4 py-3 text-xs font-semibold tracking-wider uppercase text-center sm:table-cell block">Registrations</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700 sm:table-row-group block">
                                        @foreach($this->events as $event)
                                        <tr class="border-b border-gray-200 dark:border-gray-700 sm:table-row block">
                                            <td class="px-4 py-4 block sm:table-cell">
                                                <span class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400 sm:hidden">Title</span>
                                                <a href="{{ route('dashboard.events.update', $event->slug) }}" class="text-sm text-blue-600 hover:underline block sm:inline">{{ $event->title }}</a>
                                            </td>
                                            <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300 block sm:table-cell">
                                                <span class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400 sm:hidden">Start date</span>
                                                {{ \Carbon\Carbon::parse($event->start_time)->format('F j, Y H:i') }}
                                            </td>
                                            <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300 block sm:table-cell">
                                                <span class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400 sm:hidden">Organizer</span>
                                                {{ $event->organizer }}
                                            </td>
                                            <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300 block sm:table-cell">
                                                <span class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400 sm:hidden">Status</span>
                                                {{ $event->status }}
                                            </td>
                                            <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300 block sm:table-cell">
                                                <span class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400 sm:hidden">Capacity</span>
                                                {{ $event->capacity }}
                                            </td>
                                            <td class="px-4 py-4 text-xs text-right block sm:table-cell">
                                                <span class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400 sm:hidden">Actions</span>
                                                <div class="flex flex-col gap-2 sm:flex-row sm:justify-end sm:gap-2">
                                                    <x-button label="Update" link="{{ route('dashboard.events.update', $event->slug) }}" class="btn-active btn-sm" icon="o-pencil" tooltip="Update Event!" />
                                                    <x-button label="Registrations" link="{{ route('dashboard.events.registrations.show', $event->slug) }}" class="btn-info btn-sm" icon="o-users" tooltip="Registrations!" />
                                                    <x-button icon="o-clipboard-document" class="btn-warning btn-sm" wire:click="cloneEvent({{ $event->id }})" label="Clone" tooltip="Clone Event!" />
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>

                            </div>
                            <div class="mt-4">
                                {{ $this->events->links() }}
                            </div>

                        </div>
                        @endif

                    </div>

                </div>
            </div>
        </div>
    </div>
</div>