<x-layouts.main>

    <x-slot name="title">
        {{ $title ?? 'CorvusApp' }}
    </x-slot>


    <!-- Page Heading -->
    @if (isset($header))
    <div class="mb-5 bg-white border-b border-gray-200/80 dark:border-gray-200/10 dark:bg-gray-900/40">
        <div class="py-6 mx-auto max-w-6xl sm:px-6 lg:px-12">
            {{ $header }}
        </div>
    </div>
    @endif

    <div class="mx-auto max-w-6xl">
        <div class="sm:px-6 lg:px-8">
            {{ $slot }}
        </div>
    </div>
</x-layouts.main>