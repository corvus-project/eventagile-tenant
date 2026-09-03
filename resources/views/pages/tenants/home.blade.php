<?php

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
            return Event::paginate(9);
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
<x-slot name="hero">
    <section class="gradient-bg text-white py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <div>
                    <h1 class="text-4xl md:text-5xl font-bold mb-6">{{ Setting::get('site_slogan') }}</h1>
                    <p class="text-xl mb-8 text-green-50">{{ Setting::get('site_description') }}</p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="{{route('tenant.list')}}"
                            class="bg-white text-primary px-8 py-4 rounded-lg font-semibold hover:bg-gray-100 transition text-center">
                            View Classes
                        </a>

                    </div>
                </div>
                <div class="hidden md:block">
                    <img src="https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?w=600&h=400&fit=crop" alt="Yoga"
                        class="rounded-2xl shadow-2xl">
                </div>
            </div>
        </div>
    </section>
</x-slot>

<!-- Hero Section -->

<div>



    <!-- Classes Section -->
    <section id="classes" class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-gray-900 mb-12 text-center">Available Classes</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($this->events as $event)
                <!-- Class 1: Beginner Yoga -->
                <div class="bg-white rounded-xl shadow-md overflow-hidden card-hover">
                    <div class="h-12 bg-gradient-to-br from-green-400 to-teal-500 relative">
                        <span
                            class="absolute top-4 right-4 bg-white text-primary px-3 py-1 rounded-full text-xs font-semibold"></span>
                        <div class="absolute bottom-4 left-4 text-white">

                        </div>
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $event->title }}</h3>
                        <p class="text-gray-600 mb-4">{{ $event->description }}</p>

                        <div class="space-y-2 mb-4">
                            <div class="flex items-center text-sm text-gray-600">
                                <x-heroicon-s-calendar class="text-primary mr-2 size-5" />
                                <span>Date: {{ $event->start_time->format('F j, Y H:i') }}</span>
                            </div>
                            <div class="flex items-center text-sm text-gray-600">
                                <x-heroicon-o-envelope class="text-primary mr-2 size-5" /> <span> Organizer: {{ $event->organizer }}</span>
                            </div>
                            <div class="flex items-center text-sm text-gray-600">
                                <x-heroicon-o-users class="text-primary mr-2 size-5" /> <span> Capacity: {{ $event->capacity }}</span>
                            </div>
                            <div class="flex items-center text-sm text-gray-600">
                                <x-heroicon-o-map-pin class="text-primary mr-2 size-5" />
                                <span>Location: {{ $event->location }}</span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-4 border-t">
                            <div>

                            </div>
                            <a href="{{ route('tenant.event.view', $event) }}"
                                class="bg-primary text-white px-6 py-2 rounded-lg font-medium hover:bg-secondary transition inline-block">
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="mt-4 flex justify-end-safe gap-1">
                {{ $this->events->links() }}
            </div>
        </div>
    </section>

</div>