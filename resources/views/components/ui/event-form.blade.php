@props([
'action' => null,
'event' => null,
'eventLimit' => true,
'status' => collect(),
])

<div class="w-full max-w-4xl mx-auto">
    @if (!$eventLimit)
    <div class="p-4 mb-6 text-sm text-yellow-800 bg-yellow-50 rounded-lg dark:bg-gray-800/50 dark:text-yellow-300" role="alert">
        <span class="font-medium">{{ __('You have reached the maximum number of events you can create. Please delete an existing event or contact support to increase your limit.') }}</span>
    </div>
    @else

    <x-form wire:submit="{{ $action }}" class="space-y-8">

        {{-- Basic Information --}}
        <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800/50 sm:rounded-xl dark:border dark:border-gray-200/10">
            <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700/50">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-blue-50 dark:bg-blue-900/30">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">{{ __('Basic Information') }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('The core details of your event.') }}</p>
                    </div>
                </div>
            </div>
            <div class="px-6 py-5 space-y-6">
                <div>
                    <x-input
                        label="{{ __('Event Title') }}"
                        wire:model="form.title"
                        placeholder="{{ __('Enter a catchy title for your event') }}"
                        class="w-full" />
                </div>

                <div>
                    <x-textarea
                        label="{{ __('Description') }}"
                        wire:model="form.description"
                        rows="4"
                        placeholder="{{ __('Describe what your event is about...') }}"
                        class="w-full" />
                </div>
                <div>
                    <x-textarea
                        label="{{ __('Full Description') }}"
                        wire:model="form.full_description"
                        rows="8"
                        placeholder="{{ __('Provide a detailed description of your event...') }}"
                        class="w-full" />
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <x-input
                        label="{{ __('Location') }}"
                        wire:model="form.location"
                        placeholder="{{ __('e.g. 123 Main St, London') }}"
                        icon="o-map-pin" />
                    <x-input
                        label="{{ __('Organizer') }}"
                        wire:model="form.organizer"
                        placeholder="{{ __('Who is organizing this?') }}"
                        icon="o-user" />
                </div>
            </div>
        </div>

        {{-- Date & Time --}}
        <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800/50 sm:rounded-xl dark:border dark:border-gray-200/10">
            <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700/50">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-amber-50 dark:bg-amber-900/30">
                        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">{{ __('Date & Time') }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('When will your event take place?') }}</p>
                    </div>
                </div>
            </div>
            <div class="px-6 py-5 space-y-6">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <x-datetime
                            label="{{ __('Start Time') }}"
                            wire:model="form.start_time"
                            type="datetime-local"
                            class="w-full" />
                        <p class="mt-1 text-xs text-gray-400">{{ __('When the event begins') }}</p>
                    </div>
                    <div>
                        <x-datetime
                            label="{{ __('Registration Deadline') }}"
                            wire:model="form.registration_deadline"
                            type="datetime-local"
                            class="w-full" />
                        <p class="mt-1 text-xs text-gray-400">{{ __('Optional: deadline for registrations') }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Capacity & Visibility --}}
        <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800/50 sm:rounded-xl dark:border dark:border-gray-200/10">
            <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700/50">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-emerald-50 dark:bg-emerald-900/30">
                        <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">{{ __('Capacity & Visibility') }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Manage your event audience and access.') }}</p>
                    </div>
                </div>
            </div>
            <div class="px-6 py-5 space-y-6">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <x-input
                        label="{{ __('Capacity') }}"
                        wire:model="form.capacity"
                        type="number"
                        placeholder="{{ __('e.g. 100') }}"
                        icon="o-users" />
                    <x-select
                        label="{{ __('Status') }}"
                        wire:model="form.status"
                        :options="$status"
                        placeholder="{{ __('Select a status') }}" />
                </div>

                <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-900/50">
                    <x-checkbox
                        label="{{ __('Make this event public') }}"
                        wire:model="form.is_public"
                        hint="{{ __('Public events can be discovered and registered by anyone.') }}" />
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-end gap-3 px-6 py-4 bg-white border-t border-gray-100 dark:bg-gray-800/30 dark:border-gray-700/50 sm:rounded-xl shadow-sm">
            <a
                href="{{ route('dashboard.events') }}"
                wire:navigate
                class="inline-flex items-center px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
                </svg>
                {{ __('Cancel') }}
            </a>
            <x-button
                label="{{ $event ? __('Update Event') : __('Create Event') }}"
                class="btn-primary"
                type="primary"
                submit="true"
                spinner="save"
                icon="o-check" />
        </div>
    </x-form>
    @endif
</div>