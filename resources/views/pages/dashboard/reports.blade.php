<?php

use Livewire\Component;
use Mary\Traits\Toast;


use Livewire\Attributes\Layout;

new #[Layout('layouts.admin')] class extends Component {

    use Toast;
}
?>
<x-slot name="title">
    {{ 'Reports' }}
</x-slot>
<x-slot name="header">
    <h2 class="text-lg font-semibold leading-tight text-gray-800 dark:text-gray-200">
        {{ __('Reports') }}
    </h2>
</x-slot>
<div class="flex flex-col flex-1">
    <div class="flex flex-col  flex-1 pb-5 mx-auto  w-full">
        <div class="relative flex-1 w-full ">

            <div class="pb-5">
                <div class="mx-auto space-y-6">
                    <h3>No reports are found</h3>
                </div>
            </div>
        </div>
    </div>
</div>