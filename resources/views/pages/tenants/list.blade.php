<?php

use App\Enums\EventStatus;
use App\Models\Event;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;

new #[Layout('layouts.ea-yoga')]  class extends Component
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
            return Event::where('status', '=', EventStatus::SCHEDULED->value)->where('start_time', '>', now())->orderBy('start_time', 'asc')->paginate(9);
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
    {{ Setting::get('site_name') }} - Home
</x-slot>
<x-slot name="tenant_name">
    {{ Setting::get('site_name') }}
</x-slot>
<x-slot name="list_header">
    {{ Setting::get('site_name') }}
</x-slot>
<div>

    <!-- Events List -->
    <section class="py-8 sm:py-12">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
            <div class="space-y-4 sm:space-y-6">
                @foreach($this->events as $event)

                <div class="bg-white rounded-xl shadow-md overflow-hidden card-hover">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-0">
                        <div class="h-40 md:h-auto bg-gradient-to-br from-green-400 to-teal-500 relative">
                            <span
                                class="absolute top-3 right-3 bg-white text-primary px-2.5 py-1 rounded-full text-xs font-semibold"> </span>
                            <div class="absolute bottom-3 left-3 text-white">

                            </div>
                        </div>
                        <div class="p-4 sm:p-5 md:col-span-2">
                            <div class="flex flex-col sm:flex-row justify-between sm:items-start mb-2 gap-2">
                                <div class="min-w-0">
                                    <h3 class="text-lg sm:text-2xl font-bold text-gray-900 mb-1 truncate">{{ $event->title }}</h3>
                                    <p class="text-gray-600 text-sm line-clamp-2">{{ $event->description }}</p>
                                </div>
                                <span class="text-2xl font-bold text-primary shrink-0"></span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 sm:gap-3 my-2 sm:my-3">
                                <p class="flex items-center gap-1.5 text-xs sm:text-sm text-gray-600 dark:text-gray-400">
                                    <x-heroicon-s-calendar class="text-primary shrink-0 size-4 sm:size-5" />
                                    <span class="truncate">{{ $event->start_time->format('F j, Y H:i') }}</span>
                                </p>
                                <p class="flex items-center gap-1.5 text-xs sm:text-sm text-gray-600 dark:text-gray-400">
                                    <x-heroicon-o-map-pin class="text-primary shrink-0 size-4 sm:size-5" />
                                    <span class="truncate">Location: {{ $event->location }}</span>
                                </p>
                                <p class="flex items-center gap-1.5 text-xs sm:text-sm text-gray-600 dark:text-gray-400">
                                    <x-heroicon-o-users class="text-primary shrink-0 size-4 sm:size-5" />
                                    <span>Capacity: {{ $event->capacity }}</span>
                                </p>
                            </div>

                            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between pt-3 border-t gap-3">
                                <div class="flex items-center text-sm text-gray-600">
                                    <x-heroicon-o-envelope class="text-primary mr-2 size-4 sm:size-5 shrink-0" />
                                    <span class="truncate">{{ $event->organizer }}</span>
                                </div>
                                <a href="{{ route('tenant.event.view', $event) }}"
                                    class="w-full sm:w-auto bg-primary text-white px-4 sm:px-6 py-2.5 sm:py-3 rounded-lg font-medium hover:bg-secondary transition text-center text-sm">
                                    View Details & Book
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                @endforeach
                <div class="mt-6 flex justify-center">
                    {{ $this->events->links() }}
                </div>

            </div>
        </div>
    </section>
</div>