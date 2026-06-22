<x-layouts.admin>

    <x-slot name="title">
        {{ $title ?? 'EventAgile' }}
    </x-slot>

    {{-- NAVBAR mobile only --}}
    <x-nav sticky class="lg:hidden">
        <x-slot:brand>
            <div class="ml-5 pt-5"> {{ $title ?? 'EventAgile' }}</div>
        </x-slot:brand>

        <x-slot:actions>
            <label for="main-drawer" class="lg:hidden mr-3">
                <x-icon name="o-bars-3" class="cursor-pointer" />
            </label>
        </x-slot:actions>
    </x-nav>

    {{-- MAIN --}}
    <x-main full-width>
        {{-- SIDEBAR --}}
        {{ 'bg-base-100 lg:bg-inherit'}}
        <x-slot:sidebar drawer="main-drawer" collapsible class="bg-base-100 lg:bg-blue-500">

            {{-- BRAND --}}
            <div class="p-5 bg-blue-500 lg:bg-transparent">
                <a href="{{ route('dashboard') }}" class="flex items-center shrink-0">
                    <x-ui.logo class="block w-auto text-white fill-current h-7 dark:text-gray-200" />
                </a>
            </div>

            {{-- MENU --}}
            <x-menu activate-by-route>

                @php

                $user = auth()->user();
                $navLinks['Dashboard'] = ['route' => 'dashboard', 'icon'=>'s-home'];

                if ($user && ($user->isOrganizer() || $user->isAdmin())) {
                $navLinks['Events'] = ['route' => 'dashboard.events', 'icon'=>'s-calendar'];
                $navLinks['Create Event'] = ['route' => 'dashboard.events.create', 'icon'=>'s-plus'];
                $navLinks['Users'] = ['route' => 'dashboard.users', 'icon'=>'s-users'];
                $navLinks['Reports'] = ['route' => 'reports.index', 'icon'=>'s-chart-bar'];
                $navLinks['Settings'] = ['route' => 'settings.index', 'icon'=>'s-cog'];
                }

                if ($user && $user->isAdmin()) {
                $navLinks['Users'] = ['route' => 'dashboard.users', 'icon'=>'s-users'];
                }
                @endphp

                @foreach($navLinks as $title => $value)
                @if(Request::path() === ltrim($value['route'], '/'))
                <x-menu-item icon="{{ $value['icon'] }}" route="{{ $value['route'] }}" active>{{ $title }}</x-menu-item>
                @else
                <x-menu-item icon="{{ $value['icon'] }}" route="{{ $value['route'] }}">{{ $title }}</x-menu-item>
                @endif
                @endforeach

                {{-- User --}}
                @if($user = auth()->user())
                <x-menu-separator />

                <x-menu-item icon="o-sparkles" route="{{ ('profile.edit')  }}">Profile</x-menu-item>

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

                <x-menu-separator />
                @endif
            </x-menu>
        </x-slot:sidebar>

        {{-- The `$slot` goes here --}}
        <x-slot:content>

            <!-- Page Heading -->
            @if (isset($header))
            <header class="mb-5 bg-white border-b border-gray-200/80 dark:border-gray-200/10 dark:bg-gray-900/40 rounded-lg">
                <div class="py-6 mx-auto sm:px-1 lg:px-6 md:px-6">
                    {{ $header }}
                </div>
            </header>
            @endif

            {{ $slot }}
        </x-slot:content>
    </x-main>

    {{-- Toast --}}
    <x-toast />

</x-layouts.admin>