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
    <section class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="space-y-6">
                @foreach($this->events as $event)
                <!-- Event 1: Beginner Yoga -->
                <div class="bg-white rounded-xl shadow-md overflow-hidden card-hover">
                    <div class="grid md:grid-cols-3 gap-6">
                        <div class="h-48 md:h-auto bg-gradient-to-br from-green-400 to-teal-500 relative">
                            <span
                                class="absolute top-4 right-4 bg-white text-primary px-3 py-1 rounded-full text-xs font-semibold"> </span>
                            <div class="absolute bottom-4 left-4 text-white">

                            </div>
                        </div>
                        <div class="p-6 md:col-span-2">
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ $event->title }}</h3>
                                    <p class="text-gray-600">{{ $event->description }}</p>
                                </div>
                                <span class="text-3xl font-bold text-primary ml-4"></span>
                            </div>

                            <div class="grid grid-cols-3 md:grid-cols-3 gap-4 my-4">
                                <div class="flex items-center text-sm text-gray-600">
                                    <x-heroicon-s-calendar class="text-primary mr-2 size-5" /> <span>{{ $event->start_time->format('F j, Y H:i') }}</span>
                                </div>
                                <div class="flex items-center text-sm text-gray-600">
                                    <x-heroicon-o-map-pin class="text-primary mr-2 size-5" /><span>{{ $event->location }}</span>
                                </div>
                                <div class="flex items-center text-sm text-gray-600">
                                    <x-heroicon-o-users class="text-primary mr-2 size-5" /> <span> {{ $event->capacity }}</span>
                                </div>
                            </div>

                            <div class="flex items-center justify-between pt-4 border-t">
                                <div class="flex items-center">
                                    <div>
                                        <x-heroicon-o-envelope class="text-primary mr-2 size-5" /> <span> {{ $event->organizer }}</span>
                                    </div>
                                </div>
                                <a href="{{ route('tenant.event.view', $event) }}"
                                    class="bg-primary text-white px-6 py-3 rounded-lg font-medium hover:bg-secondary transition">
                                    View Details & Book
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                @endforeach
                <div class="mt-4 flex justify-end-safe gap-1">
                    {{ $this->events->links() }}
                </div>

            </div>
        </div>
    </section>
</div>