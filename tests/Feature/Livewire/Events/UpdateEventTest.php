<?php

use App\Livewire\Events\UpdateEvent;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(UpdateEvent::class)
        ->assertStatus(200);
});
