<x-layouts.ea>

    <x-slot name="title">
        {{ $title ?? 'CorvusApp' }}
    </x-slot>

    <x-slot name="tenant_name">
        {{ $tenant_name }}
    </x-slot>

    @if (isset($list_header))
    <x-slot name="list_header">
        {{ $list_header }}
    </x-slot>
    @endif

    @if (isset($hero))
    <x-slot name="hero">
        {{ $hero }}
    </x-slot>
    @endif

    @if (isset($event_hero))
    <x-slot name="event_hero">
        {{ $event_hero }}
    </x-slot>
    @endif

    <div class="mx-auto max-w-6xl">
        <div class="sm:px-6 lg:px-8">
            {{ $slot }}
        </div>
    </div>

    <!-- Footer -->
    <footer class=" text-black py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <div class="flex items-center mb-4">
                        <span class="text-xl font-bold">{{ $tenant_name }}</span>
                    </div>
                    <p class="text-gray-400">{{ $tenant_description ?? '' }}
                    </p>
                </div>

                <div>

                </div>

                <div>
                    <h4 class="font-semibold mb-4">Contact</h4>
                    <div class="space-y-2 text-gray-400">
                        {{ Setting::get('contact')}}
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; {{ now()->year }} {{$tenant_name}} All rights reserved.</p>
            </div>
        </div>
    </footer>
</x-layouts.ea>