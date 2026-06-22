<?php

use App\Livewire\Events\CreateEvent;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(CreateEvent::class)
        ->assertStatus(200);
});
