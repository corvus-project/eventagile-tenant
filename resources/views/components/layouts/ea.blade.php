<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Used to add dark mode right away, adding here prevents any flicker -->
    <script>
        if (typeof(Storage) !== "undefined") {
            if (localStorage.getItem('dark_mode') && localStorage.getItem('dark_mode') == 'true') {
                document.documentElement.classList.add('dark');
            }
        }
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])



    <title>{{ $title ?? config('app.name')}}</title>

</head>

<body class="bg-gray-50">
    <nav class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <a href="{{route('tenant.home')}}">
                            <span class="text-xl font-bold text-gray-900"> {{ $tenant_name ?? '' }}</span></a>
                    </div>
                </div>
                <div class=" flex items-center space-x-4">
                    <a href="{{ route('tenant.home') }}"
                        class="text-gray-700 hover:text-primary px-3 py-2 text-sm font-medium">Home</a>
                    <a href="{{ route('tenant.list') }}"
                        class="text-gray-700 hover:text-primary px-3 py-2 text-sm font-medium">Classes</a>
                    <a href="{{ route('tenant.about') }}" class="text-gray-700 hover:text-primary px-3 py-2 text-sm font-medium">About</a>
                </div>
            </div>
        </div>
    </nav>

    @if (isset($hero))
    <x-ui.ea.hero>
        {{$hero}}
    </x-ui.ea.hero>
    @endif

    @if (isset($list_header))
    <x-ui.ea.list_header />
    @endif

    @if (isset($event_hero))
    <x-ui.ea.event_hero>
        {{$event_hero}}
    </x-ui.ea.event_hero>
    @endif

    {{ $slot }}
</body>

</html>