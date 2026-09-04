<x-layouts.admin>

    <x-slot name="title">
        {{ $title ?? 'EventAgile' }}
    </x-slot>

    @php
    $icons = [
        'home' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>',
        'calendar' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>',
        'plus' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>',
        'users' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/>',
        'chart' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/>',
        'cog' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.431.992a7.723 7.723 0 0 1 0 .255c-.007.378.138.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.378-.138-.75-.43-.991l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>',
        'subscription' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z"/>',
    ];
    @endphp

    <div
        x-data="{
            sidebarOpen: false,
            collapsed: localStorage.getItem('sidebar_collapsed') === 'true'
        }"
        x-init="$watch('collapsed', val => localStorage.setItem('sidebar_collapsed', val))"
        class="min-h-screen flex bg-gray-100 dark:bg-gray-950"
    >

        {{-- MOBILE NAVBAR --}}
        <div class="fixed top-0 left-0 right-0 z-30 flex items-center h-16 px-4 bg-white border-b border-gray-200 dark:bg-gray-900 dark:border-gray-700 lg:hidden">
            <button @click="sidebarOpen = true" class="p-2 -ml-2 text-gray-500 rounded-lg hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
                </svg>
            </button>
            <div class="ml-4 text-lg font-semibold text-gray-900 dark:text-white">{{ $title ?? 'EventAgile' }}</div>
        </div>

        {{-- MOBILE OVERLAY --}}
        <div
            x-show="sidebarOpen"
            x-transition:enter="transition-opacity ease-linear duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="sidebarOpen = false"
            class="fixed inset-0 z-40 bg-gray-900/50 lg:hidden"
        ></div>

        {{-- SIDEBAR --}}
        <aside
            :class="[
                collapsed ? 'w-16' : 'w-64',
                sidebarOpen ? 'translate-x-0' : '-translate-x-full',
            ]"
            class="fixed inset-y-0 left-0 z-50 flex flex-col bg-blue-500 dark:bg-gray-800 transition-all duration-300 ease-in-out lg:translate-x-0 lg:static lg:z-0"
        >

            {{-- BRAND --}}
            <div :class="collapsed ? 'px-0' : 'px-5'" class="flex items-center h-16 shrink-0 bg-blue-600 dark:bg-gray-900">
                <a href="{{ route('dashboard') }}" :class="collapsed ? 'justify-center w-full' : 'px-0'" class="flex items-center">
                    <div x-show="!collapsed">
                        <x-ui.logo class="block w-auto text-white fill-current h-7 dark:text-gray-200" />
                    </div>
                    <span x-show="collapsed" class="text-xl font-bold text-white">EA</span>
                </a>
            </div>

            {{-- NAVIGATION --}}
            @php
            $user = auth()->user();
            $navLinks = [];
            $navLinks['Dashboard'] = ['route' => 'dashboard', 'icon' => 'home'];

            if ($user && ($user->isOrganizer() || $user->isAdmin())) {
                $navLinks['Events'] = ['route' => 'dashboard.events', 'icon' => 'calendar'];
                $navLinks['Create Event'] = ['route' => 'dashboard.events.create', 'icon' => 'plus'];
                $navLinks['Users'] = ['route' => 'dashboard.users', 'icon' => 'users'];
                $navLinks['Reports'] = ['route' => 'reports.index', 'icon' => 'chart'];
                $navLinks['Subscription'] = ['route' => 'dashboard.subscription', 'icon' => 'subscription'];
                $navLinks['Settings'] = ['route' => 'settings.index', 'icon' => 'cog'];
            }

            if ($user && $user->isAdmin()) {
                $navLinks['Users'] = ['route' => 'dashboard.users', 'icon' => 'users'];
            }
            @endphp

            <nav class="flex-1 px-2 py-4 space-y-1 overflow-y-auto">
                @foreach($navLinks as $label => $link)
                @php $active = request()->routeIs($link['route']); @endphp
                <a
                    href="{{ route($link['route']) }}"
                    wire:navigate
                    title="{{ $label }}"
                    @class([
                        'flex items-center py-2.5 text-sm font-medium rounded-lg transition-colors group',
                        'bg-white/20 text-white' => $active,
                        'text-blue-100 hover:bg-white/10 hover:text-white' => !$active,
                    ])
                    :class="collapsed ? 'justify-center px-2' : 'px-3'"
                >
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        {!! $icons[$link['icon']] !!}
                    </svg>
                    <span x-show="!collapsed" class="ml-3">{{ $label }}</span>
                </a>
                @endforeach
            </nav>

            {{-- USER SECTION --}}
            @if($user)
            <div class="border-t border-blue-400/30 dark:border-gray-600"></div>
            <div class="px-2 py-3 space-y-1">
                <a
                    href="{{ route('profile.edit') }}"
                    wire:navigate
                    title="Profile"
                    @class([
                        'flex items-center py-2.5 text-sm font-medium rounded-lg transition-colors',
                        'bg-white/20 text-white' => request()->routeIs('profile.edit'),
                        'text-blue-100 hover:bg-white/10 hover:text-white' => !request()->routeIs('profile.edit'),
                    ])
                    :class="collapsed ? 'justify-center px-2' : 'px-3'"
                >
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
                    </svg>
                    <span x-show="!collapsed" class="ml-3">Profile</span>
                </a>

                <form method="POST" action="{{ route('tenant.logout') }}">
                    @csrf
                    <button
                        type="submit"
                        title="Log out"
                        :class="collapsed ? 'justify-center px-2' : 'px-3'"
                        class="flex items-center w-full py-2.5 text-sm font-medium text-blue-100 rounded-lg hover:bg-white/10 hover:text-white transition-colors"
                    >
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"/>
                        </svg>
                        <span x-show="!collapsed" class="ml-3">Log out</span>
                    </button>
                </form>
            </div>
            @endif

            {{-- COLLAPSE TOGGLE --}}
            <button
                @click="collapsed = !collapsed"
                title="Toggle sidebar"
                class="flex items-center justify-center w-full h-12 text-blue-200 border-t border-blue-400/30 dark:border-gray-600 hover:text-white hover:bg-blue-600 dark:hover:bg-gray-700 transition-colors"
            >
                <svg :class="collapsed ? 'rotate-180' : ''" class="w-5 h-5 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m18.75 4.5-7.5 7.5 7.5 7.5m-7.5 0-7.5-7.5 7.5-7.5"/>
                </svg>
                <span x-show="!collapsed" class="ml-2 text-sm">Collapse</span>
            </button>
        </aside>

        {{-- MAIN CONTENT --}}
        <main class="flex-1 pt-16 lg:pt-0 min-w-0 transition-all duration-300">
            @if (isset($header))
            <header class="bg-white border-b border-gray-200/80 dark:border-gray-200/10 dark:bg-gray-900/40">
                <div class="px-6 py-6 mx-auto sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
            @endif

            <div class="p-6">
                {{ $slot }}
            </div>
        </main>

    </div>

</x-layouts.admin>
