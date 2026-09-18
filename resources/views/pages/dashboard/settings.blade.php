<?php

use App\Models\Setting;
use App\Services\Setting as ServicesSetting;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Support\Facades\Gate;

new #[Layout('layouts.admin')] class extends  Component {

    public array $settings;
    public function mount()
    {
        Gate::authorize('update-settings');

        $settings = Setting::all();

        foreach ($settings as $setting) {
            $this->settings[$setting->name] =  $setting->payload;
        }
    }

    public function save()
    {
        Gate::authorize('update-settings');

        $this->validate([
            'settings.*' => 'required|string|max:10080',
        ]);

        foreach ($this->settings as $key => $value) {
            Setting::where('name', $key)->update(['payload' => $value]);
        }

        session()->flash('message', 'Settings updated successfully!');
    }
};
?>

<x-slot name="title">
    {{ __('Settings ') }}
</x-slot>

<x-slot name="header">
    <h2 class="text-lg font-semibold leading-tight text-gray-800 dark:text-gray-200">
        {{ __('Settings') }}
    </h2>
</x-slot>


<div class="flex flex-col flex-1">
    <div class="flex flex-col  flex-1 pb-5 mx-auto  w-full">
        <div class="relative flex-1 w-full ">


            <section
                class="p-4 bg-white shadow sm:p-8 dark:bg-gray-800 sm:rounded-lg dark:bg-gray-900/50 dark:border dark:border-gray-200/10">
                <div class="w-full max-w-2xl mx-auto">
                    @if (session('message'))
                    <div class="alert alert-warning mb-4">
                        {{ session('message') }}
                    </div>
                    @endif

                    <x-form wire:submit="save" class="mt-6 space-y-6">

                        @foreach($settings as $key => $value)
                        @if(strlen($value)
                        < 240)
                            <x-input label="{{ Str::ucfirst(Str::replace('_', ' ', $key)) }} " wire:model="settings.{{ $key }}" />
                        @else
                        <x-textarea label="{{ Str::ucfirst(Str::replace('_', ' ', $key)) }} " name="{{$key}}" wire:model="settings.{{ $key }}" rows="5">{{ $settings[$key]}}</x-textarea>
                        @endif
                        @endforeach

                        <x-slot:actions>
                            <x-button label="Update" class="btn-seconday" type="primary" submit="true" spinner="save" />
                        </x-slot:actions>
                    </x-form>

                </div>
            </section>

        </div>
    </div>

</div>