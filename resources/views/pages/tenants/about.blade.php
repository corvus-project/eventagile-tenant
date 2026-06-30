<?php

use Livewire\Component;
use Livewire\Attributes\Layout;

new #[Layout('layouts.ea-yoga')]  class extends Component {}
?>
<x-slot name="title">
    {{ Setting::get('site_name') }} - Home
</x-slot>

<x-slot name="tenant_name">
    {{ Setting::get('site_name') }}
</x-slot>

<section class="py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Left Column: Event Details -->
        <div class="md:col-span-2 space-y-8">

            <!-- About This Class -->
            <div class="bg-white rounded-xl shadow-md p-8">
                {{ Setting::get('about') }}

            </div>
        </div>
    </div>
</section>