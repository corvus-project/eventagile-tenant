<?php

use Illuminate\Support\Facades\Log;
use Livewire\Component;

use Livewire\Attributes\Layout;


new #[Layout('layouts.empty')]  class extends Component
{

    public function mount() {}
}
?>
<x-slot name="title">
    No active site found
</x-slot>
<x-slot name="header">
    <h2 class="text-3xl font-semibold leading-tight text-gray-800 dark:text-gray-200">

    </h2>
</x-slot>
<div class="pb-5">
    <div class="mx-auto space-y-6">
        No active site found. Please check your tenant configuration or contact support for assistance.

    </div>

</div>