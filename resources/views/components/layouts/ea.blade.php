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
    <nav x-data="{ mobileMenuOpen: false }" class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <a href="{{route('tenant.home')}}">
                            <span class="text-xl font-bold text-gray-900"> {{ $tenant_name ?? '' }}</span></a>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="hidden sm:flex items-center space-x-4">
                        <a href="{{ route('tenant.home') }}"
                            class="text-gray-700 hover:text-primary px-3 py-2 text-sm font-medium">Home</a>
                        <a href="{{ route('tenant.list') }}"
                            class="text-gray-700 hover:text-primary px-3 py-2 text-sm font-medium">Classes</a>
                        <a href="{{ route('tenant.about') }}" class="text-gray-700 hover:text-primary px-3 py-2 text-sm font-medium">About</a>
                    </div>

                    <button @click="mobileMenuOpen=!mobileMenuOpen" class="sm:hidden inline-flex items-center justify-center p-2 rounded-md text-gray-700 hover:text-gray-900 hover:bg-gray-100 focus:outline-none" aria-expanded="false">
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <div x-show="mobileMenuOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                        class="absolute top-16 left-0 right-0 sm:hidden bg-white border-b border-gray-200 shadow-lg z-50" x-cloak>
                        <div class="px-4 py-3 space-y-1">
                            <a href="{{ route('tenant.home') }}"
                                class="block text-gray-700 hover:text-primary px-3 py-2 text-sm font-medium rounded-md hover:bg-gray-50">Home</a>
                            <a href="{{ route('tenant.list') }}"
                                class="block text-gray-700 hover:text-primary px-3 py-2 text-sm font-medium rounded-md hover:bg-gray-50">Classes</a>
                            <a href="{{ route('tenant.about') }}" class="block text-gray-700 hover:text-primary px-3 py-2 text-sm font-medium rounded-md hover:bg-gray-50">About</a>

                            @auth()
                            <div class="pt-3 border-t border-gray-200 mt-2 space-y-1">
                                @role('admin')
                                <a href="{{ route('dashboard') }}" class="block text-gray-700 hover:text-primary px-3 py-2 text-sm font-medium rounded-md hover:bg-gray-50">View Dashboard</a>
                                @endrole
                                @role('user')
                                <a href="{{ route('tenant.my-page') }}" class="block text-gray-700 hover:text-primary px-3 py-2 text-sm font-medium rounded-md hover:bg-gray-50">My Page</a>
                                @endrole
                                <a href="{{ route('tenant.profile') }}" class="block text-gray-700 hover:text-primary px-3 py-2 text-sm font-medium rounded-md hover:bg-gray-50">Profile</a>
                                <form method="POST" action="{{ route('tenant.logout') }}" class="w-full">
                                    @csrf
                                    <button type="submit" class="w-full text-left text-gray-700 hover:text-primary px-3 py-2 text-sm font-medium rounded-md hover:bg-gray-50">Log out</button>
                                </form>
                            </div>
                            @else
                            <div class="pt-3 border-t border-gray-200 mt-2 space-y-2">
                                <a href="{{ route('login') }}" class="block text-center text-gray-700 hover:text-primary px-3 py-2 text-sm font-medium rounded-md hover:bg-gray-50">Login</a>
                                <a href="{{ route('register') }}" class="block text-center text-primary px-3 py-2 text-sm font-medium rounded-md hover:bg-primary/10">Sign Up</a>
                            </div>
                            @endauth
                        </div>
                    </div>

                    <div class="hidden sm:flex items-center w-auto">
                        @auth()
                        <div class="flex items-center w-auto">
                            @role('admin')
                            <x-ui.button type="primary" submit="true" tag="a" href="{{ route('dashboard') }}">View Dashboard</x-ui.button>
                            @endrole
                            @role('user')
                            <x-ui.button type="primary" submit="true" tag="a" href="{{ route('tenant.my-page') }}">My Page</x-ui.button>

                            <!-- User Dropdown -->
                            <div x-data="{ dropdownOpen: false }"
                                :class="{ 'block z-50 w-full p-4 border-t border-gray-100 bg-white dark:bg-gray-900 dark:border-gray-800' : open, 'hidden': ! open }"
                                class="relative flex-shrink-0 sm:p-0 sm:flex sm:w-auto sm:bg-transparent sm:items-center sm:ml-1.5"
                                x-cloak>
                                <button @click="dropdownOpen=!dropdownOpen" class="inline-flex items-center justify-between w-full sm:px-3.5 sm:py-2 py-2.5 px-4 text-sm font-medium text-gray-500 transition duration-0 bg-white border-transparent sm:border rounded-full hover:bg-slate-200/50 dark:text-white/70 dark:hover:text-gray-100 dark:bg-transparent dark:hover:bg-gray-800/70 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none">
                                    <div>{{ Auth::user()->name }}</div>
                                    <div class="ml-1">
                                        <svg class="w-4 h-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </button>
                                <div x-show="dropdownOpen" @click.away="dropdownOpen=false" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 sm:scale-95" x-transition:enter-end="transform opacity-100 sm:scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 sm:scale-100" x-transition:leave-end="transform opacity-0 sm:scale-95"
                                    class="absolute top-0 right-0 z-50 w-full mt-16 sm:mt-12 sm:origin-top-right sm:w-40" x-cloak>
                                    <div class="p-4 pt-0 mt-1 space-y-3 text-gray-600 bg-white dark:text-white/70 dark:bg-gray-900 dark:shadow-xl sm:p-2 sm:space-y-0.5 sm:border sm:shadow-md sm:rounded-lg border-gray-200/70 dark:border-white/10">
                                        <a href="{{ route('tenant.profile') }}" class="relative flex cursor-pointer hover:text-gray-700 dark:hover:text-white/70 select-none hover:bg-gray-100/70 dark:hover:bg-gray-800/80 items-center rounded-full py-2 px-4 sm:px-2.5 sm:py-1.5 text-sm outline-none transition-colors data-[disabled]:pointer-events-none data-[disabled]:opacity-50">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2">
                                                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                                                <circle cx="12" cy="7" r="4"></circle>
                                            </svg>
                                            <span>Profile</span>
                                        </a>
                                        <form method="POST" action="{{ route('tenant.logout') }}" class="w-full">
                                            @csrf
                                            <button onclick="event.preventDefault(); this.closest('form').submit();" class="relative w-full flex cursor-pointer hover:text-gray-700 dark:hover:text-white/70 select-none hover:bg-gray-100/70 dark:hover:bg-gray-800/80 items-center rounded-full py-2 px-4 sm:px-2.5 sm:py-1.5 text-sm outline-none transition-colors data-[disabled]:pointer-events-none data-[disabled]:opacity-50">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2">
                                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                                    <polyline points="16 17 21 12 16 7"></polyline>
                                                    <line x1="21" x2="9" y1="12" y2="12"></line>
                                                </svg>
                                                <span>Log out</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        @endrole
                        </div>
                        @else
                        <div class="flex items-center w-auto">
                            <x-ui.button type="secondary" submit="true" tag="a" href="{{ route('login') }}">Login</x-ui.button>
                        </div>
                        <div class="flex items-center w-auto">
                            <x-ui.button type="primary" submit="true" tag="a" href="{{ route('register') }}">Sign Up</x-ui.button>
                        </div>
                        @endauth
                    </div>
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
