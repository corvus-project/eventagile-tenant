<header x-data="{ open: false }" class="bg-blue-500 border-b border-gray-200/80 dark:bg-gray-900/40 dark:border-gray-200/[15%]">
    <div class="relative z-20 flex items-center justify-between w-full h-20 max-w-6xl px-6 mx-auto">
        <div x-data="{ mobileMenuOpen: false }" class="relative flex items-center md:space-x-2 text-neutral-800">

            <div class="relative z-50 flex items-center w-auto h-full">
                <a href="{{ '/' }}" class="flex items-center mr-2 md:mr-5 shrink-0">
                    <x-ui.logo class="block w-auto text-gray-800 fill-current h-7 dark:text-gray-200" />
                </a>
                <div @click="mobileMenuOpen=!mobileMenuOpen" class="relative flex items-center justify-center w-8 h-8 ml-5 overflow-hidden text-gray-500 bg-gray-100 rounded cursor-pointer md:hidden hover:text-gray-700 hover:bg-gray-200">
                    <div :class="{ 'rotate-0' : mobileMenuOpen }" class="flex flex-col items-center justify-center w-4 h-4 duration-300 ease-in-out">
                        <span :class="{ '-rotate-[135deg] translate-y-[5px]' : mobileMenuOpen }" class="block ease-in-out duration-300 w-full h-0.5 bg-gray-800 rounded-full"></span>
                        <span :class="{ 'w-0' : mobileMenuOpen, 'w-full' : !mobileMenuOpen }" class="block ease-in-out duration-300 w-full h-0.5 my-[3px] bg-gray-800 rounded-full"></span>
                        <span :class="{ '-rotate-45 -translate-y-[5px]' : mobileMenuOpen }" class="block ease-in-out duration-300 w-full h-0.5 bg-gray-800 rounded-full"></span>
                    </div>
                </div>
            </div>
            <div :class="{ 'flex' : mobileMenuOpen, 'hidden md:flex' :  !mobileMenuOpen }" class="fixed top-0 left-0 z-40 flex-col items-start justify-start hidden w-full h-full min-h-screen pt-20 space-y-5 text-sm font-medium duration-150 ease-out transform md:pt-0 text-neutral-500 md:h-auto md:min-h-0 md:left-auto md:items-center md:relative">

                <nav class="flex flex-col w-full p-6 space-y-2 bg-white md:p-0 md:flex-row md:space-x-2 md:space-y-0 md:w-auto md:bg-transparent md:flex ml-4">

                </nav>
            </div>
        </div>
        <div class="relative z-50 flex items-stretch space-x-3 text-neutral-800">
            <div x-data class="flex-shrink-0 hidden w-[38px] overflow-hidden rounded-full h-[38px] sm:block" x-cloak>
                <x-ui.light-dark-switch></x-ui.light-dark-switch>
            </div>
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
</header>