@props([
'label' => null,
'id' => null,
'name' => null,
'type' => 'text',
'icon' => null,
'placeholder' => null,
])

@php $wireModel = $attributes->get('wire:model'); @endphp

<div>
    @if($label)
    <label for="{{ $id ?? '' }}" class="block text-sm font-medium leading-5 text-gray-700 dark:text-gray-300">
        {{ $label  }}
    </label>
    @endif

    <div data-model="{{ $wireModel }}" class="mt-1.5 rounded-md shadow-sm relative">
        @if($icon)
        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400 dark:text-gray-500">
            @svg($icon, 'w-5 h-5')
        </div>
        @endif
        <input {{ $attributes->whereStartsWith('wire:model') }} id="{{ $id ?? '' }}" name="{{ $name ?? '' }}" type="{{ $type ?? '' }}" placeholder="{{ $placeholder ?? '' }}" required autofocus class="appearance-none flex w-full h-10 px-3 py-2 text-sm bg-white dark:text-gray-300 dark:bg-white/[4%] border rounded-md border-gray-300 dark:border-white/10 ring-offset-background placeholder:text-gray-500 dark:placeholder:text-gray-400 focus:border-blue-300 dark:focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-200/60 dark:focus:ring-blue-800/40 disabled:cursor-not-allowed disabled:opacity-50 @if($icon) pl-10 @endif @error($wireModel) border-red-300 text-red-900 placeholder-red-300 focus:border-red-300 focus:ring-red @enderror" />
    </div>

    @error($wireModel)
    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>